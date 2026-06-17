<?php

declare(strict_types=1);

if (! function_exists('cn_media_normalize_path')) {
    function cn_media_normalize_path(string $path): string
    {
        $relative = ltrim($path, '/');

        if (str_starts_with($relative, 'publication/')) {
            $relative = substr($relative, strlen('publication/'));
        }

        return $relative;
    }
}

if (! function_exists('cn_media_url')) {
    function cn_media_url(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (function_exists('app')) {
            try {
                return app(\App\Services\Media\MediaUrlService::class)->url($path);
            } catch (Throwable) {
                // Fallback standalone ci-dessous.
            }
        }

        $relative = cn_media_normalize_path($path);

        if ($relative === '') {
            return '';
        }

        $disk = getenv('MEDIA_DISK') ?: 'local';

        if (in_array($disk, ['s3', 'media'], true)) {
            $base = rtrim((string) (getenv('AWS_URL') ?: ''), '/');
            $root = trim((string) (getenv('AWS_MEDIA_ROOT') ?: ''), '/');
            $key = $root !== '' ? $root.'/'.$relative : $relative;

            if ($base !== '') {
                return $base.'/'.ltrim($key, '/');
            }

            $bucket = getenv('AWS_BUCKET') ?: '';
            $region = getenv('AWS_DEFAULT_REGION') ?: 'us-east-1';

            if ($bucket !== '') {
                return 'https://'.$bucket.'.s3.'.$region.'.amazonaws.com/'.ltrim($key, '/');
            }
        }

        return '/publication/'.$relative;
    }
}
