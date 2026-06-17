<?php

namespace App\Support;

final class ProjectPaths
{
    /**
     * Racine du projet (includes/, templates legacy).
     * Par défaut : laravel/ si includes/ est présent, sinon le parent (monorepo local).
     */
    public static function root(): string
    {
        $configured = env('CHRONONEWS_ROOT');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/\\');
        }

        if (is_dir(base_path('includes'))) {
            return base_path();
        }

        return dirname(base_path());
    }
}
