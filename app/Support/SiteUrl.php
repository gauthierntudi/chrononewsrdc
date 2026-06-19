<?php

namespace App\Support;

use Illuminate\Http\Request;

final class SiteUrl
{
    public static function base(): string
    {
        if (function_exists('app')) {
            try {
                $request = app(Request::class);

                if ($request instanceof Request) {
                    $host = $request->getHost();

                    if ($host !== '') {
                        return rtrim($request->getSchemeAndHttpHost(), '/');
                    }
                }
            } catch (\Throwable) {
                // Config .env ci-dessous.
            }
        }

        if (! empty($_SERVER['HTTP_HOST'])) {
            return rtrim(self::fromServerGlobals(), '/');
        }

        return self::configured();
    }

    public static function absolute(string $path): string
    {
        return rtrim(self::base(), '/').'/'.ltrim($path, '/');
    }

    public static function configured(): string
    {
        if (function_exists('config')) {
            try {
                $appUrl = rtrim((string) config('app.url', ''), '/');
                if ($appUrl !== '') {
                    return $appUrl;
                }

                $siteUrl = rtrim((string) config('chrononews.url', ''), '/');
                if ($siteUrl !== '') {
                    return $siteUrl;
                }
            } catch (\Throwable) {
                // Constante legacy ci-dessous.
            }
        }

        return defined('CN_SITE_URL') ? CN_SITE_URL : 'https://chrononews.web';
    }

    protected static function fromServerGlobals(): string
    {
        $scheme = 'http';

        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]);
        } elseif (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        }

        $host = trim((string) $_SERVER['HTTP_HOST'], '/');

        return $scheme.'://'.$host;
    }
}
