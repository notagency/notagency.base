<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('form')) return;

$forms = [];
$rs = CForm::GetList($by = 's_sort', $order = 'asc', array('SITE' => $_REQUEST['site']), $v3);
while ($form = $rs->Fetch()) {
    $forms[$form['SID']] = '[' . $form['SID'] . '] ' . $form['NAME'];
}

$arComponentParameters = array(
    'GROUPS' => array(
        'FORM_PARAMS' => array(
            'NAME' => GetMessage('COMP_FORM_GROUP_PARAMS')
        ),
    ),

    'PARAMETERS' => array(

        'WEB_FORM_CODE' => array(
            'NAME' => 'Код веб-формы',
            'TYPE' => 'LIST',
            'VALUES' => $forms,
            'ADDITIONAL_VALUES' => 'Y',
            'DEFAULT' => '',
            'PARENT' => 'DATA_SOURCE',
        ),

        'CACHE_TIME' => array('DEFAULT' => '3600'),
    ),
);
?>