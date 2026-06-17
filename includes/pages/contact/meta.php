<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/brand.php';

$cnMetaTitle = 'Contact — '.cn_site_name();
$cnMetaDescription = 'Contactez la rédaction de Chrono News pour toute question, signalement ou partenariat.';
$cnMetaUrl = cn_site_url().'/contact';
$cnMetaType = 'website';

include dirname(__DIR__, 2).'/front-meta.php';
