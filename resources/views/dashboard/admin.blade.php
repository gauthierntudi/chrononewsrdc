@extends('layouts.dashboard')

@section('title', 'Administration — '.config('chrononews.name'))
@section('body-class', 'admin-page')

@section('sidebar')
    @include('dashboard.partials.sidebar-admin')
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush

@section('content')
    <div id="stats-view" class="view active">
        <div class="view-header">
            <h2><i data-lucide="trending-up" class="lucide-icon" aria-hidden="true"></i> Statistiques</h2>
        </div>
        <div class="stats-cards-row" id="statsGrid">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>

        <div class="stats-charts-row">
            <div class="stats-chart-card">
                <div class="stats-chart-card__head">
                    <div>
                        <h4>Statistiques des Articles</h4>
                        <p class="stats-chart-card__subtitle">Évolution des vues et répartition du catalogue</p>
                    </div>
                    <span class="stats-chart-kpi" id="articlesChartKpi">—</span>
                </div>
                <div class="stats-chart-card__body">
                    <div class="stats-chart-canvas stats-chart-canvas--line">
                        <canvas id="articlesTrendChart"></canvas>
                    </div>
                    <div class="stats-chart-canvas stats-chart-canvas--donut">
                        <canvas id="articlesBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="stats-chart-card">
                <div class="stats-chart-card__head">
                    <div>
                        <h4>Statistiques des Publicités</h4>
                        <p class="stats-chart-card__subtitle">Impressions mensuelles et statut des campagnes</p>
                    </div>
                    <span class="stats-chart-kpi" id="adsChartKpi">—</span>
                </div>
                <div class="stats-chart-card__body">
                    <div class="stats-chart-canvas stats-chart-canvas--line">
                        <canvas id="adsTrendChart"></canvas>
                    </div>
                    <div class="stats-chart-canvas stats-chart-canvas--donut">
                        <canvas id="adsBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="validation-view" class="view">
        <div class="view-header pending-view-header">
            <div>
                <h2><i data-lucide="clock" class="lucide-icon" aria-hidden="true"></i> En attente</h2>
                <p class="view-subtitle">Articles soumis en attente de validation éditoriale</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="pendingCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="pendingRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>
        <div id="pendingCategoriesContainer" class="category-filters-container"></div>
        <div id="pendingArticlesTable" class="pending-queue data-table">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>

    <div id="all-articles-view" class="view">
        <div class="view-header all-articles-view-header">
            <div>
                <h2><i data-lucide="newspaper" class="lucide-icon" aria-hidden="true"></i> Tous les Articles</h2>
                <p class="view-subtitle">Catalogue complet avec filtres par catégorie</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="allArticlesCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="allArticlesRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="categoriesCardsContainer" class="category-filters-container"></div>

        <div class="all-articles-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="allArticlesSearchInput" placeholder="Rechercher un article, auteur, catégorie…">
            </div>
            <select id="allArticlesPerPageSelect" class="per-page-select" aria-label="Articles par page">
                <option value="8">8 par page</option>
                <option value="12" selected>12 par page</option>
                <option value="24">24 par page</option>
                <option value="48">48 par page</option>
            </select>
            <div class="articles-view-toggle" id="articlesViewToggle">
                <button type="button" class="view-toggle-btn active" data-mode="grid" title="Vue grille">
                    <i data-lucide="layout-grid" class="lucide-icon" aria-hidden="true"></i>
                    <span>Grille</span>
                </button>
                <button type="button" class="view-toggle-btn" data-mode="list" title="Vue liste">
                    <i data-lucide="list" class="lucide-icon" aria-hidden="true"></i>
                    <span>Liste</span>
                </button>
            </div>
        </div>

        <div id="allArticlesTable" class="all-articles-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="allArticlesPagination" class="pagination"></div>
    </div>

    <div id="users-view" class="view">
        <div class="view-header users-view-header">
            <div>
                <h2><i data-lucide="users" class="lucide-icon" aria-hidden="true"></i> Utilisateurs</h2>
                <p class="view-subtitle">Gestion des comptes, rôles et accès</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="usersCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="usersRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div class="all-articles-filters users-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="usersSearchInput" placeholder="Rechercher nom, email, téléphone…" autocomplete="off">
            </div>
            <select id="usersPerPageSelect" class="per-page-select" aria-label="Utilisateurs par page">
                <option value="5">5 par page</option>
                <option value="10" selected>10 par page</option>
                <option value="20">20 par page</option>
                <option value="50">50 par page</option>
            </select>
        </div>

        <div id="usersTable" class="users-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="usersPagination" class="pagination"></div>
    </div>

    @if($user->isSuperAdmin())
    <div id="newsletter-view" class="view">
        <div class="view-header users-view-header newsletter-view-header">
            <div>
                <h2><i data-lucide="mail" class="lucide-icon" aria-hidden="true"></i> Newsletter</h2>
                <p class="view-subtitle">Abonnés aux alertes e-mail du site</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="newsletterCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="newsletterRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="newsletterStatsGrid" class="stats-cards-row newsletter-stats-row" aria-label="Statistiques newsletter"></div>

        <div class="all-articles-filters users-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="newsletterSearchInput" placeholder="Rechercher par e-mail ou source…" autocomplete="off">
            </div>
            <select id="newsletterStatusSelect" class="per-page-select" aria-label="Filtrer par statut">
                <option value="all">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
            </select>
            <select id="newsletterPerPageSelect" class="per-page-select" aria-label="Abonnés par page">
                <option value="10" selected>10 par page</option>
                <option value="20">20 par page</option>
                <option value="50">50 par page</option>
            </select>
        </div>

        <div id="newsletterTable" class="users-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="newsletterPagination" class="pagination"></div>
    </div>
    @endif

    <div id="payments-view" class="view">
        <div class="view-header payments-view-header">
            <div>
                <h2><i data-lucide="banknote" class="lucide-icon" aria-hidden="true"></i> Paiements</h2>
                <p class="view-subtitle">Historique des transactions articles, publicités et abonnements</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="paymentsCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="paymentsRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="paymentsStatsGrid" class="stats-cards-row payments-stats-row"></div>

        <div class="all-articles-filters payments-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="paymentsSearchInput" placeholder="Rechercher titre, auteur, transaction…" autocomplete="off">
            </div>
            <div class="payments-filters__right">
                <div class="payments-status-filters" id="paymentsStatusFilters" role="group" aria-label="Filtrer par statut">
                    <button type="button" class="payments-status-btn active" data-status="all">Toutes</button>
                    <button type="button" class="payments-status-btn" data-status="reussi">Réussies</button>
                    <button type="button" class="payments-status-btn" data-status="en_attente">En attente</button>
                    <button type="button" class="payments-status-btn" data-status="echoue">Échouées</button>
                </div>
                <select id="paymentsPerPageSelect" class="per-page-select" aria-label="Paiements par page">
                    <option value="5">5 par page</option>
                    <option value="10" selected>10 par page</option>
                    <option value="20">20 par page</option>
                    <option value="50">50 par page</option>
                </select>
            </div>
        </div>

        <div class="payments-layout">
            <div class="payments-layout__main">
                <div id="paymentsTable" class="payments-panel">
                    <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
                </div>
                <div id="paymentsPagination" class="pagination"></div>
            </div>

            <aside class="payments-stats-panel stats-panel" aria-label="Statistiques des transactions">
                <div class="stats-panel-header">
                    <h3>Transactions</h3>
                    <select id="paymentsChartPeriod" class="stats-period-select" aria-label="Période du graphique">
                        <option value="monthly" selected>Mensuel</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="daily">Quotidien</option>
                    </select>
                </div>

                <div class="stats-amount-display">
                    <div class="stats-amount-value" id="paymentsChartAmount">$0.00</div>
                    <div class="stats-amount-subtitle" id="paymentsChartSubtitle">Chargement des statistiques…</div>
                </div>

                <div class="chart-container payments-line-chart">
                    <canvas id="paymentsLineChart" aria-label="Évolution des transactions"></canvas>
                </div>

                <div class="stats-section-title">Vue des Transactions</div>

                <div class="donut-chart-container">
                    <div class="donut-chart-wrapper">
                        <canvas id="paymentsDonutChart" aria-label="Répartition des transactions"></canvas>
                        <div class="donut-center-text">
                            <div class="donut-center-amount" id="paymentsDonutAmount">$0.00</div>
                            <div class="donut-center-growth" id="paymentsDonutGrowth">—</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#1E5EFF;"></span>
                            <span>Réussies</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#f59e0b;"></span>
                            <span>En attente</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#d11810;"></span>
                            <span>Échouées</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    <div id="ads-view" class="view">
        <div class="view-header ads-view-header">
            <div>
                <h2><i data-lucide="megaphone" class="lucide-icon" aria-hidden="true"></i> Publicités</h2>
                <p class="view-subtitle">Validation, diffusion et suivi des campagnes publicitaires</p>
            </div>
            <div class="view-header-actions">
                @if($access['ownAds'] ?? false)
                <button type="button" class="btn btn-primary btn-sm" id="userAdAddBtn">
                    <i data-lucide="plus" class="lucide-icon" aria-hidden="true"></i>
                    Nouvelle publicité
                </button>
                @endif
                <span class="pending-count-badge" id="adsCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="adsRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="adsStatsGrid" class="stats-cards-row ads-stats-row"></div>

        <div class="ads-filters">
            <div class="search-box ads-search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="adsSearchInput" placeholder="Rechercher titre, annonceur, emplacement…" autocomplete="off">
            </div>
            <div class="ads-filters__right">
            <div class="ads-filter-group">
                <label for="adsFilterValidation">Validation</label>
                <select id="adsFilterValidation" class="per-page-select">
                    <option value="">Toutes</option>
                    <option value="en_attente">En attente</option>
                    <option value="valide">Validées</option>
                    <option value="refuse">Refusées</option>
                </select>
            </div>
            <div class="ads-filter-group">
                <label for="adsFilterPayment">Paiement</label>
                <select id="adsFilterPayment" class="per-page-select">
                    <option value="">Tous</option>
                    <option value="paye">Payées</option>
                    <option value="en_attente">En attente</option>
                    <option value="gratuit">Gratuites</option>
                </select>
            </div>
            <div class="ads-filter-group">
                <label for="adsFilterBroadcast">Diffusion</label>
                <select id="adsFilterBroadcast" class="per-page-select">
                    <option value="">Toutes</option>
                    <option value="active">Actives</option>
                    <option value="inactive">Inactives</option>
                    <option value="terminee">Terminées</option>
                </select>
            </div>
            <div class="ads-filter-group">
                <label for="adsFilterPlacement">Emplacement</label>
                <select id="adsFilterPlacement" class="per-page-select">
                    <option value="">Tous</option>
                    <option value="pub-header">Header</option>
                    <option value="pub-modal">Modal</option>
                    <option value="pub-float">Flottant</option>
                    <option value="pub-body-1">Corps 1</option>
                    <option value="pub-body-2">Corps 2</option>
                    <option value="pub-body-3">Corps 3</option>
                    <option value="pub-body-sidebar-1">Sidebar 1</option>
                    <option value="pub-body-sidebar-2">Sidebar 2</option>
                    <option value="pub-footer">Footer</option>
                </select>
            </div>
            <select id="adsPerPageSelect" class="per-page-select ads-per-page-select" aria-label="Publicités par page">
                <option value="5">5 par page</option>
                <option value="10" selected>10 par page</option>
                <option value="20">20 par page</option>
                <option value="50">50 par page</option>
            </select>
            </div>
        </div>

        <div id="adsList" class="ads-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="adsPagination" class="pagination"></div>
    </div>
    <div id="home-video-view" class="view">
        <div class="view-header home-video-view-header">
            <div>
                <h2><i data-lucide="video" class="lucide-icon" aria-hidden="true"></i> Vidéos Accueil</h2>
                <p class="view-subtitle">Gérer les vidéos YouTube affichées sur la page d'accueil</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="homeVideosCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="homeVideoRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="homeVideoAddBtn">
                    <i data-lucide="plus" class="lucide-icon" aria-hidden="true"></i>
                    Ajouter une vidéo
                </button>
            </div>
        </div>

        <div id="homeVideosGrid" class="home-videos-grid">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>
    <div id="ads-pricing-view" class="view">
        <div class="view-header ads-pricing-view-header">
            <div>
                <h2><i data-lucide="tags" class="lucide-icon" aria-hidden="true"></i> Tarifs Publicitaires</h2>
                <p class="view-subtitle">Tarifs USD par format et durée de diffusion (7, 15 et 30 jours)</p>
            </div>
            <div class="view-header-actions">
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="adsPricingRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>
        <div id="adsPricingGrid" class="ads-rates-grid">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>
    <div id="settings-view" class="view">
        <div class="view-header settings-view-header">
            <div>
                <h2><i data-lucide="settings" class="lucide-icon" aria-hidden="true"></i> Paramètres</h2>
                <p class="view-subtitle">Configuration globale de la plateforme</p>
            </div>
        </div>
        <div id="settingsContent" class="settings-layout">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>

    <div id="profile-view" class="view">
        <div class="view-header profile-view-header">
            <div>
                <h2><i data-lucide="circle-user" class="lucide-icon" aria-hidden="true"></i> Mon Profil</h2>
                <p class="view-subtitle">Gérez vos informations personnelles et votre photo</p>
            </div>
        </div>
        @include('dashboard.partials.profile-form')
    </div>
