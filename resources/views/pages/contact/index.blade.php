@extends('layouts.public')

@section('title', 'Contact — '.config('chrononews.name'))

@section('meta')
{!! \App\Support\LegacyInclude::render('includes/pages/contact/meta.php') !!}
@endsection

@section('body-class', 'elementor-page elementor-page-13574')

@section('content')
{!! \App\Support\LegacyInclude::render('includes/pages/contact/render.php') !!}
@endsection

@section('before-footer')
{!! \App\Support\LegacyInclude::render('includes/pages/contact/go-top.php') !!}
@endsection

@section('after-wrapper')
{!! \App\Support\LegacyInclude::render('includes/pages/contact/sidebar.php') !!}
@endsection

@push('styles')
<style id="cn-contact-head-styles">
{!! \App\Support\LegacyInclude::render('includes/pages/contact/head-styles.php') !!}
</style>
@endpush

@push('scripts')
{!! \App\Support\LegacyInclude::render('includes/pages/contact/scripts.php') !!}
@endpush
