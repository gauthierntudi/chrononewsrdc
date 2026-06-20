(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const dashboardUrl = document.body.dataset.dashboardUrl || '/dashboard';
    const redirectUrl = document.body.dataset.redirectUrl || '';
    const authAction = document.body.dataset.authAction || '';

    let isRegisterMode = false;
    let currentEmail = '';
    let otpInputs = [];
    let resendTimer = null;
    let resendSecondsLeft = 0;
    const DEFAULT_RESEND_COOLDOWN = 60;

    document.addEventListener('DOMContentLoaded', () => {
        checkSession();
        setupEventListeners();
        setupOTPInputs();

        if (authAction === 'buy' && !isRegisterMode) {
            const toggleBtn = document.getElementById('toggleMode');
            if (toggleBtn) {
                toggleBtn.innerHTML = '<ion-icon name="person-add-outline"></ion-icon> Pas encore de compte ? Inscrivez-vous pour acheter';
            }
        }
    });

    function setupEventListeners() {
        document.getElementById('emailForm').addEventListener('submit', handleEmailSubmit);
        document.getElementById('otpForm').addEventListener('submit', handleOTPSubmit);
        document.getElementById('toggleMode')?.addEventListener('click', () => toggleMode());
        document.getElementById('backToEmail').addEventListener('click', backToEmail);
        document.getElementById('resendOtp')?.addEventListener('click', handleResendOtp);
    }

    function setupOTPInputs() {
        otpInputs = Array.from(document.querySelectorAll('.otp-inputs input'));

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => handleOTPInput(e, index));
            input.addEventListener('keydown', (e) => handleOTPKeydown(e, index));
            input.addEventListener('paste', handleOTPPaste);
            input.addEventListener('focus', function () { this.select(); });
        });
    }

    function handleOTPInput(e, index) {
        const input = e.target;
        const value = input.value;

        if (value.length > 0 && /^[0-9]$/.test(value)) {
            input.classList.add('filled');
            if (index < otpInputs.length - 1) otpInputs[index + 1].focus();
        } else {
            input.value = '';
            input.classList.remove('filled');
        }
    }

    function handleOTPKeydown(e, index) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            e.preventDefault();
            otpInputs[index].value = '';
            otpInputs[index].classList.remove('filled');
            if (e.key === 'Backspace' && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].classList.remove('filled');
            }
        } else if (e.key === 'ArrowLeft' && index > 0) {
            e.preventDefault();
            otpInputs[index - 1].focus();
        } else if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
            e.preventDefault();
            otpInputs[index + 1].focus();
        }
    }

    function handleOTPPaste(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').trim();
        if (!/^\d{6}$/.test(pastedData)) return;

        pastedData.split('').forEach((char, index) => {
            if (index < otpInputs.length) {
                otpInputs[index].value = char;
                otpInputs[index].classList.add('filled');
            }
        });
        otpInputs[5]?.focus();
    }

    function resetOTPInputs() {
        otpInputs.forEach((input) => {
            input.value = '';
            input.classList.remove('filled');
        });
        otpInputs[0]?.focus();
    }

    function getOTPValue() {
        return otpInputs.map((input) => input.value).join('');
    }

    function buildPostLoginUrl() {
        if (!redirectUrl) {
            return dashboardUrl;
        }

        let target = redirectUrl;

        if (authAction === 'buy' && !target.includes('action=buy')) {
            const hashIndex = target.indexOf('#');
            const hash = hashIndex >= 0 ? target.slice(hashIndex) : '';
            const base = hashIndex >= 0 ? target.slice(0, hashIndex) : target;
            const separator = base.includes('?') ? '&' : '?';
            target = `${base}${separator}action=buy${hash}`;
        }

        return target;
    }

    async function apiPost(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            const firstError = data.errors
                ? Object.values(data.errors).flat().find(Boolean)
                : null;
            const error = new Error(firstError || data.message || `Erreur ${res.status}`);
            error.status = res.status;
            error.payload = data;
            throw error;
        }

        return data;
    }

    async function checkSession() {
        try {
            const res = await fetch('/auth/check-session', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.logged_in) {
                window.location.href = buildPostLoginUrl();
            }
        } catch (error) {
            console.error('Session check failed', error);
        }
    }

    function toggleMode(forceRegister) {
        if (forceRegister === true) {
            isRegisterMode = true;
        } else if (forceRegister === false) {
            isRegisterMode = false;
        } else {
            isRegisterMode = !isRegisterMode;
        }

        const nameGroup = document.getElementById('nameGroup');
        const toggleBtn = document.getElementById('toggleMode');
        const submitBtn = document.getElementById('submitBtn');
        const title = document.querySelector('#email-form h2');

        if (isRegisterMode) {
            nameGroup.style.display = 'block';
            toggleBtn.innerHTML = '<ion-icon name="log-in-outline"></ion-icon> Déjà un compte ? Connectez-vous';
            submitBtn.innerHTML = '<ion-icon name="person-add-outline"></ion-icon> S\'inscrire';
            title.textContent = authAction === 'buy' ? 'Créer un compte pour acheter' : 'Inscription';
        } else {
            nameGroup.style.display = 'none';
            document.getElementById('nom')?.value = '';
            toggleBtn.innerHTML = authAction === 'buy'
                ? '<ion-icon name="person-add-outline"></ion-icon> Pas encore de compte ? Inscrivez-vous pour acheter'
                : '<ion-icon name="person-add-outline"></ion-icon> Nouveau compte ? Inscrivez-vous';
            submitBtn.innerHTML = '<ion-icon name="arrow-forward-outline"></ion-icon> Continuer';
            title.textContent = authAction === 'buy' ? 'Connexion pour acheter' : 'Connexion / Inscription';
        }
    }

    function suggestRegister(message) {
        showToast(message, 'warning');
        toggleMode(true);

        const emailInput = document.getElementById('email');
        if (emailInput && currentEmail) {
            emailInput.value = currentEmail;
        }

        document.getElementById('nom')?.focus();
    }

    async function handleEmailSubmit(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        const nom = document.getElementById('nom')?.value.trim() || '';
        currentEmail = email;

        if (isRegisterMode) {
            if (!nom) return showToast('Veuillez entrer votre nom', 'error');
            await register(nom, email);
        } else {
            await requestOTP(email);
        }
    }

    async function register(nom, email) {
        try {
            showLoader('Inscription en cours...');
            const data = await apiPost('/auth/register', { nom, email });
            hideLoader();
            showToast(data.message || 'Code OTP envoyé à votre email.', 'success');
            showOTPForm(email, data.cooldown_seconds);
        } catch (error) {
            hideLoader();
            showToast(error.message, 'error');
        }
    }

    async function requestOTP(email) {
        try {
            showLoader('Envoi du code OTP...');
            const data = await apiPost('/auth/request-otp', { email });
            hideLoader();
            showToast(data.message || 'Code OTP envoyé à votre email.', 'success');
            showOTPForm(email, data.cooldown_seconds);
        } catch (error) {
            hideLoader();

            if (error.status === 422 && /aucun compte/i.test(error.message || '')) {
                suggestRegister(error.message);
                return;
            }

            showToast(error.message, 'error');
        }
    }

    async function handleResendOtp() {
        if (!currentEmail || resendSecondsLeft > 0) return;

        try {
            showLoader('Renvoi du code OTP...');
            const data = await apiPost('/auth/resend-otp', { email: currentEmail });
            hideLoader();
            showToast(data.message || 'Code OTP envoyé à votre email.', 'success');
            startResendCooldown(data.cooldown_seconds || DEFAULT_RESEND_COOLDOWN);
            resetOTPInputs();
        } catch (error) {
            hideLoader();

            if (error.status === 422 && /aucun compte/i.test(error.message || '')) {
                suggestRegister(error.message);
                return;
            }

            showToast(error.message, 'error');
            const match = error.message?.match(/(\d+)\s+secondes/);
            if (match) {
                startResendCooldown(parseInt(match[1], 10));
            }
        }
    }

    function startResendCooldown(seconds = DEFAULT_RESEND_COOLDOWN) {
        const btn = document.getElementById('resendOtp');
        const label = document.getElementById('resendOtpLabel');
        if (!btn || !label) return;

        resendSecondsLeft = Math.max(1, Number(seconds) || DEFAULT_RESEND_COOLDOWN);
        btn.disabled = true;
        btn.classList.add('is-disabled');

        const tick = () => {
            label.textContent = `Renvoyer le code (${resendSecondsLeft}s)`;
            resendSecondsLeft -= 1;

            if (resendSecondsLeft < 0) {
                clearInterval(resendTimer);
                resendTimer = null;
                btn.disabled = false;
                btn.classList.remove('is-disabled');
                label.textContent = 'Renvoyer le code';
                return;
            }
        };

        clearInterval(resendTimer);
        tick();
        resendTimer = setInterval(tick, 1000);
    }

    function clearResendCooldown() {
        clearInterval(resendTimer);
        resendTimer = null;
        resendSecondsLeft = 0;

        const btn = document.getElementById('resendOtp');
        const label = document.getElementById('resendOtpLabel');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('is-disabled');
        }
        if (label) label.textContent = 'Renvoyer le code';
    }

    async function handleOTPSubmit(e) {
        e.preventDefault();
        const otp = getOTPValue();

        if (otp.length !== 6) {
            showToast('Le code OTP doit contenir 6 chiffres', 'error');
            return;
        }

        try {
            showLoader('Vérification...');
            await apiPost('/auth/verify-otp', { email: currentEmail, otp });
            hideLoader();
            showToast('Connexion réussie ! Redirection...', 'success');
            setTimeout(() => {
                window.location.href = buildPostLoginUrl();
            }, 800);
        } catch (error) {
            hideLoader();
            showToast(error.message || 'Erreur lors de la vérification', 'error');
            resetOTPInputs();
        }
    }

    function showOTPForm(email = currentEmail, cooldownSeconds = DEFAULT_RESEND_COOLDOWN) {
        currentEmail = email;
        const emailDisplay = document.getElementById('otpEmailDisplay');
        if (emailDisplay) emailDisplay.textContent = email;

        document.getElementById('email-form').classList.remove('active');
        document.getElementById('otp-form').classList.add('active');
        resetOTPInputs();
        startResendCooldown(cooldownSeconds);
    }

    function backToEmail() {
        document.getElementById('otp-form').classList.remove('active');
        document.getElementById('email-form').classList.add('active');
        resetOTPInputs();
        clearResendCooldown();
    }

    function showLoader(text = 'Chargement...') {
        const overlay = document.getElementById('loaderOverlay');
        const loaderText = document.getElementById('loaderText');
        if (loaderText) loaderText.textContent = text;
        overlay?.classList.add('active');
    }

    function hideLoader() {
        document.getElementById('loaderOverlay')?.classList.remove('active');
    }

    function showToast(message, type = 'info') {
        const text = message != null && String(message).trim() !== ''
            ? String(message)
            : 'Une erreur est survenue';

        if (typeof iziToast === 'undefined') {
            alert(text);
            return;
        }

        const colors = {
            success: '#10b981',
            error: '#E10600',
            warning: '#f59e0b',
            info: '#1E5EFF',
        };

        const icons = {
            success: 'green',
            error: 'red',
            warning: 'orange',
            info: 'blue',
        };

        try {
            iziToast.show({
                title: '',
                message: text,
                color: icons[type] || icons.info,
                backgroundColor: colors[type] || colors.info,
                iconColor: '#fff',
                position: 'topRight',
                timeout: 4000,
                transitionIn: 'fadeInDown',
                transitionOut: 'fadeOutUp',
                displayMode: 2,
            });
        } catch (error) {
            console.error('Toast error', error);
            alert(text);
        }
    }
})();
