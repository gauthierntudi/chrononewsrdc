# CHRONONEWS — Migration Laravel

Migration complète du legacy PHP vers **Laravel 13** (`laravel/`).

> **Suivi détaillé :** voir [`MIGRATION-SUIVI.md`](MIGRATION-SUIVI.md) (phases, checklist, inventaire legacy, journal de bord).

## Structure du projet

```
CHRONONEWS/
├── laravel/          ← Nouvelle application (document root = laravel/public)
├── publication/      ← Legacy CMS (référence — à supprimer à la fin)
├── index.php         ← Legacy front (référence)
├── MIGRATION.md      ← Setup technique (ce fichier)
└── MIGRATION-SUIVI.md ← Suivi de migration (checklist)
```

## Démarrage local

```bash
cd laravel
cp .env.example .env   # si pas déjà fait
php artisan key:generate
```

### MySQL (MAMP)

Dans `laravel/.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=chrononews
DB_USERNAME=root
DB_PASSWORD=root

APP_TIMEZONE=Africa/Lubumbashi
SITE_NAME="Fintech Médias"
SITE_URL=http://localhost:8000
```

Créer la base :

```sql
CREATE DATABASE chrononews CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis :

```bash
php artisan migrate:fresh --seed
php artisan serve
```

→ http://127.0.0.1:8000

## Phases (résumé)

Voir le détail dans [`MIGRATION-SUIVI.md`](MIGRATION-SUIVI.md).

| Phase | Statut |
|-------|--------|
| 1 — Fondations | ✅ |
| 2 — Auth OTP & CMS | ✅ |
| 3 — Paiements | ✅ |
| 4 — Front public | 🔄 |
| 5 — Dashboard admin | ✅ |
| 6 — Finitions | ⏳ |

## Mapping legacy → Laravel (aperçu)

| Legacy | Laravel |
|--------|---------|
| `actualites` | `articles` |
| `block_news` | `article_blocks` |
| `users.mail` | `users.email` |
| `users.nom` | `users.name` |
| `statut_validation: valide` | `validation_status: approved` |
| `statut_paiement: paye` | `payment_status: paid` |
| `paiements` | `payments` |
| `publicites` | `advertisements` |
| `global_settings` | `settings` |

## Variables d'environnement sensibles

Ne jamais committer `.env`. Configurer en prod :

- `FLEXPAY_MERCHANT`, `FLEXPAY_TOKEN`, `FLEXPAY_CALLBACK_SECRET`
- `MAXICASH_MERCHANT_ID`, `MAXICASH_MERCHANT_PASSWORD`
- `MAIL_*` pour OTP

## Document root production

Pointer Apache/Nginx vers `laravel/public/`, pas la racine du repo.

Après déploiement, lier les assets legacy :

```bash
bash laravel/scripts/link-legacy-assets.sh
```

Le front public (accueil, articles, catégories…) est servi par Laravel via `LegacyFrontController` qui inclut les vues PHP legacy. Migration progressive vers Blade prévue en Phase 4b.
