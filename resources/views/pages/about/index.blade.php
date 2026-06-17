@extends('layouts.public')

@section('title', 'Qui sommes-nous ? — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/about/meta.php') !!}
@endsection

@section('body-class', 'elementor-page elementor-page-13574 e--ua-blink e--ua-edge e--ua-mac e--ua-webkit')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/about/render.php') !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/about/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/about/sidebar.php') !!}
@endsection

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/about/scripts.php') !!}
@endpush
