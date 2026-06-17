<?php

declare(strict_types=1);

/** @return array<string, array<string, string>> */
function cn_social_network_styles(): array
{
    return [
        'facebook' => [
            'li_class' => 'jl_facebook_url',
            'mobile_li_class' => 'jl_facebook',
            'icon' => 'jli-facebook',
            'aria' => 'facebook',
            'color_header' => '#4080ff',
            'color_rich' => '#4080FF',
            'color_footer' => '#4080FF',
        ],
        'twitter' => [
            'li_class' => 'jl_twitter_url',
            'mobile_li_class' => 'jl_twitter',
            'icon' => 'jli-x',
            'aria' => 'X',
            'color_header' => 'var(--jl-social-skin, #000)',
            'color_rich' => '#292b30',
            'color_footer' => '#bd081c',
            'extra_li_class' => 'jl_dk_sc',
        ],
        'instagram' => [
            'li_class' => 'jl_instagram_url',
            'mobile_li_class' => 'jl_instagram',
            'icon' => 'jli-instagram',
            'aria' => 'instagram',
            'color_header' => '#5e368b',
            'color_rich' => '#e83685',
            'color_footer' => '#5e368b',
        ],
        'linkedin' => [
            'li_class' => 'jl_linkedin_url',
            'mobile_li_class' => 'jl_linkedin',
            'icon' => 'jli-linkedin',
            'aria' => 'linkedin',
            'color_header' => '#0650e6',
            'color_rich' => '#2c408b',
            'color_footer' => '#0088cc',
        ],
        'youtube' => [
            'li_class' => 'jl_youtube_url',
            'mobile_li_class' => 'jl_youtube',
            'icon' => 'jli-youtube',
            'aria' => 'YouTube',
            'color_header' => '#ff0000',
            'color_rich' => '#ff0000',
            'color_footer' => '#ff0000',
        ],
        'tiktok' => [
            'li_class' => 'jl_tiktok_url',
            'mobile_li_class' => 'jl_tiktok',
            'icon' => 'jli-tiktok',
            'aria' => 'tiktok',
            'color_header' => '#c204e4',
            'color_rich' => '#980ac1',
            'color_footer' => '#1db954',
        ],
    ];
}

/** @return array<string, array<string, string>> */
function cn_social_default_links(): array
{
    return [
        'facebook' => [
            'url' => 'https://web.facebook.com/FinTechMedias',
            'title' => 'Facebook',
            'count' => '23k',
            'count_label' => 'Likes',
        ],
        'twitter' => [
            'url' => 'https://twitter.com/fintechmedias',
            'title' => 'Twitter',
            'count' => '93k',
            'count_label' => 'Follows',
        ],
        'instagram' => [
            'url' => 'https://instagram.com/fintechmedias',
            'title' => 'Instagram',
            'count' => '32k',
            'count_label' => 'Follows',
        ],
        'linkedin' => [
            'url' => 'https://www.linkedin.com/in/fintechmedias/',
            'title' => 'Linkedin',
            'count' => '42k',
            'count_label' => 'Pin',
        ],
        'youtube' => [
            'url' => 'https://youtube.com/@FinTechMedias',
            'title' => 'YouTube',
            'count' => '100k',
            'count_label' => 'Subscribers',
        ],
        'tiktok' => [
            'url' => 'http://tiktok.com/@fintechmedias',
            'title' => 'Tiktok',
            'count' => '100k',
            'count_label' => 'Subscribers',
        ],
    ];
}

function cn_social_resolve_db(): ?PDO
{
    global $db;

    if (isset($db) && $db instanceof PDO) {
        return $db;
    }

    if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof PDO) {
        return $GLOBALS['db'];
    }

    return null;
}

/** @return array<string, array<string, string>> */
function cn_social_links(?PDO $pdo = null): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $pdo = $pdo ?? cn_social_resolve_db();
    $defaults = cn_social_default_links();
    $links = [];

    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('SELECT setting_value FROM global_settings WHERE setting_key = :key LIMIT 1');
            $stmt->execute(['key' => 'social_media']);
            $raw = $stmt->fetchColumn();

            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    foreach (array_keys(cn_social_network_styles()) as $network) {
                        if (! array_key_exists($network, $decoded)) {
                            if (isset($defaults[$network])) {
                                $links[$network] = $defaults[$network];
                            }

                            continue;
                        }

                        $row = $decoded[$network];
                        if (! is_array($row)) {
                            continue;
                        }

                        $url = trim((string) ($row['url'] ?? ''));
                        if ($url === '') {
                            continue;
                        }

                        $networkDefaults = $defaults[$network] ?? [];
                        $links[$network] = [
                            'url' => $url,
                            'title' => trim((string) ($row['title'] ?? ($networkDefaults['title'] ?? ''))),
                            'count' => trim((string) ($row['count'] ?? ($networkDefaults['count'] ?? ''))),
                            'count_label' => trim((string) ($row['count_label'] ?? ($networkDefaults['count_label'] ?? ''))),
                        ];
                    }

                    $cache = array_filter($links, static fn (array $row): bool => trim((string) ($row['url'] ?? '')) !== '');

                    return $cache;
                }
            }
        } catch (Throwable) {
            // Retombe sur les valeurs par défaut.
        }
    }

    $links = $defaults;
    $cache = array_filter($links, static fn (array $row): bool => trim((string) ($row['url'] ?? '')) !== '');

    return $cache;
}

function cn_social_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
