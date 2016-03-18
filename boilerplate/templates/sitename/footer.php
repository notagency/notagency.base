<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

    if (
        $APPLICATION->GetProperty('layout') && 
        file_exists($_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/layouts/' . $APPLICATION->GetProperty('layout') . '/footer.php')
    )
    {
        require 'layouts/' . $APPLICATION->GetProperty('layout') . '/footer.php';
    }
    else
    {
        require 'layouts/default/footer.php';
    }
    ?>
    <footer>
        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
            "AREA_FILE_RECURSIVE" => "Y",
                "AREA_FILE_SHOW" => "sect",
                "AREA_FILE_SUFFIX" => "sidebar_inc",
                "COMPONENT_TEMPLATE" => ".default",
                "EDIT_TEMPLATE" => ".default"
            ),
            false,
            array(
            "ACTIVE_COMPONENT" => "Y"
            )
        );?>
        <?$APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            Array(
                "AREA_FILE_SHOW" => "file",
                "AREA_FILE_SUFFIX" => "copyright",
                "COMPONENT_TEMPLATE" => ".default",
                "EDIT_TEMPLATE" => "",
                "PATH" => SITE_TEMPLATE_PATH . "/include_area/copyright.php"
            )
        );?>
        <?$APPLICATION->IncludeFile('include_area/copyright.php');?>
    </footer>
</body>
</html>