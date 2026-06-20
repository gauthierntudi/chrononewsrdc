<?php

declare(strict_types=1);

/**
 * Chemins publics neutres (évite /wp-content, /wp-includes et *.php bloqués par Cloudflare).
 */
if (! function_exists('cn_front_asset')) {
    function cn_front_asset(string $path): string
    {
        return '/assets/front/'.ltrim($path, '/');
    }
}

if (! function_exists('cn_front_theme')) {
    function cn_front_theme(string $path): string
    {
        return cn_front_asset('themes/'.ltrim($path, '/'));
    }
}

if (! function_exists('cn_front_plugin')) {
    function cn_front_plugin(string $path): string
    {
        return cn_front_asset('plugins/'.ltrim($path, '/'));
    }
}

if (! function_exists('cn_front_upload')) {
    function cn_front_upload(string $path): string
    {
        return cn_front_asset('uploads/'.ltrim($path, '/'));
    }
}

if (! function_exists('cn_front_core')) {
    function cn_front_core(string $path): string
    {
        return cn_front_asset('core/'.ltrim($path, '/'));
    }
}

if (! function_exists('cn_ajax_url')) {
    function cn_ajax_url(string $endpoint): string
    {
        return match ($endpoint) {
            'get_ad' => '/publication/ajax/get-ad',
            'live_search' => '/publication/ajax/live-search',
            'track_ad' => '/publication/ajax/track-ad',
            'newsletter_subscribe' => '/publication/ajax/newsletter-subscribe',
            'og_image' => '/og-image',
            default => '/publication/ajax/'.ltrim($endpoint, '/'),
        };
    }
}
