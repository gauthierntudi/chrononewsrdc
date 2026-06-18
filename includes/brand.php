<?php

declare(strict_types=1);

if (defined('CN_BRAND_LOADED')) {
    return;
}

define('CN_BRAND_LOADED', true);
define('CN_SITE_NAME', 'ChronoNews');
define('CN_SITE_URL', 'https://chrononews.web');
define('CN_TAGLINE', "L'info qui n'attend pas");

/** Couleur primaire brand */
define('CN_PRIMARY_COLOR', '#d11810');
define('CN_PRIMARY_HOVER', '#b0140e');
define('CN_PRIMARY_RGB', '209, 24, 16');

/** Images brand — source unique : laravel/public/assets/img/ */
define('CN_ASSETS_IMG', '/assets/img');
define('CN_FAVICON', CN_ASSETS_IMG.'/icon-chono-news.jpg');
define('CN_LOGO_LIGHT', CN_ASSETS_IMG.'/logo-front.png');
define('CN_LOGO_DARK', CN_ASSETS_IMG.'/logo-front-02.png');

/** Tailles logo header (modifier ici — le CSS est dans includes/logo-styles.php) */
define('CN_LOGO_HEADER_WRAP', '230px');
define('CN_LOGO_HEADER_IMG', '170px');
define('CN_LOGO_HEADER_WRAP_MOBILE', '150px');
define('CN_LOGO_HEADER_IMG_MOBILE', '140px');

/** Tailles logo footer */
define('CN_LOGO_FOOTER_WRAP', '230px');
define('CN_LOGO_FOOTER_IMG', '190px');
define('CN_LOGO_FOOTER_WRAP_MOBILE', '200px');
define('CN_LOGO_FOOTER_IMG_MOBILE', '190px');

/** Thème — light par défaut (opt_dark: 0 dans jlParamsOpt) */
define('CN_THEME_COLOR', '#ffffff');
define('CN_TWITTER_SITE', '@chrononews');
define('CN_TWITTER_CREATOR', '@chrononews');

/** Footer — fond noir fixe, textes clairs */
define('CN_FOOTER_BG', '#000000');
define('CN_FOOTER_LOGO', CN_LOGO_DARK);
define('CN_FOOTER_TEXT', '#ffffff');
define('CN_FOOTER_TEXT_MUTED', 'rgba(255, 255, 255, 0.73)');

function cn_site_name(): string
{
    return CN_SITE_NAME;
}

function cn_site_url(): string
{
    return CN_SITE_URL;
}

function cn_tagline(): string
{
    return CN_TAGLINE;
}

function cn_primary_color(): string
{
    return CN_PRIMARY_COLOR;
}

function cn_primary_color_hover(): string
{
    return CN_PRIMARY_HOVER;
}

function cn_favicon(): string
{
    return CN_FAVICON;
}

function cn_logo_light(): string
{
    return CN_LOGO_LIGHT;
}

function cn_logo_dark(): string
{
    return CN_LOGO_DARK;
}

function cn_logo_footer(): string
{
    return CN_FOOTER_LOGO;
}

function cn_og_image(): string
{
    return CN_SITE_URL.CN_FAVICON;
}

function cn_config_string(string $key, string $default): string
{
    if (! function_exists('config')) {
        return $default;
    }

    try {
        $value = config($key, $default);

        return is_string($value) || is_numeric($value) ? (string) $value : $default;
    } catch (Throwable) {
        return $default;
    }
}

function cn_contact_phone(): string
{
    return cn_config_string('chrononews.contact.phone', '+243 995 801 328');
}

function cn_contact_whatsapp(): string
{
    $whatsapp = cn_config_string('chrononews.contact.whatsapp', '');

    return $whatsapp !== '' ? $whatsapp : cn_contact_phone();
}

function cn_contact_email(): string
{
    return cn_config_string('chrononews.contact.email', 'contact@fintechmedias.cd');
}

function cn_contact_address(): string
{
    return cn_config_string('chrononews.contact.address', 'Kinshasa, RDC');
}
