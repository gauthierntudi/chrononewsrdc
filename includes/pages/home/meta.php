<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Accueil — '.cn_site_name();
$cnMetaUrl = cn_site_url().'/';
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
