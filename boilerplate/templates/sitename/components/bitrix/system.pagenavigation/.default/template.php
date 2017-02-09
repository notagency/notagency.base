<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); 

$strNavQueryString = $arResult["sUrlPath"] . '?' . ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryString .= 'PAGEN_' . $arResult["NavNum"] . '=%d';
$showDivider = $arResult['NavPageCount'] > $arResult['nPageWindow'];

?><span class="paging__total">Страница <?=$arResult["NavPageNomer"]?>/<?=$arResult["NavPageCount"]?></span>
<div class="paging__float-wrap">
    <ul class="paging__list"><?

        if ($arResult["NavShowAlways"] || $arResult["NavPageCount"] > 1)
        {
            if ($arResult["NavPageNomer"] != 1) 
            {
                ?><a href="<?=sprintf($strNavQueryString, $arResult["NavPageNomer"] - 1)?>" class="paging__ctrl paging__ctrl--prev"></a><?
            }
            
            if ($showDivider && $arResult["nStartPage"] > 1)
            {
                ?><li class="paging__item"><a href="<?=sprintf($strNavQueryString, 1)?>">1</a></li><?
                ?><li class="paging__divider">...</li><?
            }

            for ($PAGE_NUMBER = $arResult["nStartPage"]; $PAGE_NUMBER <= $arResult["nEndPage"]; $PAGE_NUMBER++) 
            {
                if ($PAGE_NUMBER == $arResult["NavPageNomer"]) 
                {
                    ?><li class="paging__item active"><?= $PAGE_NUMBER ?></li><?
                } 
                else 
                {
                    ?><li class="paging__item">
                        <a href="<?=sprintf($strNavQueryString, $PAGE_NUMBER)?>"><?=$PAGE_NUMBER?></a>
                    </li><?
                }
            }
            
            if ($showDivider && $arResult["nEndPage"] < $arResult["NavPageCount"])
            {
                ?><li class="paging__divider">...</li>
                <li class="paging__item"><a href="<?=sprintf($strNavQueryString, $arResult["NavPageCount"])?>"><?=$arResult["NavPageCount"]?></a></li><?
            }        
                
            if ($arResult["NavPageNomer"] != $arResult["NavPageCount"]) 
            {
                ?><a href="<?=sprintf($strNavQueryString, $arResult["NavPageNomer"] + 1)?>" class="paging__ctrl paging__ctrl--next"></a><?
            }
        }
        
    ?></ul><?
    if ($arResult["NavPageNomer"] != 1) 
    {
        ?><a href="<?=sprintf($strNavQueryString, 1)?>" class="paging__link paging__link--first">Первая</a><?
    }
    if ($arResult["NavPageNomer"] != $arResult["NavPageCount"]) 
    {
        ?><a href="<?=sprintf($strNavQueryString, $arResult["NavPageCount"])?>" class="paging__link">Последняя</a><?
    }
?></div><?