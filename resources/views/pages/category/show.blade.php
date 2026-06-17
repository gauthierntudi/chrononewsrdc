@extends('layouts.public')

@section('title', $category.' — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/category/meta.php', ['category_slug' => $category]) !!}
@endsection

@section('body-root-class', 'archive category wp-embed-responsive wp-theme-bopea jl_cbgca jlac_smseah jl_spop_single4 jl_share_l_bg logo_foot_white jl_weg_title jl_sright_side jl_nav_stick jl_nav_active jl_nav_slide mobile_nav_class is-lazyload jl_en_day_night jl-has-sidebar jl_tline jl_sticky_smart elementor-default elementor-kit-5')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/category/render.php', [
    'category_slug' => $category,
    'page' => $page,
]) !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/category/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/category/sidebar.php') !!}
@endsection

@push('styles')
<style id="cn-category-head-extra">
{!! \App\Support\LegacyInclude::render('includes/pages/category/head-extra.php') !!}
</style>
@endpush

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/category/scripts.php') !!}
@endpush
