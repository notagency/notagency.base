<?php

/**
 * Набор функций для работы с инфоблоками
 * @link https://bitbucket.org/notagency/notagency.base
 * @author Dmitry Savchenkov <ds@notagency.ru>
 * @copyright Copyright © 2016 NotAgency
 */

namespace Notagency\Base;

\Bitrix\Main\Loader::IncludeModule('iblock');

class IblockTools
{
    private static $iblockId = [];
    private static $iblockPropertyEnumId = [];

    /**
     * Получить ID инфоблока по коду
     *
     * @param string $code
     * @param boolean $checkSiteID
     * @return int|bool
     */
    public static function getIblockId($code, $checkSiteID = true)
    {
        if (!empty(self::$iblockId[$code])) {
            return self::$iblockId[$code];
        }

        $arFilter = [
            'CODE' => $code
        ];

        if($checkSiteID and !empty(SITE_ID)){
            $arFilter['SITE_ID'] = SITE_ID;
        }

        if ($iblock = \CIBlock::GetList([], $arFilter)->fetch()) {
            self::$iblockId[$code] = $iblock['ID'];
            return $iblock['ID'];
        }
        return false;
    }

    /**
     * Получить ID значения свойства типа "список" у инфоблока
     *
     * @param string $iblockCode
     * @param string $propertyCode
     * @param string $enumCode
     * @return int|bool
     */
    public static function getIblockPropertyEnumId($iblockCode, $propertyCode, $enumCode)
    {
        if (!empty(self::$iblockPropertyEnumId[$iblockCode][$propertyCode][$enumCode])) {
            return self::$iblockPropertyEnumId[$iblockCode][$propertyCode][$enumCode];
        }

        if (!$iblockId = self::getIblockId($iblockCode)) {
            return false;
        }

        if ($enum = \CIBlockProperty::GetPropertyEnum($propertyCode, [], ['IBLOCK_ID' => $iblockId, 'EXTERNAL_ID' => $enumCode])->fetch()) {
            self::$iblockPropertyEnumId[$iblockCode][$propertyCode][$enumCode] = $enum['ID'];
            return $enum['ID'];
        }
        return false;
    }
}

?>
