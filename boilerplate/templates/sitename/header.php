<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Page\Asset,
    Bitrix\Main\Config\Option;

$APPLICATION->SetPageProperty('OG_URL', 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);

?><!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang=""><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang=""><![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru">
<!--<![endif]-->
<head><?

    ?><meta charset="utf-8"><?
    ?><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><?
    ?><title><?$APPLICATION->ShowTitle()?></title><?
    ?><meta name="description" content=""><?
    ?><meta id="viewport" name="viewport" width="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no"><?
    
    ?><meta property="og:title" content="<?$APPLICATION->ShowProperty('OG_TITLE')?>" />
    <meta property="og:description" content="<?$APPLICATION->ShowProperty('OG_DESCRIPTION')?>" />
    <meta property="og:url" content="<?$APPLICATION->ShowProperty('OG_URL')?>" />
    <meta property="og:image" content="<?$APPLICATION->ShowProperty('OG_IMAGE')?>" />
    <meta property="og:type" content="website" />
    <meta property="fb:app_id" content="<?=Option::get('socialservices', 'facebook_appid')?>" /><?
    
    $APPLICATION->ShowHead();
    
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/css/styles.css');    
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/scripts.js');
    
?></head><?
?><body><?

    $APPLICATION->ShowPanel();
    ?>
    <header>
        <?$APPLICATION->IncludeComponent(
            "bitrix:menu", 
            "top", 
            array(
                "ALLOW_MULTI_SELECT" => "N",
                "CHILD_MENU_TYPE" => "top",
                "COMPONENT_TEMPLATE" => "top",
                "DELAY" => "N",
                "MAX_LEVEL" => "1",
                "MENU_CACHE_GET_VARS" => array(
                ),
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_TYPE" => "A",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "ROOT_MENU_TYPE" => "top",
                "USE_EXT" => "N",
            ),
            false
        );?>
    </header>
    <?
    
    if (
        $APPLICATION->GetProperty('layout') && 
        file_exists($_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/layouts/' . $APPLICATION->GetProperty('layout') . '/header.php')
    )
    {
        require 'layouts/' . $APPLICATION->GetProperty('layout') . '/header.php';
    }
    else
    {
        require 'layouts/default/header.php';
    }
    ?>