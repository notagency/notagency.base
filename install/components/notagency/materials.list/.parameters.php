<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

$arSorts = [
    'ASC'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_ASC'), 
    'DESC'=>GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_DESC')
];

if(!CModule::IncludeModule('iblock'))
    return;

$iblocks = [];
$currentIblockId = false;
$fields = [];
$sectionProperties = [];
$elementProperties = [];


//select iblock types
$iblockTypes = CIBlockParameters::GetIBlockTypes(array('-'=>' '));

if ($arCurrentValues['IBLOCK_TYPE'] != '-')
{
	//select iblocks
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
        if ($item['CODE'] == $arCurrentValues['IBLOCK_CODE'])
        {
            $currentIblockId = $item['ID'];
        }
		$iblocks[$item['CODE']] = '['.$item['CODE'].'] '.$item['NAME'];
	}
}
if ($currentIblockId)
{
    //select section properties
    $filter = [
        'ENTITY_ID' => 'IBLOCK_' . $currentIblockId . '_SECTION',
    ];
    $rs = CUserTypeEntity::GetList([], $filter);
    while($field = $rs->Fetch())
    {
        $sectionProperties[$field['FIELD_NAME']] = $field['FIELD_NAME'];
    }

    //select element properties
    $filter = [
        'ACTIVE'=>'Y', 
        'IBLOCK_ID'=> $currentIblockId,
    ];
    $rsProp = CIBlockProperty::GetList([], $filter);
    while ($item = $rsProp->Fetch())
    {
        $elementProperties[$item['CODE']] = '['.$item['CODE'].'] '.$item['NAME'];
    }
}

$arComponentParameters = array(
    'GROUPS' => array(
        'ELEMENTS_SORTING' => array(
            'NAME' => 'Сортировка элементов',
            'SORT' => 150,
        ),
        'SECTION_SORTING' => array(
            'NAME' => 'Сортировка разделов',
            'SORT' => 160,
        ),
    ),
	'PARAMETERS' => array(
		'IBLOCK_TYPE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_LIST_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $iblockTypes,
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
        'SELECT_SECTIONS' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('NIK_ELEMENTS_LIST_SELECT_SECTIONS'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
            'REFRESH' => 'Y',
        ),
		'ELEMENT_SORT_BY1' => array(
			'PARENT' => 'ELEMENTS_SORTING',
			'NAME' => 'Поле для 1-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'ACTIVE_FROM',
			'VALUES' => CIBlockParameters::GetElementSortFields(),
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_ORDER1' => array(
			'PARENT' => 'ELEMENTS_SORTING',
			'NAME' => 'Направление 1-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_BY2' => array(
			'PARENT' => 'ELEMENTS_SORTING',
            'NAME' => 'Поле для 2-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'SORT',
            'VALUES' => CIBlockParameters::GetElementSortFields(),
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_ORDER2' => array(
			'PARENT' => 'ELEMENTS_SORTING',
            'NAME' => 'Направление 2-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'ASC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
        'ELEMENT_SORT_BY3' => array(
            'PARENT' => 'ELEMENTS_SORTING',
            'NAME' => 'Поле для 3-ей сортировки',
            'TYPE' => 'LIST',
            'DEFAULT' => 'SORT',
            'VALUES' => CIBlockParameters::GetElementSortFields(),
            'ADDITIONAL_VALUES' => 'Y',
        ),
        'ELEMENT_SORT_ORDER3' => array(
            'PARENT' => 'ELEMENTS_SORTING',
            'NAME' => 'Направление 3-ей сортировки',
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
            'SIZE' => 8,
            'VALUES' => [
                'NAME' => 'Название',
                'CODE' => 'Символьный код',
                'DETAIL_PAGE_URL' => 'Ссылка на детальную страницу',
                'ACTIVE_FROM' => 'Начало активности',
                'ACTIVE_TO' => 'Окончание активности',
                'PREVIEW_TEXT' => 'Описание для анонса',
                'PREVIEW_PICTURE' => 'Картинка для анонса',
                'DETAIL_TEXT' => 'Детальное для анонса',
                'DETAIL_PICTURE' => 'Детальная картинка',
                'IBLOCK_SECTION_ID' => 'ID раздела',
                'TAGS' => 'Теги',
                'SORT' => 'Индекс сортировки',
            ],
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
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => 'Название переменной в которой содержится массив фильтрации элементов инфоблока',
			'TYPE' => 'STRING',
			'DEFAULT' => '',
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

$arComponentParameters['PARAMETERS']['SELECT_BY_SECTION'] = array(
    'PARENT' => 'BASE',
    'NAME' => 'Выбирать элементы по разделу',
    'TYPE' => 'LIST',
    'VALUES' => [
        'NO' => 'нет',
        'CODE' => 'по коду раздела',
        'ID' => 'по id раздела',
    ],
    'DEFAULT' => 'NO',
    'REFRESH' => 'Y',
);

if ($arCurrentValues['SELECT_BY_SECTION'] == 'ID')
{
    $arComponentParameters['PARAMETERS']['SECTION_ID'] = array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('IBLOCK_SECTION_ID'),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
    );
}
else if ($arCurrentValues['SELECT_BY_SECTION'] == 'CODE')
{
    $arComponentParameters['PARAMETERS']['SECTION_CODE'] = array(
        'PARENT' => 'BASE',
        'NAME' => GetMessage('IBLOCK_SECTION_CODE'),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
    );
}
if (in_array($arCurrentValues['SELECT_BY_SECTION'], ['ID', 'CODE']))
{
    $arComponentParameters['PARAMETERS']['INCLUDE_SUBSECTIONS'] = array(
        'PARENT' => 'BASE',
        'NAME' => 'Выбирать элементы из всех подразделов раздела',
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
    );
}

if ($arCurrentValues['SELECT_SECTIONS'] == 'Y')
{
    $arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], array(
        'SECTION_SORT_BY1' => array(
			'PARENT' => 'SECTION_SORTING',
			'NAME' => 'Поле для 1-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'SORT',
			'VALUES' => CIBlockParameters::GetSectionSortFields(),
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_ORDER1' => array(
			'PARENT' => 'SECTION_SORTING',
			'NAME' => 'Направление 1-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_BY2' => array(
			'PARENT' => 'SECTION_SORTING',
			'NAME' => 'Поле для 2-ой сортировки',
			'TYPE' => 'LIST',
			'DEFAULT' => 'ID',
            'VALUES' => CIBlockParameters::GetSectionSortFields(),
			'ADDITIONAL_VALUES' => 'Y',
		),
		'SECTION_SORT_ORDER2' => array(
			'PARENT' => 'SECTION_SORTING',
			'NAME' => 'Направление 2-ой сортировки',
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
    ));
}

if ($arCurrentValues['CUSTOM_DATE_FORMAT'] == 'Y')
{
    $arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], array(
        'ACTIVE_DATE_FORMAT' => CIBlockParameters::GetDateFormat(GetMessage('NOTAGENCY_MATERIALS_LIST_COMPONENT_IBLOCK_DESC_ACTIVE_DATE_FORMAT'), 'ADDITIONAL_SETTINGS'),
    ));
}