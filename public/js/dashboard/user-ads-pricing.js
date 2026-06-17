(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const access = cfg.access || {};

    if (!access.adRatesView) return;

    let ratesCache = [];
    let pricingSearch = '';
    let pricingSearchTimeout = null;
    let pricingReady = false;
    let pricingLoaded = false;

    document.addEventListener('DOMContentLoaded', () => {
        setupUserAdsPricing();
        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'ads-pricing') loadUserAdsPricing({ skipIfCached: true });
        });
    });

    function setupUserAdsPricing() {
        if (pricingReady) return;
        pricingReady = true;

        document.getElementById('userAdsPricingRefreshBtn')?.addEventListener('click', () => {
            pricingLoaded = false;
            loadUserAdsPricing({ force: true });
        });

        document.getElementById('userAdsPricingCreateBtn')?.addEventListener('click', () => {
            U.showView('ads');
        });

        document.getElementById('userAdsPricingSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(pricingSearchTimeout);
            pricingSearchTimeout = setTimeout(() => {
                pricingSearch = event.target.value.trim().toLowerCase();
                renderUserAdsPricingGrid();
            }, 200);
        });
    }

    async function loadUserAdsPricing({ skipIfCached = false, force = false } = {}) {
        const grid = document.getElementById('adsPricingGrid');
        if (!grid) return;
        if (skipIfCached && pricingLoaded && !force) return;

        grid.innerHTML = U.loadingHtml();

        try {
            const data = await U.api(`${cfg.apiBase}/advertisement-rates`);
            ratesCache = data.rates || [];

            renderUserAdsPricingInfo();
            renderUserAdsPricingStats(ratesCache);
            updateUserAdsPricingCountLabel(ratesCache.length);
            renderUserAdsPricingGrid();
            pricingLoaded = true;
        } catch (error) {
            grid.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(grid);
        }
    }

    function renderUserAdsPricingInfo() {
        const info = document.getElementById('userAdsPricingInfo');
        if (!info) return;

        if (access.adsFree) {
            info.hidden = false;
            info.innerHTML = `
                <div class="ads-pricing-info__inner ads-pricing-info__inner--free">
                    ${U.icon('gift')}
                    <div>
                        <strong>Publicités gratuites</strong>
                        <p>Votre rôle bénéficie de campagnes publicitaires sans frais. Les tarifs ci-dessous sont affichés à titre informatif.</p>
                    </div>
                </div>
            `;
            U.refreshIcons(info);
            return;
        }

        info.hidden = false;
        info.innerHTML = `
            <div class="ads-pricing-info__inner">
                ${U.icon('info')}
                <div>
                    <strong>Tarification en USD</strong>
                    <p>Le montant final dépend du format choisi et de la durée (7, 15 ou 30 jours). Après création, procédez au paiement pour soumettre votre campagne à validation.</p>
                </div>
            </div>
        `;
        U.refreshIcons(info);
    }

    function renderUserAdsPricingStats(rates) {
        const grid = document.getElementById('userAdsPricingStatsGrid');
        if (!grid) return;

        const prices7 = rates.map((rate) => Number(rate.price_7_days ?? 0));
        const prices30 = rates.map((rate) => Number(rate.price_30_days ?? 0));
        const min7 = prices7.length ? Math.min(...prices7) : 0;
        const max30 = prices30.length ? Math.max(...prices30) : 0;

        const cards = [
            { color: 'stat-card-blue', icon: 'layout-grid', label: 'Formats', value: rates.length },
            { color: 'stat-card-green', icon: 'calendar', label: 'À partir de (7 j)', value: access.adsFree ? 'Gratuit' : `$${formatUsd(min7)}` },
            { color: 'stat-card-orange', icon: 'calendar-days', label: 'Jusqu\'à (30 j)', value: access.adsFree ? 'Gratuit' : `$${formatUsd(max30)}` },
            { color: 'stat-card-teal', icon: 'banknote', label: 'Devise', value: 'USD' },
        ];

        grid.innerHTML = cards.map((card) => `
            <div class="stat-card ${card.color}">
                <div class="stat-icon">${U.icon(card.icon)}</div>
                <div class="stat-content">
                    <div class="stat-label">${card.label}</div>
                    <div class="stat-value">${card.value}</div>
                </div>
            </div>
        `).join('');
        U.refreshIcons(grid);
    }

    function updateUserAdsPricingCountLabel(count) {
        const label = document.getElementById('userAdsPricingCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 format' : `${count} formats`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function filterRates(rates) {
        if (!pricingSearch) return rates;
        return rates.filter((rate) => {
            const haystack = [
                rate.label,
                rate.format,
                rate.dimensions,
            ].filter(Boolean).join(' ').toLowerCase();
            return haystack.includes(pricingSearch);
        });
    }

    function renderUserAdsPricingGrid() {
        const grid = document.getElementById('adsPricingGrid');
        if (!grid) return;

        const filtered = filterRates(ratesCache);

        if (!ratesCache.length) {
            grid.innerHTML = `
                <div class="pending-empty ads-rates-empty">
                    <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">${U.icon('tags')}</div>
                    <h3>Aucun tarif configuré</h3>
                    <p>Les tarifs publicitaires apparaîtront ici une fois définis par l'administration.</p>
                </div>
            `;
            U.refreshIcons(grid);
            return;
        }

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="pending-empty ads-rates-empty">
                    <div class="pending-empty__icon" style="background:#eff6ff;color:#1E5EFF;">${U.icon('search')}</div>
                    <h3>Aucun format trouvé</h3>
                    <p>Aucun tarif ne correspond à « ${U.escapeHtml(pricingSearch)} ».</p>
                </div>
            `;
            U.refreshIcons(grid);
            return;
        }

        grid.innerHTML = filtered.map((rate) => renderUserAdsRateCard(rate)).join('');
        U.refreshIcons(grid);
    }

    function formatUsd(amount) {
        return Number(amount ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderUserAdsRateCard(rate) {
        const price7 = access.adsFree ? 'Gratuit' : `$${formatUsd(rate.price_7_days)}`;
        const price15 = access.adsFree ? 'Gratuit' : `$${formatUsd(rate.price_15_days)}`;
        const price30 = access.adsFree ? 'Gratuit' : `$${formatUsd(rate.price_30_days)}`;

        return `
            <article class="ads-rate-card ads-rate-card--readonly">
                <div class="ads-rate-card__head">
                    <h3>${U.escapeHtml(rate.label || rate.format)}</h3>
                    <span class="ads-rate-card__dimensions">${U.icon('maximize-2')}${U.escapeHtml(rate.dimensions || '—')}</span>
                </div>
                <ul class="ads-rate-card__prices">
                    <li><span>${U.icon('calendar')}7 jours</span><strong>${price7}</strong></li>
                    <li><span>${U.icon('calendar')}15 jours</span><strong>${price15}</strong></li>
                    <li><span>${U.icon('calendar')}30 jours</span><strong>${price30}</strong></li>
                </ul>
            </article>
        `;
    }

    window.DashboardUserAdsPricing = { load: loadUserAdsPricing };
})();
