<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

/** @var string $category_slug */
$cnMetaTitle = $category_slug.' — '.cn_site_name();
$cnMetaDescription = cn_tagline();
$cnMetaUrl = cn_site_url().category_url($category_slug);
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
