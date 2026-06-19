<?php

declare(strict_types=1);

/** @var PDO $db Fourni par LegacyInclude */
/** @var int $article_id */

require_once dirname(__DIR__, 2).'/brand.php';

$article_id = (int) ($article_id ?? 0);

$stmt = $db->prepare("
    SELECT a.titre, a.contenu, a.date_add, a.date_update, a.created_at, u.nom AS auteur_nom
    FROM actualites a
    LEFT JOIN users u ON u.id = a.id_redaction
    WHERE a.id = :id
      AND a.status = 1
      AND a.statut_validation = 'valide'
      AND a.statut_paiement IN ('paye','gratuit')
    LIMIT 1
");
$stmt->execute(['id' => $article_id]);
$metaArticle = $stmt->fetch(PDO::FETCH_ASSOC);

if (! $metaArticle) {
    return;
}

$cnMetaTitle = clean_title($metaArticle['titre']).' — '.cn_site_name();
$cnMetaDescription = excerpt($metaArticle['contenu'], 160);
$cnMetaUrl = cn_site_url().cn_article_page_url($article_id, (string) $metaArticle['titre']);
$cnMetaType = 'article';
$cnMetaOgImage = cn_og_image($article_id);
$cnMetaOgImageAlt = clean_title($metaArticle['titre']);

include dirname(__DIR__, 2).'/front-meta.php';
?>
<meta property="article:published_time" content="<?= htmlspecialchars(date('c', strtotime($metaArticle['date_add'] ?? $metaArticle['created_at'])), ENT_QUOTES, 'UTF-8') ?>">
<?php if (! empty($metaArticle['date_update'])): ?>
<meta property="article:modified_time" content="<?= htmlspecialchars(date('c', strtotime($metaArticle['date_update'])), ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<meta name="author" content="<?= htmlspecialchars($metaArticle['auteur_nom'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:label1" content="Written by">
<meta name="twitter:data1" content="<?= htmlspecialchars($metaArticle['auteur_nom'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?>">
