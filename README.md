# Chrono News

Application **Laravel 13** — plateforme d'actualités (API, dashboard, front public via templates legacy).

## Prérequis

- PHP 8.2+
- Composer
- MySQL (ou SQLite pour un test rapide)

## Installation

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```

Configurer `.env` : base de données, `SITE_NAME`, `SITE_URL`, mail, paiements (FlexPay), etc.

## Médias (S3)

Les uploads utilisateurs sont stockés sur **AWS S3** en production :

```env
MEDIA_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=chrononews-files3
```

Migration depuis un dossier local :

```bash
php artisan media:migrate-to-s3 --source=/chemin/vers/publication/uploads
```

Le bucket doit autoriser la lecture publique de `uploads/*` (politique de compartiment S3).

## Structure

```
laravel/
├── app/                 Code Laravel (API, services)
├── includes/            Templates front (header, pages, sidebar…)
├── public/              Document root
│   ├── wp-content/      Assets thème Bopea / Elementor
│   ├── img/             Images statiques du site
│   └── publication/img/ Icônes moyens de paiement
├── resources/views/     Vues Blade (wrappers)
└── routes/              Routes web + API
```

## Démarrage local

```bash
php artisan serve
```

→ http://localhost:8000

## Monorepo local (optionnel)

Si vous travaillez encore dans `CHRONONEWS/` avec le legacy à côté :

```env
CHRONONEWS_ROOT=/chemin/vers/CHRONONEWS
MEDIA_LOCAL_ROOT=/chemin/vers/CHRONONEWS/publication
```

Sans ces variables, l'app utilise `laravel/` comme racine (déploiement autonome).

## Ce qui n'est pas versionné

- `.env` (secrets)
- `vendor/` → `composer install`
- `storage/` runtime, logs
- `node_modules/`, `public/build/`

## Documentation migration

Voir `docs/MIGRATION.md` et `docs/MIGRATION-SUIVI.md` (historique de la migration legacy → Laravel).
