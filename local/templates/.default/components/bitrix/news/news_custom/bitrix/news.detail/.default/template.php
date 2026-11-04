
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$section = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"])->Fetch();
?>

<div class="article-card">
    <div class="article-card__title">
        <?= htmlspecialcharsbx($arResult["NAME"]) ?>
    </div>

    <?php if ($section): ?>
        <div class="article-card__section">
            <?= htmlspecialcharsbx($section["NAME"]) ?>
        </div>
    <?php endif; ?>

    <div class="article-card__date">
        <?= $arResult["DISPLAY_ACTIVE_FROM"] ?>
    </div>

    <div class="article-card__content">

        <?php if ($arResult["DETAIL_PICTURE"]): ?>
            <div class="article-card__image sticky">
                <img
                    src="<?= htmlspecialcharsbx($arResult["DETAIL_PICTURE"]["SRC"]) ?>"
                    alt="<?= htmlspecialcharsbx($arResult["NAME"]) ?>"
                    data-object-fit="cover"
                >
            </div>
        <?php endif; ?>

        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <?= $arResult["DETAIL_TEXT"] ?: $arResult["PREVIEW_TEXT"] ?>
            </div>

            <a class="article-card__button" href="<?= $arResult["LIST_PAGE_URL"] ?>">
                Назад к новостям
            </a>
        </div>

    </div>
</div>
