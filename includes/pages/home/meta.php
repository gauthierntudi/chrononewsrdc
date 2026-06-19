<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Accueil — '.cn_site_name();
$cnMetaDescription = cn_tagline();
$cnMetaUrl = cn_site_url().'/';
$cnMetaType = 'website';
$cnMetaOgImageAlt = cn_site_name().' — '.cn_tagline();

include dirname(__DIR__, 2).'/front-meta.php';
