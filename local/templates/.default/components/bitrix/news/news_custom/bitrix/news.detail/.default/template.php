<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div class="article-card">
    <div class="article-card__title">
        <?=htmlspecialcharsbx($arResult["NAME"])?>
    </div>

    <?php if (!empty($arResult["IBLOCK_SECTION_ID"])): ?>
        <?php
        $section = CIBlockSection::GetByID(
            $arResult["IBLOCK_SECTION_ID"]
        )->Fetch();
        ?>

        <?php if ($section): ?>
            <div class="article-card__section">
                <?=htmlspecialcharsbx($section["NAME"])?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($arResult["DISPLAY_ACTIVE_FROM"])): ?>
        <div class="article-card__date">
            <?=$arResult["DISPLAY_ACTIVE_FROM"]?>
        </div>
    <?php endif; ?>

    <div class="article-card__content">

        <?php if (!empty($arResult["DETAIL_PICTURE"])): ?>
            <div class="article-card__image sticky">
                <img
                    src="<?=htmlspecialcharsbx($arResult["DETAIL_PICTURE"]["SRC"])?>"
                    alt="<?=htmlspecialcharsbx($arResult["NAME"])?>"
                    data-object-fit="cover"
                >
            </div>
        <?php endif; ?>

        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <?php if (!empty($arResult["DETAIL_TEXT"])): ?>
                    <?=$arResult["DETAIL_TEXT"]?>
                <?php else: ?>
                    <?=$arResult["PREVIEW_TEXT"]?>
                <?php endif; ?>
            </div>

            <a
                class="article-card__button"
                href="<?=$arResult["LIST_PAGE_URL"]?>"
            >
                Назад к новостям
            </a>
        </div>

    </div>
</div>
