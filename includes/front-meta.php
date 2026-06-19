<?php

declare(strict_types=1);

require_once __DIR__.'/brand.php';

/** @var string|null $cnMetaTitle Titre OG (sans suffixe site) */
/** @var string|null $cnMetaDescription */
/** @var string|null $cnMetaUrl URL canonique / og:url */
/** @var string|null $cnMetaType og:type — website | article */
/** @var string|null $cnMetaOgImage URL absolue image OG (1200×630) */
/** @var string|null $cnMetaOgImageAlt Texte alternatif og:image / twitter:image:alt */

$cnMetaTitle = $cnMetaTitle ?? cn_site_name();
$cnMetaDescription = $cnMetaDescription ?? cn_tagline();
$cnMetaUrl = $cnMetaUrl ?? cn_site_url();
$cnMetaType = $cnMetaType ?? 'website';
$cnMetaOgImage = $cnMetaOgImage ?? cn_og_image();
$cnMetaOgImageAlt = $cnMetaOgImageAlt ?? cn_site_name();

$cnMetaOgImageEsc = htmlspecialchars($cnMetaOgImage, ENT_QUOTES, 'UTF-8');
$cnMetaOgImageAltEsc = htmlspecialchars($cnMetaOgImageAlt, ENT_QUOTES, 'UTF-8');
$cnMetaOgSecure = str_starts_with($cnMetaOgImage, 'https://');
?>
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<meta name="theme-color" content="<?= htmlspecialchars(CN_THEME_COLOR, ENT_QUOTES, 'UTF-8') ?>">
<meta name="description" content="<?= htmlspecialchars($cnMetaDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= $cnMetaOgImageEsc ?>">
<?php if ($cnMetaOgSecure): ?>
<meta property="og:image:secure_url" content="<?= $cnMetaOgImageEsc ?>">
<?php endif; ?>
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= $cnMetaOgImageAltEsc ?>">
<meta property="og:title" content="<?= htmlspecialchars($cnMetaTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($cnMetaUrl, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:site_name" content="<?= htmlspecialchars(cn_site_name(), ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($cnMetaDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="<?= htmlspecialchars($cnMetaType, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="<?= $cnMetaOgImageEsc ?>">
<meta name="twitter:image:alt" content="<?= $cnMetaOgImageAltEsc ?>">
<meta name="twitter:site" content="<?= htmlspecialchars(CN_TWITTER_SITE, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:creator" content="<?= htmlspecialchars(CN_TWITTER_CREATOR, ENT_QUOTES, 'UTF-8') ?>">
