<?php
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Loader,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\IO\Directory;

class notagency_base extends CModule
{
    var $MODULE_ID = 'notagency.base';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME = '★ NotAgency - базовый модуль';
    var $MODULE_DESCRIPTION = '';
    var $PARTNER_NAME='NotAgency';
    var $PARTNER_URI='http://notagency.ru/';

    function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
    }

    public function DoUninstall()
    {
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
    
    public function InstallFiles()
    {
        //проверяем создана ли директория в которую будем ставить компоненты
        CheckDirPath($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/notagency/');
        //открываем директорию
        if ($handle = @opendir(__DIR__ . '/components/notagency'))
        {
            //читаем директорию
            while (($entity = readdir($handle)) !== false)
            {
                if ($entity == "." || $entity == "..")
                    continue;

                //создаем симлинк для того чтобы обносления после composer update сразу же применялись к компоненту
                $this->_symlink (__DIR__ . '/components/notagency/' . $entity, $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/' . $entity);
            }
        }
        return true;
    }
    
    public function UnInstallFiles()
    {
        //удаляем только те симлинки на компоненты, которые есть в данном модуле
        if ($handle = @opendir(__DIR__ . '/components/notagency'))
        {
            //читаем директорию
            while (($entity = readdir($handle)) !== false)
            {
                if ($entity == "." || $entity == "..")
                    continue;
                
                if (
                    Directory::isDirectoryExists(__DIR__ . '/components/notagency/' . $entity) 
                    && Directory::isDirectoryExists($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/' . $entity)
                )
                {
                    //удаляем симлинк
                    $this->_unlink($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/' . $entity);
                }
            }
            @rmdir($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/');
        }
        return true;
    }
    
    /**
    * A function to emulate symbolic links on Windows.
    * Uses the junction utility available at:
    * http://www.sysinternals.com
    * Note that this will only work on NTFS volumes.
    *
    * The syntax of the junction utility is:
    * junction <junction directory> <junction target>
    *
    * Note that the parameter order of the Junction command
    * is the reverse of the symlink function!
    *
    * @link http://php.net/manual/ru/function.symlink.php#70927
    *
    * @param string $target
    * @param string $link
    */
    private function _symlink($target, $link) {
        if ($_SERVER['WINDIR'] || $_SERVER['windir'])
        {
            exec('junction "' . $link . '" "' . $target . '"');
        }
        else
        {
            symlink($target, $link);
        }
    }

    private function _unlink($link) {
        if ($_SERVER['WINDIR'] || $_SERVER['windir'])
        {
            exec('junction -d "' . $link . '"');
        }
        else
        {
            @unlink($link);
        }
    }
}