# Suivi de migration — CHRONONEWS → Laravel

Document de référence pour piloter la migration complète du legacy PHP vers **Laravel 13**.

| | |
|---|---|
| **Projet** | Fintech Médias / CHRONONEWS |
| **Début migration** | 7 juin 2026 |
| **Site en production** | Non (migration sans contrainte d'uptime) |
| **Stack cible** | Laravel 13 · PHP 8.5+ · MySQL · Blade |
| **App Laravel** | [`laravel/`](laravel/) |
| **Setup technique** | [`MIGRATION.md`](MIGRATION.md) |

---

## Vue d'ensemble

```
Progression globale  [████████████░░░░░░░░]  ~65 %
```

| Phase | Nom | Statut | Progression |
|-------|-----|--------|-------------|
| 1 | Fondations | ✅ Terminé | 100 % |
| 2 | Auth OTP & CMS | ✅ Terminé | 100 % |
| 3 | Paiements | ✅ Terminé | 100 % |
| 4 | Front public | 🔄 En cours | 70 % |
| 5 | Dashboard admin | ✅ Terminé | 100 % |
| 6 | Finitions & déploiement | ⏳ À faire | 0 % |

**Légende :** ✅ Terminé · 🔄 En cours · ⏳ À faire · 🚧 Bloqué · ❌ Annulé

---

## Journal de bord

| Date | Action | Par |
|------|--------|-----|
| 2026-06-07 | Analyse architecture legacy + audit sécurité | Agent |
| 2026-06-07 | Création projet Laravel 13 dans `laravel/` | Agent |
| 2026-06-07 | Migrations, models, enums, seeders, routes squelette | Agent |
| 2026-06-07 | Fix migrations compatibles BDD legacy (`chrononews.db`, MyISAM, pas de FK) | Agent |
| 2026-06-07 | Phase 2 : Auth OTP, API articles/blocs, validation admin, login + dashboard | Agent |
| 2026-06-07 | Phase 2 complète : upload, auto-tagging, stats admin, users, settings, home videos | Agent |
| 2026-06-16 | Phase 4 : routes Laravel front + LegacyFrontController + assets symlinkés | Agent |
| 2026-06-16 | Accueil migré : `HomeController` + Blade (`layouts/public`) + partials `includes/pages/home/*` (design Elementor identique) | Agent |
| 2026-06-16 | Catégories migrées : `CategoryController` + partials `includes/pages/category/*` (pagination Laravel, pub flottante) | Agent |
| 2026-06-16 | Recherche migrée : `SearchController` + partials `includes/pages/search/*` + live search header corrigé | Agent |
| 2026-06-07 | Article migré : `ArticleController` + partials `includes/pages/article/*` (paywall, pubs, paiement FlexPay) | Agent |

### Note BDD legacy

Si tu pointes Laravel vers la **base legacy** (`chrononews.db`) :

- Exécuter `php artisan migrate --force` (pas `migrate:fresh` — ça ne supprime pas les données legacy)
- La table `sessions` est créée automatiquement si absente
- Les tables legacy (`actualites`, `users`, `paiements`…) sont **conservées**
- Les nouvelles tables Laravel (`articles`, `article_blocks`…) coexistent en parallèle
- Pas de clés étrangères vers `users` (MyISAM incompatible)

Pour une install **vierge**, créer une base `chrononews` dédiée et utiliser `migrate:fresh --seed`.

---

## Décisions

### Prises

| # | Sujet | Décision | Date |
|---|-------|----------|------|
| D1 | Type de migration | Migration **complète** (pas de coexistence prod) | 2026-06-07 |
| D2 | Emplacement Laravel | Dossier `laravel/`, legacy conservé à la racine en référence | 2026-06-07 |
| D3 | Schéma BDD | **Renommage propre** (ex. `actualites` → `articles`) | 2026-06-07 |
| D4 | Framework | Laravel **13** (installé via composer) | 2026-06-07 |

### En attente (guidance requise)

| # | Sujet | Options | Impact |
|---|-------|---------|--------|
| D5 | Dashboard admin | Livewire / Blade+Alpine / Inertia+Vue | Vitesse dev, UX |
| D6 | Passerelle paiement prod | FlexPay / MaxiCash / Les deux | Phase 3 |
| D7 | Design front public | Reprendre bopea à l'identique / Simplifier | Phase 4, durée |
| D8 | Correcteur IA (Gemini) | Porter / Reporter / Supprimer | Scope Phase 5 |

---

## Phase 1 — Fondations ✅

**Objectif :** Projet Laravel opérationnel, schéma BDD, couche domaine.

| Tâche | Statut | Fichiers / notes |
|-------|--------|------------------|
| Installer Laravel 13 | ✅ | `laravel/` |
| Migration `users` (OTP, rôles, profil) | ✅ | `database/migrations/0001_01_01_000000_create_users_table.php` |
| Migration tables métier | ✅ | `database/migrations/2026_06_07_000001_create_chrononews_core_tables.php` |
| Models Eloquent (12) | ✅ | `app/Models/` |
| Enums (6) | ✅ | `app/Enums/` |
| Config applicative | ✅ | `config/chrononews.php` |
| Seeder initial | ✅ | `database/seeders/DatabaseSeeder.php` |
| Routes web (squelette) | ✅ | `routes/web.php` |
| Routes API (squelette) | ✅ | `routes/api.php` |
| `.gitignore` racine | ✅ | `.gitignore` |
| Doc setup | ✅ | `MIGRATION.md` |
| Test `migrate:fresh --seed` | ✅ | SQLite local OK |

---

## Phase 2 — Auth OTP & CMS ✅

**Objectif :** Connexion par email/OTP, CRUD articles, workflow validation.

### Auth

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| `OtpService` (génération, expiration, rate limit) | ✅ | `publication/classes/Auth.php` |
| Notification email OTP | ✅ | `publication/classes/EmailService.php` |
| `AuthController` API (register, request_otp, verify_otp, logout) | ✅ | `publication/api/auth.php` |
| Session Laravel (remplace `$_SESSION`) | ✅ | `Auth.php` |
| Rate limiting OTP (email) | ✅ | Audit sécurité |
| Ne **jamais** renvoyer l'OTP dans la réponse JSON | ✅ | Fix audit |
| Middleware `EnsureUserRole` | ✅ | — |
| Page login Blade + JS fetch | ✅ | `publication/index.html` |
| Routes web `/auth/*` (CSRF) + API `/api/v1/auth/*` | ✅ | — |

### Articles & blocs

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| `ArticlePolicy` (rôles, propriété) | ✅ | `publication/classes/Article.php` |
| `ArticleController` CRUD API | ✅ | `publication/api/articles.php` |
| `ArticleBlockController` (+ vérif propriété — fix IDOR) | ✅ | `articles.php` cases blocks |
| `ArticleService` (create/update, statuts selon rôle) | ✅ | `Article.php` |
| Upload fichiers (`UploadService`, whitelist MIME + dimensions pub) | ✅ | `publication/api/upload.php` |
| `AutoTaggingService` (tags FR, table `article_tags`) | ✅ | `includes/auto_tagging.php` |
| Validation admin (approve/reject) | ✅ | `publication/api/admin.php` |
| Dashboard minimal (stats, articles, modération, users) | ✅ | — |

### Admin API

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| Stats dashboard | ✅ | `admin.php` |
| Liste articles en attente / tous les articles | ✅ | `admin.php` |
| Gestion utilisateurs (liste, création, toggle, rôle) | ✅ | `admin.php`, `Auth.php` |
| Paramètres globaux (`settings` / `global_settings`) | ✅ | `admin.php` |
| Vidéos home (CRUD + toggle) | ✅ | `admin.php` |
| Endpoints publics site (videos, settings, plans) | ✅ | `admin.php` |

**Fichiers clés Phase 2 :**

- `app/Services/Auth/OtpService.php`
- `app/Services/Media/UploadService.php`
- `app/Services/Article/AutoTaggingService.php`
- `app/Services/Admin/AdminStatsService.php`
- `app/Services/Admin/UserManagementService.php`
- `app/Http/Controllers/Api/V1/*` (Auth, Article, Upload, Admin*)
- `resources/views/auth/login.blade.php`
- `resources/views/dashboard/index.blade.php`

**Routes principales :**

| Méthode | Route | Auth |
|---------|-------|------|
| POST | `/auth/*` ou `/api/v1/auth/*` | Public / session |
| POST | `/api/v1/upload` | Utilisateur connecté |
| CRUD | `/api/v1/articles/*` | Utilisateur connecté |
| GET/POST | `/api/v1/admin/*` | admin / superadmin |
| GET | `/api/v1/site/*` | Public |

---

## Phase 3 — Paiements

**Objectif :** Publication payante, premium, abonnements, publicités payantes.

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| `PaymentService` | ⏳ | `publication/classes/Payment.php` |
| `FlexPayGateway` | ⏳ | `Payment.php`, `api/callback.php` |
| `MaxicashGateway` | ⏳ | `api/maxicash_callback.php` |
| Montant calculé **côté serveur** (fix audit) | ⏳ | `api/payments.php` |
| Callbacks **signés** (secret HMAC) | ⏳ | Audit sécurité |
| `PaymentController` API | ⏳ | `publication/api/payments.php` |
| Activation article après paiement | ⏳ | `Payment::handleCallback()` |
| Abonnements (`user_subscriptions`) | ⏳ | `Payment.php` |
| Achats premium (`article_purchases`) | ✅ via legacy | `viewer.php` paywall → `includes/pages/article/` |
| Emails confirmation paiement | ⏳ | `EmailService.php` |
| Page retour paiement | ⏳ | `publication/payment_return.php` |

---

## Phase 4 — Front public 🔄

**Objectif :** Site lecteur — accueil, article, catégorie, recherche, pages statiques.

**Approche actuelle :** migration page par page vers Blade en conservant le HTML/CSS Bopea legacy via `LegacyInclude`. L’accueil (`/`) est sur `HomeController` ; les autres pages passent encore par `LegacyFrontController`.

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| Routes Laravel (`/`, `/article/`, `/categorie/`, `/recherche`…) | ✅ | `.htaccess` |
| Accueil `/` → `HomeController` + partials legacy | ✅ | `index.php` → `includes/pages/home/` |
| Catégories `/categorie/{cat}` → `CategoryController` | ✅ | `category.php` → `includes/pages/category/` |
| Recherche `/recherche` → `SearchController` | ✅ | `search.php` → `includes/pages/search/` |
| `LegacyFront` + `LegacyFrontController` | ✅ | `index.php`, `viewer.php`, etc. |
| Symlinks assets (`img`, `css`, `wp-content`, `publication`) | ✅ | racine repo |
| BDD legacy lue depuis `laravel/.env` | ✅ | `publication/config/database.php` |
| Layout Blade (header, footer, sidebar) | 🔄 | header/footer via `LegacyInclude` |
| `PublicArticleService` (lecture native Eloquent) | ✅ | `article_loader.php` |
| Pages Blade natives (accueil + catégories + recherche + article complets) | ✅ | — |
| Page article + paywall | ✅ | `viewer.php` → `includes/pages/article/` |
| Assets CSS bopea (extraire, nettoyer) | ⏳ | `css/`, `wp-content/themes/bopea/` |
| Live search AJAX | ✅ via symlink `publication/ajax` | `live-search.php` |
| OG image | ✅ route `/og-image.php` | `og-image.php` |
| HTML Purifier sur contenu blocs (fix XSS) | ⏳ | Audit sécurité |

---

## Phase 5 — Dashboard admin

**Objectif :** Back-office rédaction, modération, pubs, paiements.

| Tâche | Statut | Legacy source |
|-------|--------|---------------|
| Choix stack admin (voir D5) | ⏳ | — |
| Dashboard utilisateur | ⏳ | `publication/dashboard/user/` |
| Dashboard super admin | ⏳ | `publication/dashboard/admin/` |
| Éditeur / publish article | ⏳ | `publish.html`, `publisher.js` |
| Gestion publicités | ⏳ | `publicites.php`, `admin-ads.js` |
| Tarifs publicités | ⏳ | `ads-pricing.html`, `tarifs_publicites.php` |
| Historique paiements | ⏳ | `payments.html`, `payments-interface.js` |
| Profil utilisateur | ⏳ | `profile.js` |
| Correcteur IA (si D8 = Porter) | ⏳ | `ai_proxy.php`, `ai-text-corrector.js` |

---

## Phase 6 — Finitions & déploiement

| Tâche | Statut | Notes |
|-------|--------|-------|
| Tests Feature (auth, articles, paiements) | ⏳ | |
| Tests paiement sandbox FlexPay/MaxiCash | ⏳ | Clés requises |
| Suppression fichiers legacy | ⏳ | Voir inventaire ci-dessous |
| Suppression scripts debug/test | ⏳ | Audit sécurité |
| Rotation secrets (SMTP, API keys) | ⏳ | Ne pas reprendre `config.php` legacy |
| Config prod `.env` | ⏳ | |
| Document root → `laravel/public` | ⏳ | |
| HTTPS + `SESSION_SECURE_COOKIE=true` | ⏳ | |
| CORS restrictif | ⏳ | |
| Sitemap / robots.txt | ⏳ | |
| Déploiement fintechmedias.cd | ⏳ | |

---

## Mapping BDD legacy → Laravel

| Table legacy | Table Laravel | Statut migration données |
|--------------|---------------|------------------------|
| `users` | `users` | ⏳ Script import à prévoir |
| `actualites` | `articles` | ⏳ |
| `block_news` | `article_blocks` | ⏳ |
| `article_tags` | `article_tags` | ⏳ |
| `paiements` | `payments` | ⏳ |
| `publicites` | `advertisements` | ⏳ |
| `tarifs_publicites` | `advertisement_rates` | ✅ Seeder |
| `subscription_plans` | `subscription_plans` | ✅ Seeder |
| `user_subscriptions` | `user_subscriptions` | ⏳ |
| `user_purchased_articles` | `article_purchases` | ⏳ |
| `global_settings` | `settings` | ✅ Seeder partiel |
| `home_video` | `home_videos` | ⏳ |
| `newsletter_subscribers` | `newsletter_subscribers` | ⏳ |

### Mapping valeurs enum

| Legacy | Laravel |
|--------|---------|
| `statut_validation: en_attente` | `validation_status: pending` |
| `statut_validation: valide` | `validation_status: approved` |
| `statut_validation: rejete` | `validation_status: rejected` |
| `statut_paiement: en_attente` | `payment_status: pending` |
| `statut_paiement: paye` | `payment_status: paid` |
| `statut_paiement: gratuit` | `payment_status: free` |
| `paiements.statut: en_attente` | `payments.status: pending` |
| `paiements.statut: reussi` | `payments.status: succeeded` |
| `paiements.statut: echoue` | `payments.status: failed` |
| `users.mail` | `users.email` |
| `users.nom` | `users.name` |
| `users.Titre` | `users.job_title` |
| `actualites.alaune = YES` | `articles.is_featured = true` |

---

## Inventaire fichiers legacy

### CMS / API (`publication/`)

| Fichier legacy | Remplacement Laravel | Statut |
|----------------|---------------------|--------|
| `classes/Article.php` | `ArticleService` + `Article` model | ⏳ |
| `classes/Auth.php` | `OtpService` + `AuthController` | ⏳ |
| `classes/Payment.php` | `PaymentService` + Gateways | ⏳ |
| `classes/Publicite.php` | `AdvertisementService` | ⏳ |
| `classes/EmailService.php` | Notifications Laravel | ⏳ |
| `api/auth.php` | `routes/api.php` + `AuthController` | ⏳ |
| `api/articles.php` | `ArticleController` | ⏳ |
| `api/admin.php` | `AdminController` | ⏳ |
| `api/payments.php` | `PaymentController` | ⏳ |
| `api/publicites.php` | `AdvertisementController` | ⏳ |
| `api/upload.php` | `UploadController` | ⏳ |
| `api/callback.php` | `WebhookController` | ⏳ |
| `api/ai_proxy.php` | Supprimer ou `AiService` sécurisé | ⏳ |
| `api/debug_session.php` | **Supprimer** | ⏳ |
| `api/migrate_video.php` | **Supprimer** | ⏳ |
| `dashboard/user/*` | Dashboard Livewire/Blade | ⏳ |
| `dashboard/admin/*` | Dashboard admin | ⏳ |

### Front public (racine)

| Fichier legacy | Remplacement Laravel | Statut |
|----------------|---------------------|--------|
| `index.php` | `HomeController` + `includes/pages/home/*` | 🔄 accès direct legacy conservé |
| `viewer.php` | `ArticleController@show` | ✅ |
| `category.php` | `CategoryController` + `includes/pages/category/*` | 🔄 accès direct legacy conservé |
| `search.php` | `SearchController` + `includes/pages/search/*` | 🔄 accès direct legacy conservé |
| `contact.php` | `ContactController` | ⏳ |
| `includes/header.php` | `layouts/partials/header.blade.php` | ⏳ |
| `includes/footer.php` | `layouts/partials/footer.blade.php` | ⏳ |
| `includes/sidebar.php` | `layouts/partials/sidebar.blade.php` | ⏳ |
| `includes/article_loader.php` | Eloquent `Article::published()` | ⏳ |
| `.htaccess` | `routes/web.php` | ⏳ |

### À supprimer (ne pas porter)

| Fichier | Raison |
|---------|--------|
| `test_insert_delete.php`, `test_delete.php` | Scripts debug |
| `publication/diags.php`, `gemini.php` | Diagnostic non sécurisé |
| `publication/api/debug_session.php` | Fuite session |
| `*old.php`, `indexolf.html` | Versions obsolètes |
| `wp-json/**` | Dump WordPress statique |
| Majorité de `wp-content/` | Assets à extraire sélectivement |

---

## Correctifs sécurité (intégrés à la migration)

Issues identifiées lors de l'audit — à cocher au fil de l'implémentation Laravel.

| # | Issue | Phase | Statut |
|---|-------|-------|--------|
| S1 | Proxy IA ouvert (SSRF) | 5 ou ❌ | ⏳ |
| S2 | Callbacks paiement non authentifiés | 3 | ⏳ |
| S3 | Scripts migration DB publics | 6 | ⏳ |
| S4 | `debug_session.php` exposé | 6 | ⏳ |
| S5 | Secrets en clair dans repo | 6 | ⏳ |
| S6 | IDOR blocs articles | 2 | ⏳ |
| S7 | Montant paiement client-side | 3 | ⏳ |
| S8 | Stored XSS blocs HTML | 4 | ⏳ |
| S9 | Upload extension non whitelistée | 2 | ⏳ |
| S10 | OTP sans rate limit + fuite en fallback | 2 | ⏳ |
| S11 | CORS `*` | 2–3 | ⏳ |
| S12 | Absence CSRF | 2 | ⏳ |

---

## Checklist environnement

### Développement local

- [ ] MySQL créé (`chrononews` ou `chrononews.db`)
- [ ] `laravel/.env` configuré (copie depuis `.env.example`)
- [ ] `php artisan key:generate`
- [ ] **`php artisan migrate --force`** (obligatoire — crée `sessions`, `cache`, tables Laravel)
- [ ] `php artisan db:seed` (optionnel si BDD legacy déjà peuplée)
- [ ] `php artisan serve` → http://127.0.0.1:8000
- [ ] SMTP configuré pour tests OTP (dev : `MAIL_MAILER=log` → OTP dans `storage/logs/laravel.log`)
- [ ] Sandbox FlexPay / MaxiCash (Phase 3)

### Production (fintechmedias.cd)

- [ ] Document root → `laravel/public`
- [ ] `.env` prod (secrets **nouveaux**, pas legacy)
- [ ] `APP_DEBUG=false`
- [ ] HTTPS + cookies sécurisés
- [ ] Queue worker (emails, callbacks)
- [ ] Logs + monitoring
- [ ] Backup BDD

---

## Prochaine session

**Priorité : Phase 4 — Front public (suite)**

1. `PublicArticleService` + pages Blade natives (accueil, article)
2. Porter header/footer en partials Blade
3. Phase 6 : document root prod → `laravel/public`

**Production :** pointer Apache/Nginx vers `laravel/public/` et exécuter `bash laravel/scripts/link-legacy-assets.sh`.

---

## Commandes utiles

```bash
# Démarrer le serveur dev
cd laravel && php artisan serve

# Réinitialiser la BDD
php artisan migrate:fresh --seed

# Lister les routes
php artisan route:list

# Créer un controller
php artisan make:controller Api/AuthController
```

---

*Dernière mise à jour : 16 juin 2026*
