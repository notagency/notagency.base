<?php

namespace Notagency\Components;

\CBitrixComponent::includeComponentClass('notagency:materials.list');

class MaterialsDetail extends MaterialsList
{
    protected $checkParams = [
        'IBLOCK_CODE' => ['type' => 'string'],
    ];

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        $arParams['SELECT_SECTIONS'] = 'N';
        $arParams['ELEMENTS_COUNT'] = 1;
        $arParams['PAGING'] = 'N';
        if (array_key_exists($arParams['REQUEST_ELEMENT_CODE'], $_REQUEST) && !empty($_REQUEST[$arParams['REQUEST_ELEMENT_CODE']])) {
            $arParams['ELEMENT_CODE'] = htmlspecialchars(trim($_REQUEST[$arParams['REQUEST_ELEMENT_CODE']]));
        }
        if (array_key_exists($arParams['REQUEST_ELEMENT_ID'], $_REQUEST) && intval($_REQUEST[$arParams['REQUEST_ELEMENT_ID']])) {
            $arParams['ELEMENT_ID'] = intval($_REQUEST[$arParams['REQUEST_ELEMENT_ID']]);
        }
        return $arParams;
    }

    protected function executeMain()
    {
        if ($this->arParams['ELEMENT_ID']) {
            $this->elementsFilter['ID'] = $this->arParams['ELEMENT_ID'];
        }
        if ($this->arParams['ELEMENT_CODE']) {
            $this->elementsFilter['CODE'] = $this->arParams['ELEMENT_CODE'];
        }
        if (!empty($this->elementsFilter)) {
            parent::executeMain();
            $this->arResult['ELEMENT'] = $this->arResult['ELEMENTS'][0];
        }
        unset($this->arResult['ELEMENTS']);
        if (intval($this->arResult['ELEMENT']['IBLOCK_SECTION_ID'])) {
            $this->arParams['SELECT_SECTIONS_TREE'] = 'Y';
            $this->arParams['SECTION_ID'] = $this->arResult['ELEMENT']['IBLOCK_SECTION_ID'];
            $this->selectSections();
        }
    }

    public function showResult()
    {
        if ($is404 = empty($this->arResult['ELEMENT'])) {
            $this->handle404();
        } else {
            $this->includeComponentTemplate($this->templatePage);
        }
    }

    protected function executeEpilog()
    {
        $this->handleNavChain();
        $this->handleTitle();
    }

    protected function handleNavChain()
    {
        global $APPLICATION;

        if ($includeSectionNameIntoChain = $this->arParams['INCLUDE_SECTIONS_NAMES_INTO_CHAIN'] == 'Y') {
            //считаем, что разделы уже отсортированы по margin_left в materials.list
            if (!empty($this->arResult['SECTIONS'])) {
                foreach ($this->arResult['SECTIONS'] as $section) {
                    $APPLICATION->AddChainItem(trim($section['NAME']), $section['SECTION_PAGE_URL']);
                }
            }
        }

        $includeIntoChain = $this->arParams['INCLUDE_INTO_CHAIN'];

        $chainFieldNames = explode('.', $this->arParams['INCLUDE_FIELD_INTO_CHAIN']);
        $chainPropertyNames = explode('.', $this->arParams['INCLUDE_PROPERTY_INTO_CHAIN']);

        $chainEntity = '';
        switch ($includeIntoChain) {
            case 'FIELD':
                foreach ($chainFieldNames as $chainFieldName) {
                    if (!array_key_exists($chainFieldName, $this->arResult['ELEMENT'])) {
                        continue;
                    }
                    if ($chainFieldName == 'DATE_ACTIVE_FROM' && $this->arResult['ELEMENT']['DISPLAY_ACTIVE_FROM']) {
                        $chainEntity .= ' ' . $this->arResult['ELEMENT']['DISPLAY_ACTIVE_FROM'];
                    } else {
                        $chainEntity .= ' ' . $this->arResult['ELEMENT'][$chainFieldName];
                    }
                }
                break;
            case 'PROPERTY':
                foreach ($chainPropertyNames as $chainPropertyName) {
                    if (!array_key_exists($chainPropertyName, $this->arResult['ELEMENT']['PROPERTIES'])) {
                        continue;
                    }
                    if (empty($this->arResult['ELEMENT']['PROPERTIES'][$chainPropertyName]['VALUE'])) {
                        continue;
                    }


                    $chainEntity .= ' ' . $this->arResult['ELEMENT']['PROPERTIES'][$chainPropertyName]['VALUE'];
                }
                break;
        }
        if (!empty($chainEntity)) {
            $APPLICATION->AddChainItem(trim($chainEntity));
        }
    }
    
    protected function handleTitle()
    {
        global $APPLICATION;
        $setTitleFrom = $this->arParams['SET_TITLE_FROM'];
        $setTitleFromProperty = $this->arParams['SET_TITLE_FROM_PROPERTY'];

        switch ($setTitleFrom) {
            case 'NAME':
                $APPLICATION->SetTitle($this->arResult['ELEMENT']['NAME']);
                break;
            case 'PROPERTY':
                if (
                    array_key_exists($setTitleFromProperty, $this->arResult['ELEMENT']['PROPERTIES'])
                    && !empty($this->arResult['ELEMENT']['PROPERTIES'][$setTitleFromProperty]['VALUE'])
                ) {
                    $APPLICATION->SetTitle($this->arResult['ELEMENT']['PROPERTIES'][$setTitleFromProperty]['VALUE']);
                }
                break;
        }
    }
}