<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Qui sommes-nous ? — '.cn_site_name();
$cnMetaDescription = 'Découvrez Chrono News, média d\'actualités générales — '.cn_tagline();
$cnMetaUrl = cn_site_url().'/qui-sommes-nous';
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
