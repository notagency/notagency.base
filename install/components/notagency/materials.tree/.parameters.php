<?
if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = CComponentUtil::GetComponentProps('notagency:materials.list', $arCurrentValues);

$arComponentParameters['PARAMETERS']['SELECT_SECTIONS'] = array(
    'PARENT' => 'BASE',
    'NAME' => GetMessage('NIK_ELEMENTS_LIST_SELECT_SECTIONS'),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'Y',
    'REFRESH' => 'Y',
);