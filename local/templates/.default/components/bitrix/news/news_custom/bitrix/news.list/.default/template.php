
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>
<?php
$selectedSection = (int)($_GET["SECTION_ID"] ?? 0);

$sections = CIBlockSection::GetList(
    ["SORT" => "ASC"],
    [
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ACTIVE" => "Y",
        "GLOBAL_ACTIVE" => "Y"
    ],
    false,
    ["ID", "NAME"]
);
?>

<div class="news-sections">
    <a href="/" class="<?=$selectedSection === 0 ? 'active' : ''?>">
        Все новости
    </a>

    <?php while ($section = $sections->Fetch()): ?>
        <a
            href="/?SECTION_ID=<?=$section["ID"]?>"
            class="<?=$selectedSection === (int)$section["ID"] ? 'active' : ''?>"
        >
            <?=htmlspecialcharsbx($section["NAME"])?>
        </a>
    <?php endwhile; ?>
</div>
<div class="news-list">

    <?php if ($arParams["DISPLAY_TOP_PAGER"]): ?>
        <?=$arResult["NAV_STRING"]?>
    <?php endif; ?>
    <?php if (isset($_GET["SECTION_ID"]) && (int)$_GET["SECTION_ID"] > 0): ?>
        <div class="article-card__section">
            <a href="/">
                Все новости
            </a>
        </div>
    <?php endif; ?>
    <?php foreach ($arResult["ITEMS"] as $arItem): ?>

        <?php
        $this->AddEditAction(
            $arItem["ID"],
            $arItem["EDIT_LINK"],
            CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT")
        );

        $this->AddDeleteAction(
            $arItem["ID"],
            $arItem["DELETE_LINK"],
            CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"),
            ["CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")]
        );

        $sectionName = "";

        if (!empty($arItem["IBLOCK_SECTION_ID"])) {
            $section = CIBlockSection::GetByID(
                $arItem["IBLOCK_SECTION_ID"]
            )->Fetch();

            if ($section) {
                $sectionName = $section["NAME"];
            }
        }
        ?>

        <div class="article-card" id="<?=$this->GetEditAreaId($arItem["ID"])?>">
            <div class="article-card__content">

                <?php if (
                    $arParams["DISPLAY_PICTURE"] != "N"
                    && is_array($arItem["PREVIEW_PICTURE"])
                ): ?>
                    <div class="article-card__image">
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                            <img
                                src="<?=htmlspecialcharsbx($arItem["PREVIEW_PICTURE"]["SRC"])?>"
                                alt="<?=htmlspecialcharsbx($arItem["NAME"])?>"
                            >
                        </a>
                    </div>
                <?php endif; ?>

                <div class="article-card__text">

                    <?php if ($sectionName): ?>
                        <div class="article-card__section">
                            <a href="/?SECTION_ID=<?=$arItem["IBLOCK_SECTION_ID"]?>">
                                <?=htmlspecialcharsbx($sectionName)?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (
                        $arParams["DISPLAY_NAME"] != "N"
                        && $arItem["NAME"]
                    ): ?>
                        <div class="article-card__title">
                            <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                                <?=htmlspecialcharsbx($arItem["NAME"])?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (
                        $arParams["DISPLAY_DATE"] != "N"
                        && $arItem["DISPLAY_ACTIVE_FROM"]
                    ): ?>
                        <div class="article-card__date">
                            <?=$arItem["DISPLAY_ACTIVE_FROM"]?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    <?php endforeach; ?>

    <?php if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <?=$arResult["NAV_STRING"]?>
    <?php endif; ?>

</div>
