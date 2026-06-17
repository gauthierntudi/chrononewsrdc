<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

/** @var string $q */
$label = $q !== '' ? 'Recherche : '.$q : 'Recherche';
$cnMetaTitle = $label.' — '.cn_site_name();
$cnMetaDescription = cn_tagline();
$cnMetaUrl = cn_site_url().cn_search_page_url($q, 1);
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
