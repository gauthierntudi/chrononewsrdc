<?php

namespace App\Support;

class Media
{
    public static function url(?string $path, ?string $default = null): string
    {
        $default ??= asset('assets/img/user.jpg');

        if ($path === null || trim($path) === '') {
            return $default;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
