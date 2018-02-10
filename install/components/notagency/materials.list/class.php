<?php

/**
* Типовой список
* @link https://bitbucket.org/notagency/notagency.base
* @author Dmitry Savchenkov <ds@notagency.ru>
* @copyright Copyright © 2016 NotAgency
*/

namespace Notagency\Components;

use Notagency\Base\ComponentsBase,
Notagency\Base\Tools;

if (!\Bitrix\Main\Loader::includeModule('notagency.base')) return false;

class MaterialsList extends ComponentsBase
{
    protected $needModules = ['iblock'];

    protected $checkParams = [
        'IBLOCK_CODE' => ['type' => 'string']
    ];

    protected $elementsFilter = [];

    /**
    * @inheritdoc
    */
    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        $arParams['IBLOCK_CODE'] = htmlspecialchars(trim($arParams['IBLOCK_CODE']));
        $arParams['SECTION_CODE'] = htmlspecialchars(trim($arParams['SECTION_CODE']));
        $arParams['SECTION_ID'] = intval($arParams['SECTION_ID']);

        if (strlen($arParams['ELEMENT_SORT_BY1']) <= 0) $arParams['ELEMENT_SORT_BY1'] = 'SORT';
        if ($arParams['ELEMENT_SORT_ORDER1'] != 'DESC') $arParams['ELEMENT_SORT_ORDER1'] = 'ASC';

        if (strlen($arParams['ELEMENT_SORT_BY2']) <= 0) $arParams['ELEMENT_SORT_BY2'] = 'ID';
        if ($arParams['ELEMENT_SORT_ORDER2'] != 'DESC') $arParams['ELEMENT_SORT_ORDER2'] = 'ASC';

        if (strlen($arParams['ELEMENT_SORT_BY3']) <= 0) $arParams['ELEMENT_SORT_BY3'] = 'ID';
        if ($arParams['ELEMENT_SORT_ORDER3'] != 'DESC') $arParams['ELEMENT_SORT_ORDER3'] = 'ASC';

        if (strlen($arParams['SECTION_SORT_BY1']) <= 0) $arParams['SECTION_SORT_BY1'] = 'SORT';
        if ($arParams['SECTION_SORT_ORDER1'] != 'DESC') $arParams['SECTION_SORT_ORDER1'] = 'ASC';

        if (strlen($arParams['SECTION_SORT_BY2']) <= 0) $arParams['SECTION_SORT_BY2'] = 'ID';
        if ($arParams['SECTION_SORT_ORDER2'] != 'DESC') $arParams['SECTION_SORT_ORDER2'] = 'ASC';

        if (intval($_GET['page']) && !intval($arParams['PAGE'])) $arParams['PAGE'] = intval($_GET['page']);
        if ($arParams['PAGING'] == 'Y') {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N'); //не сохраняем в сессии параметры пагинации потому что это сбивает с толку пользователей
            $nav = \CDBResult::GetNavParams();
            if ($nav) $arParams['PAGE'] = intval($nav['PAGEN']);
            else if ($arParams['PAGE']) $arParams['PAGE'] = intval($_GET['page']);
        }

        $arParams['PREPROD_SERVER'] = defined('PREPROD_SERVER') && PREPROD_SERVER;

