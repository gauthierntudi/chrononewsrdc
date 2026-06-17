(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const LEGACY_PAYMENTS_API = '/publication/api/payments.php';

    let subscriptionsLoaded = false;
    let activeSubscription = null;
    let pollTimer = null;

    document.addEventListener('DOMContentLoaded', () => {
        setupSubscriptionModal();

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'subscriptions') {
                loadUserSubscriptions({ skipIfCached: true });
            }
        });

        const params = new URLSearchParams(window.location.search);
        if (params.get('view') === 'subscriptions') {
            loadUserSubscriptions({ force: true });
        }
    });

    function setupSubscriptionModal() {
        document.getElementById('subscriptionPaymentModalClose')?.addEventListener('click', closeSubscriptionPaymentModal);
        document.getElementById('subscriptionPaymentCancel')?.addEventListener('click', closeSubscriptionPaymentModal);
        document.getElementById('subscriptionPaymentForm')?.addEventListener('submit', handleSubscriptionPaymentSubmit);
    }

    async function loadUserSubscriptions({ force = false, skipIfCached = false } = {}) {
        if (skipIfCached && subscriptionsLoaded && !force) {
            return;
        }

        const grid = document.getElementById('subscriptionPlansGrid');
        const currentContainer = document.getElementById('currentSubscriptionContainer');

        try {
            const [statusData, plansData] = await Promise.all([
                U.api(`${cfg.apiBase}/subscription`),
                U.api(`${cfg.apiBase}/site/subscription-plans`),
            ]);

            activeSubscription = statusData.subscription || null;

            if (activeSubscription?.status === 'active') {
                currentContainer.hidden = false;
                document.getElementById('subPlanName').textContent = activeSubscription.plan_name || 'Abonnement';
                document.getElementById('subExpiryDate').textContent = formatDate(
                    activeSubscription.end_date || activeSubscription.ends_at
                );
            } else {
                currentContainer.hidden = true;
            }

            const plans = (plansData.plans || []).filter((plan) => plan.is_active !== false && plan.is_active !== 0);

            if (!plans.length) {
                grid.innerHTML = '<div class="empty-state">Aucun plan d\'abonnement disponible pour le moment.</div>';
                subscriptionsLoaded = true;
                window.DashboardIcons?.refresh();
                return;
            }

            grid.innerHTML = plans.map((plan) => {
                const price = parseFloat(plan.price);
                const isFeatured = String(plan.name || '').toLowerCase().includes('mensuel');
                const planClass = String(plan.name || '').toLowerCase().includes('hebdo')
                    ? 'plan-hebdomadaire'
                    : String(plan.name || '').toLowerCase().includes('annuel')
                        ? 'plan-annuel'
                        : 'plan-mensuel';
                const isCurrentPlan = activeSubscription?.plan_id == plan.id;
                const hasActiveSub = activeSubscription?.status === 'active';
                const btnDisabled = hasActiveSub ? 'disabled' : '';
                const btnText = hasActiveSub ? 'Abonnement actif' : 'Choisir ce plan';
                const btnStyle = hasActiveSub ? 'opacity:0.5;cursor:not-allowed;' : '';

                return `
                <div class="pricing-card ${isFeatured ? 'featured' : ''}" style="${isCurrentPlan ? 'border:2px solid #10b981;' : ''}">
                    <div class="pricing-header">
                        <div class="pricing-name ${planClass}">${escapeHtml(plan.name)}</div>
                        <div class="pricing-price">
                            <span class="pricing-currency">$</span>
                            ${price.toFixed(0)}
                        </div>
                        <div class="pricing-period">pour ${plan.duration_days} jours</div>
                    </div>
                    <ul class="pricing-features">
                        <li class="pricing-feature">
                            <i data-lucide="check-circle" class="lucide-icon" aria-hidden="true"></i>
                            <span>Accès illimité aux articles premium</span>
                        </li>
                        <li class="pricing-feature">
                            <i data-lucide="check-circle" class="lucide-icon" aria-hidden="true"></i>
                            <span>Lecture sans achat à l'unité</span>
                        </li>
                        <li class="pricing-feature">
                            <i data-lucide="check-circle" class="lucide-icon" aria-hidden="true"></i>
                            <span>${escapeHtml(plan.description || 'Contenu premium exclusif')}</span>
                        </li>
                    </ul>
                    <button type="button" class="btn-pricing" data-plan-id="${plan.id}" data-plan-price="${price}" data-plan-name="${escapeHtml(plan.name)}" ${btnDisabled} style="${btnStyle}">
                        ${btnText}
                    </button>
                </div>`;
            }).join('');

            grid.querySelectorAll('.btn-pricing:not([disabled])').forEach((btn) => {
                btn.addEventListener('click', () => {
                    openSubscriptionPaymentModal(
                        btn.dataset.planId,
                        btn.dataset.planPrice,
                        btn.dataset.planName
                    );
                });
            });

            subscriptionsLoaded = true;
            window.DashboardIcons?.refresh();
        } catch (error) {
            console.error(error);
            if (grid) {
                grid.innerHTML = `<div class="empty-state">Impossible de charger les abonnements. ${escapeHtml(error.message)}</div>`;
            }
        }
    }

    function openSubscriptionPaymentModal(planId, price, planName) {
        const amount = parseFloat(price);
        const formattedPrice = Number.isFinite(amount) ? amount.toFixed(2) : '0.00';
        const formattedPriceShort = Number.isFinite(amount) ? amount.toFixed(0) : '0';

        const planInput = document.getElementById('subscriptionPaymentPlanId');
        const amountInput = document.getElementById('subscriptionPaymentAmount');
        const amountDisplay = document.getElementById('subscriptionPaymentAmountDisplay');
        const description = document.getElementById('subscriptionPaymentDescription');
        const modal = document.getElementById('subscriptionPaymentModal');

        if (!planInput || !amountInput || !amountDisplay || !description || !modal) {
            console.error('Modal abonnement introuvable dans le DOM');
            U.showToast('Impossible d\'ouvrir le paiement. Rechargez la page.', 'error');
            return;
        }

        planInput.value = `plan_${planId}`;
        amountInput.value = String(price);
        amountDisplay.textContent = `$${formattedPrice}`;
        description.innerHTML =
            `Abonnement <strong>${escapeHtml(planName)}</strong> : <strong style="color:#d11810;">${formattedPriceShort} USD</strong>`;

        const cardOption = document.querySelector('input[name="subscriptionPaymentMethod"][value="carte_bancaire"]')?.closest('.payment-method');
        if (amount <= 1) {
            if (cardOption) cardOption.style.display = 'none';
            const mpesaInput = document.querySelector('input[name="subscriptionPaymentMethod"][value="mpesa"]');
            if (mpesaInput) mpesaInput.checked = true;
        } else if (cardOption) {
            cardOption.style.display = '';
        }

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        window.DashboardIcons?.refresh();
    }

    function closeSubscriptionPaymentModal() {
        const modal = document.getElementById('subscriptionPaymentModal');
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }

    function showSubscriptionLoader(show) {
        const loader = document.getElementById('subscriptionPaymentLoader');
        if (!loader) return;
        loader.hidden = !show;
        loader.classList.toggle('is-hidden', !show);
        loader.style.display = show ? 'flex' : 'none';
    }

    async function handleSubscriptionPaymentSubmit(event) {
        event.preventDefault();

        const planRef = document.getElementById('subscriptionPaymentPlanId').value;
        const montant = parseFloat(document.getElementById('subscriptionPaymentAmount').value || '0');
        const methode = document.querySelector('input[name="subscriptionPaymentMethod"]:checked')?.value;
        const telephone = document.getElementById('subscriptionPaymentPhone').value.trim();

        if (!telephone) {
            U.showToast('Veuillez entrer un numéro de téléphone', 'error');
            return;
        }

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Traitement…';
        showSubscriptionLoader(true);

        try {
            const response = await fetch(`${LEGACY_PAYMENTS_API}?action=initiate`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({
                    article_id: planRef,
                    montant,
                    methode,
                    telephone,
                }),
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Erreur lors du paiement');
            }

            if (data.is_redirect && data.redirect_url) {
                closeSubscriptionPaymentModal();
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = data.redirect_url;
                if (data.params) {
                    Object.entries(data.params).forEach(([key, value]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    });
                }
                document.body.appendChild(form);
                form.submit();
                return;
            }

            if (data.direct_success && data.orderNumber) {
                closeSubscriptionPaymentModal();
                showSubscriptionLoader(false);
                U.showToast('Validez le paiement sur votre téléphone…', 'info');
                pollSubscriptionPaymentStatus(data.orderNumber);
                return;
            }

            throw new Error('Réponse de paiement inattendue');
        } catch (error) {
            console.error(error);
            U.showToast(error.message || 'Erreur de communication', 'error');
        } finally {
            showSubscriptionLoader(false);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            window.DashboardIcons?.refresh();
        }
    }

    function pollSubscriptionPaymentStatus(orderNumber, attempt = 1) {
        if (attempt > 60) {
            U.showToast('Délai dépassé. Vérifiez vos messages ou réessayez.', 'warning');
            return;
        }

        pollTimer = setTimeout(async () => {
            try {
                const response = await fetch(
                    `${LEGACY_PAYMENTS_API}?action=check_status&orderNumber=${encodeURIComponent(orderNumber)}&attempt=${attempt}`,
                    { credentials: 'same-origin', headers: { Accept: 'application/json' } }
                );
                const data = await response.json();

                if (data.success && data.statut === 'reussi') {
                    U.showToast('Abonnement activé avec succès !', 'success');
                    const returnUrl = new URLSearchParams(window.location.search).get('return_url');
                    if (returnUrl) {
                        setTimeout(() => {
                            window.location.href = decodeURIComponent(returnUrl);
                        }, 1500);
                    } else {
                        loadUserSubscriptions({ force: true });
                    }
                    return;
                }

                if (data.success && (data.statut === 'echoue' || data.statut === 'annule')) {
                    U.showToast('Le paiement a échoué ou a été annulé.', 'error');
                    return;
                }

                pollSubscriptionPaymentStatus(orderNumber, attempt + 1);
            } catch (error) {
                console.error(error);
                pollSubscriptionPaymentStatus(orderNumber, attempt + 1);
            }
        }, 3000);
    }

    function formatDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.DashboardUserSubscriptions = { load: loadUserSubscriptions };
})();
