<?php

declare(strict_types=1);

require_once __DIR__.'/legacy-paths.php';

if (! defined('ARTICLE_PRICE')) {
    $price = 1.0;
    if (function_exists('config')) {
        try {
            $price = (float) config('chrononews.article.default_price', 1.0);
        } catch (Throwable) {
            // ignore
        }
    }
    define('ARTICLE_PRICE', $price);
}

if (! defined('OTP_LENGTH')) {
    $length = 6;
    if (function_exists('config')) {
        try {
            $length = (int) config('chrononews.otp.length', 6);
        } catch (Throwable) {
            // ignore
        }
    }
    define('OTP_LENGTH', $length);
}

if (! defined('OTP_EXPIRATION_MINUTES')) {
    $minutes = 10;
    if (function_exists('config')) {
        try {
            $minutes = (int) config('chrononews.otp.expires_minutes', 10);
        } catch (Throwable) {
            // ignore
        }
    }
    define('OTP_EXPIRATION_MINUTES', $minutes);
}

if (! defined('SITE_NAME')) {
    $name = 'Chrono News';
    if (function_exists('config')) {
        try {
            $name = (string) config('chrononews.name', $name);
        } catch (Throwable) {
            // ignore
        }
    }
    define('SITE_NAME', $name);
}

if (! defined('SITE_URL')) {
    $url = 'https://chrononews.web';
    if (function_exists('config')) {
        try {
            $url = rtrim((string) config('chrononews.url', $url), '/');
        } catch (Throwable) {
            // ignore
        }
    }
    define('SITE_URL', $url);
}

if (! defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', cn_publication_root().'/uploads/');
}

if (! defined('MAX_FILE_SIZE')) {
    $size = 5_242_880;
    if (function_exists('config')) {
        try {
            $size = (int) config('chrononews.upload.max_size', $size);
        } catch (Throwable) {
            // ignore
        }
    }
    define('MAX_FILE_SIZE', $size);
}

if (! function_exists('date_default_timezone_get') || date_default_timezone_get() === 'UTC') {
    $tz = 'Africa/Lubumbashi';
    if (function_exists('config')) {
        try {
            $tz = (string) config('chrononews.timezone', $tz);
        } catch (Throwable) {
            // ignore
        }
    }
    date_default_timezone_set($tz);
}
