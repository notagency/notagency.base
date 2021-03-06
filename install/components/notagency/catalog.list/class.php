<?php
namespace Notagency\Components;

\CBitrixComponent::includeComponentClass('notagency:materials.list');

class CatalogList extends MaterialsList
{
    protected $needModules = [
        'iblock',
        'catalog',
    ];

    protected function processElement($element)
    {
        $element = parent::processElement($element);
        $element = $this->processAmount($element);
        $element = $this->processPrice($element);
        return $element;
    }

    protected function processAmount($element)
    {
        $product = \CCatalogProduct::GetByID($element['ID']);
        $element['QUANTITY'] = $product['QUANTITY'];

        $res = \CCatalogMeasureRatio::getList([], ['IBLOCK_ID' => $element['IBLOCK_ID'], 'PRODUCT_ID' => $product['ID']], false, false, []);
        if($arRation = $res->Fetch()){
             $element['CATALOG_RATION'] = $arRation['RATIO'];
        }
        
        if (empty($product['MEASURE'])) {
            $arDefaultMeasure = \CCatalogMeasure::getDefaultMeasure(true, true);
            $element['CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
        } else {
            $rsMeasures = \CCatalogMeasure::getList(array(), array('ID' => $product['MEASURE']));
            if ($arMeasure = $rsMeasures->GetNext()) {
                $element['CATALOG_MEASURE_NAME'] = $arMeasure['SYMBOL_RUS'];
            }
        }
        return $element;
    }

    protected function processPrice($element)
    {
        $element['CATALOG_DETAILS'] = \CCatalogProduct::GetOptimalPrice($element['ID']);
        $element['PRICE_FORMATTED'] = $this->formatPrice($element['CATALOG_DETAILS']['PRICE']['PRICE'], $element['CATALOG_DETAILS']['PRICE']['CURRENCY'], true);
        return $element;
    }

    protected function formatPrice($price, $currency = 'RUB', $useTemplate = false)
    {
        $price = \CCurrencyLang::CurrencyFormat($price, $currency, $useTemplate);
        return $price;
    }
}
