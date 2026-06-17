<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Politique de confidentialité — '.cn_site_name();
$cnMetaDescription = 'Politique de confidentialité et protection des données personnelles sur Chrono News.';
$cnMetaUrl = cn_site_url().'/politique-de-confidentialite';
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
