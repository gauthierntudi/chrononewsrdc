<?php

declare(strict_types=1);

if (! function_exists('cn_repo_root')) {
    function cn_repo_root(): string
    {
        if (defined('CN_REPO_ROOT')) {
            return CN_REPO_ROOT;
        }

        $includesDir = dirname(__DIR__);

        if (is_dir($includesDir.'/../app')) {
            $root = dirname($includesDir);
        } else {
            $root = dirname($includesDir, 2);
        }

        define('CN_REPO_ROOT', $root);

        return $root;
    }
}

if (! function_exists('cn_publication_root')) {
    function cn_publication_root(): string
    {
        if (defined('CN_PUBLICATION_ROOT')) {
            return CN_PUBLICATION_ROOT;
        }

        foreach ([cn_repo_root().'/publication', dirname(cn_repo_root()).'/publication'] as $path) {
            if (is_dir($path)) {
                define('CN_PUBLICATION_ROOT', $path);

                return $path;
            }
        }

        define('CN_PUBLICATION_ROOT', cn_repo_root().'/publication');

        return CN_PUBLICATION_ROOT;
    }
}
