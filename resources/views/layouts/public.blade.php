<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <title>@yield('title', config('chrononews.name'))</title>
    @include('partials.favicon')
    @hasSection('meta')
        @yield('meta')
    @else
        <meta name="color-scheme" content="light">
        <meta name="supported-color-schemes" content="light">
        <meta name="theme-color" content="#ffffff">
        <meta name="description" content="{{ config('chrononews.brand.tagline') }}">
    @endif
    {!! \App\Support\LegacyInclude::render('includes/front-head-assets.php', $legacyHead ?? []) !!}
    @stack('styles')
</head>
<body class="@yield('body-root-class', 'home wp-embed-responsive wp-theme-bopea jl_cbgca jlac_smseah jl_spop_single1 jl_share_l_bg logo_foot_white jl_weg_title jl_sright_side jl_nav_stick jl_nav_active jl_nav_slide mobile_nav_class is-lazyload jl_en_day_night jl-has-sidebar jl_tline jl_sticky_smart elementor-default elementor-kit-5') @yield('body-class')">
<div class="options_layout_wrapper jl_clear_at">
    <div class="options_layout_container tp_head_off">
        {!! \App\Support\LegacyInclude::render('includes/header.php', $legacyHeader ?? []) !!}
        @php
            $breakingNewsLayout = request()->routeIs('home') ? 'home' : 'inner';
        @endphp
        {!! \App\Support\LegacyInclude::render('includes/partials/breaking-news.php', [
            'breakingNewsLayout' => $breakingNewsLayout,
        ]) !!}

        @yield('content')

        {!! \App\Support\LegacyInclude::render('includes/footer.php') !!}

        @yield('before-footer')
    </div>
</div>
@yield('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/front-footer-scripts.php') !!}
@stack('scripts')
</body>
</html>
