<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class Categories
{
    /** @return list<string> */
    public static function all(): array
    {
        return config('chrononews.article.categories', [
            'Actualités', 'Institutions', 'Politique', 'Économie',
            'Justice & Sécurité', 'Développement & Infrastructures',
            'Société', 'International', 'Sport', 'Interviews', 'Décryptage',
        ]);
    }

    /** @return array<string, string> */
    public static function slugs(): array
    {
        $legacy = ProjectPaths::root().'/includes/categories.php';
        if (is_file($legacy)) {
            require_once $legacy;
            if (function_exists('chrononews_category_slugs')) {
                return chrononews_category_slugs();
            }
        }

        return [
            'Actualités' => 'actualites',
            'Institutions' => 'institutions',
            'Politique' => 'politique',
            'Économie' => 'economie',
            'Justice & Sécurité' => 'justice-securite',
            'Développement & Infrastructures' => 'developpement-infrastructures',
            'Société' => 'societe',
            'International' => 'international',
            'Sport' => 'sport',
            'Interviews' => 'interviews',
            'Décryptage' => 'decryptage',
        ];
    }

    public static function fromSlug(string $segment): ?string
    {
        $segment = trim(urldecode($segment));
        if ($segment === '') {
            return null;
        }

        $bySlug = array_flip(self::slugs());
        if (isset($bySlug[$segment])) {
            return $bySlug[$segment];
        }

        if ($segment === 'opinions') {
            return 'Décryptage';
        }

        foreach (self::all() as $name) {
            if ($name === $segment || self::normalize($segment) === $name) {
                return $name;
            }
        }

        return null;
    }

    public static function normalize(?string $category): string
    {
        $category = trim((string) $category);

        return match ($category) {
            'Opinions', 'Opinion' => 'Décryptage',
            default => $category,
        };
    }

    public static function color(?string $category): string
    {
        $category = self::normalize($category);
        $map = [
            'Actualités' => '#d11810',
            'Institutions' => '#1E5EFF',
            'Politique' => '#e6a406',
            'Économie' => '#ce5105',
            'Justice & Sécurité' => '#434547',
            'Développement & Infrastructures' => '#09b960',
            'Société' => '#6709dc',
            'International' => '#0457d3',
            'Sport' => '#059669',
            'Interviews' => '#2b3a6c',
            'Décryptage' => '#626a6b',
        ];

        return $map[$category] ?? '#d11810';
    }

    public static function slug(string $category): string
    {
        $map = self::slugs();

        return $map[self::normalize($category)] ?? \Illuminate\Support\Str::slug($category, '-', 'fr') ?: 'actualites';
    }

    public static function url(string $category): string
    {
        return route('categories.show', ['category' => self::slug($category)]);
    }

    public static function isValid(?string $category): bool
    {
        return in_array(self::normalize($category), self::all(), true);
    }

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            'Actualités' => 'Les faits marquants et l\'actualité du moment.',
            'Institutions' => 'Gouvernance, institutions et décisions publiques.',
            'Politique' => 'Analyses et dossiers politiques.',
            'Économie' => 'Économie, finance et marchés.',
            'Justice & Sécurité' => 'Justice, sécurité et ordre public.',
            'Développement & Infrastructures' => 'Projets, infrastructures et développement.',
            'Société' => 'Vie sociale, culture et communautés.',
            'International' => 'Actualité internationale et relations extérieures.',
            'Sport' => 'Sport et compétitions.',
            'Interviews' => 'Entretiens et portraits.',
            'Décryptage' => 'Analyses approfondies et décryptages de l\'actualité.',
        ];
    }
}