        if (strlen($arParams['FILTER_NAME']) > 0 && preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])) {
            $this->elementsFilter = array_merge($this->elementsFilter, $GLOBALS[$arParams['FILTER_NAME']]);
            $this->addCacheAdditionalId($GLOBALS[$arParams['FILTER_NAME']]);
        }

        //удаляем пустые элементы массива
        if (!empty($arParams['ELEMENT_PROPERTIES'])) {
            $arParams['ELEMENT_PROPERTIES'] = array_filter($arParams['ELEMENT_PROPERTIES']);
        }
        if (!empty($arParams['ELEMENT_FIELDS'])) {
            $arParams['ELEMENT_FIELDS'] = array_filter($arParams['ELEMENT_FIELDS']);
        }

        if(!empty($arParams['CACHE_TEMPLATE']) and $arParams['CACHE_TEMPLATE'] == 'N')
            $this->cacheTemplate = false;
        return $arParams;
    }

    /**
    * @inheritdoc
    */
    protected function executeMain()
    {
        $this->selectIblock();
        $this->selectSections();
        $this->selectElements();
        $this->buildTree();
        $this->setPanelButtons();
    }

    /**
    * @inheritdoc
    */
    protected function executeEpilog()
    {
        //не кешируется
        $this->showPanelButtons();
    }

    /**
    * Выбирает поля инфоблока, результат в $arResult['IBLOCK']
    * @throws \Exception
    */
    protected function selectIblock()
    {
        $filter = [
            'CODE' => $this->arParams['IBLOCK_CODE'],
            'SITE_ID' => SITE_ID,
        ];
        $result = \CIBlock::GetList([], $filter)->fetch();
        if (empty($result)) {
            throw new \Exception('iblock with code "' . $this->arParams['IBLOCK_CODE'] . '" doesn\'t found');
        }
        $result['LIST_PAGE_URL'] = \CIBlock::ReplaceDetailUrl($result['LIST_PAGE_URL'], $result);
        $this->arResult['IBLOCK'] = $result;
    }

    /**
    * Возвращает массив полей для выборки у разделов
    * @return array
    */
    protected function getSectionsSelect()
    {
        $select = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
            'DEPTH_LEVEL',
            'LEFT_MARGIN',
            'RIGHT_MARGIN',
            'IBLOCK_SECTION_ID',
        ];
        if (is_array($this->arParams['SECTION_FIELDS']) && count($this->arParams['SECTION_FIELDS'])) {
            $select = array_merge($select, $this->arParams['SECTION_FIELDS']);
        }
        if (is_array($this->arParams['SECTION_PROPERTIES']) && count($this->arParams['SECTION_PROPERTIES'])) {
            $select = array_merge($select, $this->arParams['SECTION_PROPERTIES']);
        }
        return $select;
    }

    /**
    * Выбирает разделы
    * Список разделов в $arResult['SECTIONS']
    * Выбранный раздел в $arResult['CURRENT_SECTION']
    * @throws \Exception
    */
    protected function selectSections()
    {
        $sections = [];
        $currentSection = null;
        $needParticularSection = false;
        if (
        $this->arParams['SELECT_SECTIONS'] != 'Y'
        && !$this->arParams['SECTION_CODE']
        && !$this->arParams['SECTION_ID']
        ) {
            return;
        }

        $sort = [
            $this->arParams['SECTION_SORT_BY1'] => $this->arParams['SECTION_SORT_ORDER1'],
            $this->arParams['SECTION_SORT_BY2'] => $this->arParams['SECTION_SORT_ORDER2'],
        ];
        $filter = [
            'GLOBAL_ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->arResult['IBLOCK']['ID'],
        ];
        if ($this->arParams['SECTION_CODE']) {
            $filter['CODE'] = $this->arParams['SECTION_CODE'];
            $needParticularSection = true;
        } else if ($this->arParams['SECTION_ID']) {
            $filter['ID'] = $this->arParams['SECTION_ID'];
            $needParticularSection = true;
        }
        $select = $this->getSectionsSelect();
        $rs = \CIBlockSection::GetList($sort, $filter, false, $select);
        while ($section = $rs->GetNext()) {
            $sections[] = $this->processSection($section);
        }

        if ($needParticularSection && !empty($sections)) {
            $currentSection = current($sections);
        }

        if ($currentSection && $this->arParams['SELECT_SECTIONS_TREE'] == 'Y') {
            //Если выбран какой-то конкретный раздел (по ID, или коду) и нужно получить всю его ветку
            $sections = $this->getSectionsTree($currentSection['LEFT_MARGIN'], $currentSection['RIGHT_MARGIN']);
        }

        $this->arResult['SECTIONS'] = $sections;
        $this->arResult['CURRENT_SECTION'] = $currentSection;
    }

    /**
    * Вызывается в цикле для каждого раздела
    * @param array $section - результат CIBlockSection::GetList()
    * @return array $section
    * @throws \Exception
    */
    protected function processSection($section)
    {
        //наследуемые свойства
        if ($inheritedPropertyValues = (new \Bitrix\Iblock\InheritedProperty\SectionValues($section['IBLOCK_ID'], $section['ID']))->getValues()) {
            $section['IPROPERTY_VALUES'] = $inheritedPropertyValues;
        }
        return $section;
    }

    /**
    * Выбирает всю ветку разделов по левой и правой границе какого-либо конкретного раздела
    * @param int $leftMargin - nestedSets
    * @param int $rightMargin - nestedSets
    * @return array
    */
    protected function getSectionsTree($leftMargin, $rightMargin)
    {
        if (!$leftMargin || !$rightMargin) {
            return [];
        }
        $sections = [];
        $sort = [
            'LEFT_MARGIN' => 'ASC',
        ];
        $filter = [
            'GLOBAL_ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->arResult['IBLOCK']['ID'],
            '<LEFT_BORDER' => $rightMargin,
            '>RIGHT_BORDER' => $leftMargin,
        ];
        $select = $this->getSectionsSelect();
        $rs = \CIBlockSection::GetList($sort, $filter, false, $select);
        while ($section = $rs->GetNext()) {
            $sections[] = $this->processSection($section);
        }
        return $sections;
    }

    /**
    * Выбирает поля элемента, результат в $arResult['ELEMENTS']
    * Постраничная навигация - $arResult['NAV_STRING']
    * @throws \Exception
    */
    protected function selectElements()
    {
        $elements = [];
        //order
        $sort = [
            $this->arParams['ELEMENT_SORT_BY1'] => $this->arParams['ELEMENT_SORT_ORDER1'],
            $this->arParams['ELEMENT_SORT_BY2'] => $this->arParams['ELEMENT_SORT_ORDER2'],
            $this->arParams['ELEMENT_SORT_BY3'] => $this->arParams['ELEMENT_SORT_ORDER3'],
        ];

        //filter
        $filter = [
            'IBLOCK_ID' => $this->arResult['IBLOCK']['ID'],
        ];

        if (!$this->arParams['PREPROD_SERVER']) {
            $filter['ACTIVE'] = 'Y';
        }

        if ($this->arParams['SECTION_CODE']) {
            $filter['SECTION_CODE'] = $this->arParams['SECTION_CODE'];
            if ($this->arParams['INCLUDE_SUBSECTIONS'] == 'Y') {
                $filter['INCLUDE_SUBSECTIONS'] = 'Y';
            }
        } else if ($this->arParams['SECTION_ID']) {
            $filter['SECTION_ID'] = $this->arParams['SECTION_ID'];
            if ($this->arParams['INCLUDE_SUBSECTIONS'] == 'Y') {
                $filter['INCLUDE_SUBSECTIONS'] = 'Y';
            }
        }
        if (is_array($this->elementsFilter)) {
            $filter = array_merge($filter, $this->elementsFilter);
            //echo "<pre>".print_r($filter,true)."</pre>\n"; 
        }

        //nav
        $nav = false;
        if ($this->arParams['PAGING'] == 'Y') {
            $nav = [
                'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                'iNumPage' => $this->arParams['PAGE'],
            ];
        } else if ($this->arParams['PAGING'] != 'Y' && $this->arParams['ELEMENTS_COUNT']) {
            $nav = [
                'nTopCount' => $this->arParams['ELEMENTS_COUNT'],
            ];
        }

        //select
        if (is_array($this->arParams['ELEMENT_FIELDS']) && count($this->arParams['ELEMENT_FIELDS'])) {
            $select = array_merge($this->arParams['ELEMENT_FIELDS'], ['ID', 'IBLOCK_ID']);
        } else {
            $select = array(
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_ID',
                'SECTION_ID',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_PAGE_URL',
            );
        }
        $res = \CIBlockElement::GetList($sort, $filter, false, $nav, $select);
        if (is_array($this->arParams['ELEMENT_PROPERTIES']) && count($this->arParams['ELEMENT_PROPERTIES'])) {
            while ($element = $res->GetNext()) {
                $element['PROPERTIES'] = $this->getElementProperties($element['ID']);
                $element = $this->processElement($element);
                $elements[] = $element;
            }
        } else {
            while ($ob = $res->GetNextElement()) {
                $element = $ob->GetFields();
                $element['PROPERTIES'] = $ob->GetProperties();
                $element = $this->processElement($element);
                $elements[] = $element;
            }
        }
        $this->arResult['ELEMENTS'] = $elements;
        if ($this->arParams['PAGING'] == 'Y') {
            $this->arResult['NAV_RESULT'] = $res;

            $templateName = empty($this->arParams['PAGING_TEMPLATE_NAME']) ? '.default' : $this->arParams['PAGING_TEMPLATE_NAME'];
            if(!empty($this->arParams['USE_AJAX']) and $this->arParams['USE_AJAX'] == 'Y')
            {
                $res->ajaxParams = [
                    'AJAX_COMPONENT_ID' => $this->arParams['AJAX_COMPONENT_ID'],
                    'AJAX_PARAM_NAME' => $this->arParams['AJAX_PARAM_NAME'],
                ];    
            }

            $this->arResult['NAV_STRING'] = $res->GetPageNavString($navigationTitle = '', $templateName, $showAlways = false, $parentComponent = $this);
        }
    }

    /**
    * Выбирает свойства элемента
    * @param int $elementId - ID элемента инфоблока
    * @throws \Exception
    * @return array - результат CIBlockElement::GetProperty()
    */
    protected function getElementProperties($elementId)
    {
        $props = [];
        foreach ($this->arParams['ELEMENT_PROPERTIES'] as $propertyCode) {
            if (empty($propertyCode))
                continue;
            $rs = \CIBlockElement::GetProperty($this->arResult['IBLOCK']['ID'], $elementId, array(), array('CODE' => $propertyCode));
            while ($prop = $rs->Fetch()) {
                $props[$prop['CODE']]['NAME'] = $prop['NAME'];
                $props[$prop['CODE']]['PROPERTY_TYPE'] = $prop['PROPERTY_TYPE'];
                $props[$prop['CODE']]['MULTIPLE'] = $prop['MULTIPLE'];
                if (!empty($prop['VALUE'])) {
                    if ($prop['MULTIPLE'] == 'Y') {
                        $props[$prop['CODE']]['DESCRIPTION'][] = $prop['DESCRIPTION'];
                        $props[$prop['CODE']]['VALUE'][] = $prop['VALUE'];
                        if ($prop['PROPERTY_TYPE'] == 'L') {
                            $props[$prop['CODE']]['VALUE_XML_ID'][] = $prop['VALUE_XML_ID'];
                            $props[$prop['CODE']]['VALUE_ENUM'][] = $prop['VALUE_ENUM'];
                        }
                    } else {
                        $props[$prop['CODE']]['DESCRIPTION'] = $prop['DESCRIPTION'];
                        $props[$prop['CODE']]['VALUE'] = $prop['VALUE'];
                        if ($prop['PROPERTY_TYPE'] == 'L') {
                            $props[$prop['CODE']]['VALUE_XML_ID'] = $prop['VALUE_XML_ID'];
                            $props[$prop['CODE']]['VALUE_ENUM'] = $prop['VALUE_ENUM'];
                        }
                    }
                }
            }
        }
        return $props;
    }

    /**
    * Вызывается в цикле для каждого элемента
    * @param array $element - результат CIBlockElement::GetList()
    * @return array $element
    * @throws \Exception
    */
    protected function processElement($element)
    {
        if (array_key_exists('DATE_ACTIVE_FROM', $element)) {
            $element['DISPLAY_ACTIVE_FROM'] = self::formatDisplayDate($element['DATE_ACTIVE_FROM'], $this->arParams['ACTIVE_DATE_FORMAT']);
        }
        if (array_key_exists('DATE_ACTIVE_TO', $element)) {
            $element['DISPLAY_ACTIVE_TO'] = self::formatDisplayDate($element['DATE_ACTIVE_TO'], $this->arParams['ACTIVE_DATE_FORMAT']);
        }
        if ($inheritedPropertyValues = (new \Bitrix\Iblock\InheritedProperty\ElementValues($element['IBLOCK_ID'], $element['ID']))->getValues()) {
            $element['IPROPERTY_VALUES'] = $inheritedPropertyValues;
        }
        foreach ($element['PROPERTIES'] as &$property) {
            //обработка свойства типа "Файл"
            if ($property['PROPERTY_TYPE'] == 'F') {
                if ($property['MULTIPLE'] == 'Y' && count($property['VALUE'])) {
                    foreach ($property['VALUE'] as $fileId) {
                        if (!intval($fileId)) {
                            continue;
                        }
                        if ($file = \CFile::GetFileArray($fileId)) {
                            $property['VALUE_DETAILS'][] = $this->processFile($file);
                        }
                    }
                } else if ($property['MULTIPLE'] != 'Y' && intval($property['VALUE'])) {
                    if ($file = \CFile::GetFileArray($property['VALUE'])) {
                        $property['VALUE_DETAILS'] = $this->processFile($file);
                    }
                }
            }
        }
        //выберем ID разделов к которым привязан данный элемент инфоблока т.к. IBLOCK_SECTION_ID может содержать только один ID какого-то раздела
        $rsSections = \CIBlockElement::GetElementGroups($element['ID'], true, ['ID']);
        if ($rsSections->SelectedRowsCount() > 1) {
            $element['IBLOCK_SECTION_ID'] = [];
            while ($section = $rsSections->Fetch()) {
                $element['IBLOCK_SECTION_ID'][] = $section['ID'];
            }
        }
        return $element;
    }

    /**
    * Обработка файла
    * @param array $file - результат CFile::GetFileArray()
    * @return array $file
    * @throws \Exception
    */
    protected function processFile($file)
    {
        if (!is_array($file)) {
            return false;
        }
        //получаем размер файла в читабельном формате с единицами измерения
        if (!empty($file['FILE_SIZE'])) {
            $file['DISPLAY_SIZE'] = Tools::formatFileSize($file['FILE_SIZE']);
        }
        //парсим имя файла и расширение через pathinfo
        if (!empty($file['ORIGINAL_NAME'])) {
            $fileInfo = pathinfo($file['ORIGINAL_NAME']);
            $file['FILE_NAME'] = $fileInfo['filename'];
            $file['FILE_EXTENSION'] = $fileInfo['extension'];
        }
        return $file;
    }

    /**
    * Строит дерево разделов и элементов, результат в $arResult['TREE']
    */
    protected function buildTree()
    {
        if (!empty($this->arResult['SECTIONS'])) {
            foreach ($this->arResult['SECTIONS'] as $section) {
                $this->arResult['TREE'][$section['ID']] = $section;
            }

            if (!empty($this->arResult['ELEMENTS'])) {
                foreach ($this->arResult['ELEMENTS'] as $element) {
                    if (is_array($element['IBLOCK_SECTION_ID'])) {
                        foreach ($element['IBLOCK_SECTION_ID'] as $sectionId) {
                            if (array_key_exists($sectionId, $this->arResult['TREE'])) {
                                $this->arResult['TREE'][$sectionId]['ELEMENTS'][] = $element;
                            }
                        }
                    } else if (intval($element['IBLOCK_SECTION_ID']) > 0) {
                        if (array_key_exists($element['IBLOCK_SECTION_ID'], $this->arResult['TREE'])) {
                            $this->arResult['TREE'][$element['IBLOCK_SECTION_ID']]['ELEMENTS'][] = $element;
                        }
                    }
                }
            }
        }
    }

    /**
    * Устанавливает кнопки управления компонентом в публичной части в режиме редактирования
    */
    protected function setPanelButtons()
    {
        foreach ($this->arResult['ELEMENTS'] as $i => $element) {
            $buttons = \CIBlock::GetPanelButtons(
                $element['IBLOCK_ID'],
                $element['ID'],
                $element['IBLOCK_SECTION_ID'],
                [
                    'SECTION_BUTTONS' => $this->arParams['ADD_PANEL_SECTION_BUTTONS'] == 'Y'
                ]
            );
            $this->arResult['ELEMENTS'][$i]['EDIT_LINK'] = $buttons['edit']['edit_element']['ACTION_URL'];
            $this->arResult['ELEMENTS'][$i]['DELETE_LINK'] = $buttons['edit']['delete_element']['ACTION_URL'];
        }
    }

    /**
    * Отображает кнопки управления компонентом в публичной части в режиме редактирования
    */
    protected function showPanelButtons()
    {
        $buttons = \CIBlock::GetPanelButtons(
            $this->arResult['IBLOCK']['ID'],
            0,
            0,
            [
                'SECTION_BUTTONS' => $this->arParams['ADD_PANEL_SECTION_BUTTONS'] == 'Y'
            ]
        );
        global $APPLICATION;
        if ($this->arParams['SHOW_PANEL_BUTTONS'] == 'Y' && $APPLICATION->GetShowIncludeAreas()) {
            $this->AddIncludeAreaIcons(\CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
        }
    }

    public function showResult()
    {
        if (
        ($this->arParams['SECTION_CODE'] || $this->arParams['SECTION_ID'])
        && empty($this->arResult['SECTIONS'])
        ) {
            $this->handle404();
        } else {
            $this->includeComponentTemplate($this->templatePage);
        }
    }

    public function handle404()
    {
        $this->abortCache();
        if ($this->arParams['SET_STATUS_404'] === 'Y') {
            $this->return404();
        }
        if ($this->arParams['SHOW_404'] === 'Y') {
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
            require \Bitrix\Main\Application::getDocumentRoot() . SITE_TEMPLATE_PATH . '/header.php';
            require \Bitrix\Main\Application::getDocumentRoot() . '/404.php';
        } else {
            $this->includeComponentTemplate($this->templatePage);
        }
    }
}