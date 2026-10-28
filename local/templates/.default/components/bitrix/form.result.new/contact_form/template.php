<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 */

function setFieldClass($html, $class)
{
    return preg_replace_callback('/<(input|textarea)\b[^>]*>/i', function ($matches) use ($class) {
        $tag = $matches[0];
        if (preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/i', $tag, $classMatch)) {
            $classes = $classMatch[2];

            if (!preg_match('/(?:^|\s)' . preg_quote($class, '/') . '(?:\s|$)/', $classes)) {
                $newClasses = trim($classes . ' ' . $class);
                $tag = str_replace($classMatch[0], 'class="' . $newClasses . '"', $tag);
            }
        } else {
            $tag = preg_replace('/<(input|textarea)\b/i', '<$1 class="' . $class . '"', $tag, 1);
        }
        return $tag;
    }, $html);
}

?>

<?php if ($arResult["isFormErrors"] == "Y"): ?>
    <?= $arResult["FORM_ERRORS_TEXT"]; ?>
<?php endif; ?>

<?= $arResult["FORM_NOTE"] ?? ''; ?>

<?php if ($arResult["isFormNote"] != "Y"): ?>
    <?= $arResult["FORM_HEADER"] ?>

    <div class="contact-form">
        <div class="contact-form__head">
            <div class="contact-form__head-title">Связаться</div>
            <div class="contact-form__head-text">
                Наши сотрудники помогут выполнить подбор услуги и&nbsp;расчет цены с&nbsp;учетом
            ваших требований
            </div>
        </div>

        <div class="contact-form__form">
            <div class="contact-form__form-inputs">
                <?php foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                    <?php
                    $fieldType = $arQuestion["STRUCTURE"][0]["FIELD_TYPE"] ?? "";

                    if ($fieldType == "hidden") {
                        echo $arQuestion["HTML_CODE"];
                        continue;
                    }

                    if ($fieldType == "textarea") {
                        continue;
                    }

                    $fieldHtml = setFieldClass($arQuestion["HTML_CODE"], "input__input");
                    ?>

                    <div class="input contact-form__input">
                        <label class="input__label">
                            <div class="input__label-text">
                                <?= $arQuestion["CAPTION"] ?>
                                <?php if ($arQuestion["REQUIRED"] == "Y"): ?>
                                    <?= $arResult["REQUIRED_SIGN"]; ?>
                                <?php endif; ?>
                            </div>

                            <?= $fieldHtml ?>

                            <?php if (isset($arResult["FORM_ERRORS"][$FIELD_SID])): ?>
                                <div class="input__notification"><?= htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID]) ?></div>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="contact-form__form-message">
                <?php foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                    <?php
                    $fieldType = $arQuestion["STRUCTURE"][0]["FIELD_TYPE"] ?? "";

                    if ($fieldType != "textarea") {
                        continue;
                    }

                    $fieldHtml = setFieldClass($arQuestion["HTML_CODE"], "input__input");
                    ?>

                    <div class="input">
                        <label class="input__label">
                            <div class="input__label-text">
                                <?= $arQuestion["CAPTION"] ?>
                                <?php if ($arQuestion["REQUIRED"] == "Y"): ?>
                                    <?= $arResult["REQUIRED_SIGN"] ?>
                                <?php endif; ?>
                            </div>

                            <?= $fieldHtml ?>

                            <?php if (isset($arResult["FORM_ERRORS"][$FIELD_SID])): ?>
                                <div class="input__notification"><?= htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID]) ?></div>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($arResult["isUseCaptcha"] == "Y"): ?>
                <div class="input contact-form__input">
                    <label class="input__label">
                        <div class="input__label-text"><?= GetMessage("FORM_CAPTCHA_TABLE_TITLE") ?></div>

                        <input type="hidden" name="captcha_sid" value="<?= htmlspecialcharsbx($arResult["CAPTCHA_CODE"]) ?>">
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($arResult["CAPTCHA_CODE"]) ?>" width="180" height="40" alt="">

                        <div class="input__label-text">
                            <?= GetMessage("FORM_CAPTCHA_FIELD_TITLE") ?>
                            <?= $arResult["REQUIRED_SIGN"] ?>
                        </div>

                        <input type="text" name="captcha_word" class="input__input" value="">
                    </label>
                </div>
            <?php endif; ?>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">
                    Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.
                </div>

                <button class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" value="<?= htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == "" ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]); ?>" <?= (intval($arResult["F_RIGHT"]) < 10) ? 'disabled="disabled"' : ''; ?>>
                    <div class="form-button__title">
                        <?= htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == "" ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]); ?>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <?= $arResult["FORM_FOOTER"] ?>
<?php endif; ?>