<?php

namespace Dev\Site\Agents;

class Iblock
{
    public static function clearOldLogs()
    {
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = 9;
            $count = 0;

            $rsLogs = \CIBlockElement::GetList(
                [
                    'ACTIVE_FROM' => 'DESC',
                    'ID' => 'DESC'
                ],
                [
                    'IBLOCK_ID' => $iblockId,
                    'CHECK_PERMISSIONS' => 'N'
                ],
                false,
                false,
                ['ID']
            );

            while ($log = $rsLogs->Fetch()) {
                $count++;

                if ($count > 10) {
                    \CIBlockElement::Delete($log['ID']);
                }
            }
        }

        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
