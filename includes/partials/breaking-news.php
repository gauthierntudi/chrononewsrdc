<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/brand.php';
require_once dirname(__DIR__).'/categories.php';
require_once dirname(__DIR__).'/legacy-helpers.php';

if (! isset($db) || ! $db instanceof PDO) {
    require_once dirname(__DIR__).'/legacy-database.php';
    $db = Database::getInstance()->getConnection();
}

if (! legacy_front_schema_ready($db)) {
    return;
}

if (! cn_breaking_news_enabled($db)) {
    return;
}

$limit = 12;
$stmt = $db->prepare("
    SELECT a.id, a.titre, a.categorie
    FROM actualites a
    WHERE a.status = 1
      AND a.statut_validation = 'valide'
      AND a.statut_paiement IN ('paye', 'gratuit')
    ORDER BY COALESCE(a.date_add, a.created_at) DESC
    LIMIT :limit
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$breakingArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($breakingArticles === []) {
    return;
}

$breakingItems = [];
foreach ($breakingArticles as $row) {
    $id = (int) ($row['id'] ?? 0);
    $title = clean_title((string) ($row['titre'] ?? ''));
    if ($id <= 0 || $title === '') {
        continue;
    }

    $breakingItems[] = [
        'id' => $id,
        'title' => $title,
        'category' => category_normalize((string) ($row['categorie'] ?? '')),
        'url' => cn_article_page_url($id, $title),
    ];
}

if ($breakingItems === []) {
    return;
}

$breakingNewsLayout = ($breakingNewsLayout ?? 'inner') === 'home' ? 'home' : 'inner';
?>
<div class="cn-breaking-news cn-breaking-news--<?= $breakingNewsLayout ?>" role="region" aria-label="Breaking News">
    <div class="cn-breaking-news__container">
        <div class="cn-breaking-news__bar">
            <div class="cn-breaking-news__label" aria-hidden="true">
                <span class="cn-breaking-news__dot"></span>
                <span class="cn-breaking-news__label-text cn-breaking-news__label-text--full">Breaking News</span>
                <span class="cn-breaking-news__label-text cn-breaking-news__label-text--short">News</span>
            </div>
            <div class="cn-breaking-news__track swiper cn-breaking-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($breakingItems as $item): ?>
                        <div class="swiper-slide cn-breaking-news__slide">
                            <a class="cn-breaking-news__link" href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($item['category'] !== ''): ?>
                                    <span class="cn-breaking-news__cat"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <span class="cn-breaking-news__title"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
