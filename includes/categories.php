<?php

declare(strict_types=1);

if (! function_exists('chrononews_categories')) {
    /** @return list<string> */
    function chrononews_categories(): array
    {
        return [
            'Actualités',
            'Institutions',
            'Politique',
            'Économie',
            'Justice & Sécurité',
            'Développement & Infrastructures',
            'Société',
            'International',
            'Sport',
            'Interviews',
            'Décryptage',
        ];
    }
}

if (! function_exists('chrononews_category_colors')) {
    /** @return array<string, string> */
    function chrononews_category_colors(): array
    {
        return [
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
    }
}

if (! function_exists('category_normalize')) {
    /** Ancien libellé « Opinions » → « Décryptage ». */
    function category_normalize(?string $cat): string
    {
        $cat = trim((string) $cat);

        return match ($cat) {
            'Opinions', 'Opinion' => 'Décryptage',
            default => $cat,
        };
    }
}

if (! function_exists('category_color')) {
    function category_color(?string $cat): string
    {
        $map = chrononews_category_colors();

        return $map[category_normalize($cat)] ?? '#d11810';
    }
}

if (! function_exists('chrononews_category_slugs')) {
    /** Slugs URL stables (sans accents, espaces, &). */
    /** @return array<string, string> nom affiché => slug */
    function chrononews_category_slugs(): array
    {
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
}

if (! function_exists('category_slug')) {
    function category_slug(string $cat): string
    {
        $map = chrononews_category_slugs();

        return $map[category_normalize($cat)] ?? slugify($cat, 'actualites');
    }
}

if (! function_exists('category_from_slug')) {
    /** Résout un segment d’URL (slug ou ancien nom encodé) vers le nom affiché. */
    function category_from_slug(string $segment): ?string
    {
        $segment = trim(urldecode($segment));
        if ($segment === '') {
            return null;
        }

        $bySlug = array_flip(chrononews_category_slugs());
        if (isset($bySlug[$segment])) {
            return $bySlug[$segment];
        }

        if ($segment === 'opinions') {
            return 'Décryptage';
        }

        foreach (chrononews_categories() as $name) {
            if ($name === $segment || category_normalize($segment) === $name) {
                return $name;
            }
        }

        return null;
    }
}

if (! function_exists('category_url')) {
    function category_url(string $cat): string
    {
        return '/categorie/'.category_slug($cat);
    }
}

if (! function_exists('pick_categories_for_you')) {
    /** @return list<string> */
    function pick_categories_for_you(int $seed, int $count = 4): array
    {
        $cats = chrononews_categories();
        $n = count($cats);

        for ($i = $n - 1; $i > 0; $i--) {
            $j = ($seed + $i * 1103515245) % ($i + 1);
            [$cats[$i], $cats[$j]] = [$cats[$j], $cats[$i]];
        }

        return array_slice($cats, 0, max(1, min($count, $n)));
    }
}

if (! function_exists('chrononews_category_descriptions')) {
    /** @return array<string, string> */
    function chrononews_category_descriptions(): array
    {
        return [
            'Actualités' => 'Les dernières informations et faits marquants de l\'actualité.',
            'Institutions' => 'Vie institutionnelle, gouvernance et décisions publiques.',
            'Politique' => 'Analyses et décryptages de l\'actualité politique.',
            'Économie' => 'Économie, finances et marchés au cœur de l\'information.',
            'Justice & Sécurité' => 'Faits judiciaires, sécurité publique et ordre social.',
            'Développement & Infrastructures' => 'Grands projets, travaux publics et aménagement du territoire.',
            'Société' => 'Faits de société, culture et vie quotidienne.',
            'International' => 'Actualité internationale et relations extérieures.',
            'Sport' => 'Résultats, compétitions et actualité sportive.',
            'Interviews' => 'Entretiens exclusifs avec les acteurs de l\'actualité.',
            'Décryptage' => 'Analyses approfondies et décryptages de l\'actualité.',
        ];
    }
}

if (! function_exists('category_description')) {
    function category_description(string $cat): string
    {
        return chrononews_category_descriptions()[category_normalize($cat)] ?? '';
    }
}
