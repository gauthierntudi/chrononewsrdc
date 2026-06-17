<?php

declare(strict_types=1);

require_once __DIR__.'/brand.php';

$favicon = cn_favicon();
?>
<link rel="icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" type="image/jpeg" sizes="32x32">
<link rel="icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" type="image/jpeg" sizes="192x192">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>">
<meta name="msapplication-TileImage" content="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>">
