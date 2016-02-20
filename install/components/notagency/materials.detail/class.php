<?
namespace Notagency\Components;

\CBitrixComponent::includeComponentClass('notagency:elements.list');

class ElementsDetail extends ElementsList
{
    protected $checkParams = [
        'IBLOCK_CODE' => ['type' => 'string'],
    ];

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        $arParams['ELEMENTS_COUNT'] = 1;
        $arParams['PAGING'] = 'N';
        return $arParams;
    }

    protected function executeMain()
    {
        if (!empty($this->arParams['ELEMENT_CODE']))
        {
            $this->elementsFilter['CODE'] = htmlspecialcharsbx($this->arParams['ELEMENT_CODE']);
        }
        if (!empty($this->arParams['ELEMENT_ID']))
        {
            $this->elementsFilter['ID'] = htmlspecialcharsbx($this->arParams['ELEMENT_ID']);
        }
        parent::executeMain();
        $this->arResult['ELEMENT'] = $this->arResult['ELEMENTS'][0];
        unset($this->arResult['ELEMENTS']);
    }

    protected function executeEpilog()
    {
        global $APPLICATION;
        if ($this->arParams['INCLUDE_TITLE_INTO_CHAIN'] == 'Y') {
            $name = $this->arResult['ELEMENT']['NAME'];
            if (SITE_ID == 'en' && !empty($this->arResult['ELEMENT']['PROPERTIES'][self::ENGLISH_ELEMENT_NAME_PROPERTY]['VALUE'])) {
                $name = $this->arResult['ELEMENT']['PROPERTIES'][self::ENGLISH_ELEMENT_NAME_PROPERTY]['VALUE'];
            }
            $APPLICATION->AddChainItem($name, $this->arResult['ELEMENT']['DETAIL_PAGE_URL']);
        }
    }
}