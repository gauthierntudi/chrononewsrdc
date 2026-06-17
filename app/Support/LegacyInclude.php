<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class LegacyInclude
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public static function render(string $relativePath, array $variables = []): string
    {
        $root = ProjectPaths::root();
        $path = $root.'/'.ltrim($relativePath, '/');

        if (! is_file($path)) {
            return '';
        }

        LegacySessionBridge::bootstrapStandalone();

        $legacyBootstrap = $root.'/includes/legacy-paths.php';
        if (is_file($legacyBootstrap)) {
            require_once $legacyBootstrap;
        }
        $legacyConfig = $root.'/includes/legacy-config.php';
        if (is_file($legacyConfig)) {
            require_once $legacyConfig;
        }
        $legacyDatabase = $root.'/includes/legacy-database.php';
        if (is_file($legacyDatabase)) {
            require_once $legacyDatabase;
        }

        $legacyCategories = $root.'/includes/categories.php';
        if (is_file($legacyCategories)) {
            require_once $legacyCategories;
        }

        $legacyBrand = $root.'/includes/brand.php';
        if (is_file($legacyBrand)) {
            require_once $legacyBrand;
        }

        $legacyHelpers = $root.'/includes/legacy-helpers.php';
        if (is_file($legacyHelpers)) {
            require_once $legacyHelpers;
        }

        $socialLinks = $root.'/includes/social-links.php';
        if (is_file($socialLinks)) {
            require_once $socialLinks;
        }

        $mediaUrl = $root.'/includes/media-url.php';
        if (is_file($mediaUrl)) {
            require_once $mediaUrl;
        }

        $db = DB::connection()->getPdo();
        $GLOBALS['db'] = $db;

        foreach ($variables as $key => $value) {
            $$key = $value;
        }

        ob_start();

        try {
            include $path;
        } finally {
            // noop
        }

        return ob_get_clean() ?: '';
    }
}
