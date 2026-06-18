<?php

return [

    'name' => env('SITE_NAME', 'ChronoNews'),
    'url' => env('SITE_URL', 'https://chrononews.web'),
    'timezone' => env('APP_TIMEZONE', 'Africa/Lubumbashi'),

    /*
    | Charte graphique — chartes-infos.txt
    | Rouge #d11810 · Noir #111111 · Bleu #1E5EFF · Montserrat / Poppins
    */
    'brand' => [
        'assets' => [
            'favicon' => 'assets/img/icon-chono-news.jpg',
            'logo' => 'assets/img/logo-01.png',
            'logo_admin' => 'assets/img/logo-front-on-black.png',
            'logo_email' => env(
                'SITE_EMAIL_LOGO_URL',
                'https://mypullzond243.b-cdn.net/chrononews/images/logo-front-on-black.png',
            ),
            'logo_front_light' => 'assets/img/logo-front.png',
            'logo_front_dark' => 'assets/img/logo-front-02.png',
        ],
        'colors' => [
            'red' => '#d11810',
            'red_hover' => '#b0140e',
            'black' => '#111111',
            'black_soft' => '#1A1A1A',
            'white' => '#FFFFFF',
            'blue' => '#1E5EFF',
        ],
        'fonts' => [
            'display' => 'Barlow Semi Condensed',
            'body' => 'Google Sans',
        ],
        'tagline' => 'L\'info qui n\'attend pas',
        'social' => [
            'facebook' => env('SOCIAL_FACEBOOK', 'https://web.facebook.com/FinTechMedias'),
            'twitter' => env('SOCIAL_TWITTER', 'https://twitter.com/fintechmedias'),
            'instagram' => env('SOCIAL_INSTAGRAM', 'https://instagram.com/fintechmedias'),
            'linkedin' => env('SOCIAL_LINKEDIN', 'https://www.linkedin.com/in/fintechmedias/'),
            'youtube' => env('SOCIAL_YOUTUBE', 'https://youtube.com/@FinTechMedias'),
        ],
        'contact_email' => env('SITE_CONTACT_EMAIL', env('MAIL_CONTACT_ADDRESS', 'contact@fintechmedias.cd')),
    ],

    'contact' => [
        'phone' => env('SITE_CONTACT_PHONE', '+243 995 801 328'),
        'whatsapp' => env('SITE_CONTACT_WHATSAPP') ?: env('SITE_CONTACT_PHONE', '+243 995 801 328'),
        'email' => env('SITE_CONTACT_EMAIL', env('MAIL_CONTACT_ADDRESS', 'contact@fintechmedias.cd')),
        'address' => env('SITE_CONTACT_ADDRESS', 'Kinshasa, RDC'),
    ],

    'article' => [
        'default_price' => (float) env('ARTICLE_DEFAULT_PRICE', 1.00),
        'currency' => env('PAYMENT_CURRENCY', 'USD'),
        'categories' => \App\Enums\ArticleCategory::values(),
    ],

    'ai' => [
        'enabled' => filter_var(env('AI_ENABLED', false), FILTER_VALIDATE_BOOL),
        'provider' => env('AI_PROVIDER', 'gemini'),
        'api_key' => env('AI_API_KEY', ''),
        'endpoint' => env('AI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/'),
        'model' => env('AI_MODEL', 'gemini-2.5-flash'),
    ],

    'otp' => [
        'length' => (int) env('OTP_LENGTH', 6),
        'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 10),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'max_requests_per_hour' => (int) env('OTP_MAX_REQUESTS_PER_HOUR', 10),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
    ],

    'upload' => [
        'max_size' => (int) env('UPLOAD_MAX_SIZE', 5_242_880),
        'allowed_images' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'allowed_ad_images' => ['image/jpeg', 'image/png', 'image/gif'],
        'allowed_videos' => ['video/mp4', 'video/webm', 'video/ogg'],
        'ad_formats' => [
            'rectangle' => ['width' => 672, 'height' => 560],
            'portrait' => ['width' => 512, 'height' => 562],
            'large_portrait' => ['width' => 768, 'height' => 1024],
            'large_rectangle' => ['width' => 1024, 'height' => 768],
            'paysage_small' => ['width' => 1456, 'height' => 180],
            'paysage_medium' => ['width' => 1920, 'height' => 400],
            'paysage_large' => ['width' => 3456, 'height' => 502],
        ],
    ],

    /*
    | Stockage des médias dynamiques (articles, pubs, profils).
    | local  → publication/uploads sur le serveur (/publication/… en URL)
    | s3     → bucket AWS S3 (recommandé Laravel Cloud)
    */
    'media' => [
        'disk' => env('MEDIA_DISK', 'local'),
        'url' => env('AWS_URL'),
        'local_root' => env('MEDIA_LOCAL_ROOT', \App\Support\ProjectPaths::root().'/publication'),
        'local_prefix' => env('MEDIA_LOCAL_PREFIX', '/publication'),
    ],

    'flexpay' => [
        'merchant' => env('FLEXPAY_MERCHANT'),
        'token' => env('FLEXPAY_TOKEN'),
        'mobile_url' => env('FLEXPAY_MOBILE_URL', 'https://backend.flexpay.cd/api/rest/v1/paymentService'),
        'card_url' => env('FLEXPAY_CARD_URL', 'https://cardpayment.flexpay.cd/v1/pay'),
        'check_url' => env('FLEXPAY_CHECK_URL', 'https://backend.flexpay.cd/api/rest/v1/check'),
        'callback_secret' => env('FLEXPAY_CALLBACK_SECRET'),
    ],

    'maxicash' => [
        'merchant_id' => env('MAXICASH_MERCHANT_ID'),
        'merchant_password' => env('MAXICASH_MERCHANT_PASSWORD'),
        'pay_sync_url' => env('MAXICASH_PAY_SYNC_URL', 'https://webapi.maxicashapp.com/Integration/PayNowSync'),
        'card_url' => env('MAXICASH_CARD_URL', 'https://webapi.maxicashapp.com/Integration/PayCreditCard'),
    ],

];
