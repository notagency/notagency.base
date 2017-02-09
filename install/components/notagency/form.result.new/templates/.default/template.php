<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?><h1><?=$arResult['FORM']['NAME']?></h1><?php
?><p><?=$arResult['FORM']['DESCRIPTION']?></p><?php

if ($arResult['ERRORS']) {
    ShowError(join('<br>', $arResult['ERRORS']));
}

if ($arResult['SUCCESS']) {
    ?><p>Данные отправлены успешно!</p><?php
} else {

    ?>
    <form method="post" name="webform" enctype="multipart/form-data">

        <?=bitrix_sessid_post()?>
        <input type="hidden" name="WEB_FORM_ID" value="<?=$arResult['FORM']['ID']?>" />

        <?php foreach ($arResult['FORM']['QUESTIONS'] as $code => $question) {
            ?><div><?php

            echo $question['CAPTION'];
            echo '&nbsp;';

            if (in_array($question['ANSWERS'][0]['FIELD_TYPE'], ['dropdown', 'multiselect'])) {
                ?><select <?=$question['ANSWERS'][0]['FIELD_TYPE'] == 'multiselect' ? 'multiple' : ''?> name="<?=$question['ANSWERS'][0]['FIELD_NAME']?>"><?php
            }


            foreach ($question['ANSWERS'] as $answer) {

                switch ($answer['FIELD_TYPE']) {
                    case 'checkbox':
                        ?><label>
                        <?= $answer['MESSAGE'] ?>
                        <input name="<?= $answer['FIELD_NAME'] ?>" type="checkbox"
                               value="<?= $answer['ID'] ?>" <?= $answer['CHECKED'] ? 'checked' : '' ?> />
                        </label><?php
                        break;
                    case 'radio':
                        ?><label>
                        <?= $answer['MESSAGE'] ?>
                        <input name="<?= $answer['FIELD_NAME'] ?>" type="radio"
                               value="<?= $answer['ID'] ?>" <?= $answer['CHECKED'] ? 'checked' : '' ?> />
                        </label><?php
                        break;
                    case 'dropdown':
                    case 'multiselect':
                        ?><option <?= $answer['SELECTED'] ? 'selected' : '' ?> value="<?=$answer['ID']?>"><?=$answer['MESSAGE']?></option><?php
                        break;
                    case 'textarea':
                        ?><textarea name="<?= $answer['FIELD_NAME'] ?>"><?=$answer['VALUE']?></textarea><?php
                        break;
                    case 'file':
                        echo \CFile::InputFile($answer['FIELD_NAME'], 100, false, false, 0, '');
                        break;
                    case 'image':
                        echo \CFile::InputFile($answer['FIELD_NAME'], 100, false, false, 0, 'IMAGE');
                        break;
                    case 'date':
                        $APPLICATION->IncludeComponent(
                            'bitrix:main.calendar',
                            '',
                            array(
                                'SHOW_INPUT' => 'Y',
                                'FORM_NAME' => 'webform',
                                'INPUT_NAME' => $answer['FIELD_NAME'],
                                'SHOW_TIME' => 'N',
                            ),
                            null,
                            array('HIDE_ICONS' => 'Y')
                        );
                        break;
                    default:
                        ?><input name="<?= $answer['FIELD_NAME'] ?>" type="text" value="<?= $answer['VALUE'] ?>" /><?php
                        break;
                }
                echo '&nbsp;';
            }

            if (in_array($question['ANSWERS'][0]['FIELD_TYPE'], ['dropdown', 'multiselec'])) {
                ?></select><?php
            }

            if (array_key_exists(ToUpper($code), $arResult['FIELD_ERRORS'])) {
                ShowError($arResult['FIELD_ERRORS'][ToUpper($code)]);
            }
            ?></div><br/><?php

        } ?>
        <div>
            <input type="submit" value="<?=$arResult['FORM']['SUBMIT_CAPTION']?>">
        </div>
    </form>

    <?php
}