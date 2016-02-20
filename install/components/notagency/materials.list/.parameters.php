<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

$arSorts = [
    'ASC'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_ASC'), 
    'DESC'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_DESC')
];
$arSortFields = [
    'ID'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FID'),
    'NAME'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FNAME'),
    'DATE_ACTIVE_FROM'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FACT'),
    'SORT'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FSORT'),
];

$arSortSectionFields = [
    'ID'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FID'),
    'NAME'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FNAME'),
    'SORT'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_FSORT'),
];

if(!CModule::IncludeModule('iblock'))
    return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(array('-'=>' '));

//select iblocks
$iblocks = [];
$iblocksIds = [];
$order = [
    'SORT' => 'ASC',
    'NAME' => 'ASC', 
];
$filter = [
    'TYPE' => $arCurrentValues['IBLOCK_TYPE'] != '-' ? $arCurrentValues['IBLOCK_TYPE'] : '',
];
$rs = CIBlock::GetList($order, $filter);
while ($item = $rs->Fetch())
{
    $iblocksIds[$item['CODE']] = $item['ID'];
    $iblocks[$item['CODE']] = '['.$item['CODE'].'] '.$item['NAME'];
}

//select iblock's element's fields and properties
$iblockId = !empty($iblocksIds[$arCurrentValues['IBLOCK_CODE']]) ? $iblocksIds[$arCurrentValues['IBLOCK_CODE']] : false;

if ($iblockId)
{
    //select fields
    $fields = [];
    $rawFields = CIBlock::GetFields($iblockId);
    foreach ($rawFields as $fieldCode=>$field)
    {
        $fields[$fieldCode] = $field['NAME'];
    }
    
    //select section properties
    $sectionProperties = [];
    $filter = [
        'ENTITY_ID' => 'IBLOCK_' . $iblockId . '_SECTION',
    ];
    $rs = CUserTypeEntity::GetList([], $filter );
    while($field = $rs->Fetch())
    {
        $sectionProperties[$field['FIELD_NAME']] = $field['FIELD_NAME'];
    }

    //select element properties
    $elementProperties = [];
    $filter = [
        'ACTIVE'=>'Y', 
        'IBLOCK_ID'=> $iblockId,
    ];
    $rsProp = CIBlockProperty::GetList([], $filter);
    while ($item = $rsProp->Fetch())
    {
        $elementProperties[$item['CODE']] = '['.$item['CODE'].'] '.$item['NAME'];
    }
}

$arComponentParameters = array(

	'PARAMETERS' => array(
		'IBLOCK_TYPE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_LIST_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arTypesEx,
			'DEFAULT' => 'nik',
			'REFRESH' => 'Y',
		),
		'IBLOCK_CODE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_NEWS_LIST_CODE'),
			'TYPE' => 'LIST',
			'VALUES' => $iblocks,
			'REFRESH' => 'Y',

		),
		'ELEMENTS_COUNT' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_LIST_CONT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '20',
		),
		'ELEMENT_SORT_BY1' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBORD1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DATE_ACTIVE_FROM',
			'VALUES' => $arSortFields,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_ORDER1' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBBY1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_BY2' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBORD2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'SORT',
			'VALUES' => $arSortFields,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_ORDER2' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBBY2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'ASC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
        'ELEMENT_FIELDS' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => 'Поля элементов',
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $fields,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_PROPERTIES' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => 'Свойства элементов',
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $elementProperties,
			'ADDITIONAL_VALUES' => 'Y',
		),
        'FILTER_NAME' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => 'Название переменной в которой содержится массив фильтрации элементов инфоблока',
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
        'SELECT_SECTIONS' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NIK_ELEMENTS_LIST_SELECT_SECTIONS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
            'REFRESH' => 'Y',
		),
        'CUSTOM_DATE_FORMAT' => array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => 'Особый формат даты',
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
            'REFRESH' => 'Y',
		),
        'SHOW_PANEL_BUTTONS' => array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('NIK_ELEMENTS_LIST_SHOW_PANEL_BUTTONS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
            'REFRESH' => 'Y',
		),
		'CACHE_TIME'  =>  array('DEFAULT'=>36000000),
		'CACHE_GROUPS' => array(
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_BNL_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
	),
);

if ($arCurrentValues['SELECT_SECTIONS'] == 'Y')
{
    $arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], array(
		'SECTION_CODE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_SECTION_CODE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
        'SECTION_ID' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_SECTION_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
        'SECTION_SORT_BY1' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBSORD1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DATE_ACTIVE_FROM',
			'VALUES' => $arSortSectionFields,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_ORDER1' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBSBY1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_BY2' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBSORD2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'SORT',
			'VALUES' => $arSortSectionFields,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_ORDER2' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_IBSBY2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'ASC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
        'SECTION_PROPERTIES' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => 'Свойства разделов',
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $sectionProperties,
			'ADDITIONAL_VALUES' => 'Y',
		),
        'INCLUDE_SUBSECTIONS' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => 'Выбирать элементы из всех подразделов раздела',
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
    ));
}

if ($arCurrentValues['CUSTOM_DATE_FORMAT'] == 'Y')
{
    $arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], array(
        'ACTIVE_DATE_FORMAT' => CIBlockParameters::GetDateFormat(GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_ACTIVE_DATE_FORMAT'), 'ADDITIONAL_SETTINGS'),
    ));
}