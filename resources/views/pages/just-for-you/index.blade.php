@extends('layouts.public')

@section('title', 'Juste pour vous — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/just-for-you/meta.php') !!}
@endsection

@section('body-root-class', 'archive category wp-embed-responsive wp-theme-bopea jl_cbgca jlac_smseah jl_spop_single4 jl_share_l_bg logo_foot_white jl_weg_title jl_sright_side jl_nav_stick jl_nav_active jl_nav_slide mobile_nav_class is-lazyload jl_en_day_night jl-has-sidebar jl_tline jl_sticky_smart elementor-default elementor-kit-5')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/just-for-you/render.php', [
    'page' => $page,
]) !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/just-for-you/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/just-for-you/sidebar.php') !!}
@endsection

@push('styles')
<style id="cn-just-for-you-head-extra">
{!! \App\Support\LegacyInclude::render('includes/pages/category/head-extra.php') !!}
</style>
@endpush

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/just-for-you/scripts.php') !!}
@endpush
