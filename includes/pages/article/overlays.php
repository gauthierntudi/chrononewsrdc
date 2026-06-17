<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

if (empty($currentArticle)) {
    return;
}

require __DIR__.'/overlays-part1.php';
require __DIR__.'/overlays-payment.php';
