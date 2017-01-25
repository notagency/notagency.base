<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('iblock'))
    return;

if (!empty($arCurrentValues['IBLOCK_CODE'])) {
    //select iblock's element's fields and properties
    $filter = [
        'CODE' => $arCurrentValues['IBLOCK_CODE']
    ];
    if ($iblock = CIBlock::GetList([], $filter)->fetch()) {
        //select fields
        $fields = [];
        $rawFields = CIBlock::GetFields($iblock['ID']);
        foreach ($rawFields as $fieldCode => $field) {
            $fields[$fieldCode] = $field['NAME'];
        }

        //element properties
        $elementProperties = [];
        $filter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblock['ID'],
        ];
        $rsProp = CIBlockProperty::GetList([], $filter);
        while ($item = $rsProp->Fetch()) {
            $elementProperties[$item['CODE']] = '[' . $item['CODE'] . '] ' . $item['NAME'];
        }
    }
}

$arComponentParameters = CComponentUtil::GetComponentProps('notagency:materials.list', $arCurrentValues);

$arComponentParameters['PARAMETERS']['SELECT_ELEMENT_BY'] = array(
    'PARENT' => 'BASE',
    'NAME' => 'Выбирать элемент по ID или по коду',
    'TYPE' => 'LIST',
    'VALUES' => [
        'CODE' => 'по коду',
        'ID' => 'по id',
    ],
    'DEFAULT' => 'CODE',
    'REFRESH' => 'Y',
);

if ($arCurrentValues['SELECT_ELEMENT_BY'] == 'ID') {
    $arComponentParameters['PARAMETERS']['REQUEST_ELEMENT_ID'] = array(
        'PARENT' => 'BASE',
        'NAME' => 'GET или POST переменная, в которой передается id элемента',
        'TYPE' => 'STRING',
        'DEFAULT' => 'element_id',
    );
} else if ($arCurrentValues['SELECT_ELEMENT_BY'] == 'CODE') {
    $arComponentParameters['PARAMETERS']['REQUEST_ELEMENT_CODE'] = array(
        'PARENT' => 'BASE',
        'NAME' => 'GET или POST переменная, в которой передается код элемента',
        'TYPE' => 'STRING',
        'DEFAULT' => 'element_code',
    );
}

$arComponentParameters['PARAMETERS']['INCLUDE_SECTIONS_NAMES_INTO_CHAIN'] = array(
    'PARENT' => 'ADDITIONAL_SETTINGS',
    'NAME' => 'Добавлять названия разделов в навигационную цепочку',
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'Y',
);

$arComponentParameters['PARAMETERS']['INCLUDE_INTO_CHAIN'] = array(
    'PARENT' => 'ADDITIONAL_SETTINGS',
    'NAME' => 'Добавить поля или свойства элемента инфоблока в навигационную цепочку',
    'TYPE' => 'LIST',
    'VALUES' => [
        '' => 'Нет',
        'FIELD' => 'Поля',
        'PROPERTY' => 'Свойства',
    ],
    'REFRESH' => 'Y',
);

if ($arCurrentValues['INCLUDE_INTO_CHAIN'] == 'FIELD') {
    $arComponentParameters['PARAMETERS']['INCLUDE_FIELD_INTO_CHAIN'] = array(
        'PARENT' => 'ADDITIONAL_SETTINGS',
        'NAME' => 'Добавить поля в навигационную цепочку (разделить точкой, если несколько)',
        'TYPE' => 'LIST',
        'VALUES' => [
            'NAME' => $fields['NAME'],
        ],
        'ADDITIONAL_VALUES' => 'Y',
    );
} else if ($arCurrentValues['INCLUDE_INTO_CHAIN'] == 'PROPERTY') {
    $arComponentParameters['PARAMETERS']['INCLUDE_PROPERTY_INTO_CHAIN'] = array(
        'PARENT' => 'ADDITIONAL_SETTINGS',
        'NAME' => 'Добавить свойства в навигационную цепочку (разделить точкой, если несколько)',
        'TYPE' => 'LIST',
        'VALUES' => $elementProperties,
        'ADDITIONAL_VALUES' => 'Y',
    );
}

$arComponentParameters['PARAMETERS']['SET_TITLE_FROM'] = array(
    'PARENT' => 'ADDITIONAL_SETTINGS',
    'NAME' => 'Установить заголовок из названия или свойства',
    'TYPE' => 'LIST',
    'VALUES' => [
        '' => 'Нет',
        'NAME' => 'Из названия',
        'PROPERTY' => 'Из значения свойства',
    ],
    'REFRESH' => 'Y',
);
if ($arCurrentValues['SET_TITLE_FROM'] == 'PROPERTY') {
    $arComponentParameters['PARAMETERS']['SET_TITLE_FROM_PROPERTY'] = array(
        'PARENT' => 'ADDITIONAL_SETTINGS',
        'NAME' => 'Установить заголовок из значения свойства',
        'TYPE' => 'LIST',
        'VALUES' => $elementProperties,
        'ADDITIONAL_VALUES' => 'Y',
    );
}

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);

unset($arComponentParameters['PARAMETERS']['ELEMENTS_COUNT']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_BY1']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_BY2']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_BY3']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_ORDER1']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_ORDER2']);
unset($arComponentParameters['PARAMETERS']['ELEMENT_SORT_ORDER3']);
unset($arComponentParameters['PARAMETERS']['SECTION_SORT_BY1']);
unset($arComponentParameters['PARAMETERS']['SECTION_SORT_BY2']);
unset($arComponentParameters['PARAMETERS']['SECTION_SORT_ORDER1']);
unset($arComponentParameters['PARAMETERS']['SECTION_SORT_ORDER2']);
unset($arComponentParameters['PARAMETERS']['SELECT_BY_SECTION']);
unset($arComponentParameters['PARAMETERS']['SECTION_ID']);
unset($arComponentParameters['PARAMETERS']['SECTION_CODE']);
unset($arComponentParameters['PARAMETERS']['SELECT_SECTIONS']);
