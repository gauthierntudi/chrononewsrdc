<?php

namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SocialMediaSettingsService
{
    public const SETTING_KEY = 'social_media';

    /** @return array<string, array<string, mixed>> */
    public function catalog(): array
    {
        return [
            'facebook' => [
                'label' => 'Facebook',
                'icon' => 'jli-facebook',
                'aria' => 'facebook',
                'default_count_label' => 'Likes',
            ],
            'twitter' => [
                'label' => 'X (Twitter)',
                'icon' => 'jli-x',
                'aria' => 'X',
                'default_count_label' => 'Follows',
            ],
            'instagram' => [
                'label' => 'Instagram',
                'icon' => 'jli-instagram',
                'aria' => 'instagram',
                'default_count_label' => 'Abonnés',
            ],
            'linkedin' => [
                'label' => 'LinkedIn',
                'icon' => 'jli-linkedin',
                'aria' => 'linkedin',
                'default_count_label' => 'Abonnés',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'icon' => 'jli-youtube',
                'aria' => 'YouTube',
                'default_count_label' => 'Subscribers',
            ],
            'tiktok' => [
                'label' => 'TikTok',
                'icon' => 'jli-tiktok',
                'aria' => 'tiktok',
                'default_count_label' => 'Followers',
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function defaults(): array
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

    /** @return array<string, array<string, mixed>> */
    public function get(): array
    {
        $raw = Setting::getValue(self::SETTING_KEY);
        $stored = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($stored)) {
            return $this->defaults();
        }

        return $this->mergeWithDefaults($stored);
    }

    /** @return array<string, string> network => url */
    public function urlMap(): array
    {
        $map = [];

        foreach ($this->get() as $network => $row) {
            $url = trim((string) ($row['url'] ?? ''));
            if ($url !== '') {
                $map[$network] = $url;
            }
        }

        return $map;
    }

    /** @param  array<string, array<string, mixed>>  $payload */
    public function update(array $payload): void
    {
        $normalized = $this->validateAndNormalize($payload);
        Setting::setValue(self::SETTING_KEY, json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** @param  array<string, array<string, mixed>>  $payload */
    public function validateAndNormalize(array $payload): array
    {
        $rules = [];
        foreach (array_keys($this->catalog()) as $network) {
            $rules["{$network}.url"] = ['nullable', 'string', 'max:500'];
            $rules["{$network}.title"] = ['nullable', 'string', 'max:100'];
            $rules["{$network}.count"] = ['nullable', 'string', 'max:30'];
            $rules["{$network}.count_label"] = ['nullable', 'string', 'max:50'];
        }

        $validated = Validator::make($payload, $rules)->validate();
        $normalized = [];

        foreach ($this->catalog() as $network => $meta) {
            $row = $validated[$network] ?? [];
            $url = trim((string) ($row['url'] ?? ''));

            if ($url === '') {
                $normalized[$network] = ['url' => ''];

                continue;
            }

            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                throw ValidationException::withMessages([
                    "{$network}.url" => ["L'URL {$meta['label']} n'est pas valide."],
                ]);
            }

            $normalized[$network] = [
                'url' => $url,
                'title' => trim((string) ($row['title'] ?? $meta['label'])) ?: $meta['label'],
                'count' => trim((string) ($row['count'] ?? '')),
                'count_label' => trim((string) ($row['count_label'] ?? $meta['default_count_label'])) ?: $meta['default_count_label'],
            ];
        }

        return $normalized;
    }

    /** @param  array<string, array<string, mixed>>  $stored */
    protected function mergeWithDefaults(array $stored): array
    {
        $merged = $this->defaults();

        foreach ($this->catalog() as $network => $meta) {
            if (! isset($stored[$network]) || ! is_array($stored[$network])) {
                continue;
            }

            $url = trim((string) ($stored[$network]['url'] ?? ''));
            if ($url === '') {
                unset($merged[$network]);

                continue;
            }

            $merged[$network] = [
                'url' => $url,
                'title' => trim((string) ($stored[$network]['title'] ?? $meta['label'])) ?: $meta['label'],
                'count' => trim((string) ($stored[$network]['count'] ?? $merged[$network]['count'] ?? '')),
                'count_label' => trim((string) ($stored[$network]['count_label'] ?? $meta['default_count_label'])) ?: $meta['default_count_label'],
            ];
        }

        return $merged;
    }
}
