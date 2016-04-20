<?
namespace Notagency\Components;

\CBitrixComponent::includeComponentClass('notagency:materials.detail');

class CatalogDetail extends MaterialsDetail
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
        return $element;
    }

    protected function processPrice($element)
    {
        $element['CATALOG_DETAILS'] = \CCatalogProduct::GetOptimalPrice($element['ID']);
        $element['PRICE_FORMATTED'] = $this->formatPrice($element['CATALOG_DETAILS']['PRICE']['PRICE']);
        return $element;
    }

    protected function formatPrice($price)
    {
        $price = \CCurrencyLang::CurrencyFormat($price, 'RUB', false);
        return $price;
    }
}