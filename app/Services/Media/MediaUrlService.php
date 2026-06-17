<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;

class MediaUrlService
{
    public function disk(): string
    {
        return (string) config('chrononews.media.disk', 'local');
    }

    public function usesCloud(): bool
    {
        return in_array($this->disk(), ['s3', 'media'], true);
    }

    public function normalizeRelativePath(string $path): string
    {
        $relative = ltrim($path, '/');

        if (str_starts_with($relative, 'publication/')) {
            $relative = substr($relative, strlen('publication/'));
        }

        return $relative;
    }

    public function url(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = $this->normalizeRelativePath($path);

        if ($relative === '') {
            return '';
        }

        if ($this->usesCloud()) {
            return Storage::disk($this->disk())->url($relative);
        }

        $prefix = rtrim((string) config('chrononews.media.local_prefix', '/publication'), '/');

        return $prefix.'/'.$relative;
    }

    public function publicBaseUrl(): string
    {
        if ($this->usesCloud()) {
            return $this->cloudBaseUrl();
        }

        return rtrim((string) config('chrononews.media.local_prefix', '/publication'), '/');
    }

    protected function cloudBaseUrl(): string
    {
        $configured = rtrim((string) config('chrononews.media.url', ''), '/');
        if ($configured !== '') {
            return $configured;
        }

        $disk = $this->disk();
        $config = config("filesystems.disks.{$disk}", []);

        $custom = rtrim((string) ($config['url'] ?? ''), '/');
        if ($custom !== '') {
            return $custom;
        }

        $bucket = (string) ($config['bucket'] ?? '');
        if ($bucket === '') {
            return '';
        }

        $region = (string) ($config['region'] ?? 'us-east-1');
        $root = trim((string) ($config['root'] ?? ''), '/');

        $base = "https://{$bucket}.s3.{$region}.amazonaws.com";
        if ($root !== '') {
            $base .= '/'.$root;
        }

        return rtrim($base, '/');
    }
}
