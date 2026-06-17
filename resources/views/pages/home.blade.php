@extends('layouts.public')

@section('title', 'Accueil — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/home/meta.php') !!}
@endsection

@section('body-class', 'wp-singular page-template page-template-home-page-builder page-template-home-page-builder-php page page-id-13574 elementor-page elementor-page-13574')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/home/render.php') !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/home/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/home/sidebar.php') !!}
@endsection

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/home/scripts.php') !!}
@endpush
