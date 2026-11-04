<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
$sectionId = (int)($_GET["SECTION_ID"] ?? 0);
$sections = CIBlockSection::GetList(
    ["SORT" => "ASC"],
    [
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ACTIVE" => "Y"
    ]
);
?>
<div class="article-sections">
    <a href="/" class="<?= $sectionId === 0 ? "active" : "" ?>">
        Все новости
    </a>

    <?php while ($section = $sections->Fetch()): ?>
        <a
            href="/?SECTION_ID=<?= $section["ID"] ?>"
            class="<?= $sectionId === (int)$section["ID"] ? "active" : "" ?>"
        >
            <?= htmlspecialcharsbx($section["NAME"]) ?>
        </a>
    <?php endwhile; ?>
</div>
<div class="article-list">
    <?php foreach ($arResult["ITEMS"] as $arItem): ?>
        <?php
        $section = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"])->Fetch();
        ?>
        <a class="article-item article-list__item" href="<?= $arItem["DETAIL_PAGE_URL"] ?>" data-anim="anim-3">
            <div class="article-item__background">
                <img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>" alt="<?= $arItem["NAME"] ?>">
            </div>

            <div class="article-item__wrapper">
                <?php if ($section): ?>
                    <div class="article-item__category">
                        <?= htmlspecialcharsbx($section["NAME"]) ?>
                    </div>
                <?php endif; ?>
                <div class="article-item__title">
                    <?= $arItem["NAME"] ?>
                </div>

                <div class="article-item__content">
                    <?= $arItem["PREVIEW_TEXT"] ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>