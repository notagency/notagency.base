<?php

/**
 * Базовый класс компонентов
 * @link https://bitbucket.org/notagency/notagency.base
 * @author Dmitry Savchenkov <ds@notagency.ru>
 * @copyright Copyright © 2016 NotAgency
 */

namespace Notagency\Base;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class ComponentsBase extends \CBitrixComponent
{
    /**
     * @var array symbolic codes of modules which are required by component
     */
    protected $needModules = [];

    /**
     * @var array additional cache IDs
     */
    private $cacheAdditionalId = [];

    /**
     * @var string cache dir
     */
    protected $cacheDir = false;

    /**
     * @var bool Should we cache component's template? Default: true
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
     * Includes required modules
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
     * Start cache
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
        if ($this->checkParams) {
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
     * Restarts buffer if the ajax-request
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
     * Writes cache
     */
    public function writeCache()
    {
        $this->endResultCache();
    }

    /**
     * Resets cache
     */
    public function abortCache()
    {
        $this->abortResultCache();
    }

    /**
     * Executed before main code. To be extended by child class.
     * Result WILL NOT be cached.
     *
     * For example, one can put here some code to handle GET or POST requests and turn off component's cache.
     * Exceptions could be thrown here.
     */
    protected function executeProlog() {}
    
    /**
     * Executes main component's code. To be extended by child class. 
     * Result WILL be cached
     */
    protected function executeMain() {}

    /**
     * Executed after main code. To be extended by child class.
     * Result WILL NOT be cached.
     *
     * Here bitrix deffered functions like $APPLICATION->SetTitle() and etc could be called.
     */
    protected function executeEpilog() {}

    /**
     * Stop to execute script if it is the ajax-request
     */
    private function stopAjax()
    {
        if ($this->isAjax() && $this->arParams['USE_AJAX'] === 'Y') {
            exit;
        }
    }

    /**
     * Shows result. Default: includes component's template
     * @uses $this->templatePage
     */
    public function showResult()
    {
        $this->includeComponentTemplate($this->templatePage);
    }

    /**
     * Sets 404 http-status
     */
    public function return404()
    {
        @define('ERROR_404', 'Y');
        \CHTTP::SetStatus('404 Not Found');
    }

    /**
     * Called when an error occurs.
     * Resets cache, show error message (two modes: for users and for admins).
     *
     * @param \Exception $exception
     */
    protected function catchException(\Exception $exception)
    {
        $this->abortCache();
        if ($GLOBALS['USER']->IsAdmin()) {
            $this->showExceptionAdmin($exception);
        } else {
            $this->showExceptionUser($exception);
        }
    }

    /**
     * Shows error for user
     *
     * @param \Exception $exception
     */
    protected function showExceptionUser(\Exception $exception)
    {
        ShowError(Loc::getMessage('NIK_COMPONENT_ERROR_OCCURED'));
    }

    /**
     * Shows error for admin
     *
     * @param \Exception $exception
     */
    protected function showExceptionAdmin(\Exception $exception)
    {
        ShowError($exception->getMessage());
        ShowError(nl2br($exception));
    }

    /**
     * Checks whether it is the ajax-request
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
     * Register cache tag
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
        } else {
            return \CIBlockFormatProperties::DateFormat($format, MakeTimeStamp($date, \CSite::GetDateFormat()));
        }
    }

}

?>