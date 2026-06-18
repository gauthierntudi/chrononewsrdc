<?php

namespace App\Support\Mail;

use App\Services\Admin\SocialMediaSettingsService;
use Illuminate\Support\Facades\View;

final class ChrononewsMail
{
    public static function siteName(): string
    {
        return (string) config('chrononews.name', 'Chrono News');
    }

    public static function siteUrl(): string
    {
        return rtrim((string) config('chrononews.url', config('app.url', 'http://localhost')), '/');
    }

    public static function tagline(): string
    {
        return (string) config('chrononews.brand.tagline', "L'info qui n'attend pas");
    }

    public static function color(string $key): string
    {
        return (string) config("chrononews.brand.colors.{$key}", '#E10600');
    }

    public static function asset(string $path): string
    {
        return self::siteUrl().'/'.ltrim($path, '/');
    }

    public static function logoUrl(): string
    {
        $emailLogo = config('chrononews.brand.assets.logo_email');

        if (is_string($emailLogo) && $emailLogo !== '') {
            return $emailLogo;
        }

        return self::asset((string) config('chrononews.brand.assets.logo_front_dark', 'assets/img/logo-front-02.png'));
    }

    public static function contactEmail(): string
    {
        return (string) config('chrononews.contact.email', 'contact@fintechmedias.cd');
    }

    public static function contactPhone(): string
    {
        return (string) config('chrononews.contact.phone', '+243 995 801 328');
    }

    public static function contactWhatsapp(): string
    {
        return (string) config('chrononews.contact.whatsapp', self::contactPhone());
    }

    public static function contactAddress(): string
    {
        return (string) config('chrononews.contact.address', 'Kinshasa, RDC');
    }

    /** @return array<string, string> */
    public static function socialLinks(): array
    {
        return app(SocialMediaSettingsService::class)->urlMap();
    }

    /** @return array<string, string> */
    public static function footerLinks(): array
    {
        $base = self::siteUrl();

        return [
            'Contact' => $base.'/contact',
            'Accueil' => $base.'/',
            'Qui sommes-nous' => $base.'/qui-sommes-nous',
            'Confidentialité' => $base.'/politique-de-confidentialite',
        ];
    }

    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'carte_bancaire' => 'Carte bancaire',
            'mpesa' => 'M-Pesa',
            'airtel_money' => 'Airtel Money',
            'orange_money' => 'Orange Money',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    public static function paymentMethodIcon(string $method): string
    {
        $icons = [
            'carte_bancaire' => 'pictos/card.jpg',
            'mpesa' => 'pictos/mpesa01.jpg',
            'airtel_money' => 'pictos/airtel.jpg',
            'orange_money' => 'pictos/orange3.jpg',
        ];

        if (! isset($icons[$method])) {
            return '';
        }

        return self::asset('assets/img/'.$icons[$method]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function render(string $view, array $data = []): string
    {
        return View::make($view, array_merge(self::sharedData(), $data))->render();
    }

    /** @return array<string, mixed> */
    public static function sharedData(): array
    {
        return [
            'brandName' => self::siteName(),
            'brandUrl' => self::siteUrl(),
            'brandTagline' => self::tagline(),
            'brandLogo' => self::logoUrl(),
            'colorRed' => self::color('red'),
            'colorRedHover' => self::color('red_hover'),
            'colorBlack' => self::color('black'),
            'colorBlackSoft' => self::color('black_soft'),
            'colorWhite' => self::color('white'),
            'colorBlue' => self::color('blue'),
            'socialLinks' => self::socialLinks(),
            'footerLinks' => self::footerLinks(),
            'contactEmail' => self::contactEmail(),
            'contactPhone' => self::contactPhone(),
            'contactWhatsapp' => self::contactWhatsapp(),
            'contactAddress' => self::contactAddress(),
            'year' => date('Y'),
        ];
    }
}
