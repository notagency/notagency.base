<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


?><ul><?php

foreach ($arResult['SECTIONS'] as $section) {

    ?><li><?php

    for ($space = 0; $space < $section['DEPTH_LEVEL']; $space++) {
        echo '&nbsp;&nbsp;&nbsp;';
    }
    echo $section['NAME'];

    if ($arResult['TREE'][$section['ID']]['ELEMENTS']) {
        ?><ul><?php
        foreach ($arResult['TREE'][$section['ID']]['ELEMENTS'] as $element) {
            ?><li><?=$element['NAME'] ?></li><?php
        }
        ?></ul><?php
    }
    ?></li><?php
}
?></ul>