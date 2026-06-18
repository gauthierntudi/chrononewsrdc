<?php

declare(strict_types=1);

require_once __DIR__.'/media-url.php';

if (! function_exists('legacy_front_schema_ready')) {
    /**
     * Le front legacy interroge `actualites` / `publicites`.
     * Sur une base vide (migrations Laravel seules), ces tables n'existent pas.
     */
    function legacy_front_schema_ready(?PDO $connection = null): bool
    {
        static $ready = null;

        if ($ready !== null) {
            return $ready;
        }

        global $db;
        $pdo = $connection ?? ($db ?? null);

        if (! $pdo instanceof PDO) {
            $ready = false;

            return $ready;
        }

        try {
            $pdo->query('SELECT 1 FROM actualites LIMIT 1');
            $ready = true;
        } catch (Throwable) {
            $ready = false;
        }

        return $ready;
    }
}

if (! function_exists('clean_title')) {
    function clean_title(?string $title): string
    {
        if ($title === null) {
            return '';
        }

        return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (! function_exists('slugify')) {
    function slugify(?string $text, string $fallback = 'article', array $options = []): string
    {
        $defaults = [
            'separator' => '-',
            'lowercase' => true,
            'trim' => true,
            'remove_stop_words' => false,
            'max_length' => null,
            'preserve_emojis' => false,
        ];

        $options = array_merge($defaults, $options);

        if (empty($text)) {
            return $fallback;
        }

        $text = clean_title($text);

        if ($options['lowercase']) {
            $text = mb_strtolower($text, 'UTF-8');
        }

        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        }

        $pattern = $options['preserve_emojis']
            ? '~[^\p{L}\p{N}\p{Emoji}'.preg_quote($options['separator'], '~').']+~u'
            : '~[^\p{L}\p{N}'.preg_quote($options['separator'], '~').']+~u';

        $text = preg_replace($pattern, $options['separator'], $text);
        $text = preg_replace('~[\s_]+~', $options['separator'], $text);
        $text = preg_replace('~'.preg_quote($options['separator'], '~').'+~', $options['separator'], $text);

        if ($options['trim']) {
            $text = trim($text, $options['separator']);
        }

        if ($options['max_length'] && mb_strlen($text) > $options['max_length']) {
            $text = mb_substr($text, 0, $options['max_length']);
            $text = rtrim($text, $options['separator']);
        }

        return $text ?: $fallback;
    }
}

if (! function_exists('parse_cover_images')) {
    function parse_cover_images(?string $cover): array
    {
        if ($cover === null) {
            return [];
        }

        $parts = array_map('trim', explode(',', $cover));

        return array_values(array_filter($parts, fn ($part) => $part !== ''));
    }
}

if (! function_exists('excerpt')) {
    function excerpt(?string $content, int $max = 160): string
    {
        if (! $content) {
            return '';
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($content)) ?? '');

        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1, 'UTF-8').'…';
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }

        $timestamp = strtotime($datetime);

        return $timestamp ? date('M j, Y', $timestamp) : '';
    }
}

if (! function_exists('vues_int')) {
    function vues_int(mixed $views): int
    {
        $digits = preg_replace('/[^\d]/', '', (string) $views);

        return $digits === '' ? 0 : (int) $digits;
    }
}

if (! function_exists('has_video')) {
    function has_video(array $row): bool
    {
        $videos = trim((string) ($row['videos'] ?? ''));

        return $videos !== '' && $videos !== '0';
    }
}

if (! function_exists('fill_repeat')) {
    function fill_repeat(array $rows, int $needed): array
    {
        if (count($rows) >= $needed) {
            return array_slice($rows, 0, $needed);
        }

        if ($rows === []) {
            return [];
        }

        $out = $rows;
        $i = 0;

        while (count($out) < $needed) {
            $out[] = $rows[$i % count($rows)];
            $i++;
        }

        return $out;
    }
}

if (! function_exists('add_exclude_ids')) {
    function add_exclude_ids(array &$bag, $items): void
    {
        foreach ((array) $items as $it) {
            if (is_array($it) && isset($it['id'])) {
                $bag[] = (int) $it['id'];
            }
            if (is_numeric($it)) {
                $bag[] = (int) $it;
            }
        }
        $bag = array_values(array_unique(array_filter($bag)));
    }
}

