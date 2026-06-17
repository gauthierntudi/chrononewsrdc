<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Page' }} — {{ config('chrononews.name') }}</title>
</head>
<body>
    <h1>{{ $title ?? 'À venir' }}</h1>
    <p>Cette page sera migrée depuis le legacy PHP.</p>
    <p><a href="{{ route('home') }}">← Accueil</a></p>
</body>
</html>
