<?

/**
 * @link https://bitbucket.org/notagency/notagency.base
 * @copyright Copyright © 2016 NotAgency
 */

namespace Notagency\Base;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class ComponentsBase extends \CBitrixComponent
{
    /**
     * @var array The codes of modules that will be included before executing component
     */
    protected $needModules = array();

    /**
     * @var array Additional cache ID
     */
    private $cacheAdditionalId;

    /**
     * @var string Cache dir
     */
    protected $cacheDir = false;

    /**
     * @var bool Caching template of the component (default not cache)
     */
    protected $cacheTemplate = true;

    /**
     * @var string Salt for component ID for AJAX request
     */
    protected $ajaxComponentIdSalt;

    /**
     * @var string Template page name
     */
    protected $templatePage;

    final protected function executeBase()
    {
        $this->includeModules();
        $this->checkParams();
        $this->startAjax();
        $this->executeProlog();

        if ($this->startCache()) {
            $this->executeMain();

            if ($this->cacheTemplate) {
                $this->showResult();
            }

            $this->writeCache();
        }

        if (!$this->cacheTemplate) {
            $this->showResult();
        }
        
        $this->executeEpilog();
        $this->stopAjax();
    }

    public function executeComponent()
    {
        try {
            $this->executeBase();
        } catch (\Exception $e) {
            $this->catchException($e);
        }
        return $this->arResult;
    }

    /**
     * Include modules
     * @uses $this->needModules
     * @throws \Bitrix\Main\LoaderException
     */
    public function includeModules()
    {
        if (empty($this->needModules)) {
            return false;
        }

        foreach ($this->needModules as $module) {
            if (!Main\Loader::includeModule($module)) {
                throw new Main\LoaderException(Loc::getMessage('NIK_FAILED_INCLUDE_MODULE', ['#MODULE#' => $module]));
            }
        }
    }