@endsection

@section('modals')
    <div id="articlePreviewModal" class="modal article-preview-modal-wrap" aria-hidden="true">
        <div class="modal-content article-preview-modal" role="dialog" aria-labelledby="articlePreviewModalTitle">
            <div class="article-preview-modal__toolbar">
                <div class="article-preview-modal__toolbar-text">
                    <span class="article-preview-modal__eyebrow">Aperçu article</span>
                    <h3 id="articlePreviewModalTitle">Chargement…</h3>
                </div>
                <button type="button" class="modal-close article-preview-modal__close" id="articlePreviewModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body article-preview-modal__body" id="articlePreviewModalBody">
                <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
            </div>
            <div class="modal-footer article-preview-modal__footer" id="articlePreviewModalFooter" hidden>
                <button type="button" class="btn btn-secondary btn-sm" id="articlePreviewModalCloseBtn">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                    Fermer
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="articlePreviewModalEditBtn">
                    <i data-lucide="pencil" class="lucide-icon" aria-hidden="true"></i>
                    Modifier
                </button>
            </div>
        </div>
    </div>

    @if($access['ownAds'] ?? false)
        @include('dashboard.partials.user-ad-create-modal')
    @endif

    @if($access['globalAds'] ?? false)
        @include('dashboard.partials.admin-ad-edit-modal')
    @endif

    <div id="adRefuseModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="adRefuseMessage">
        <div class="app-dialog">
            <p class="app-dialog__message" id="adRefuseMessage"></p>
            <p class="app-dialog__hint">Indiquez le motif du refus pour l'annonceur.</p>
            <textarea id="adRefuseInput" class="app-dialog__textarea" rows="4" placeholder="Ex. : Format d'image non conforme…"></textarea>
            <div class="app-dialog__actions">
                <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="adRefuseCancel">Annuler</button>
                <button type="button" class="app-dialog__btn app-dialog__btn--confirm" id="adRefuseConfirm">Refuser</button>
            </div>
        </div>
    </div>

    <div id="adImageModal" class="modal article-preview-modal-wrap" aria-hidden="true">
        <div class="modal-content article-preview-modal ad-image-modal" role="dialog" aria-labelledby="adImageModalTitle">
            <div class="article-preview-modal__toolbar">
                <div class="article-preview-modal__toolbar-text">
                    <span class="article-preview-modal__eyebrow">Aperçu publicité</span>
                    <h3 id="adImageModalTitle">Publicité</h3>
                </div>
                <button type="button" class="modal-close article-preview-modal__close" id="adImageModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body ad-image-modal__body">
                <img id="adImageModalImg" alt="">
            </div>
        </div>
    </div>

    <div id="rejectReasonModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="rejectReasonMessage">
        <div class="app-dialog app-dialog--form">
            <p class="app-dialog__message" id="rejectReasonMessage"></p>
            <p class="app-dialog__hint">Indiquez la raison du rejet. Elle pourra être communiquée à l'auteur.</p>
            <textarea id="rejectReasonInput" class="app-dialog__textarea" rows="4" placeholder="Ex. : Le contenu ne respecte pas les normes éditoriales…"></textarea>
            <div class="app-dialog__actions">
                <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="rejectReasonCancel">Annuler</button>
                <button type="button" class="app-dialog__btn app-dialog__btn--confirm" id="rejectReasonConfirm">Rejeter</button>
            </div>
        </div>
    </div>

    <div id="userEditModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="userEditModalTitle">
        <div class="app-dialog app-dialog--form app-dialog--wide user-edit-dialog">
            <div class="user-edit-modal__header">
                <h3 id="userEditModalTitle">Modifier l'utilisateur</h3>
                <button type="button" class="user-edit-modal__close" id="userEditModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <form id="userEditForm" class="user-edit-form">
                <input type="hidden" id="userEditId" name="id">
                <input type="hidden" id="userEditCover" name="cover">

                <div class="user-edit-avatar-section">
                    <div class="user-edit-avatar-ring">
                        <img id="userEditAvatarPreview" src="{{ asset('assets/img/user.jpg') }}" alt="Photo de profil" class="user-edit-avatar-preview">
                    </div>
                    <div class="user-edit-avatar-actions">
                        <button type="button" class="btn btn-secondary btn-sm" id="userEditAvatarBtn">
                            <i data-lucide="camera" class="lucide-icon" aria-hidden="true"></i>
                            Changer la photo
                        </button>
                        <p class="user-edit-avatar-hint">Recadrage carré · JPG/PNG · max 5 Mo</p>
                    </div>
                    <input type="file" id="userEditAvatarInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                </div>

                <div class="user-edit-form__grid">
                    <label class="user-edit-field">
                        <span>Nom *</span>
                        <input type="text" id="userEditNom" name="nom" required maxlength="255">
                    </label>
                    <label class="user-edit-field">
                        <span>Email *</span>
                        <input type="email" id="userEditEmail" name="email" required maxlength="255">
                    </label>
                    <label class="user-edit-field">
                        <span>Téléphone</span>
                        <input type="text" id="userEditPhone" name="telephone" maxlength="50">
                    </label>
                    <label class="user-edit-field">
                        <span>Titre / fonction</span>
                        <input type="text" id="userEditTitre" name="titre" maxlength="255">
                    </label>
                    <label class="user-edit-field">
                        <span>Rôle</span>
                        <select id="userEditRole" name="role">
                            <option value="user">Utilisateur</option>
                            <option value="journaliste">Journaliste</option>
                            <option value="admin">Administrateur</option>
                            <option value="superadmin">Super administrateur</option>
                        </select>
                    </label>
                    <label class="user-edit-field user-edit-status-field">
                        <span>Compte actif</span>
                        <div class="user-edit-status-control">
                            <span class="switch">
                                <input type="checkbox" id="userEditStatus" name="status" value="1">
                                <span class="slider round"></span>
                            </span>
                        </div>
                    </label>
                </div>
                <label class="user-edit-field user-edit-field--full">
                    <span>Bio</span>
                    <textarea id="userEditBio" name="bio" rows="3" maxlength="5000"></textarea>
                </label>
                <div class="user-edit-form__grid">
                    <label class="user-edit-field">
                        <span>Facebook</span>
                        <input type="text" id="userEditFacebook" name="facebook" maxlength="255">
                    </label>
                    <label class="user-edit-field">
                        <span>YouTube</span>
                        <input type="text" id="userEditYoutube" name="youtube" maxlength="255">
                    </label>
                    <label class="user-edit-field user-edit-field--mb">
                        <span>Twitter / X</span>
                        <input type="text" id="userEditTwitter" name="twitter" maxlength="255">
                    </label>
                    <label class="user-edit-field user-edit-field--mb">
                        <span>Instagram</span>
                        <input type="text" id="userEditInstagram" name="instagram" maxlength="255">
                    </label>
                </div>
                <p class="app-dialog__hint" id="userEditHint"></p>
                <div class="app-dialog__actions">
                    <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="userEditCancel">Annuler</button>
                    <button type="submit" class="app-dialog__btn app-dialog__btn--confirm" id="userEditSave">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="adsPricingModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="adsPricingModalTitle">
        <div class="app-dialog app-dialog--form user-edit-dialog">
            <div class="user-edit-modal__header">
                <h3 id="adsPricingModalTitle">Modifier le tarif</h3>
                <button type="button" class="user-edit-modal__close" id="adsPricingModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <form id="adsPricingForm" class="user-edit-form">
                <input type="hidden" id="adsPricingId" name="id">
                <div class="user-edit-form__grid user-edit-form__grid--single">
                    <label class="user-edit-field user-edit-field--full">
                        <span>Format</span>
                        <input type="text" id="adsPricingFormat" disabled>
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Dimensions</span>
                        <input type="text" id="adsPricingDimensions" disabled>
                    </label>
                    <label class="user-edit-field">
                        <span>Prix 7 jours (USD) *</span>
                        <input type="number" id="adsPricing7" name="price_7_days" min="0" step="0.01" required>
                    </label>
                    <label class="user-edit-field">
                        <span>Prix 15 jours (USD) *</span>
                        <input type="number" id="adsPricing15" name="price_15_days" min="0" step="0.01" required>
                    </label>
                    <label class="user-edit-field">
                        <span>Prix 30 jours (USD) *</span>
                        <input type="number" id="adsPricing30" name="price_30_days" min="0" step="0.01" required>
                    </label>
                </div>
                <div class="app-dialog__actions">
                    <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="adsPricingCancel">Annuler</button>
                    <button type="submit" class="app-dialog__btn app-dialog__btn--confirm" id="adsPricingSave">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="subscriptionPlanModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="subscriptionPlanModalTitle">
        <div class="app-dialog app-dialog--form user-edit-dialog">
            <div class="user-edit-modal__header">
                <h3 id="subscriptionPlanModalTitle">Ajouter un plan</h3>
                <button type="button" class="user-edit-modal__close" id="subscriptionPlanModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <form id="subscriptionPlanForm" class="user-edit-form">
                <input type="hidden" id="subscriptionPlanId" name="id">
                <div class="user-edit-form__grid">
                    <label class="user-edit-field user-edit-field--full">
                        <span>Nom du plan *</span>
                        <input type="text" id="subscriptionPlanName" name="name" required maxlength="255" placeholder="Ex. Hebdomadaire">
                    </label>
                    <label class="user-edit-field">
                        <span>Durée (jours) *</span>
                        <input type="number" id="subscriptionPlanDuration" name="duration_days" required min="1" placeholder="30">
                    </label>
                    <label class="user-edit-field">
                        <span>Prix ($) *</span>
                        <input type="number" id="subscriptionPlanPrice" name="price" required min="0" step="0.01" placeholder="10.00">
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Description</span>
                        <textarea id="subscriptionPlanDescription" name="description" rows="3" maxlength="1000" placeholder="Avantages du plan…"></textarea>
                    </label>
                    <label class="user-edit-field user-edit-status-field">
                        <span>Plan actif</span>
                        <div class="user-edit-status-control">
                            <span class="switch">
                                <input type="checkbox" id="subscriptionPlanActive" name="is_active" value="1" checked>
                                <span class="slider round"></span>
                            </span>
                        </div>
                    </label>
                </div>
                <div class="app-dialog__actions">
                    <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="subscriptionPlanCancel">Annuler</button>
                    <button type="submit" class="app-dialog__btn app-dialog__btn--confirm" id="subscriptionPlanSave">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="socialMediaModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="socialMediaModalTitle">
        <div class="app-dialog app-dialog--form user-edit-dialog">
            <div class="user-edit-modal__header">
                <h3 id="socialMediaModalTitle">Modifier le réseau social</h3>
                <button type="button" class="user-edit-modal__close" id="socialMediaModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <form id="socialMediaForm" class="user-edit-form">
                <input type="hidden" id="socialMediaNetwork" name="network">
                <div class="user-edit-form__grid">
                    <label class="user-edit-field user-edit-field--full">
                        <span>URL</span>
                        <input type="url" id="socialMediaUrl" name="url" maxlength="500" placeholder="https://…">
                        <small class="home-video-field-hint">Laissez vide pour masquer ce réseau sur le site.</small>
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Titre affiché</span>
                        <input type="text" id="socialMediaTitle" name="title" maxlength="100" placeholder="Facebook">
                    </label>
                    <label class="user-edit-field">
                        <span>Compteur</span>
                        <input type="text" id="socialMediaCount" name="count" maxlength="30" placeholder="23k">
                    </label>
                    <label class="user-edit-field">
                        <span>Libellé compteur</span>
                        <input type="text" id="socialMediaCountLabel" name="count_label" maxlength="50" placeholder="Likes">
                    </label>
                </div>
                <div class="app-dialog__actions">
                    <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="socialMediaCancel">Annuler</button>
                    <button type="submit" class="app-dialog__btn app-dialog__btn--confirm" id="socialMediaSave">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="homeVideoModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="homeVideoModalTitle">
        <div class="app-dialog app-dialog--form user-edit-dialog">
            <div class="user-edit-modal__header">
                <h3 id="homeVideoModalTitle">Ajouter une vidéo</h3>
                <button type="button" class="user-edit-modal__close" id="homeVideoModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <form id="homeVideoForm" class="user-edit-form">
                <input type="hidden" id="homeVideoId" name="id">
                <div class="user-edit-form__grid user-edit-form__grid--single">
                    <label class="user-edit-field user-edit-field--full">
                        <span>ID YouTube *</span>
                        <input type="text" id="homeVideoYoutubeId" name="youtube_id" required maxlength="50" placeholder="Ex. tgbNymZ7vqY" autocomplete="off">
                        <small class="home-video-field-hint">À la fin de l'URL : youtube.com/watch?v=<strong>ID</strong></small>
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Titre *</span>
                        <input type="text" id="homeVideoTitle" name="title" required maxlength="255" placeholder="Titre de la vidéo">
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Sous-titre</span>
                        <input type="text" id="homeVideoSubtitle" name="subtitle" maxlength="255" placeholder="Description courte (optionnel)">
                    </label>
                    <label class="user-edit-field user-edit-field--full">
                        <span>Site web (URL)</span>
                        <input type="url" id="homeVideoWebsiteUrl" name="website_url" maxlength="500" placeholder="https://…">
                    </label>
                    <label class="user-edit-field user-edit-status-field">
                        <span>Vidéo active</span>
                        <div class="user-edit-status-control">
                            <span class="switch">
                                <input type="checkbox" id="homeVideoActive" name="is_active" value="1" checked>
                                <span class="slider round"></span>
                            </span>
                        </div>
                    </label>
                </div>
                <div class="app-dialog__actions">
                    <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="homeVideoCancel">Annuler</button>
                    <button type="submit" class="app-dialog__btn app-dialog__btn--confirm" id="homeVideoSave">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="userAvatarCropperModal" class="modal user-avatar-cropper-modal" aria-hidden="true" role="dialog" aria-labelledby="userAvatarCropperTitle">
        <div class="modal-content user-avatar-cropper-content">
            <div class="user-avatar-cropper__header">
                <h3 id="userAvatarCropperTitle">
                    <i data-lucide="crop" class="lucide-icon" aria-hidden="true"></i>
                    Recadrer la photo
                </h3>
                <button type="button" class="user-avatar-cropper__close" id="userAvatarCropperClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="user-avatar-cropper__body">
                <div class="user-avatar-cropper__wrapper">
                    <img id="userAvatarCropperImage" alt="Recadrage">
                </div>
            </div>
            <div class="user-avatar-cropper__footer">
                <button type="button" class="btn btn-secondary" id="userAvatarCropperCancel">Annuler</button>
                <button type="button" class="btn btn-primary" id="userAvatarCropperConfirm">
                    <i data-lucide="check" class="lucide-icon" aria-hidden="true"></i>
                    Appliquer
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
@if(($access['ownAds'] ?? false) || ($access['globalAds'] ?? false))
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endif
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@if(($access['ownAds'] ?? false) || ($access['globalAds'] ?? false))
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>
@endif
@if($access['ownAds'] ?? false)
<script src="{{ asset('js/dashboard/user-ads.js') }}"></script>
@endif
@if($access['globalAds'] ?? false)
<script src="{{ asset('js/dashboard/admin-ad-edit.js') }}"></script>
@endif
<script src="{{ asset('js/dashboard/admin.js') }}"></script>
@endpush
