@php
    $favicon = config('chrononews.brand.assets.favicon', 'assets/img/icon-chono-news.jpg');
@endphp
<link rel="icon" href="{{ asset($favicon) }}" type="image/jpeg" sizes="32x32">
<link rel="icon" href="{{ asset($favicon) }}" type="image/jpeg" sizes="192x192">
<link rel="apple-touch-icon" href="{{ asset($favicon) }}">
