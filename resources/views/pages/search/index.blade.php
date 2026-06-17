@extends('layouts.public')

@section('title', ($q !== '' ? 'Recherche : '.$q.' — ' : 'Recherche — ').config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/search/meta.php', ['q' => $q]) !!}
@endsection

@section('body-root-class', 'archive category wp-embed-responsive wp-theme-bopea jl_cbgca jlac_smseah jl_spop_single4 jl_share_l_bg logo_foot_white jl_weg_title jl_sright_side jl_nav_stick jl_nav_active jl_nav_slide mobile_nav_class is-lazyload jl_en_day_night jl-has-sidebar jl_tline jl_sticky_smart elementor-default elementor-kit-5')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/search/render.php', [
    'q' => $q,
    'page' => $page,
]) !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/search/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/search/sidebar.php') !!}
@endsection

@push('styles')
<style id="cn-search-head-extra">
{!! \App\Support\LegacyInclude::render('includes/pages/category/head-extra.php') !!}
</style>
@endpush

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/search/scripts.php') !!}
@endpush
