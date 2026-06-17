<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Juste pour vous — '.cn_site_name();
$cnMetaDescription = 'Une sélection d\'articles personnalisée pour vous sur '.cn_site_name().' — '.cn_tagline();
$cnMetaUrl = cn_site_url().cn_just_for_you_page_url(1);
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
