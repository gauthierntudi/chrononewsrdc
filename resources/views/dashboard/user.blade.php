@extends('layouts.dashboard')

@section('title', 'Dashboard — '.config('chrononews.name'))
@section('body-class', '')

@section('sidebar')
    @include('dashboard.partials.sidebar-user')
@endsection

@section('content')
    <div id="stats-view" class="view active">
        <div class="view-header">
            <h2><i data-lucide="bar-chart-3" class="lucide-icon" aria-hidden="true"></i> Statistiques</h2>
            <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="userStatsRefreshBtn" title="Actualiser">
                <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                Actualiser
            </button>
        </div>

        <div id="statsContent">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>

    <div id="validation-view" class="view">
        <div class="view-header pending-view-header">
            <div>
                <h2><i data-lucide="clock" class="lucide-icon" aria-hidden="true"></i> En attente</h2>
                <p class="view-subtitle">Vos articles en attente de validation éditoriale</p>
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

    <div id="articles-view" class="view">
        <div class="view-header all-articles-view-header">
            <div>
                <h2><i data-lucide="newspaper" class="lucide-icon" aria-hidden="true"></i> Tous mes Articles</h2>
                <p class="view-subtitle">Catalogue de vos articles avec filtres par catégorie</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="userArticlesCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="userArticlesRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="userArticlesCategoriesContainer" class="category-filters-container"></div>

        <div class="all-articles-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="userArticlesSearchInput" placeholder="Rechercher un article, catégorie…">
            </div>
            <select id="userArticlesPerPageSelect" class="per-page-select" aria-label="Articles par page">
                <option value="8">8 par page</option>
                <option value="12" selected>12 par page</option>
                <option value="24">24 par page</option>
                <option value="48">48 par page</option>
            </select>
            <div class="articles-view-toggle" id="userArticlesViewToggle">
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

        <div id="userArticlesPanel" class="all-articles-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="userArticlesPagination" class="pagination"></div>
    </div>

    <div id="create-view" class="view">
        <div class="view-header">
            <h2><i data-lucide="circle-plus" class="lucide-icon" aria-hidden="true"></i> Créer un Article</h2>
            <button type="button" class="btn btn-secondary" data-view-link="articles">
                <i data-lucide="arrow-left" class="lucide-icon" aria-hidden="true"></i>
                Retour
            </button>
        </div>
        <div id="createContent"></div>
    </div>

    <div id="payments-view" class="view">
        <div class="view-header payments-view-header">
            <div>
                <h2><i data-lucide="banknote" class="lucide-icon" aria-hidden="true"></i> Mes Paiements</h2>
                <p class="view-subtitle">Historique de vos transactions articles, publicités et abonnements</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="userPaymentsCountLabel">—</span>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="userPaymentsRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="userPaymentsStatsGrid" class="stats-cards-row payments-stats-row"></div>

        <div class="all-articles-filters payments-filters">
            <div class="search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="userPaymentsSearchInput" placeholder="Rechercher titre, transaction…" autocomplete="off">
            </div>
            <div class="payments-filters__right">
                <div class="payments-status-filters" id="userPaymentsStatusFilters" role="group" aria-label="Filtrer par statut">
                    <button type="button" class="payments-status-btn active" data-status="all">Toutes</button>
                    <button type="button" class="payments-status-btn" data-status="reussi">Réussies</button>
                    <button type="button" class="payments-status-btn" data-status="en_attente">En attente</button>
                    <button type="button" class="payments-status-btn" data-status="echoue">Échouées</button>
                </div>
                <select id="userPaymentsPerPageSelect" class="per-page-select" aria-label="Paiements par page">
                    <option value="5">5 par page</option>
                    <option value="10" selected>10 par page</option>
                    <option value="20">20 par page</option>
                    <option value="50">50 par page</option>
                </select>
            </div>
        </div>

        <div class="payments-layout">
            <div class="payments-layout__main">
                <div id="userPaymentsTable" class="payments-panel">
                    <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
                </div>
                <div id="userPaymentsPagination" class="pagination"></div>
            </div>

            <aside class="payments-stats-panel stats-panel" aria-label="Statistiques des transactions">
                <div class="stats-panel-header">
                    <h3>Transactions</h3>
                    <select id="userPaymentsChartPeriod" class="stats-period-select" aria-label="Période du graphique">
                        <option value="monthly" selected>Mensuel</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="daily">Quotidien</option>
                    </select>
                </div>

                <div class="stats-amount-display">
                    <div class="stats-amount-value" id="userPaymentsChartAmount">$0.00</div>
                    <div class="stats-amount-subtitle" id="userPaymentsChartSubtitle">Chargement des statistiques…</div>
                </div>

                <div class="chart-container payments-line-chart">
                    <canvas id="userPaymentsLineChart" aria-label="Évolution des transactions"></canvas>
                </div>

                <div class="stats-section-title">Vue des Transactions</div>

                <div class="donut-chart-container">
                    <div class="donut-chart-wrapper">
                        <canvas id="userPaymentsDonutChart" aria-label="Répartition des transactions"></canvas>
                        <div class="donut-center-text">
                            <div class="donut-center-amount" id="userPaymentsDonutAmount">$0.00</div>
                            <div class="donut-center-growth" id="userPaymentsDonutGrowth">—</div>
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
                <h2><i data-lucide="megaphone" class="lucide-icon" aria-hidden="true"></i> Mes Publicités</h2>
                <p class="view-subtitle">{{ ($access['adsFree'] ?? false) ? 'Campagnes publicitaires gratuites' : 'Créez et gérez vos campagnes publicitaires payantes' }}</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="userAdsCountLabel">—</span>
                <button type="button" class="btn btn-primary btn-sm" id="userAdAddBtn">
                    <i data-lucide="plus" class="lucide-icon" aria-hidden="true"></i>
                    Nouvelle publicité
                </button>
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="userAdsRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="userAdsStatsGrid" class="stats-cards-row ads-stats-row"></div>

        <div class="ads-filters">
            <div class="search-box ads-search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="userAdsSearchInput" placeholder="Rechercher titre, emplacement…" autocomplete="off">
            </div>
            <div class="ads-filters__right">
                <div class="ads-filter-group">
                    <label for="userAdsFilterValidation">Validation</label>
                    <select id="userAdsFilterValidation" class="per-page-select">
                        <option value="">Toutes</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validées</option>
                        <option value="refuse">Refusées</option>
                    </select>
                </div>
                <div class="ads-filter-group">
                    <label for="userAdsFilterPayment">Paiement</label>
                    <select id="userAdsFilterPayment" class="per-page-select">
                        <option value="">Tous</option>
                        <option value="paye">Payées</option>
                        <option value="en_attente">En attente</option>
                        <option value="gratuit">Gratuites</option>
                    </select>
                </div>
                <div class="ads-filter-group">
                    <label for="userAdsFilterBroadcast">Diffusion</label>
                    <select id="userAdsFilterBroadcast" class="per-page-select">
                        <option value="">Toutes</option>
                        <option value="active">Actives</option>
                        <option value="inactive">Inactives</option>
                        <option value="terminee">Terminées</option>
                    </select>
                </div>
                <div class="ads-filter-group">
                    <label for="userAdsFilterPlacement">Emplacement</label>
                    <select id="userAdsFilterPlacement" class="per-page-select">
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
                <select id="userAdsPerPageSelect" class="per-page-select ads-per-page-select" aria-label="Publicités par page">
                    <option value="5">5 par page</option>
                    <option value="10" selected>10 par page</option>
                    <option value="20">20 par page</option>
                    <option value="50">50 par page</option>
                </select>
            </div>
        </div>

        <div id="userAdsList" class="ads-panel">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
        <div id="userAdsPagination" class="pagination"></div>
    </div>

    <div id="ads-pricing-view" class="view">
        <div class="view-header ads-pricing-view-header">
            <div>
                <h2><i data-lucide="tags" class="lucide-icon" aria-hidden="true"></i> Tarifs Publicitaires</h2>
                <p class="view-subtitle">Tarifs USD par format et durée de diffusion (7, 15 et 30 jours)</p>
            </div>
            <div class="view-header-actions">
                <span class="pending-count-badge" id="userAdsPricingCountLabel">—</span>
                @if($access['ownAds'] ?? false)
                <button type="button" class="btn btn-primary btn-sm" id="userAdsPricingCreateBtn">
                    <i data-lucide="plus" class="lucide-icon" aria-hidden="true"></i>
                    Nouvelle publicité
                </button>
                @endif
                <button type="button" class="btn btn-secondary btn-sm pending-refresh-btn" id="userAdsPricingRefreshBtn" title="Actualiser">
                    <i data-lucide="refresh-cw" class="lucide-icon" aria-hidden="true"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <div id="userAdsPricingInfo" class="ads-pricing-info" hidden></div>

        <div id="userAdsPricingStatsGrid" class="stats-cards-row ads-stats-row"></div>

        <div class="ads-filters ads-pricing-filters">
            <div class="search-box ads-search-box">
                <i data-lucide="search" class="lucide-icon" aria-hidden="true"></i>
                <input type="text" id="userAdsPricingSearchInput" placeholder="Rechercher un format, dimensions…" autocomplete="off">
            </div>
        </div>

        <div id="adsPricingGrid" class="ads-rates-grid">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement...</div>
        </div>
    </div>

    <div id="subscriptions-view" class="view">
        <div class="view-header">
            <h2><i data-lucide="credit-card" class="lucide-icon" aria-hidden="true"></i> Mon Abonnement</h2>
            <p class="view-subtitle">Accédez à tous les articles premium avec un abonnement</p>
        </div>

        <div id="currentSubscriptionContainer" class="current-sub-card" hidden>
            <div class="flex items-center justify-between mb-6 relative z-10">
                <div class="sub-status-badge">Actif</div>
                <i data-lucide="check-circle" class="lucide-icon" style="font-size: 32px; color: #a5f3fc;" aria-hidden="true"></i>
            </div>

            <div class="current-sub-info relative z-10">
                <h3>Plan actuel</h3>
                <div class="current-sub-plan" id="subPlanName">-</div>

                <div class="current-sub-details">
                    <div class="sub-detail-item">
                        <p>Expire le</p>
                        <h4 id="subExpiryDate">-</h4>
                    </div>
                    <div class="sub-detail-item">
                        <p>Statut</p>
                        <h4>En cours</h4>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-gray-800 mb-6 mt-8">Choisir un plan</h3>
        <div id="subscriptionPlansGrid" class="pricing-plans-grid">
            <div class="loading"><i data-lucide="loader-circle" class="lucide-icon lucide-spin" aria-hidden="true"></i> Chargement des plans...</div>
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
    @include('dashboard.partials.subscription-payment-modal')
    @include('dashboard.partials.user-ad-create-modal')

    <div id="userAdPaymentModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="userAdPaymentModalTitle">
        <div class="modal-content payment-modal-content">
            <button type="button" class="modal-close" id="userAdPaymentModalClose" aria-label="Fermer">&times;</button>
            <div id="userAdPaymentLoader" class="payment-loader-overlay is-hidden" hidden style="display: none;">
                <div class="payment-loader-content">
                    <div class="spinner"></div>
                    <p class="loader-text">Veuillez patienter s'il vous plaît</p>
                    <p class="loader-subtext">Ne quittez pas cette page pendant le traitement…</p>
                </div>
            </div>
            <div class="modal-body">
                <h3 id="userAdPaymentModalTitle" class="visually-hidden">Paiement publicité</h3>
                <div class="payment-amount" id="userAdPaymentAmountDisplay">$0.00</div>
                <div class="payment-description">
                    <p>Pour publier votre publicité, veuillez effectuer un paiement de <strong style="color:#d11810;"><span id="userAdPaymentAmountText">0</span> USD</strong></p>
                </div>
                <div class="payment-subtitle">Sélectionnez votre méthode de paiement préférée</div>
                <form id="userAdPaymentForm">
                    <input type="hidden" id="userAdPaymentAdId" value="">
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="userAdPaymentMethod" value="carte_bancaire" checked>
                            <div class="payment-method-content">
                                <img src="https://fintechmedias.cd/img/pictos/card.jpg" alt="Carte Bancaire" class="payment-method-img">
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="userAdPaymentMethod" value="mpesa">
                            <div class="payment-method-content">
                                <img src="https://fintechmedias.cd/img/pictos/mpesa01.jpg" alt="M-Pesa" class="payment-method-img">
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="userAdPaymentMethod" value="airtel_money">
                            <div class="payment-method-content">
                                <img src="https://fintechmedias.cd/img/pictos/airtel.jpg" alt="Airtel Money" class="payment-method-img">
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="userAdPaymentMethod" value="orange_money">
                            <div class="payment-method-content">
                                <img src="https://fintechmedias.cd/img/pictos/orange3.jpg" alt="Orange Money" class="payment-method-img">
                            </div>
                        </label>
                    </div>
                    <div class="phone-field-container" id="userAdPhoneFieldContainer">
                        <input type="tel" id="userAdPaymentPhone" placeholder="Ex: 243820000000 ou 081XXXXXXX" class="payment-phone-input" required>
                        <p class="phone-field-hint">Format : code pays + numéro (ex: 243820000000)</p>
                    </div>
                    <div class="payment-actions">
                        <button type="submit" class="btn btn-payment-primary">
                            <i data-lucide="lock" class="lucide-icon" aria-hidden="true"></i>
                            Procédez au paiement
                        </button>
                        <button type="button" class="btn btn-payment-cancel" id="userAdPaymentCancel">Annuler</button>
                    </div>
                    <div class="payment-security">Guaranteed safe &amp; secure checkout</div>
                </form>
            </div>
        </div>
    </div>

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

    <div id="userAdImageModal" class="modal article-preview-modal-wrap" aria-hidden="true">
        <div class="modal-content article-preview-modal ad-image-modal" role="dialog" aria-labelledby="userAdImageModalTitle">
            <div class="article-preview-modal__toolbar">
                <div class="article-preview-modal__toolbar-text">
                    <span class="article-preview-modal__eyebrow">Aperçu publicité</span>
                    <h3 id="userAdImageModalTitle">Publicité</h3>
                </div>
                <button type="button" class="modal-close article-preview-modal__close" id="userAdImageModalClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body ad-image-modal__body">
                <img id="userAdImageModalImg" alt="">
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>
<script src="{{ asset('js/dashboard/profile.js') }}"></script>
<script src="{{ asset('js/dashboard/user-stats.js') }}"></script>
<script src="{{ asset('js/dashboard/user-pending.js') }}"></script>
<script src="{{ asset('js/dashboard/user-articles.js') }}"></script>
<script src="{{ asset('js/dashboard/user-payments.js') }}"></script>
<script src="{{ asset('js/dashboard/user-ads.js') }}"></script>
<script src="{{ asset('js/dashboard/user-ads-pricing.js') }}"></script>
<script src="{{ asset('js/dashboard/user-subscriptions.js') }}"></script>
<script src="{{ asset('js/dashboard/user.js') }}"></script>
@endpush
