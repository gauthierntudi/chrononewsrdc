<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — {{ config('chrononews.name') }}</title>
    @include('partials.favicon')
    @include('partials.fonts-auth-dashboard')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
</head>
<body class="auth-page"
      data-dashboard-url="{{ route('dashboard') }}"
      data-redirect-url="{{ $redirectUrl ?? '' }}"
      data-auth-action="{{ $authAction ?? '' }}">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="{{ asset(config('chrononews.brand.assets.logo_admin')) }}" alt="{{ config('chrononews.name') }}">
                <p>{{ config('chrononews.brand.tagline') }}</p>
            </div>

            @if(($authAction ?? null) === 'buy')
                <div class="auth-context-banner" role="status">
                    <ion-icon name="cart-outline"></ion-icon>
                    Connectez-vous ou créez un compte pour finaliser votre achat.
                </div>
            @endif

            <div id="auth-forms">
                <div id="email-form" class="auth-form active">
                    <h2>Connexion / Inscription</h2>
                    <form id="emailForm">
                        <div class="form-group">
                            <label for="email"><ion-icon name="mail-outline"></ion-icon> Email</label>
                            <div class="input-with-icon">
                                <ion-icon name="mail-outline"></ion-icon>
                                <input type="email" id="email" name="email" required placeholder="votre@email.com" autocomplete="email">
                            </div>
                        </div>
                        <div class="form-group" id="nameGroup" style="display: none;">
                            <label for="nom"><ion-icon name="person-outline"></ion-icon> Nom complet</label>
                            <div class="input-with-icon">
                                <ion-icon name="person-outline"></ion-icon>
                                <input type="text" id="nom" name="nom" placeholder="Votre nom complet" autocomplete="name">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <ion-icon name="arrow-forward-outline"></ion-icon>
                                Continuer
                            </button>
                        </div>
                        <div class="form-toggle">
                            <button type="button" id="toggleMode" class="btn-link">
                                <ion-icon name="person-add-outline"></ion-icon>
                                Nouveau compte ? Inscrivez-vous
                            </button>
                        </div>
                    </form>
                </div>

                <div id="otp-form" class="auth-form">
                    <h2>Vérification</h2>
                    <p class="otp-message">
                        <ion-icon name="shield-checkmark-outline"></ion-icon>
                        Entrez le code à 6 chiffres envoyé à <strong id="otpEmailDisplay"></strong>
                    </p>
                    <form id="otpForm">
                        <div class="otp-inputs" id="otpInputs">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="{{ $i }}" autocomplete="one-time-code" aria-label="Chiffre {{ $i + 1 }}">
                            @endfor
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <ion-icon name="checkmark-outline"></ion-icon>
                                Vérifier
                            </button>
                            <button type="button" id="backToEmail" class="btn btn-secondary">
                                <ion-icon name="arrow-back-outline"></ion-icon>
                                Retour
                            </button>
                        </div>
                        <div class="otp-resend">
                            <button type="button" id="resendOtp" class="btn-link" disabled>
                                <ion-icon name="refresh-outline"></ion-icon>
                                <span id="resendOtpLabel">Renvoyer le code</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader-container">
            <div class="loader"></div>
            <div class="loader-text" id="loaderText">Chargement...</div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="{{ asset('js/auth-login.js') }}"></script>
</body>
</html>