    /**
     * Cache init
     * @return bool
     */
    public function startCache()
    {
        global $USER;

        if ($this->arParams['CACHE_TYPE'] && $this->arParams['CACHE_TYPE'] !== 'N' && $this->arParams['CACHE_TIME'] > 0) {
            if ($this->templatePage) {
                $this->cacheAdditionalId[] = $this->templatePage;
            }

            if ($this->arParams['CACHE_GROUPS'] === 'Y') {
                $this->cacheAdditionalId[] = $USER->GetGroups();
            }

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->cacheAdditionalId, $this->cacheDir)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     */
    private function checkParams()
    {
        if ($this->checkParams)
        {
            foreach ($this->checkParams as $key => $params) {
                $exception = false;

                switch ($params['type']) {
                    case 'int':

                        if (!is_numeric($this->arParams[$key]) && $params['error'] !== false) {
                            $exception = new Main\ArgumentTypeException($key, 'integer');
                        } else {
                            $this->arParams[$key] = intval($this->arParams[$key]);
                        }

                        break;

                    case 'string':

                        $this->arParams[$key] = htmlspecialchars(trim($this->arParams[$key]));

                        if (strlen($this->arParams[$key]) <= 0 && $params['error'] !== false) {
                            $exception = new Main\ArgumentNullException($key);
                        }

                        break;

                    case 'array':

                        if (!is_array($this->arParams[$key])) {
                            if ($params['error'] === false) {
                                $this->arParams[$key] = array($this->arParams[$key]);
                            } else {
                                $exception = new Main\ArgumentTypeException($key, 'array');
                            }
                        }

                        break;

                    default:
                        $exception = new Main\NotSupportedException('Not supported type of parameter for automatical checking');
                        break;
                }
            }
        }
    }

    /**
     * Restart buffer if AJAX request
     */
    private function startAjax()
    {
        if ($this->arParams['USE_AJAX'] !== 'Y') {
            return false;
        }

        if (strlen($this->arParams['AJAX_PARAM_NAME']) <= 0) {
            $this->arParams['AJAX_PARAM_NAME'] = 'compid';
        }

        if (strlen($this->arParams['AJAX_COMPONENT_ID']) <= 0) {
            $this->arParams['AJAX_COMPONENT_ID'] = \CAjax::GetComponentID($this->getName(), $this->getTemplateName(), $this->ajaxComponentIdSalt);
        }

        if ($this->isAjax()) {
            global $APPLICATION;

            if ($this->arParams['AJAX_HEAD_RELOAD'] === 'Y') {
                $APPLICATION->ShowAjaxHead();
            } else {
                $APPLICATION->RestartBuffer();
            }

            if ($this->arParams['AJAX_TYPE'] === 'JSON') {
                header('Content-Type: application/json');
            }

            if (strlen($this->arParams['AJAX_TEMPLATE_PAGE']) > 0) {
                $this->templatePage = basename($this->arParams['AJAX_TEMPLATE_PAGE']);
            }
        }
    }

    /**
     * Write cache to disk
     */
    public function writeCache()
    {
        $this->endResultCache();
    }

    /**
     * Resets the cache
     */
    public function abortCache()
    {
        $this->abortResultCache();
    }

    /**
     * Execute before getting results. Not cached
     */
    protected function executeProlog() {}
    
    /**
     * A method for extending the results of the child classes.
     * The result this method will be cached
     */
    protected function executeMain()
    {
    }

    /**
     * Execute after getting results. Not cached
     */
    protected function executeEpilog() {}

    /**
     * Stop execute script if AJAX request
     */
    private function stopAjax()
    {
        if ($this->isAjax() && $this->arParams['USE_AJAX'] === 'Y') {
            exit;
        }
    }

    /**
     * Show results. Default: include template of the component
     * @uses $this->templatePage
     */
    public function showResult()
    {
        $this->includeComponentTemplate($this->templatePage);
    }

    /**
     * Set status 404 and throw exception
     * @param bool $notifier Sent notify to admin email
     * @param \Exception|null|false $exception Exception which will be throwing or "false" what not throwing exceptions. Default — throw new \Exception()
     * @throws \Exception
     */
    public function return404()
    {
        @define('ERROR_404', 'Y');
        \CHTTP::SetStatus('404 Not Found');
    }

    /**
     * Called when an error occurs
     *
     * Resets the cache, show error message (two mode: for users and for admins),
     * sending notification to admin email
     *
     * @param \Exception $exception
     */
    protected function catchException(\Exception $exception)
    {
        $this->abortCache();
        if ($GLOBALS['USER']->IsAdmin())
            $this->showExceptionAdmin($exception);
        else
            $this->showExceptionUser($exception);
    }

    /**
     * Display of the error for user
     *
     * @param \Exception $exception
     */
    protected function showExceptionUser(\Exception $exception)
    {
        ShowError(Loc::getMessage('NIK_COMPONENT_ERROR_OCCURED'));
    }

    /**
     * Display of the error for admin
     *
     * @param \Exception $exception
     */
    protected function showExceptionAdmin(\Exception $exception)
    {
        ShowError($exception->getMessage());
        ShowError(nl2br($exception));
    }

    /**
     * Is AJAX request
     *
     * @return bool
     */
    public function isAjax()
    {
        if (
            strlen($this->arParams['AJAX_COMPONENT_ID']) > 0
            && strlen($this->arParams['AJAX_PARAM_NAME']) > 0
            && $_REQUEST[$this->arParams['AJAX_PARAM_NAME']] === $this->arParams['AJAX_COMPONENT_ID']
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Register tag in cache
     *
     * @param string $tag Tag
     */
    public static function registerCacheTag($tag)
    {
        if ($tag) {
            Application::getInstance()->getTaggedCache()->registerTag($tag);
        }
    }

    /**
     * Add additional ID to cache
     *
     * @param mixed $id
     */
    public function addCacheAdditionalId($id)
    {
        $this->cacheAdditionalId[] = $id;
    }

    public static function formatDisplayDate($date, $format)
    {
        if (empty($date)) {
            return '';
        }
        return \CIBlockFormatProperties::DateFormat($format, MakeTimeStamp($date, \CSite::GetDateFormat()));
    }

}