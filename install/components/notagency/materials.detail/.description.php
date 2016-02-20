<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    'NAME' => basename(__DIR__),
    'DESCRIPTION' => '',
    'ICON' => '/images/news_detail.gif',
    'SORT' => 20,
    'CACHE_PATH' => 'Y',
    'PATH' => array(
      	'ID' => basename(dirname(__DIR__)),
    ),
);

?>