@extends('layouts.public')

@section('title', 'Politique de confidentialité — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/privacy/meta.php') !!}
@endsection

@section('body-class', 'elementor-page elementor-page-13574')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/privacy/render.php') !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/privacy/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/privacy/sidebar.php') !!}
@endsection

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/privacy/scripts.php') !!}
@endpush
