<?php

namespace App\Support;

use Illuminate\Support\Str;

final class FrontHelper
{
    public static function cleanTitle(?string $title): string
    {
        if ($title === null) {
            return '';
        }

        return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function slugify(?string $text, string $fallback = 'article'): string
    {
        $text = self::cleanTitle($text);

        if ($text === '') {
            return $fallback;
        }

        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        }

        $slug = Str::slug($text, '-');

        return $slug !== '' ? $slug : $fallback;
    }

    /** @return list<string> */
    public static function parseCoverImages(?string $cover): array
    {
        if ($cover === null || $cover === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $cover))));
    }

    public static function coverUrl(?string $cover, int $index = 0): ?string
    {
        $images = self::parseCoverImages($cover);

        if (! isset($images[$index])) {
            return null;
        }

        $url = function_exists('cn_media_url')
            ? cn_media_url($images[$index])
            : '/publication/'.ltrim($images[$index], '/');

        return $url !== '' ? $url : null;
    }

    public static function excerpt(?string $content, int $max = 160): string
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

    public static function formatDate(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }

        $timestamp = strtotime($datetime);

        return $timestamp ? date('M j, Y', $timestamp) : '';
    }

    public static function viewsInt(mixed $views): int
    {
        $digits = preg_replace('/[^\d]/', '', (string) $views);

        return $digits === '' ? 0 : (int) $digits;
    }

    public static function hasVideo(array $article): bool
    {
        $videos = trim((string) ($article['videos'] ?? ''));

        return $videos !== '' && $videos !== '0';
    }

    public static function articleUrl(array $article): string
    {
        $title = $article['title'] ?? $article['titre'] ?? '';
        $id = (int) ($article['id'] ?? 0);

        return route('articles.show', [
            'article' => $id,
            'slug' => self::slugify($title),
        ]);
    }

    public static function donutPercent(int $views, int $base = 2000): int
    {
        if ($views <= 0) {
            return 0;
        }

        $percent = (int) round(($views / max(1, $base)) * 100);

        return max(0, min(100, $percent));
    }
}
