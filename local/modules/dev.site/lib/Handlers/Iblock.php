<?php

namespace Dev\Site\Handlers;

class Iblock
{
    public function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality
                    );
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList(
                                [],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ],
                                false,
                                false,
                                ['ID']
                            )
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList(
                                [],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ],
                                false,
                                false,
                                ['ID', 'IBLOCK_ID', 'NAME']
                            );
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

    public static function OnAfterIBlockElementAddHandler(&$arFields)
    {
        self::addLog($arFields, true);
    }

    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        self::addLog($arFields, false);
    }

    private static function addLog(&$arFields, $isAdd)
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return;
        }

        $elementId = (int)$arFields['ID'];
        $iblockId = (int)$arFields['IBLOCK_ID'];
        $logIblockId = 9;

        if ($iblockId === $logIblockId || !$elementId || !$iblockId) {
            return;
        }

        $element = \CIBlockElement::GetList(
            [],
            [
                'ID' => $elementId,
                'IBLOCK_ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE', 'TIMESTAMP_X', 'IBLOCK_SECTION_ID']
        )->Fetch();

        if (!$element) {
            return;
        }

        $iblock = \CIBlock::GetList([], [
            'ID' => $iblockId,
            'CHECK_PERMISSIONS' => 'N'
        ])->Fetch();

        if (!$iblock) {
            return;
        }
        $logSection = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $logIblockId,
                '=CODE' => $iblock['CODE'],
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            ['ID', 'NAME', 'CODE']
        )->Fetch();

        /*
         * Если раздел журнала не найден, создаём новый раздел
         */
        if (!$logSection) {
            $section = new \CIBlockSection();

            $sectionId = $section->Add([
                'IBLOCK_ID' => $logIblockId,
                'NAME' => $iblock['NAME'],
                'CODE' => $iblock['CODE'],
                'ACTIVE' => 'Y'
            ]);

            if (!$sectionId) {
                return;
            }
        } else {
            $sectionId = (int)$logSection['ID'];
        }
        /*
         * Получаем разделы исходного элемента
         */
        $sectionNames = [];
        $parentSectionId = (int)$element['IBLOCK_SECTION_ID'];

        while ($parentSectionId > 0) {
            $sourceSection = \CIBlockSection::GetList(
                [],
                [
                    'ID' => $parentSectionId,
                    'IBLOCK_ID' => $iblockId,
                    'CHECK_PERMISSIONS' => 'N'
                ],
                false,
                ['ID', 'NAME', 'IBLOCK_SECTION_ID']
            )->Fetch();

            if (!$sourceSection) {
                break;
            }

            $sectionNames[] = $sourceSection['NAME'];
            $parentSectionId = (int)$sourceSection['IBLOCK_SECTION_ID'];
        }

        $sectionNames = array_reverse($sectionNames);

        /*
        * Создаём запись журнала в разделе.
        */
        $previewText = $iblock['NAME'];

        if (!empty($sectionNames)) {
            $previewText .= ' -> ' . implode(' -> ', $sectionNames);
        }

        $previewText .= ' -> ' . $element['NAME'];

        /*
         * Проверка существует ли уже запись журнала для элемента
         * Если существует то обновляем, иначе создаём новую
         */
        $activeFrom = $isAdd
            ? $element['DATE_CREATE']
            : $element['TIMESTAMP_X'];
        $existingLog = \CIBlockElement::GetList(
            [],
            [
            'IBLOCK_ID' => $logIblockId,
            'SECTION_ID' => $sectionId,
            '=NAME' => (string)$elementId,
            'CHECK_PERMISSIONS' => 'N'
        ],
            false,
            false,
            ['ID']
        )->Fetch();
        /*
        * Поля записи журнала
        */
        $logFields = [
            'IBLOCK_ID' => $logIblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => (string)$elementId,
            'ACTIVE' => 'Y',
            'ACTIVE_FROM' => $activeFrom,
            'PREVIEW_TEXT' => $previewText,
            'PREVIEW_TEXT_TYPE' => 'text'
        ];

        $logElement = new \CIBlockElement();
        /*
         * Если запись уже существует, обновляем её, иначе создаём новую
         */
        if ($existingLog) {
            $result = $logElement->Update($existingLog['ID'], $logFields);
        } else {
            $result = $logElement->Add($logFields);
        }

        if (!$result) {
            AddMessage2Log(
                'Ошибка записи журнала: ' . $logElement->LAST_ERROR,
                'dev.site'
            );
        }
    }
}
