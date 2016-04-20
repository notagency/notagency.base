<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


?><ul><?
foreach ($arResult['SECTIONS'] as $section)
{

    ?><li><? 
        for ($space = 0; $space < $section['DEPTH_LEVEL']; $space++)
        {
            echo '&nbsp;&nbsp;&nbsp;';
        }
        echo $section['NAME']; 
        if ($arResult['TREE'][$section['ID']]['ELEMENTS']) 
        {
            ?><ul><?
            foreach ($arResult['TREE'][$section['ID']]['ELEMENTS'] as $element)
            {
                ?><li><?=$element['NAME']?></li><?
            }
            ?></ul><?
        }
    ?></li><?
}
?></ul><?