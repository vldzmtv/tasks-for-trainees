<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');
$row = 1;
$IBLOCK_ID = 8;

$el = new CIBlockElement();
$arProps = [];

$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);

while ($arProp = $rsProp->Fetch()) {
    $key = trim($arProp['VALUE']);

    if ($arProp['PROPERTY_CODE'] == 'OFFICE') {
        $key = str_replace(['»', '«', '(', ')'], '', $key);
        $key = mb_strtolower($key, 'UTF-8');
        $arKey = explode(' ', $key);
        $key = '';

        foreach ($arKey as $part) {
            if (mb_strlen($part, 'UTF-8') > 2) {
                $key .= trim($part) . ' ';
            }
        }
        $key = trim($key);
    } else {
        $key = mb_strtolower($key, 'UTF-8');
    }
    $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
}

$rsElements = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $IBLOCK_ID],
    false,
    false,
    ['ID']
);

while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}

if (($handle = fopen("vacancy.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if ($row == 1) {
            $row++;
            continue;
        }
        if (empty(trim($data[3]))) {
            continue;
        }

        $PROP['ACTIVITY'] = $data[9];
        $PROP['FIELD'] = $data[11];
        $PROP['OFFICE'] = $data[1];
        $PROP['LOCATION'] = $data[2];
        $PROP['REQUIRE'] = $data[4];
        $PROP['DUTY'] = $data[5];
        $PROP['CONDITIONS'] = $data[6];
        $PROP['EMAIL'] = $data[12];
        $PROP['DATE'] = date('d.m.Y');
        $PROP['TYPE'] = $data[8];
        $PROP['SALARY_TYPE'] = '';
        $PROP['SALARY_VALUE'] = $data[7];
        $PROP['SCHEDULE'] = $data[10];

        foreach ($PROP as $key => &$value) {
            $value = trim($value);
            $value = str_replace(["\r", "\n"], '', $value);
            $value = preg_replace('/^\*+/', '', $value);
            if (stripos($value, '•') !== false) {
                $value = explode('•', $value);
                array_splice($value, 0, 1);
                foreach ($value as &$str) {
                    $str = trim($str);
                }
                unset($str);
            } elseif (!empty($arProps[$key])) {
                $arSimilar = [];
                $value = mb_strtolower($value, 'UTF-8');
                foreach ($arProps[$key] as $propKey => $propVal) {
                    if ($key == 'OFFICE') {
                        $value = str_replace(['»', '«', '(', ')'], '', $value);
                        $arKey = explode(' ', $value);
                        $normalizedValue = '';
                        foreach ($arKey as $part) {
                            if (mb_strlen($part, 'UTF-8') > 2) {
                                $normalizedValue .= trim($part) . ' ';
                            }
                        }
                        $value = trim($normalizedValue);
                        if ($value == 'центральный офис') {
                            $value .= ' свеза ' . $data[2];
                        } elseif ($value == 'лесозаготовка') {
                            $value = 'свеза ресурс ' . $value;
                        }
                        $arSimilar[similar_text($value, $propKey)] = $propVal;
                    }
                    if (stripos($propKey, $value) !== false) {
                        $value = $propVal;
                        break;
                    }
                    if (similar_text($propKey, $value) > 50) {
                        $value = $propVal;
                    }
                }
                if ($key == 'OFFICE' && !is_numeric($value)) {
                    ksort($arSimilar);
                    $value = array_pop($arSimilar);
                }
            }
        }
        unset($value);
        if ($PROP['SALARY_VALUE'] == '-') {
            $PROP['SALARY_VALUE'] = '';
        } elseif (mb_strtolower($PROP['SALARY_VALUE'], 'UTF-8') == 'по договоренности') {
            $PROP['SALARY_VALUE'] = '';
            $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['договорная'];
        } else {
            $arSalary = explode(' ', $PROP['SALARY_VALUE']);
            if (mb_strtolower($arSalary[0], 'UTF-8') == 'от' ||
                mb_strtolower($arSalary[0], 'UTF-8') == 'до') {
                $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE'][mb_strtolower($arSalary[0], 'UTF-8')];
                array_splice($arSalary, 0, 1);
                $PROP['SALARY_VALUE'] = implode(' ', $arSalary);
            } else {
                $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
            }
        }
        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => trim(preg_replace('/^\*+/', '', $data[3])),
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];
        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($handle);
}
