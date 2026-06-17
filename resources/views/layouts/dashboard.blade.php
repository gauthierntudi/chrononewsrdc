<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard — '.config('chrononews.name'))</title>
    @include('partials.favicon')
    @include('partials.fonts-auth-dashboard')
    <link rel="stylesheet" href="{{ asset('css/dashboard/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/pricing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/brand.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { corePlugins: { preflight: false } };
    </script>
    @stack('styles')
</head>
<body class="dashboard-page @yield('body-class')">
    @include('dashboard.partials.navbar')

    <div class="dashboard-container">
        @yield('sidebar')

        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Menu">
            <i data-lucide="menu" class="lucide-icon" aria-hidden="true"></i>
        </button>

        <div class="view-loader" id="viewLoader">
            <div class="view-loader-content">
                <div class="view-loader-spinner"></div>
                <div class="view-loader-text">Chargement...</div>
            </div>
        </div>

        <main class="dashboard-content md:ml-64 transition-all duration-300">
            @yield('content')
        </main>
    </div>

    @yield('modals')

    <div id="appDialogModal" class="modal app-dialog-modal" aria-hidden="true" role="dialog" aria-labelledby="appDialogMessage">
        <div class="app-dialog">
            <p class="app-dialog__message" id="appDialogMessage"></p>
            <div class="app-dialog__actions">
                <button type="button" class="app-dialog__btn app-dialog__btn--cancel" id="appDialogCancel">Annuler</button>
                <button type="button" class="app-dialog__btn app-dialog__btn--confirm" id="appDialogConfirm">Confirmer</button>
            </div>
        </div>
    </div>

    <div id="profileCropperModal" class="modal user-avatar-cropper-modal" aria-hidden="true" role="dialog" aria-labelledby="profileCropperTitle">
        <div class="modal-content user-avatar-cropper-content">
            <div class="user-avatar-cropper__header">
                <h3 id="profileCropperTitle">
                    <i data-lucide="crop" class="lucide-icon" aria-hidden="true"></i>
                    Recadrer la photo
                </h3>
                <button type="button" class="user-avatar-cropper__close" id="profileCropperClose" aria-label="Fermer">
                    <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="user-avatar-cropper__body">
                <div class="user-avatar-cropper__wrapper">
                    <img id="profileCropperImage" alt="Recadrage">
                </div>
            </div>
            <div class="user-avatar-cropper__footer">
                <button type="button" class="btn btn-secondary" id="profileCropperCancel">Annuler</button>
                <button type="button" class="btn btn-primary" id="profileCropperConfirm">
                    <i data-lucide="check" class="lucide-icon" aria-hidden="true"></i>
                    Appliquer
                </button>
            </div>
        </div>
    </div>

    <script>
        window.CHRONONEWS_DASHBOARD = {
            apiBase: '/api/v1',
            loginUrl: @json(route('login')),
            dashboardUrl: @json(route('dashboard')),
            adminDashboardUrl: @json(route('dashboard.admin')),
            user: @json($user->toAuthArray()),
            isAdmin: @json($user->isAdmin()),
            isSuperAdmin: @json($user->isSuperAdmin()),
            access: @json($access ?? \App\Support\DashboardAccess::for($user)),
            defaultAvatar: @json(asset('assets/img/user.jpg')),
            mediaBaseUrl: @json(app(\App\Services\Media\MediaUrlService::class)->publicBaseUrl()),
            categories: @json(config('chrononews.article.categories', [])),
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset('js/dashboard/utils.js') }}"></script>
    <script src="{{ asset('js/dashboard/dialogs.js') }}"></script>
    <script src="{{ asset('js/dashboard/icons.js') }}"></script>
    <script src="{{ asset('js/dashboard/notifications.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script src="{{ asset('js/dashboard/profile.js') }}"></script>
    @stack('scripts')
</body>
</html>
