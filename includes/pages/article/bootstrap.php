<?php

declare(strict_types=1);

/** @var PDO $db Fourni par LegacyInclude */

require_once dirname(__DIR__, 2).'/legacy-config.php';
require_once dirname(__DIR__, 2).'/legacy-database.php';
require_once dirname(__DIR__, 2).'/legacy-auth.php';
require_once dirname(__DIR__, 2).'/article_loader.php';
require_once dirname(__DIR__, 2).'/auto_tagging.php';

$article_id = isset($article_id) ? (int) $article_id : (int) ($_GET['article'] ?? 0);
$skip_view_increment = (bool) ($skip_view_increment ?? false);

if ($article_id <= 0) {
    $currentArticle = null;

    return;
}

$currentArticle = load_article($article_id);

if (! $currentArticle) {
    return;
}

$auth = null;
$user = cn_legacy_current_user($db);
$is_logged_in = $user !== null && $user !== false;
$has_access = false;
$is_paid_article = isset($currentArticle['is_paid']) && (int) $currentArticle['is_paid'] === 1;

$default_price = defined('ARTICLE_PRICE') ? ARTICLE_PRICE : 0.0;

try {
    $price_stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'default_article_price'");
    $price_stmt->execute();
    if ($row = $price_stmt->fetch(PDO::FETCH_ASSOC)) {
        $default_price = (float) $row['setting_value'];
    }
} catch (Exception $e) {
    // Fallback config
}

$article_price = (isset($currentArticle['price']) && (float) $currentArticle['price'] > 0)
    ? (float) $currentArticle['price']
    : $default_price;

if (! $is_paid_article || ($user && ($user['role'] === 'admin' || $user['role'] === 'superadmin' || (int) $user['id'] === (int) $currentArticle['id_redaction']))) {
    $has_access = true;
} elseif ($is_logged_in) {
    $purchase_stmt = $db->prepare("SELECT id FROM user_purchased_articles WHERE user_id = :user_id AND article_id = :article_id AND access_status = 'active'");
    $purchase_stmt->execute(['user_id' => $user['id'], 'article_id' => $article_id]);
    if ($purchase_stmt->rowCount() > 0) {
        $has_access = true;
    }

    if (! $has_access) {
        $sub_stmt = $db->prepare("SELECT id FROM user_subscriptions WHERE user_id = :user_id AND status = 'active' AND end_date > NOW()");
        $sub_stmt->execute(['user_id' => $user['id']]);
        if ($sub_stmt->rowCount() > 0) {
            $has_access = true;
        }
    }
}

if (! $skip_view_increment) {
    increment_article_views($article_id, $currentArticle['vues']);
    $currentArticle['vues_int'] = (int) $currentArticle['vues'] + 1;
} else {
    $currentArticle['vues_int'] = (int) ($currentArticle['vues_int'] ?? $currentArticle['vues'] ?? 0);
}

$prevArticle = get_previous_article($currentArticle['id'], $currentArticle['date_add']);
$nextArticle = get_next_article($currentArticle['id'], $currentArticle['date_add']);
$similarArticles = get_similar_articles($currentArticle['id'], 4);

$excludeHomeIds = [];

$sqlMust = "
SELECT a.*, u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND COALESCE(a.date_add, a.created_at) >= (NOW() - INTERVAL 45 DAY)
ORDER BY RAND()
LIMIT 2
";

$stmt = $db->prepare($sqlMust);
$stmt->execute();
$mustRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$shareUrlRaw = cn_site_url().cn_article_page_url($article_id, (string) $currentArticle['titre']);
$shareUrl = urlencode($shareUrlRaw);
$shareTitle = urlencode(clean_title($currentArticle['titre']));
$covers_share = parse_cover_images($currentArticle['cover'] ?? null);
$shareImage = ! empty($covers_share)
    ? urlencode(cn_media_url($covers_share[0]))
    : '';

$links = [
    'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}",
    'twitter' => "https://twitter.com/intent/tweet?text={$shareTitle}&url={$shareUrl}",
    'pinterest' => "https://pinterest.com/pin/create/button/?url={$shareUrl}&media={$shareImage}&description={$shareTitle}",
    'linkedin' => "https://www.linkedin.com/shareArticle?mini=true&url={$shareUrl}&title={$shareTitle}",
    'whatsapp' => "https://api.whatsapp.com/send?text={$shareTitle}%20{$shareUrl}",
    'flipboard' => "https://share.flipboard.com/bookmarklet/popout?v=2&title={$shareTitle}&url={$shareUrl}",
    'telegram' => "https://telegram.me/share/url?url={$shareUrl}&text={$shareTitle}",
    'tumblr' => "https://www.tumblr.com/share/link?url={$shareUrl}&name={$shareTitle}",
    'email' => "mailto:?subject={$shareTitle}&body={$shareUrl}",
    'line' => "https://social-plugins.line.me/lineit/share?url={$shareUrl}",
];
