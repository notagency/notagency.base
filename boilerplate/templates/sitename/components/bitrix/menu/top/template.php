<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult)
{
    foreach ($arResult as $item)
    {
        ?><a href="<?=$item['LINK']?>" <?if ($item['PARAMS']['target']=='_blank'):?>target="_blank"<?endif?> class="<?if ($item['SELECTED']):?>active<?endif?>"><?=$item['TEXT']?></a><?
    }
}