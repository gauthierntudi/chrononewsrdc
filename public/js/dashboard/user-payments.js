(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const access = cfg.access || {};

    if (!access.ownPayments) return;

    const CHART_COLORS = {
        red: '#E10600',
        blue: '#1E5EFF',
        orange: '#f59e0b',
    };

    const chartInstances = {};

    let paymentsPage = 1;
    let paymentsPerPage = 10;
    let paymentsSearch = '';
    let paymentsStatus = 'all';
    let paymentsSearchTimeout = null;
    let paymentsFiltersReady = false;
    let paymentsChartPeriod = 'monthly';
    let paymentsChartStats = null;
    let paymentsLoaded = false;

    const PAYMENT_METHOD_IMAGES = {
        carte_bancaire: '/assets/img/pictos/card.jpg',
        mpesa: '/assets/img/pictos/mpesa01.jpg',
        airtel_money: '/assets/img/pictos/airtel.jpg',
        orange_money: '/assets/img/pictos/orange3.jpg',
    };

    document.addEventListener('DOMContentLoaded', () => {
        setupUserPaymentsFilters();

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'payments') loadUserPayments({ skipIfCached: true });
        });
    });

    function setupUserPaymentsFilters() {
        if (paymentsFiltersReady) return;
        paymentsFiltersReady = true;

        document.getElementById('userPaymentsRefreshBtn')?.addEventListener('click', () => {
            loadUserPayments({ force: true });
        });

        document.getElementById('userPaymentsSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(paymentsSearchTimeout);
            paymentsSearchTimeout = setTimeout(() => {
                paymentsSearch = event.target.value.trim();
                paymentsPage = 1;
                loadUserPayments({ force: true });
            }, 300);
        });

        document.getElementById('userPaymentsPerPageSelect')?.addEventListener('change', (event) => {
            paymentsPerPage = Number(event.target.value) || 10;
            paymentsPage = 1;
            loadUserPayments({ force: true });
        });

        document.getElementById('userPaymentsStatusFilters')?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-status]');
            if (!btn) return;

            paymentsStatus = btn.dataset.status || 'all';
            paymentsPage = 1;

            document.querySelectorAll('#userPaymentsStatusFilters .payments-status-btn').forEach((el) => {
                el.classList.toggle('active', el.dataset.status === paymentsStatus);
            });

            loadUserPayments({ force: true });
        });

        document.getElementById('userPaymentsChartPeriod')?.addEventListener('change', (event) => {
            paymentsChartPeriod = event.target.value || 'monthly';
            if (paymentsChartStats) {
                initPaymentsCharts(paymentsChartStats);
            }
        });
    }

    async function loadUserPayments({ skipIfCached = false, force = false } = {}) {
        const container = document.getElementById('userPaymentsTable');
        const pagination = document.getElementById('userPaymentsPagination');
        const statsGrid = document.getElementById('userPaymentsStatsGrid');
        if (!container) return;
        if (skipIfCached && paymentsLoaded && !force) return;

        container.innerHTML = U.loadingHtml();
        if (pagination) pagination.innerHTML = '';
        if (statsGrid) statsGrid.innerHTML = '';

        try {
            const params = new URLSearchParams({
                page: String(paymentsPage),
                per_page: String(paymentsPerPage),
            });
            if (paymentsSearch) params.set('search', paymentsSearch);
            if (paymentsStatus && paymentsStatus !== 'all') params.set('status', paymentsStatus);

            const data = await U.api(`${cfg.apiBase}/payments?${params.toString()}`);
            const items = data.data?.payments || [];
            const stats = data.data?.stats || {};
            const total = Number(data.data?.total ?? 0);
            const totalPages = Number(data.data?.totalPages ?? 1);

            updatePaymentsCountLabel(total);
            renderPaymentsStats(stats);
            paymentsChartStats = stats;
            initPaymentsCharts(stats);

            if (!items.length) {
                container.innerHTML = `
                    <div class="pending-empty payments-empty">
                        <div class="pending-empty__icon" style="background:#fef2f2;color:#E10600;">
                            ${U.icon('receipt')}
                        </div>
                        <h3>Aucun paiement trouvé</h3>
                        <p>${paymentsSearch || paymentsStatus !== 'all' ? 'Essayez d\'autres filtres.' : 'Aucune transaction enregistrée pour le moment.'}</p>
                    </div>
                `;
                U.refreshIcons(container);
                paymentsLoaded = true;
                return;
            }

            container.innerHTML = `
                <div class="pending-table-wrap payments-table-wrap">
                    <table class="pending-table payments-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Élément</th>
                                <th>Auteur</th>
                                <th>Moyen</th>
                                <th>Statut</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((payment) => renderPaymentRow(payment)).join('')}
                        </tbody>
                    </table>
                    <div class="payments-table-footer">
                        <span>${total} transaction${total > 1 ? 's' : ''} au total</span>
                    </div>
                </div>
            `;

            renderPaymentsPagination(totalPages);
            U.refreshIcons(container);
            paymentsLoaded = true;
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function updatePaymentsCountLabel(count) {
        const label = document.getElementById('userPaymentsCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 transaction' : `${count} transactions`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function renderPaymentsStats(stats) {
        const grid = document.getElementById('userPaymentsStatsGrid');
        if (!grid) return;

        const cards = [
            { color: 'stat-card-blue', icon: 'layers', label: 'Transactions', value: stats.total_count ?? 0 },
            { color: 'stat-card-orange', icon: 'banknote', label: 'Volume total', value: `$${Number(stats.total_amount ?? 0).toFixed(2)}` },
            { color: 'stat-card-green', icon: 'circle-check', label: 'Réussies', value: `$${Number(stats.succeeded_amount ?? 0).toFixed(2)}` },
            { color: 'stat-card-red', icon: 'circle-x', label: 'Échouées', value: `$${Number(stats.failed_amount ?? 0).toFixed(2)}` },
        ];

        grid.innerHTML = cards.map((c) => `
            <div class="stat-card ${c.color}">
                <div class="stat-icon">${U.icon(c.icon)}</div>
                <div class="stat-content">
                    <div class="stat-label">${c.label}</div>
                    <div class="stat-value">${c.value}</div>
                </div>
            </div>
        `).join('');

        U.refreshIcons(grid);
    }

    function initPaymentsCharts(stats) {
        if (typeof Chart === 'undefined' || !stats) return;

        const chart = stats.chart || {};
        const periodData = chart[paymentsChartPeriod] || chart.monthly || { labels: [], amounts: [], counts: [] };
        const totalAmount = Number(stats.total_amount ?? 0);
        const succeededAmount = Number(stats.succeeded_amount ?? 0);
        const successRate = totalAmount > 0
            ? ((succeededAmount / totalAmount) * 100).toFixed(1)
            : '0.0';

        const amountEl = document.getElementById('userPaymentsChartAmount');
        const subtitleEl = document.getElementById('userPaymentsChartSubtitle');
        const donutAmountEl = document.getElementById('userPaymentsDonutAmount');
        const donutGrowthEl = document.getElementById('userPaymentsDonutGrowth');

        if (amountEl) amountEl.textContent = `$${succeededAmount.toFixed(2)}`;
        if (subtitleEl) {
            subtitleEl.textContent = `${stats.succeeded_count ?? 0} paiements réussis sur ${stats.total_count ?? 0} transactions`;
        }
        if (donutAmountEl) donutAmountEl.textContent = `$${totalAmount.toFixed(2)}`;
        if (donutGrowthEl) donutGrowthEl.textContent = `${successRate}% réussis`;

        renderLineChart('userPaymentsTrend', 'userPaymentsLineChart', {
            label: 'Montant réussi ($)',
            data: periodData.amounts || [],
            labels: periodData.labels || [],
            color: CHART_COLORS.blue,
            fillColor: 'rgba(30, 94, 255, 0.12)',
        });

        renderDoughnutChart('userPaymentsBreakdown', 'userPaymentsDonutChart', {
            labels: ['Réussies', 'En attente', 'Échouées'],
            data: [
                stats.succeeded_amount ?? 0,
                stats.pending_amount ?? 0,
                stats.failed_amount ?? 0,
            ],
            colors: [CHART_COLORS.blue, CHART_COLORS.orange, CHART_COLORS.red],
        });
    }

    function paymentTypeMeta(type) {
        const map = {
            article: { label: 'Article', icon: 'newspaper', class: 'payments-type--article' },
            publicite: { label: 'Publicité', icon: 'megaphone', class: 'payments-type--ad' },
            abonnement: { label: 'Abonnement', icon: 'credit-card', class: 'payments-type--sub' },
        };
        return map[type] || { label: 'Autre', icon: 'help-circle', class: 'payments-type--unknown' };
    }

    function paymentMethodLabel(method) {
        const map = {
            carte_bancaire: 'Carte bancaire',
            mpesa: 'M-Pesa',
            airtel_money: 'Airtel Money',
            orange_money: 'Orange Money',
        };
        return map[method] || method || '—';
    }

    function renderPaymentMethodIcon(method) {
        const label = paymentMethodLabel(method);
        const src = PAYMENT_METHOD_IMAGES[method];

        if (!src) {
            return `<span class="payments-method-empty">—</span>`;
        }

        return `
            <div class="payment-method" title="${U.escapeHtml(label)}">
                <img src="${src}" alt="${U.escapeHtml(label)}" class="method-icon-img payments-method-img">
            </div>
        `;
    }

    function paymentCoverUrl(payment) {
        if (!payment.cover) return null;
        return U.mediaUrl(payment.cover);
    }

    function renderPaymentRow(payment) {
        const type = paymentTypeMeta(payment.type);
        const status = U.formatTransactionStatus(payment.status);
        const coverUrl = paymentCoverUrl(payment);
        const title = payment.title || 'Sans titre';
        const titleShort = title.length > 48 ? `${title.slice(0, 48)}…` : title;

        return `
            <tr>
                <td>
                    <span class="payments-type ${type.class}">
                        ${U.icon(type.icon)}
                        ${type.label}
                    </span>
                </td>
                <td>
                    <div class="payments-item">
                        <div class="payments-item__thumb">
                            ${coverUrl
                                ? `<img src="${coverUrl}" alt="">`
                                : `<span class="payments-item__thumb-placeholder">${U.icon(type.icon)}</span>`}
                        </div>
                        <div class="payments-item__meta">
                            <div class="payments-item__title">${U.escapeHtml(titleShort)}</div>
                            <div class="payments-item__date">${U.formatDateShort(payment.created_at)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="payments-user__name">${U.escapeHtml(payment.user_name || cfg.user?.nom || '—')}</div>
                </td>
                <td>${renderPaymentMethodIcon(payment.method)}</td>
                <td><span class="status-badge ${status.class}">${status.label}</span></td>
                <td><strong class="payments-amount">$${Number(payment.amount || 0).toFixed(2)}</strong></td>
            </tr>
        `;
    }

    function renderPaymentsPagination(totalPages) {
        const container = document.getElementById('userPaymentsPagination');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination-controls">';

        if (paymentsPage > 1) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-payments-page="${paymentsPage - 1}" aria-label="Page précédente">${U.icon('chevron-left')}</button>`;
        }

        for (let page = 1; page <= totalPages; page += 1) {
            if (page === 1 || page === totalPages || Math.abs(page - paymentsPage) <= 1) {
                html += `<button type="button" class="pagination-btn ${page === paymentsPage ? 'active' : ''}" data-payments-page="${page}">${page}</button>`;
            } else if (page === paymentsPage - 2 || page === paymentsPage + 2) {
                html += '<span class="pagination-ellipsis">…</span>';
            }
        }

        if (paymentsPage < totalPages) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-payments-page="${paymentsPage + 1}" aria-label="Page suivante">${U.icon('chevron-right')}</button>`;
        }

        html += '</div>';
        container.innerHTML = html;
        U.refreshIcons(container);

        container.querySelectorAll('[data-payments-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                paymentsPage = Number(btn.dataset.paymentsPage);
                loadUserPayments({ force: true });
            });
        });
    }

    function destroyChartInstance(key) {
        const existing = chartInstances[key];
        if (existing && typeof existing.destroy === 'function') {
            existing.destroy();
        }
        delete chartInstances[key];
    }

    function destroyChartOnCanvas(canvas) {
        if (typeof Chart === 'undefined' || !canvas) return;
        const existing = Chart.getChart(canvas);
        if (existing && typeof existing.destroy === 'function') {
            existing.destroy();
        }
    }

    function chartFont() {
        return "'Google Sans', system-ui, sans-serif";
    }

    function formatNumber(value) {
        return Number(value ?? 0).toLocaleString('fr-FR');
    }

    function renderLineChart(instanceKey, canvasId, { label, data, labels, color, fillColor }) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        destroyChartInstance(instanceKey);
        destroyChartOnCanvas(canvas);

        chartInstances[instanceKey] = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label,
                    data,
                    borderColor: color,
                    backgroundColor: fillColor,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#2d3134',
                        titleFont: { family: chartFont(), weight: '600' },
                        bodyFont: { family: chartFont() },
                        padding: 12,
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label} : $${formatNumber(ctx.raw)}`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.18)', drawBorder: false },
                        ticks: {
                            font: { family: chartFont(), size: 11 },
                            callback: (v) => `$${formatNumber(v)}`,
                        },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: chartFont(), size: 11 } },
                    },
                },
            },
        });
    }

    function renderDoughnutChart(instanceKey, canvasId, { labels, data, colors }) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        destroyChartInstance(instanceKey);
        destroyChartOnCanvas(canvas);

        const values = data.map((v) => Number(v) || 0);
        const total = values.reduce((sum, value) => sum + value, 0);

        let chartLabels = labels;
        let chartData = values;
        let chartColors = colors;
        let isEmptyState = false;

        if (total === 0) {
            isEmptyState = true;
            chartLabels = ['Aucune donnée'];
            chartData = [1];
            chartColors = ['#e2e8f0'];
        } else {
            const segments = labels
                .map((label, index) => ({
                    label,
                    value: values[index],
                    color: colors[index],
                }))
                .filter((segment) => segment.value > 0);

            if (segments.length) {
                chartLabels = segments.map((segment) => segment.label);
                chartData = segments.map((segment) => segment.value);
                chartColors = segments.map((segment) => segment.color);
            }
        }

        chartInstances[instanceKey] = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: isEmptyState ? 0 : 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14,
                            font: { family: chartFont(), size: 11 },
                            color: '#475569',
                        },
                    },
                    tooltip: {
                        enabled: !isEmptyState,
                        backgroundColor: '#2d3134',
                        bodyFont: { family: chartFont() },
                        callbacks: {
                            label: (ctx) => {
                                const datasetTotal = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = datasetTotal ? Math.round((ctx.raw / datasetTotal) * 100) : 0;
                                return ` ${ctx.label} : $${formatNumber(ctx.raw)} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    window.DashboardUserPayments = { load: loadUserPayments };
})();