if (! function_exists('get_session_seed')) {
    function get_session_seed(): int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return random_int(100000, 999999999);
        }

        if (empty($_SESSION['seed_jpv'])) {
            $_SESSION['seed_jpv'] = random_int(100000, 999999999);
        }

        return (int) $_SESSION['seed_jpv'];
    }
}

if (! function_exists('youtube_id_from_url')) {
    function youtube_id_from_url(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('~youtu\.be/([^?&/]+)~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~[?&]v=([^?&/]+)~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~youtube\.com/shorts/([^?&/]+)~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~youtube\.com/embed/([^?&/]+)~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}

if (! function_exists('youtube_thumb')) {
    function youtube_thumb(?string $videos): ?string
    {
        if (! $videos) {
            return null;
        }
        $first = trim(explode(',', $videos)[0] ?? '');
        if ($first === '') {
            return null;
        }
        $id = youtube_id_from_url($first);
        if (! $id) {
            return null;
        }

        return 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg';
    }
}

if (! function_exists('donut_percent')) {
    function donut_percent(int $vues, ?int $base = null): int
    {
        if ($vues <= 0) {
            return 0;
        }

        $base = ($base !== null && $base > 0) ? $base : 2000;
        $p = (int) round(($vues / $base) * 100);

        return max(0, min(100, $p));
    }
}

if (! function_exists('vues_format')) {
    function vues_format(mixed $v): string
    {
        $v = vues_int($v);

        if ($v < 1000) {
            return (string) $v;
        }

        if ($v < 1000000) {
            $k = $v / 1000;

            return ($k == floor($k)) ? $k.'K' : round($k, 1).'K';
        }

        $m = $v / 1000000;

        return ($m == floor($m)) ? $m.'M' : round($m, 1).'M';
    }
}

if (! function_exists('titre_limit')) {
    function titre_limit(mixed $t, int $limit = 60): string
    {
        $t = trim((string) $t);

        if (mb_strlen($t, 'UTF-8') <= $limit) {
            return $t;
        }

        $cut = mb_substr($t, 0, $limit, 'UTF-8');
        $cut = preg_replace('/\s+\S*$/u', '', $cut);

        return $cut.'…';
    }
}

if (! function_exists('cn_category_page_url')) {
    function cn_category_page_url(string $categoryName, int $page): string
    {
        $base = category_url($categoryName);

        return $page <= 1 ? $base : $base.'/'.$page;
    }
}

if (! function_exists('cn_search_page_url')) {
    function cn_search_page_url(string $q, int $page = 1): string
    {
        if ($q === '') {
            return '/recherche';
        }

        $params = 'q='.rawurlencode($q);

        return $page <= 1 ? '/recherche?'.$params : '/recherche?'.$params.'&page='.$page;
    }
}

if (! function_exists('cn_just_for_you_page_url')) {
    function cn_just_for_you_page_url(int $page = 1): string
    {
        return $page <= 1 ? '/juste-pour-vous' : '/juste-pour-vous/'.$page;
    }
}

if (! function_exists('cn_article_page_url')) {
    function cn_article_page_url(int $articleId, ?string $title = null): string
    {
        $slug = slugify(clean_title($title));

        return '/article/'.$articleId.'/'.$slug;
    }
}

if (! function_exists('cn_breaking_news_enabled')) {
    function cn_breaking_news_enabled(?PDO $pdo = null): bool
    {
        static $enabled = null;

        if ($enabled !== null) {
            return $enabled;
        }

        global $db;
        $pdo = $pdo ?? ($db ?? null);
        $enabled = true;

        if (! $pdo instanceof PDO) {
            return $enabled;
        }

        try {
            $stmt = $pdo->prepare('SELECT setting_value FROM global_settings WHERE setting_key = :key LIMIT 1');
            $stmt->execute(['key' => 'breaking_news_enabled']);
            $raw = $stmt->fetchColumn();

            if ($raw === false || $raw === null || $raw === '') {
                return $enabled;
            }

            $enabled = ! in_array(strtolower((string) $raw), ['0', 'false', 'off', 'no'], true);
        } catch (Throwable) {
            $enabled = true;
        }

        return $enabled;
    }
}
