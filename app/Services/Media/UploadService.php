<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class UploadService
{
    /** @var array<string, array{width: int, height: int}> */
    protected array $adDimensions;

    public function __construct(
        protected MediaUrlService $mediaUrls,
    ) {
        $this->adDimensions = config('chrononews.upload.ad_formats', []);
    }

    public function handle(UploadedFile $file, string $type, ?string $adFormat = null): array
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Erreur lors de l\'upload du fichier');
        }

        $maxSize = (int) config('chrononews.upload.max_size', 5_242_880);
        if ($file->getSize() > $maxSize) {
            throw new InvalidArgumentException('Fichier trop volumineux (max 5 Mo)');
        }

        $mime = $file->getMimeType() ?: '';
        $this->validateMime($type, $mime);

        if ($type === 'ad') {
            $this->validateAdDimensions($file, $adFormat);
        }

        $directory = $this->directoryFor($type);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        $filename = uniqid('', true).'_'.time().'.'.$extension;
        $relativePath = trim($directory, '/').'/'.$filename;

        if ($this->mediaUrls->usesCloud()) {
            $stream = fopen($file->getPathname(), 'r');
            if ($stream === false) {
                throw new InvalidArgumentException('Impossible de lire le fichier uploadé');
            }

            Storage::disk($this->mediaUrls->disk())->put($relativePath, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        } else {
            $fullPath = $this->localDirectory($directory);

            if (! File::isDirectory($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            $file->move($fullPath, $filename);
        }

        return [
            'url' => '/'.$relativePath,
            'filename' => $filename,
        ];
    }

    protected function localDirectory(string $directory): string
    {
        $root = rtrim((string) config('chrononews.media.local_root'), '/');

        return $root.'/'.trim($directory, '/');
    }

    protected function validateMime(string $type, string $mime): void
    {
        if (in_array($type, ['image', 'profile', 'ad'], true)) {
            $allowed = $type === 'ad'
                ? config('chrononews.upload.allowed_ad_images', ['image/jpeg', 'image/png', 'image/gif'])
                : config('chrononews.upload.allowed_images', []);

            if (! in_array($mime, $allowed, true)) {
                throw new InvalidArgumentException('Type d\'image non autorisé. Formats acceptés : JPG, PNG, GIF, WebP');
            }

            return;
        }

        if ($type === 'video') {
            $allowed = config('chrononews.upload.allowed_videos', []);
            if (! in_array($mime, $allowed, true)) {
                throw new InvalidArgumentException('Type de vidéo non autorisé');
            }

            return;
        }

        throw new InvalidArgumentException('Type de fichier non valide');
    }

    protected function validateAdDimensions(UploadedFile $file, ?string $adFormat): void
    {
        if (! $adFormat) {
            throw new InvalidArgumentException('Format de publicité requis');
        }

        if (! isset($this->adDimensions[$adFormat])) {
            throw new InvalidArgumentException('Format de publicité invalide : '.$adFormat);
        }

        $imageInfo = @getimagesize($file->getPathname());
        if (! $imageInfo) {
            throw new InvalidArgumentException('Impossible de lire les dimensions de l\'image');
        }

        $expected = $this->adDimensions[$adFormat];
        [$width, $height] = $imageInfo;

        if ($width !== $expected['width'] || $height !== $expected['height']) {
            throw new InvalidArgumentException(sprintf(
                'Dimensions incorrectes. Le format %s requiert %dx%d pixels, mais votre image fait %dx%d pixels',
                $adFormat,
                $expected['width'],
                $expected['height'],
                $width,
                $height,
            ));
        }
    }

    protected function directoryFor(string $type): string
    {
        return match ($type) {
            'profile' => 'uploads/profile',
            'ad' => 'uploads/ads',
            'video' => 'uploads/videos',
            default => 'uploads/images',
        };
    }
}
