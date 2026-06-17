<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

if (empty($currentArticle)) {
    return;
}

require __DIR__.'/content.php';
include dirname(__DIR__, 2).'/sidebar.php';
