@extends('layouts.public')

@section('title', $articleTitle.' — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/article/meta.php', ['article_id' => $article_id]) !!}
@endsection

@section('body-root-class', 'home wp-singular page-template page-template-home-page-builder page-template-home-page-builder-php page page-id-13574 wp-embed-responsive wp-theme-bopea jl_cbgca jlac_smseah jl_spop_single1 jl_share_l_bg logo_foot_white jl_weg_title jl_sright_side jl_nav_stick jl_nav_active jl_nav_slide mobile_nav_class is-lazyload jl_en_day_night jl-has-sidebar jl_tline jl_sticky_smart elementor-default elementor-kit-5')

@section('body-class', 'elementor-page elementor-page-13574')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/article/render.php', ['article_id' => $article_id]) !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/article/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/article/overlays.php', ['article_id' => $article_id, 'skip_view_increment' => true]) !!}
@endsection

@push('styles')
{!! \App\Support\LegacyInclude::render('includes/pages/article/head-premium.php') !!}
{!! \App\Support\LegacyInclude::render('includes/pages/article/head-ads.php') !!}
{!! \App\Support\LegacyInclude::render('includes/pages/article/head-video-float.php') !!}
{!! \App\Support\LegacyInclude::render('includes/pages/article/payment-styles.php') !!}
@endpush

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/article/scripts.php', ['article_id' => $article_id, 'skip_view_increment' => true]) !!}
@endpush
