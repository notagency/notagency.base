<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['TREE']))
{
    foreach ($arResult['TREE'] as $section)
    {
        ?><h1><?=$section['NAME']?></h1><?
        foreach ($section['ELEMENTS'] as $item)
        {
            $this->AddEditAction('iblock_element_' . $item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($arResult['IBLOCK']['ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction('iblock_element_' . $item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($arResult['IBLOCK']['ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNI_ELEMENT_DELETE_CONFIRM')));

            ?><div id="<?=$this->GetEditAreaId('iblock_element_' . $item['ID']);?>"><?
                ?><h2><a href="<?=$item['DETAIL_PAGE_URL'];?>"><?=$item['NAME'];?></a></h2><?
                ?><p><?=$item['PREVIEW_TEXT'];?></p><?
            ?></div><?
        }
    }
}