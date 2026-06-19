<?php

namespace App\Services\Media;

use App\Models\Article;
use App\Support\SiteUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class OgImageService
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    public function __construct(
        protected MediaUrlService $mediaUrls,
    ) {}

    public function absoluteUrl(?int $articleId = null): string
    {
        $path = '/og-image';

        if ($articleId !== null && $articleId > 0) {
            $path .= '?id='.$articleId;
        }

        return SiteUrl::absolute(ltrim($path, '/'));
    }

    public function render(?int $articleId = null): ?string
    {
        $cachePath = $this->cachePath($articleId);

        if (is_file($cachePath)) {
            $cached = file_get_contents($cachePath);

            return $cached !== false ? $cached : null;
        }

        $jpeg = $this->buildJpeg($articleId);

        if ($jpeg === null) {
            return null;
        }

        $dir = dirname($cachePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($cachePath, $jpeg);

        return $jpeg;
    }

    protected function cachePath(?int $articleId): string
    {
        $name = ($articleId !== null && $articleId > 0) ? $articleId.'.jpg' : 'default.jpg';

        return storage_path('framework/og-cache/'.$name);
    }

    protected function buildJpeg(?int $articleId): ?string
    {
        $coverUrl = ($articleId !== null && $articleId > 0)
            ? $this->resolveCoverUrl($articleId)
            : null;

        if ($coverUrl) {
            $bytes = $this->fetchImageBytes($coverUrl);
            if ($bytes !== null) {
                $jpeg = $this->cropAndEncode($bytes);
                if ($jpeg !== null) {
                    return $jpeg;
                }
            }
        }

        return $this->renderDefault();
    }

    protected function resolveCoverUrl(int $articleId): ?string
    {
        $cover = null;

        if (Schema::hasTable('articles')) {
            $cover = Article::query()->whereKey($articleId)->value('cover');
        }

        if (! $cover && Schema::hasTable('actualites')) {
            $cover = DB::table('actualites')
                ->where('id', $articleId)
                ->where('status', 1)
                ->value('cover');
        }

        if (! $cover) {
            return null;
        }

        $first = trim(explode(',', (string) $cover)[0]);

        if ($first === '') {
            return null;
        }

        $url = $this->mediaUrls->url($first);

        return $url !== '' ? $url : null;
    }

    protected function renderDefault(): ?string
    {
        $configured = trim((string) config('chrononews.brand.og_image_url', ''));

        if ($configured !== '') {
            $bytes = $this->fetchImageBytes($configured);
            if ($bytes !== null) {
                $jpeg = $this->cropAndEncode($bytes);
                if ($jpeg !== null) {
                    return $jpeg;
                }
            }
        }

        foreach ($this->defaultLocalPaths() as $path) {
            if (! is_file($path)) {
                continue;
            }

            $bytes = file_get_contents($path);
            if ($bytes === false) {
                continue;
            }

            $jpeg = $this->cropAndEncode($bytes);
            if ($jpeg !== null) {
                return $jpeg;
            }
        }

        return null;
    }

    /** @return list<string> */
    protected function defaultLocalPaths(): array
    {
        return [
            public_path('assets/img/og-default.jpg'),
            public_path('assets/img/og-default.png'),
            public_path(ltrim((string) config('chrononews.brand.assets.logo_front_dark', 'assets/img/logo-front-02.png'), '/')),
            public_path(ltrim((string) config('chrononews.brand.assets.logo_admin', 'assets/img/logo-front-on-black.png'), '/')),
        ];
    }

    protected function fetchImageBytes(string $url): ?string
    {
        if (str_starts_with($url, '/')) {
            $local = public_path(ltrim($url, '/'));
            if (is_file($local)) {
                $bytes = file_get_contents($local);

                return $bytes !== false ? $bytes : null;
            }

            return null;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable) {
            // Fallback local ci-dessous si URL distante indisponible.
        }

        return null;
    }

    protected function cropAndEncode(string $imageBytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $srcImage = @imagecreatefromstring($imageBytes);
        if ($srcImage === false) {
            return null;
        }

        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);

        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($srcImage);

            return null;
        }

        $srcRatio = $srcW / $srcH;
        $destRatio = self::WIDTH / self::HEIGHT;

        if ($srcRatio > $destRatio) {
            $newHeight = $srcH;
            $newWidth = (int) ($srcH * $destRatio);
            $srcX = (int) (($srcW - $newWidth) / 2);
            $srcY = 0;
        } else {
            $newWidth = $srcW;
            $newHeight = (int) ($srcW / $destRatio);
            $srcX = 0;
            $srcY = (int) (($srcH - $newHeight) / 2);
        }

        $destImage = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($destImage === false) {
            imagedestroy($srcImage);

            return null;
        }

        imagecopyresampled(
            $destImage,
            $srcImage,
            0,
            0,
            $srcX,
            $srcY,
            self::WIDTH,
            self::HEIGHT,
            $newWidth,
            $newHeight,
        );

        ob_start();
        imagejpeg($destImage, null, 90);
        $jpeg = ob_get_clean();

        imagedestroy($destImage);
        imagedestroy($srcImage);

        return is_string($jpeg) && $jpeg !== '' ? $jpeg : null;
    }
}
