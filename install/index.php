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
        CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        return true;
    }
    
    public function UnInstallFiles()
    {
        //** Delete components carefully */
        if ($handle = @opendir(__DIR__ . '/components/notagency'))
        {
            while (($entity = readdir($handle)) !== false)
            {
                if ($entity == "." || $entity == "..")
                    continue;
                
                if (
                    Directory::isDirectoryExists(__DIR__ . '/components/notagency/' . $entity) 
                    && Directory::isDirectoryExists($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/' . $entity)
                )
                {
                    Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/' . $entity);
                }
            }
            @rmdir($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency/');
        }
        return true;
    }
}