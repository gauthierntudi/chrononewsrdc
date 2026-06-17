<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('chrononews.name'))</title>
    @include('partials.favicon')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: var(--cn-font-body);
            margin: 0;
            background: #f8fafc;
            color: var(--cn-text);
            line-height: 1.5;
        }
        a { color: var(--cn-blue); text-decoration: none; }
        a:hover { color: var(--cn-red); text-decoration: underline; }
        .header {
            background: var(--cn-black);
            border-bottom: 3px solid var(--cn-red);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .header__brand {
            font-family: var(--cn-font-display);
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--cn-white);
            text-decoration: none;
        }
        .header__brand:hover { color: var(--cn-white); text-decoration: none; opacity: 0.9; }
        .header__nav { display: flex; gap: 1rem; align-items: center; font-size: .9rem; }
        .header__nav a { color: var(--cn-white); font-weight: 500; }
        .header__nav a:hover { color: var(--cn-red); text-decoration: none; }
        .header__nav .muted { color: rgba(255,255,255,0.75); }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem; }
        .card {
            background: var(--cn-white);
            border: 1px solid var(--cn-border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 4%);
        }
        .card h1, .card h2 {
            font-family: var(--cn-font-display);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--cn-black);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .625rem 1rem;
            border-radius: 6px;
            border: none;
            font-size: .9375rem;
            font-weight: 600;
            font-family: var(--cn-font-body);
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary { background: var(--cn-red); color: var(--cn-white); }
        .btn-primary:hover { background: var(--cn-red-hover); text-decoration: none; }
        .btn-secondary { background: var(--cn-black-soft); color: var(--cn-white); border: none; }
        .btn-secondary:hover { background: var(--cn-black); text-decoration: none; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        .muted { color: var(--cn-text-muted); font-size: .875rem; }
        .badge {
            display: inline-block;
            background: var(--cn-red);
            color: var(--cn-white);
            font-size: .7rem;
            padding: .2rem .45rem;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: .03em;
            font-family: var(--cn-font-display);
            font-weight: 700;
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="header">
        <a href="{{ route('home') }}" class="header__brand">{{ config('chrononews.name') }}</a>
        <nav class="header__nav">
            @auth
                <span class="muted">{{ auth()->user()->name }}</span>
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Connexion</a>
            @endauth
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
