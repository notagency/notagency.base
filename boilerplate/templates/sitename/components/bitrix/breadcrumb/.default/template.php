<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(empty($arResult))
{
    return '';
}

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
    $title = htmlspecialcharsex($arResult[$index]['TITLE']);

    if($arResult[$index]['LINK'] <> '' && $index != $itemSize-1)
    {
        $strReturn .= '<a href="' . $arResult[$index]["LINK"] . '" class="breadcrumbs__item">';
        $strReturn .= $title;
        if ($index < $itemSize - 1)
        {
            $strReturn .= '<span class="breadcrumbs__separator">/</span>';
        }
        $strReturn .= '</a>';
    }
    else
    {
        $strReturn .= '<a href="javascript:void(0);" class="breadcrumbs__item breadcrumbs__item_type_current">';
        $strReturn .= $title;
        $strReturn .= '</a>';
    }
}

return $strReturn;