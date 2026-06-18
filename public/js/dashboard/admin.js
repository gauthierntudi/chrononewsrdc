(() => {
    const U = window.DashboardUtils;
    const cfg = U.cfg;

    const CHART_COLORS = {
        red: '#E10600',
        blue: '#1E5EFF',
        green: '#10b981',
        orange: '#f59e0b',
        purple: '#8b5cf6',
        slate: '#393e41',
        pink: '#ec4899',
        indigo: '#6366f1',
    };

    /** Instances Chart.js (ne pas utiliser window[id] — les canvas id polluent window). */
    const chartInstances = {};

    let allArticles = [];
    let articlesCategory = '';
    let pendingArticles = [];
    let pendingCategory = '';
    let articlesSearch = '';
    let articlesPage = 1;
    let articlesPerPage = 12;
    let articlesViewMode = 'grid';
    let articlesFiltersReady = false;
    let articlesSearchTimeout = null;
    let selectedArticles = new Set();
    let currentPreviewArticleId = null;
    let usersPage = 1;
    let usersPerPage = 10;
    let usersSearch = '';
    let usersSearchTimeout = null;
    let usersFiltersReady = false;
    let userAvatarCropper = null;
    let userAvatarCropperFile = null;
    let userEditOriginalCover = '';
    let userEditPendingCover = null;
    let paymentsPage = 1;
    let paymentsPerPage = 10;
    let paymentsSearch = '';
    let paymentsStatus = 'all';
    let paymentsSearchTimeout = null;
    let paymentsFiltersReady = false;
    let paymentsChartPeriod = 'monthly';
    let paymentsChartStats = null;
    let adsPage = 1;
    let adsPerPage = 10;
    let adsFilters = { validation: '', payment: '', broadcast: '', placement: '' };
    let adsSearch = '';
    let adsSearchTimeout = null;
    let adsFiltersReady = false;
    let homeVideosReady = false;
    let homeVideosCache = [];
    let adsPricingReady = false;
    let adsPricingCache = [];
    let settingsReady = false;
    let subscriptionPlansCache = [];
    let subscriptionPlanModalReady = false;
    let socialMediaCache = {};
    let socialCatalogCache = {};
    let socialMediaModalReady = false;
    let adRefuseTargetId = null;
    let pendingActionsReady = false;
    const viewsLoaded = new Set();

    function isViewLoaded(view) {
        return viewsLoaded.has(view);
    }

    function markViewLoaded(view) {
        viewsLoaded.add(view);
    }

    function resetViewLoaded(view) {
        viewsLoaded.delete(view);
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('dashboard:view', (e) => {
            const view = e.detail.view;
            if (view === 'stats') loadStats({ skipIfCached: true });
            if (view === 'validation') loadPending({ skipIfCached: true });
            if (view === 'all-articles') loadAllArticles({ skipIfCached: true });
            if (view === 'users') loadUsers({ skipIfCached: true });
            if (view === 'payments') loadPayments({ skipIfCached: true });
            if (view === 'ads') loadAds({ skipIfCached: true });
            if (view === 'home-video') loadHomeVideos({ skipIfCached: true });
            if (view === 'ads-pricing') loadAdsPricing({ skipIfCached: true });
            if (view === 'settings') loadSettings({ skipIfCached: true });
        });

        loadPendingCount();
        U.restoreView('stats');

        window.addEventListener('dashboard:ads-created', () => {
            resetViewLoaded('ads');
            loadAds();
        });

        window.addEventListener('dashboard:ads-updated', () => {
            resetViewLoaded('ads');
            loadAds();
        });

        document.getElementById('pendingRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('validation');
            loadPending();
        });
        document.getElementById('allArticlesRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('all-articles');
            loadAllArticles();
        });
        setupAllArticlesFilters();
        setupUsersFilters();
        setupPaymentsFilters();
        setupAdsFilters();
        setupHomeVideoModal();
        setupAdsPricingModal();
        setupSettingsForm();
        setupSubscriptionPlanModal();
        setupSocialMediaModal();
        setupArticleModal();
        setupPendingTableActions();
        setupUserEditModal();
    });

    function setupPendingTableActions() {
        if (pendingActionsReady) return;
        pendingActionsReady = true;

        const container = document.getElementById('pendingArticlesTable');
        if (!container) return;

        container.addEventListener('click', (event) => {
            const viewBtn = event.target.closest('.btn-article-view');
            if (viewBtn?.dataset?.id) {
                event.preventDefault();
                event.stopPropagation();
                showArticleModal(viewBtn.dataset.id);
                return;
            }

            const approveBtn = event.target.closest('.btn-approve');
            if (approveBtn?.dataset?.id) {
                event.preventDefault();
                moderate(approveBtn.dataset.id, 'approve', approveBtn.dataset.title);
                return;
            }

            const rejectBtn = event.target.closest('.btn-reject');
            if (rejectBtn?.dataset?.id) {
                event.preventDefault();
                moderate(rejectBtn.dataset.id, 'reject', rejectBtn.dataset.title);
            }
        });
    }

    async function loadPendingCount() {
        try {
            const data = await U.api(`${cfg.apiBase}/admin/pending-count`);
            const badge = document.getElementById('pendingBadge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function loadStats({ skipIfCached = false } = {}) {
        const grid = document.getElementById('statsGrid');
        if (!grid) return;
        if (skipIfCached && isViewLoaded('stats')) return;

        grid.innerHTML = U.loadingHtml();

        try {
            const data = await U.api(`${cfg.apiBase}/admin/stats`);
            const s = data.stats || {};
            const cards = [
                { color: 'stat-card-blue', icon: 'newspaper', label: 'Total Articles', value: s.total_articles ?? 0 },
                { color: 'stat-card-orange', icon: 'clock', label: 'En attente', value: s.articles_pending ?? 0 },
                { color: 'stat-card-green', icon: 'circle-check', label: 'Articles publiés', value: s.articles_published ?? 0 },
                { color: 'stat-card-purple', icon: 'eye', label: 'Total vues', value: s.total_views ?? 0 },
                { color: 'stat-card-teal', icon: 'users', label: 'Utilisateurs', value: s.users_total ?? 0 },
                { color: 'stat-card-warning', icon: 'layers', label: 'Paiements', value: s.total_payments ?? 0 },
                { color: 'stat-card-success', icon: 'banknote', label: 'Revenus', value: `${Number(s.total_revenue ?? 0).toFixed(2)} $` },
                { color: 'stat-card-red', icon: 'megaphone', label: 'Publicités', value: s.total_ads ?? 0 },
            ];

            grid.innerHTML = cards.map((c) => `
                <div class="stat-card ${c.color}">
                    <div class="stat-icon">
                        ${U.icon(c.icon)}
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">${c.label}</div>
                        <div class="stat-value">${c.value}</div>
                    </div>
                </div>
            `).join('');

            try {
                initCharts(s);
            } catch (chartError) {
                console.error('Graphiques stats:', chartError);
            }

            U.refreshIcons(grid);
            markViewLoaded('stats');
        } catch (error) {
            grid.innerHTML = `<div class="empty-state"><p>${U.escapeHtml(error.message)}</p></div>`;
        }
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

    function initCharts(stats) {
        if (typeof Chart === 'undefined') return;

        const articlesKpi = document.getElementById('articlesChartKpi');
        const adsKpi = document.getElementById('adsChartKpi');
        if (articlesKpi) {
            articlesKpi.textContent = `${formatNumber(stats.total_views ?? 0)} vues`;
        }
        if (adsKpi) {
            adsKpi.textContent = `${formatNumber(stats.total_ads_views ?? 0)} impressions`;
        }

        const monthLabels = last6MonthLabels();
        const viewsHistory = normalizeHistory(stats.views_history, stats.total_views ?? 0);
        const adsHistory = normalizeHistory(stats.ads_views_history, stats.total_ads_views ?? 0);

        const published = Number(stats.articles_published ?? 0);
        const pending = Number(stats.articles_pending ?? 0);
        const totalArticles = Number(stats.total_articles ?? 0);
        const otherArticles = Math.max(0, totalArticles - published - pending);

        const adsActive = Number(stats.ads_active ?? 0);
        const adsPending = Number(stats.ads_pending ?? 0);
        const totalAds = Number(stats.total_ads ?? 0);
        const adsOther = Math.max(0, totalAds - adsActive - adsPending);

        renderLineChart('articlesTrend', 'articlesTrendChart', {
            label: 'Vues articles',
            data: viewsHistory,
            labels: monthLabels,
            color: CHART_COLORS.blue,
            fillColor: 'rgba(30, 94, 255, 0.12)',
        });

        renderDoughnutChart('articlesBreakdown', 'articlesBreakdownChart', {
            labels: ['Publiés', 'En attente', 'Autres'],
            data: [published, pending, otherArticles],
            colors: [CHART_COLORS.green, CHART_COLORS.orange, CHART_COLORS.slate],
        });

        renderLineChart('adsTrend', 'adsTrendChart', {
            label: 'Impressions publicités',
            data: adsHistory,
            labels: monthLabels,
            color: CHART_COLORS.red,
            fillColor: 'rgba(225, 6, 0, 0.1)',
        });

        renderDoughnutChart('adsBreakdown', 'adsBreakdownChart', {
            labels: ['Actives', 'En attente', 'Autres'],
            data: [adsActive, adsPending, adsOther],
            colors: [CHART_COLORS.green, CHART_COLORS.orange, CHART_COLORS.indigo],
        });
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
                            label: (ctx) => `${ctx.dataset.label} : ${formatNumber(ctx.raw)}`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.18)', drawBorder: false },
                        ticks: {
                            font: { family: chartFont(), size: 11 },
                            callback: (v) => formatNumber(v),
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
                                return ` ${ctx.label} : ${formatNumber(ctx.raw)} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    function last6MonthLabels() {
        const labels = [];
        const now = new Date();
        for (let i = 5; i >= 0; i -= 1) {
            const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            labels.push(d.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', ''));
        }
        return labels;
    }

    function normalizeHistory(history, fallback) {
        const values = Array.isArray(history) && history.length ? history.map(Number) : [];
        if (values.length >= 6) return values.slice(-6);
        if (values.length > 0) {
            while (values.length < 6) values.unshift(0);
            return values;
        }
        const base = Math.max(Number(fallback) || 0, 1);
        return [0.45, 0.55, 0.5, 0.7, 0.65, 1].map((r) => Math.round(base * r));
    }

    function formatNumber(value) {
        return Number(value ?? 0).toLocaleString('fr-FR');
    }

    function chartFont() {
        return "'Google Sans', system-ui, sans-serif";
    }

    function articleCoverUrl(article) {
        let cover = article.cover || '';
        if (cover.includes(',')) cover = cover.split(',')[0].trim();
        if (!cover) return null;
        return U.mediaUrl(cover);
    }

    function isFeatured(article) {
        const value = String(article.alaune ?? article.is_featured ?? '').toUpperCase();
        return value === 'YES' || value === 'OUI' || value === '1' || value === 'TRUE';
    }

    function renderPendingAuthor(article) {
        const name = article.author?.nom || article.auteur_nom || '—';
        const email = article.author?.mail || article.auteur_email || '';
        const avatar = article.author?.cover || article.auteur_cover;
        const avatarUrl = avatar ? U.mediaUrl(avatar) : null;

        if (avatarUrl) {
            return `
                <div class="user-cell">
                    <img src="${avatarUrl}" alt="" class="user-cell-avatar">
                    <div class="user-cell-info">
                        <span class="user-cell-name">${U.escapeHtml(name)}</span>
                        ${email ? `<span class="user-cell-email">${U.escapeHtml(email)}</span>` : ''}
                    </div>
                </div>
            `;
        }

        return `
            <div class="user-cell">
                <div class="user-cell-avatar user-cell-avatar--initials">${U.getInitials(name)}</div>
                <div class="user-cell-info">
                    <span class="user-cell-name">${U.escapeHtml(name)}</span>
                    ${email ? `<span class="user-cell-email">${U.escapeHtml(email)}</span>` : ''}
                </div>
            </div>
        `;
    }

    function renderPendingRow(article) {
        const title = article.title || article.titre || 'Sans titre';
        const category = articleCategory(article);
        const categoryClass = U.getCategoryColorClass(category);
        const coverUrl = articleCoverUrl(article);
        const featured = isFeatured(article);
        const date = U.formatDateShort(article.created_at || article.date_add);

        return `
            <tr class="pending-row">
                <td class="pending-col-article">
                    <div class="pending-article-cell">
                        <div class="pending-cover ${featured ? 'pending-cover--featured' : ''}">
                            ${coverUrl
                                ? `<img src="${coverUrl}" alt="">`
                                : `<span class="pending-cover-placeholder">${U.icon('image')}</span>`}
                            ${featured ? '<span class="pending-cover-badge">À la une</span>' : ''}
                        </div>
                        <div class="pending-article-meta">
                            <div class="pending-article-title">${U.escapeHtml(title)}</div>
                            <span class="status-badge badge-warning pending-status-badge">En attente</span>
                        </div>
                    </div>
                </td>
                <td>${renderPendingAuthor(article)}</td>
                <td><span class="category-badge ${categoryClass}">${U.escapeHtml(category)}</span></td>
                <td class="pending-col-date">
                    <span class="pending-date">${U.icon('calendar')}${date}</span>
                </td>
                <td class="pending-col-actions">
                    <div class="pending-actions">
                        <button type="button" class="action-btn-modern validate btn-approve" data-id="${article.id}" data-title="${U.escapeHtml(title)}" title="Approuver">
                            ${U.icon('check')}
                        </button>
                        <button type="button" class="action-btn-modern validate btn-article-view" data-id="${article.id}" title="Voir">
                            ${U.icon('eye')}
                        </button>
                        <button type="button" class="action-btn-modern delete btn-reject" data-id="${article.id}" data-title="${U.escapeHtml(title)}" title="Rejeter">
                            ${U.icon('x')}
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function updatePendingCountLabel(count) {
        const label = document.getElementById('pendingCountLabel');
        if (!label) return;

        if (count === 0) {
            label.textContent = 'Aucun article';
            label.className = 'pending-count-badge pending-count-badge--empty';
            return;
        }

        label.textContent = count === 1 ? '1 article en attente' : `${count} articles en attente`;
        label.className = 'pending-count-badge';
    }

    async function loadPending({ skipIfCached = false } = {}) {
        const table = document.getElementById('pendingArticlesTable');
        if (!table) return;
        if (skipIfCached && isViewLoaded('validation')) return;

        table.innerHTML = U.loadingHtml();
        document.getElementById('pendingCategoriesContainer')?.replaceChildren();

        try {
            const data = await U.api(`${cfg.apiBase}/admin/articles/pending`);
            pendingArticles = U.extractArticlesList(data);
            pendingCategory = '';
            updatePendingCountLabel(pendingArticles.length);
            renderPendingCategoryFilters();
            renderPendingTable();
            markViewLoaded('validation');
        } catch (error) {
            pendingArticles = [];
            pendingCategory = '';
            document.getElementById('pendingCategoriesContainer')?.replaceChildren();
            table.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(table);
        }
    }

    async function moderate(id, action, title = '') {
        const label = title || 'Sans titre';

        if (action === 'approve') {
            if (!await U.confirm(`Approuver « ${label} » ?`, { confirmText: 'Approuver' })) {
                return;
            }

            await submitModeration(id, action);
            return;
        }

        if (action === 'reject') {
            const reason = await promptRejectReason(label);
            if (reason === null) {
                return;
            }

            if (!reason.trim()) {
                U.showToast('Veuillez indiquer une raison pour le rejet', 'warning');
                return;
            }

            await submitModeration(id, action, reason.trim());
        }
    }

    function promptRejectReason(title) {
        return new Promise((resolve) => {
            const modal = document.getElementById('rejectReasonModal');
            const messageEl = document.getElementById('rejectReasonMessage');
            const input = document.getElementById('rejectReasonInput');
            const cancelBtn = document.getElementById('rejectReasonCancel');
            const confirmBtn = document.getElementById('rejectReasonConfirm');

            if (!modal || !messageEl || !input || !cancelBtn || !confirmBtn) {
                resolve(null);
                return;
            }

            const close = (value) => {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                cancelBtn.removeEventListener('click', onCancel);
                confirmBtn.removeEventListener('click', onConfirm);
                modal.removeEventListener('click', onBackdrop);
                document.removeEventListener('keydown', onKeydown);
                resolve(value);
            };

            const onCancel = () => close(null);
            const onConfirm = () => close(input.value);
            const onBackdrop = (event) => {
                if (event.target === modal) close(null);
            };
            const onKeydown = (event) => {
                if (event.key === 'Escape') close(null);
            };

            messageEl.textContent = `Rejeter « ${title} »`;
            input.value = '';
            cancelBtn.addEventListener('click', onCancel);
            confirmBtn.addEventListener('click', onConfirm);
            modal.addEventListener('click', onBackdrop);
            document.addEventListener('keydown', onKeydown);

            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            input.focus();
        });
    }

    async function submitModeration(id, action, rejectReason = null) {
        try {
            U.showLoader('Traitement...');
            const options = { method: 'POST' };

            if (rejectReason) {
                options.body = JSON.stringify({ reject_reason: rejectReason });
            }

            const data = await U.api(`${cfg.apiBase}/admin/articles/${id}/${action}`, options);
            U.hideLoader();
            U.showToast(data.message || 'Action effectuée', 'success');
            await loadPending();
            await loadStats();
            await loadPendingCount();
            if (document.getElementById('all-articles-view')?.classList.contains('active')) {
                await loadAllArticles();
            }
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupAllArticlesFilters() {
        if (articlesFiltersReady) return;
        articlesFiltersReady = true;

        document.getElementById('allArticlesSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(articlesSearchTimeout);
            articlesSearchTimeout = setTimeout(() => {
                articlesSearch = event.target.value.trim().toLowerCase();
                articlesPage = 1;
                renderArticlesView();
            }, 250);
        });

        document.getElementById('allArticlesPerPageSelect')?.addEventListener('change', (event) => {
            articlesPerPage = Number(event.target.value) || 12;
            articlesPage = 1;
            renderArticlesView();
        });

        document.getElementById('articlesViewToggle')?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-mode]');
            if (!btn) return;
            articlesViewMode = btn.dataset.mode;
            document.querySelectorAll('#articlesViewToggle .view-toggle-btn').forEach((toggle) => {
                toggle.classList.toggle('active', toggle.dataset.mode === articlesViewMode);
            });
            renderArticlesView();
        });
    }

    function articleTitle(article) {
        return article.title || article.titre || 'Sans titre';
    }

    function articleCategory(article) {
        return U.normalizeCategory(article.category || article.categorie || '—');
    }

    function articleAuthorName(article) {
        return article.author?.nom || article.auteur_nom || '—';
    }

    function validationBadge(article) {
        const status = article.validation_status?.value ?? article.validation_status;
        const formatted = U.formatValidation(status);
        return `<span class="status-badge ${formatted.class}">${formatted.label}</span>`;
    }

    function paymentBadge(article) {
        const status = article.payment_status?.value ?? article.payment_status;
        const formatted = U.formatPayment(status);
        return `<span class="status-badge ${formatted.class}">${formatted.label}</span>`;
    }

    function updateAllArticlesCountLabel(count) {
        const label = document.getElementById('allArticlesCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 article' : `${count} articles`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function getFilteredArticles() {
        let filtered = [...allArticles];

        if (articlesCategory) {
            filtered = filtered.filter((article) => articleCategory(article) === articlesCategory);
        }

        if (articlesSearch) {
            filtered = filtered.filter((article) => {
                const haystack = [
                    articleTitle(article),
                    articleAuthorName(article),
                    articleCategory(article),
                ].join(' ').toLowerCase();
                return haystack.includes(articlesSearch);
            });
        }

        return filtered;
    }

    function buildCategoryPillItems(articles) {
        const officialCategories = Array.isArray(cfg.categories) ? cfg.categories : [];
        const counts = Object.fromEntries(officialCategories.map((cat) => [cat, 0]));
        const extraCategories = {};

        articles.forEach((article) => {
            const cat = articleCategory(article);
            if (!cat || cat === '—') return;
            if (Object.prototype.hasOwnProperty.call(counts, cat)) {
                counts[cat] += 1;
            } else {
                extraCategories[cat] = (extraCategories[cat] || 0) + 1;
            }
        });

        const pills = officialCategories.length
            ? officialCategories.map((category) => [category, counts[category] || 0])
            : Object.entries({ ...counts, ...extraCategories }).sort((a, b) => b[1] - a[1]);

        Object.entries(extraCategories)
            .filter(([category]) => !officialCategories.includes(category))
            .sort((a, b) => b[1] - a[1])
            .forEach((entry) => pills.push(entry));

        return pills;
    }

    function renderCategoryFilterBar(container, { articles, selectedCategory, onSelect }) {
        if (!container) return;

        const pills = buildCategoryPillItems(articles);

        if (!articles.length && !pills.length) {
            container.innerHTML = '';
            return;
        }

        let html = `
            <button type="button" class="category-pill ${selectedCategory === '' ? 'active' : ''}" data-category="">
                <span>Toutes</span>
                <span class="count-badge">${articles.length}</span>
            </button>
        `;

        pills.forEach(([category, count]) => {
            const colorClass = U.getCategoryColorClass(category);
            const isEmpty = count === 0;
            html += `
                <button type="button" class="category-pill ${colorClass} ${isEmpty ? 'category-pill--empty' : ''} ${selectedCategory === category ? 'active' : ''}" data-category="${String(category).replace(/"/g, '&quot;')}"${isEmpty ? ' disabled' : ''}>
                    <span>${U.escapeHtml(category)}</span>
                    <span class="count-badge">${count}</span>
                </button>
            `;
        });

        container.innerHTML = html;
        container.querySelectorAll('[data-category]').forEach((pill) => {
            pill.addEventListener('click', () => {
                if (pill.disabled) return;
                onSelect(pill.dataset.category || '');
            });
        });
    }

    function renderCategoryFilters() {
        renderCategoryFilterBar(document.getElementById('categoriesCardsContainer'), {
            articles: allArticles,
            selectedCategory: articlesCategory,
            onSelect: (category) => {
                articlesCategory = category;
                articlesPage = 1;
                renderCategoryFilters();
                renderArticlesView();
            },
        });
    }

    function renderPendingCategoryFilters() {
        renderCategoryFilterBar(document.getElementById('pendingCategoriesContainer'), {
            articles: pendingArticles,
            selectedCategory: pendingCategory,
            onSelect: (category) => {
                pendingCategory = category;
                renderPendingCategoryFilters();
                renderPendingTable();
            },
        });
    }

    function getFilteredPendingArticles() {
        if (!pendingCategory) return pendingArticles;
        return pendingArticles.filter((article) => articleCategory(article) === pendingCategory);
    }

    function renderPendingTable() {
        const table = document.getElementById('pendingArticlesTable');
        if (!table) return;

        const items = getFilteredPendingArticles();

        if (!pendingArticles.length) {
            table.innerHTML = `
                <div class="pending-empty">
                    <div class="pending-empty__icon">
                        ${U.icon('check-check')}
                    </div>
                    <h3>Tout est à jour</h3>
                    <p>Aucun article en attente de validation pour le moment.</p>
                </div>
            `;
            U.refreshIcons(table);
            return;
        }

        if (!items.length) {
            table.innerHTML = `
                <div class="pending-empty">
                    <div class="pending-empty__icon" style="background:#eff6ff;color:#1E5EFF;">
                        ${U.icon('filter')}
                    </div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun article en attente dans cette catégorie.</p>
                </div>
            `;
            U.refreshIcons(table);
            return;
        }

        table.innerHTML = `
            <div class="pending-table-wrap">
                <table class="pending-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Auteur</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th class="pending-col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map((article) => renderPendingRow(article)).join('')}
                    </tbody>
                </table>
            </div>
        `;

        U.refreshIcons(table);
    }

    function renderArticleGridCard(article) {
        const title = articleTitle(article);
        const category = articleCategory(article);
        const coverUrl = articleCoverUrl(article);
        const featured = isFeatured(article);
        const isPaid = article.is_paid === true || article.is_paid === 1 || article.is_paid === '1';
        const price = article.price ? `${Number(article.price).toFixed(2)} $` : 'Premium';
        const authorName = articleAuthorName(article);
        const authorAvatar = article.author?.cover || article.auteur_cover;
        const avatarUrl = authorAvatar ? U.mediaUrl(authorAvatar) : U.mediaUrl(null);
        const selected = isArticleSelected(article.id);

        return `
            <article class="article-admin-card ${selected ? 'article-admin-card--selected' : ''}">
                <label class="article-admin-card__select">
                    <input type="checkbox" class="btn-article-select" data-id="${article.id}" ${selected ? 'checked' : ''} aria-label="Sélectionner l'article">
                </label>
                <div class="article-admin-card__cover ${featured ? 'article-admin-card__cover--featured' : ''}">
                    ${coverUrl
                        ? `<img src="${coverUrl}" alt="">`
                        : `<span class="article-admin-card__cover-placeholder">${U.icon('image')}</span>`}
                    <span class="category-badge ${U.getCategoryColorClass(category)} article-admin-card__category">${U.escapeHtml(category)}</span>
                    ${featured ? '<span class="article-admin-card__featured">À la une</span>' : ''}
                    ${isPaid ? `<span class="article-admin-card__price">${U.icon('banknote')}${price}</span>` : ''}
                </div>
                <div class="article-admin-card__body">
                    <div class="article-admin-card__meta">
                        <span>${U.icon('calendar')}${U.formatDateShort(article.created_at || article.date_add)}</span>
                        <span>${U.icon('box')}${article.blocks_count ?? 0} blocs</span>
                        <span>${U.icon('eye')}${Number(article.views ?? 0).toLocaleString('fr-FR')}</span>
                    </div>
                    <h3 class="article-admin-card__title">${U.escapeHtml(title)}</h3>
                    <div class="article-admin-card__footer">
                        <div class="article-admin-card__author">
                            <img src="${avatarUrl}" alt="">
                            <span>${U.escapeHtml(authorName)}</span>
                        </div>
                        <div class="article-admin-card__actions">
                            <button type="button" class="action-btn-modern edit btn-article-edit" data-id="${article.id}" title="Modifier">
                                ${U.icon('pencil')}
                            </button>
                            <button type="button" class="action-btn-modern validate btn-article-view" data-id="${article.id}" title="Voir">
                                ${U.icon('eye')}
                            </button>
                            <button type="button" class="action-btn-modern delete btn-article-delete" data-id="${article.id}" data-title="${U.escapeHtml(title)}" title="Supprimer">
                                ${U.icon('trash-2')}
                            </button>
                        </div>
                    </div>
                    <div class="article-admin-card__badges">
                        ${validationBadge(article)}
                        ${paymentBadge(article)}
                    </div>
                </div>
            </article>
        `;
    }

    function renderArticleListRow(article) {
        const title = articleTitle(article);
        const category = articleCategory(article);
        const coverUrl = articleCoverUrl(article);
        const featured = isFeatured(article);
        const selected = isArticleSelected(article.id);

        return `
            <tr class="${selected ? 'all-articles-row--selected' : ''}">
                <td class="all-articles-col-select">
                    <input type="checkbox" class="btn-article-select" data-id="${article.id}" ${selected ? 'checked' : ''} aria-label="Sélectionner l'article">
                </td>
                <td>
                    <div class="pending-cover ${featured ? 'pending-cover--featured' : ''}" style="width:72px;height:48px;">
                        ${coverUrl
                            ? `<img src="${coverUrl}" alt="">`
                            : `<span class="pending-cover-placeholder">${U.icon('image')}</span>`}
                    </div>
                </td>
                <td class="article-title-cell"><strong title="${U.escapeHtml(title)}">${U.escapeHtml(title)}</strong></td>
                <td>${renderPendingAuthor(article)}</td>
                <td><span class="category-badge ${U.getCategoryColorClass(category)}">${U.escapeHtml(category)}</span></td>
                <td><span class="blocks-badge">${article.blocks_count ?? 0}</span></td>
                <td>${paymentBadge(article)}</td>
                <td>${validationBadge(article)}</td>
                <td>${article.is_published ? '<span class="status-badge badge-success">Oui</span>' : '<span class="status-badge badge-secondary">Non</span>'}</td>
                <td>${Number(article.views ?? 0).toLocaleString('fr-FR')}</td>
                <td><span class="pending-date">${U.icon('calendar')}${U.formatDateShort(article.created_at || article.date_add)}</span></td>
                <td class="pending-col-actions">
                    <div class="pending-actions">
                        <button type="button" class="action-btn-modern edit btn-article-edit" data-id="${article.id}" title="Modifier">
                            ${U.icon('pencil')}
                        </button>
                        <button type="button" class="action-btn-modern delete btn-article-delete" data-id="${article.id}" data-title="${U.escapeHtml(title)}" title="Supprimer">
                            ${U.icon('trash-2')}
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function getPaginatedArticleIds() {
        const filtered = getFilteredArticles();
        const start = (articlesPage - 1) * articlesPerPage;
        return filtered.slice(start, start + articlesPerPage).map((article) => Number(article.id));
    }

    function isArticleSelected(id) {
        return selectedArticles.has(Number(id));
    }

    function toggleArticleSelection(id, checked) {
        const articleId = Number(id);
        if (checked) {
            selectedArticles.add(articleId);
        } else {
            selectedArticles.delete(articleId);
        }

        document.querySelectorAll(`.btn-article-select[data-id="${articleId}"]`).forEach((input) => {
            input.checked = checked;
            input.closest('tr')?.classList.toggle('all-articles-row--selected', checked);
            input.closest('.article-admin-card')?.classList.toggle('article-admin-card--selected', checked);
        });

        updateSelectionBar();
        syncSelectAllCheckbox();
    }

    function selectAllArticlesOnPage(checked) {
        getPaginatedArticleIds().forEach((id) => {
            if (checked) {
                selectedArticles.add(id);
            } else {
                selectedArticles.delete(id);
            }
        });
        updateSelectionBar();
        renderArticlesView();
    }

    function clearArticleSelection() {
        selectedArticles.clear();
        updateSelectionBar();
        renderArticlesView();
    }

    function syncSelectAllCheckbox() {
        const selectAll = document.getElementById('selectAllArticles');
        if (!selectAll) return;

        const pageIds = getPaginatedArticleIds();
        selectAll.checked = pageIds.length > 0 && pageIds.every((id) => selectedArticles.has(id));
        selectAll.indeterminate = pageIds.some((id) => selectedArticles.has(id)) && !selectAll.checked;
    }

    function updateSelectionBar() {
        let bar = document.getElementById('articlesSelectionBar');

        if (selectedArticles.size === 0) {
            bar?.remove();
            return;
        }

        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'articlesSelectionBar';
            bar.className = 'articles-selection-bar';
            document.body.appendChild(bar);
        }

        const count = selectedArticles.size;
        bar.innerHTML = `
            <span class="articles-selection-bar__count">
                ${U.icon('square-check')}
                ${count} article${count > 1 ? 's' : ''} sélectionné${count > 1 ? 's' : ''}
            </span>
            <button type="button" class="btn btn-danger btn-sm" id="articlesSelectionDeleteBtn">
                ${U.icon('trash-2')}
                Supprimer
            </button>
            <button type="button" class="articles-selection-bar__clear" id="articlesSelectionClearBtn" title="Annuler la sélection">
                ${U.icon('x')}
            </button>
        `;

        bar.querySelector('#articlesSelectionDeleteBtn')?.addEventListener('click', deleteSelectedArticles);
        bar.querySelector('#articlesSelectionClearBtn')?.addEventListener('click', clearArticleSelection);
        U.refreshIcons(bar);
    }

    async function deleteSelectedArticles() {
        if (selectedArticles.size === 0) return;

        const count = selectedArticles.size;
        if (!await U.confirm(
            `Supprimer ${count} article${count > 1 ? 's' : ''} sélectionné${count > 1 ? 's' : ''} ?`,
            { confirmText: 'Supprimer' },
        )) {
            return;
        }

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/admin/articles/bulk`, {
                method: 'DELETE',
                body: JSON.stringify({ ids: Array.from(selectedArticles) }),
            });
            U.hideLoader();

            if (data.success || (data.deleted ?? 0) > 0) {
                U.showToast(data.message || 'Articles supprimés', 'success');
                selectedArticles.clear();
                updateSelectionBar();
                resetViewLoaded('all-articles');
                await loadAllArticles();
            } else {
                U.showToast(data.message || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function renderArticlesPagination(totalPages) {
        const container = document.getElementById('allArticlesPagination');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination-controls">';

        if (articlesPage > 1) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-page="${articlesPage - 1}" aria-label="Page précédente">${U.icon('chevron-left')}</button>`;
        }

        for (let page = 1; page <= totalPages; page += 1) {
            if (page === 1 || page === totalPages || Math.abs(page - articlesPage) <= 1) {
                html += `<button type="button" class="pagination-btn ${page === articlesPage ? 'active' : ''}" data-page="${page}">${page}</button>`;
            } else if (page === articlesPage - 2 || page === articlesPage + 2) {
                html += '<span class="pagination-ellipsis">…</span>';
            }
        }

        if (articlesPage < totalPages) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-page="${articlesPage + 1}" aria-label="Page suivante">${U.icon('chevron-right')}</button>`;
        }

        html += '</div>';
        container.innerHTML = html;
        U.refreshIcons(container);

        container.querySelectorAll('[data-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                articlesPage = Number(btn.dataset.page);
                renderArticlesView();
                document.getElementById('all-articles-view')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function renderArticlesView() {
        const container = document.getElementById('allArticlesTable');
        if (!container) return;

        try {
            const filtered = getFilteredArticles();
            updateAllArticlesCountLabel(filtered.length);

            if (!filtered.length) {
                container.innerHTML = `
                    <div class="pending-empty">
                        <div class="pending-empty__icon" style="background:#eff6ff;color:#1E5EFF;">
                            ${U.icon('newspaper')}
                        </div>
                        <h3>Aucun article trouvé</h3>
                        <p>Essayez un autre filtre ou une autre recherche.</p>
                    </div>
                `;
                renderArticlesPagination(0);
                return;
            }

            const totalPages = Math.ceil(filtered.length / articlesPerPage) || 1;
            if (articlesPage > totalPages) articlesPage = totalPages;
            const start = (articlesPage - 1) * articlesPerPage;
            const pageItems = filtered.slice(start, start + articlesPerPage);

            if (articlesViewMode === 'grid') {
                container.className = 'all-articles-panel';
                container.innerHTML = `
                    <div class="articles-grid">
                        ${pageItems.map((article) => renderArticleGridCard(article)).join('')}
                    </div>
                `;
            } else {
                container.className = 'all-articles-panel data-table';
                container.innerHTML = `
                    <div class="pending-table-wrap">
                        <table class="pending-table all-articles-table">
                            <thead>
                                <tr>
                                    <th class="all-articles-col-select">
                                        <input type="checkbox" id="selectAllArticles" aria-label="Tout sélectionner sur cette page" title="Tout sélectionner">
                                    </th>
                                    <th>Cover</th>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Blocs</th>
                                    <th>Paiement</th>
                                    <th>Validation</th>
                                    <th>Publié</th>
                                    <th>Vues</th>
                                    <th>Date</th>
                                    <th class="pending-col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${pageItems.map((article) => renderArticleListRow(article)).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            renderArticlesPagination(totalPages);
            bindArticleActions(container);
            syncSelectAllCheckbox();
            updateSelectionBar();
        } catch (error) {
            console.error('Rendu articles:', error);
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>Impossible d'afficher les articles.</p></div>`;
            U.refreshIcons(container);
        }
    }

    function bindArticleActions(container) {
        if (!container) return;

        container.querySelectorAll('.btn-article-select').forEach((input) => {
            input.addEventListener('change', () => {
                toggleArticleSelection(input.dataset.id, input.checked);
            });
        });

        document.getElementById('selectAllArticles')?.addEventListener('change', (event) => {
            selectAllArticlesOnPage(event.target.checked);
        });

        container.querySelectorAll('.btn-article-edit').forEach((btn) => {
            btn.addEventListener('click', () => editArticleAdmin(btn.dataset.id));
        });
        container.querySelectorAll('.btn-article-delete').forEach((btn) => {
            btn.addEventListener('click', () => deleteArticleAdmin(btn.dataset.id, btn.dataset.title));
        });
        container.querySelectorAll('.btn-article-view').forEach((btn) => {
            btn.addEventListener('click', () => showArticleModal(btn.dataset.id));
        });
        U.refreshIcons(container);
    }

    function setupArticleModal() {
        const modal = document.getElementById('articlePreviewModal');
        if (!modal) return;

        const close = () => closeArticleModal();

        document.getElementById('articlePreviewModalClose')?.addEventListener('click', close);
        document.getElementById('articlePreviewModalCloseBtn')?.addEventListener('click', close);
        document.getElementById('articlePreviewModalEditBtn')?.addEventListener('click', () => {
            if (currentPreviewArticleId) editArticleAdmin(currentPreviewArticleId);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('active')) close();
        });
    }

    function closeArticleModal() {
        const modal = document.getElementById('articlePreviewModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        currentPreviewArticleId = null;
    }

    function extractYouTubeId(url) {
        if (!url) return null;
        const value = String(url).trim();
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
            /^([a-zA-Z0-9_-]{11})$/,
        ];

        for (const pattern of patterns) {
            const match = value.match(pattern);
            if (match?.[1]) return match[1];
        }

        return null;
    }

    function renderVideoEmbed(url, className = 'article-preview__video-embed') {
        const youtubeId = extractYouTubeId(url);
        if (youtubeId) {
            return `
                <div class="${className}">
                    <iframe
                        src="https://www.youtube.com/embed/${youtubeId}"
                        title="Vidéo YouTube"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            `;
        }

        return `
            <a href="${U.escapeHtml(url)}" class="article-preview__video-link" target="_blank" rel="noopener">
                ${U.icon('video')}
                ${U.escapeHtml(url)}
            </a>
        `;
    }

    function extractArticleFromResponse(data) {
        if (!data || typeof data !== 'object') return null;
        const article = data.article || data.data?.article;
        return article && typeof article === 'object' ? article : null;
    }

    function extractBlocksFromResponse(data) {
        if (!data || typeof data !== 'object') return [];
        return data.blocks || data.data?.blocks || [];
    }

    async function showArticleModal(articleId) {
        const id = String(articleId ?? '').trim();
        if (!id || id === 'undefined' || id === 'null') {
            U.showToast('Identifiant article invalide', 'error');
            return;
        }

        const modal = document.getElementById('articlePreviewModal');
        const body = document.getElementById('articlePreviewModalBody');
        const footer = document.getElementById('articlePreviewModalFooter');
        const titleEl = document.getElementById('articlePreviewModalTitle');
        if (!modal || !body) {
            U.showToast('Modal d\'aperçu indisponible', 'error');
            return;
        }

        currentPreviewArticleId = id;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        footer?.setAttribute('hidden', '');
        if (titleEl) titleEl.textContent = 'Chargement…';
        body.innerHTML = `<div class="article-preview-loading">${U.icon('loader-circle', 'lucide-spin')}<span>Chargement de l'article…</span></div>`;
        U.refreshIcons(modal);

        try {
            const articleData = await U.api(`${cfg.apiBase}/admin/articles/${id}`);
            const article = extractArticleFromResponse(articleData);
            if (!article) {
                throw new Error('Article introuvable ou réponse invalide');
            }

            let blocks = [];
            try {
                const blocksData = await U.api(`${cfg.apiBase}/admin/articles/${id}/blocks`);
                blocks = extractBlocksFromResponse(blocksData);
            } catch (blocksError) {
                console.warn('Blocs article non chargés', blocksError);
            }

            if (titleEl) titleEl.textContent = articleTitle(article);
            body.innerHTML = renderArticlePreview(article, blocks);
            footer?.removeAttribute('hidden');
            bindArticlePreviewBlocks(body);
            U.refreshIcons(body);
            U.refreshIcons(modal);
        } catch (error) {
            if (titleEl) titleEl.textContent = 'Erreur';
            body.innerHTML = `<div class="article-preview-error">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(body);
            U.refreshIcons(modal);
        }
    }

    function bindArticlePreviewBlocks(container) {
        container.querySelectorAll('[data-block-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const item = toggle.closest('.article-preview-block');
                const expanded = item?.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
        });
    }

    function renderArticlePreviewStat(icon, label, value) {
        return `
            <div class="article-preview-stat">
                <span class="article-preview-stat__icon">${U.icon(icon)}</span>
                <div>
                    <span class="article-preview-stat__label">${label}</span>
                    <strong class="article-preview-stat__value">${value}</strong>
                </div>
            </div>
        `;
    }

    function renderArticlePreview(article, blocks) {
        const title = articleTitle(article);
        const category = articleCategory(article);
        const categoryClass = U.getCategoryColorClass(category);
        const authorName = articleAuthorName(article);
        const authorEmail = article.author?.mail || article.auteur_email || '';
        const authorAvatar = article.author?.cover || article.auteur_cover;
        const avatarUrl = authorAvatar ? U.mediaUrl(authorAvatar) : null;
        const date = U.formatDateShort(article.created_at || article.date_add);
        const coverUrl = articleCoverUrl(article);
        const caption = article.caption || article.legende || '';
        const content = article.content || article.contenu || '';
        const videos = article.videos || '';
        const featured = isFeatured(article);
        const isPaid = article.is_paid === true || article.is_paid === 1 || article.is_paid === '1';
        const price = article.price ? `${Number(article.price).toFixed(2)} $` : null;
        const views = Number(article.views ?? 0).toLocaleString('fr-FR');
        const blocksCount = blocks.length || article.blocks_count || 0;

        const heroCover = coverUrl
            ? `<img src="${coverUrl}" alt="" class="article-preview-hero__bg" aria-hidden="true">`
            : '';

        const badges = [
            `<span class="category-badge ${categoryClass}">${U.escapeHtml(category)}</span>`,
            validationBadge(article),
            paymentBadge(article),
            article.is_published
                ? '<span class="status-badge badge-success">Publié</span>'
                : '<span class="status-badge badge-secondary">Non publié</span>',
            featured ? '<span class="status-badge badge-info">À la une</span>' : '',
            isPaid ? `<span class="status-badge badge-warning">Premium${price ? ` · ${price}` : ''}</span>` : '',
        ].filter(Boolean).join('');

        const blocksHtml = blocks.length
            ? `
                <section class="article-preview-section">
                    <div class="article-preview-section__head">
                        <h4>${U.icon('layers')} Blocs complémentaires</h4>
                        <span class="article-preview-section__count">${blocks.length}</span>
                    </div>
                    <div class="article-preview-blocks">
                        ${blocks.map((block, index) => renderArticlePreviewBlock(block, index)).join('')}
                    </div>
                </section>
            `
            : '';

        return `
            <article class="article-preview">
                <header class="article-preview-hero ${coverUrl ? 'article-preview-hero--cover' : 'article-preview-hero--plain'}">
                    ${heroCover}
                    <div class="article-preview-hero__content">
                        <div class="article-preview-hero__badges">${badges}</div>
                        <h2 class="article-preview-hero__title">${U.escapeHtml(title)}</h2>
                        ${caption && coverUrl ? `<p class="article-preview-hero__caption">${U.escapeHtml(caption)}</p>` : ''}
                    </div>
                </header>

                <div class="article-preview-stats">
                    ${renderArticlePreviewStat('user', 'Auteur', U.escapeHtml(authorName))}
                    ${renderArticlePreviewStat('calendar', 'Date', date)}
                    ${renderArticlePreviewStat('eye', 'Vues', views)}
                    ${renderArticlePreviewStat('box', 'Blocs', blocksCount)}
                </div>

                <div class="article-preview-author-card">
                    ${avatarUrl
                        ? `<img src="${avatarUrl}" alt="" class="article-preview-author-card__avatar">`
                        : `<div class="article-preview-author-card__avatar article-preview-author-card__avatar--initials">${U.getInitials(authorName)}</div>`}
                    <div class="article-preview-author-card__info">
                        <strong>${U.escapeHtml(authorName)}</strong>
                        ${authorEmail ? `<span>${U.escapeHtml(authorEmail)}</span>` : ''}
                    </div>
                    <span class="article-preview-author-card__id">#${article.id}</span>
                </div>

                ${coverUrl ? `
                    <section class="article-preview-section">
                        <div class="article-preview-section__head">
                            <h4>${U.icon('image')} Image principale</h4>
                        </div>
                        <figure class="article-preview-figure">
                            <img src="${coverUrl}" alt="${U.escapeHtml(title)}" class="article-preview-figure__img">
                            ${caption ? `<figcaption>${U.escapeHtml(caption)}</figcaption>` : ''}
                        </figure>
                    </section>
                ` : (caption ? `<p class="article-preview__caption">${U.escapeHtml(caption)}</p>` : '')}

                ${content ? `
                    <section class="article-preview-section">
                        <div class="article-preview-section__head">
                            <h4>${U.icon('file-text')} Description</h4>
                        </div>
                        <div class="article-preview__content prose">${U.formatArticleContent(content)}</div>
                    </section>
                ` : `
                    <div class="article-preview-empty">
                        ${U.icon('file')}
                        <p>Aucun contenu principal renseigné.</p>
                    </div>
                `}

                ${videos ? `
                    <section class="article-preview-section">
                        <div class="article-preview-section__head">
                            <h4>${U.icon('video')} Vidéo</h4>
                        </div>
                        ${renderVideoEmbed(videos)}
                    </section>
                ` : ''}

                ${blocksHtml}
            </article>
        `;
    }

    function renderArticlePreviewBlock(block, index) {
        const title = block.title || block.titre || `Bloc ${index + 1}`;
        const coverUrl = block.cover ? U.mediaUrl(block.cover) : null;
        const caption = block.caption || block.legende || '';
        const content = block.content || block.contenu || '';
        const videos = block.videos || '';
        const hasBody = Boolean(title || coverUrl || caption || content || videos);
        const openByDefault = index === 0;

        return `
            <div class="article-preview-block ${openByDefault ? 'is-open' : ''}">
                <button type="button" class="article-preview-block__toggle" data-block-toggle aria-expanded="${openByDefault ? 'true' : 'false'}">
                    <span class="article-preview-block__index">${index + 1}</span>
                    <span class="article-preview-block__label">${U.escapeHtml(title)}</span>
                    ${U.icon('chevron-down', 'article-preview-block__chevron')}
                </button>
                <div class="article-preview-block__panel">
                    ${!hasBody ? '<p class="article-preview-empty article-preview-empty--inline">Bloc vide.</p>' : ''}
                    ${coverUrl ? `<img src="${coverUrl}" class="article-preview-block__cover" alt="">` : ''}
                    ${caption ? `<p class="article-preview-block__caption">${U.escapeHtml(caption)}</p>` : ''}
                    ${content ? `<div class="article-preview-block__content prose">${U.formatArticleContent(content)}</div>` : ''}
                    ${videos ? renderVideoEmbed(videos, 'article-preview-block__video') : ''}
                </div>
            </div>
        `;
    }

    function editArticleAdmin(id) {
        window.location.href = `/dashboard/admin/publish?id=${id}`;
    }

    async function deleteArticleAdmin(id, title = '') {
        const label = title || articleTitle(allArticles.find((article) => String(article.id) === String(id)) || {});

        if (!await U.confirm(`Supprimer « ${label || 'Sans titre'} » ?`, { confirmText: 'Supprimer' })) {
            return;
        }

        try {
            U.showLoader('Suppression...');
            const data = await U.api(`${cfg.apiBase}/admin/articles/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Article supprimé', 'success');
            selectedArticles.delete(Number(id));
            updateSelectionBar();
            resetViewLoaded('all-articles');
            await loadAllArticles();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function loadAllArticles({ skipIfCached = false } = {}) {
        const container = document.getElementById('allArticlesTable');
        if (!container) return;
        if (skipIfCached && isViewLoaded('all-articles')) return;

        container.innerHTML = U.loadingHtml();
        selectedArticles.clear();
        updateSelectionBar();

        try {
            document.getElementById('categoriesCardsContainer')?.replaceChildren();
            document.getElementById('allArticlesPagination')?.replaceChildren();

            const data = await U.api(`${cfg.apiBase}/admin/articles?limit=100`);
            allArticles = U.extractArticlesList(data);

            articlesCategory = '';
            articlesSearch = '';
            articlesPage = 1;
            const searchInput = document.getElementById('allArticlesSearchInput');
            if (searchInput) searchInput.value = '';

            updateAllArticlesCountLabel(allArticles.length);
            renderCategoryFilters();

            if (!allArticles.length) {
                container.innerHTML = `
                    <div class="pending-empty">
                        <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">
                            ${U.icon('inbox')}
                        </div>
                        <h3>Aucun article</h3>
                        <p>Le catalogue est vide pour le moment.</p>
                    </div>
                `;
                U.refreshIcons(container);
                markViewLoaded('all-articles');
                return;
            }

            renderArticlesView();
            markViewLoaded('all-articles');
        } catch (error) {
            console.error('Tous les articles:', error);
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
        }
    }

    async function loadUsers({ skipIfCached = false } = {}) {
        const container = document.getElementById('usersTable');
        const pagination = document.getElementById('usersPagination');
        if (!container) return;
        if (skipIfCached && isViewLoaded('users')) return;

        container.innerHTML = U.loadingHtml();
        if (pagination) pagination.innerHTML = '';

        try {
            const params = new URLSearchParams({
                page: String(usersPage),
                per_page: String(usersPerPage),
            });
            if (usersSearch) params.set('search', usersSearch);

            const data = await U.api(`${cfg.apiBase}/admin/users?${params.toString()}`);
            const items = data.data?.users || [];
            const total = Number(data.data?.total ?? 0);
            const totalPages = Number(data.data?.totalPages ?? 1);

            updateUsersCountLabel(total);

            if (!items.length) {
                container.innerHTML = `
                    <div class="pending-empty users-empty">
                        <div class="pending-empty__icon" style="background:#eff6ff;color:#1E5EFF;">
                            ${U.icon('users')}
                        </div>
                        <h3>Aucun utilisateur trouvé</h3>
                        <p>${usersSearch ? 'Essayez une autre recherche.' : 'Aucun compte enregistré pour le moment.'}</p>
                    </div>
                `;
                U.refreshIcons(container);
                markViewLoaded('users');
                return;
            }

            container.innerHTML = `
                <div class="pending-table-wrap users-table-wrap">
                    <table class="pending-table users-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Connexion</th>
                                <th>Inscrit le</th>
                                <th class="pending-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((user) => renderUserRow(user)).join('')}
                        </tbody>
                    </table>
                    <div class="users-table-footer">
                        <span>${total} utilisateur${total > 1 ? 's' : ''} au total</span>
                    </div>
                </div>
            `;

            bindUserActions(container);
            renderUsersPagination(totalPages);
            U.refreshIcons(container);
            markViewLoaded('users');
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function updateUsersCountLabel(count) {
        const label = document.getElementById('usersCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 utilisateur' : `${count} utilisateurs`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function userRoleValue(user) {
        return user.role?.value ?? user.role ?? 'user';
    }

    function userRoleLabel(role) {
        const map = {
            user: 'Utilisateur',
            journaliste: 'Journaliste',
            admin: 'Administrateur',
            superadmin: 'Super administrateur',
        };
        return map[role] || role;
    }

    function userRoleBadgeClass(role) {
        const map = {
            user: 'badge-secondary',
            journaliste: 'badge-info',
            admin: 'badge-warning',
            superadmin: 'badge-purple',
        };
        return map[role] || 'badge-secondary';
    }

    function renderUserRow(user) {
        const name = user.nom || user.name || '—';
        const email = user.mail || user.email || '';
        const phone = user.telephone || user.phone || '';
        const jobTitle = user.Titre || user.job_title || '';
        const role = userRoleValue(user);
        const isSuperAdmin = role === 'superadmin';
        const avatarUrl = user.cover ? U.mediaUrl(user.cover) : U.mediaUrl(null);
        const isOnline = Number(user.connect ?? 0) === 1;
        const isActive = Number(user.status ?? 0) === 1;
        const createdAt = user.created_at ? U.formatDateShort(user.created_at) : '—';

        const roleCell = isSuperAdmin
            ? `<span class="status-badge ${userRoleBadgeClass(role)} users-role-badge">${U.escapeHtml(userRoleLabel(role))}</span>`
            : `
                <select class="users-role-select" data-user-id="${user.id}" data-user-name="${U.escapeHtml(name)}" aria-label="Rôle de ${U.escapeHtml(name)}">
                    <option value="user" ${role === 'user' ? 'selected' : ''}>Utilisateur</option>
                    <option value="journaliste" ${role === 'journaliste' ? 'selected' : ''}>Journaliste</option>
                    <option value="admin" ${role === 'admin' ? 'selected' : ''}>Administrateur</option>
                    <option value="superadmin" ${role === 'superadmin' ? 'selected' : ''}>Super admin</option>
                </select>
            `;

        const actionCell = (() => {
            const editBtn = cfg.isSuperAdmin
                ? `<button type="button" class="action-btn-modern btn-user-edit" data-id="${user.id}" title="Modifier">${U.icon('pencil')}</button>`
                : '';
            const toggleBtn = isActive
                ? `<button type="button" class="action-btn-modern delete btn-user-toggle" data-id="${user.id}" data-name="${U.escapeHtml(name)}" data-active="1" title="Désactiver">${U.icon('ban')}</button>`
                : `<button type="button" class="action-btn-modern validate btn-user-toggle" data-id="${user.id}" data-name="${U.escapeHtml(name)}" data-active="0" title="Activer">${U.icon('circle-check')}</button>`;
            const lockBtn = `<button type="button" class="action-btn-modern users-action-locked" disabled title="Compte protégé">${U.icon('lock')}</button>`;

            return `<div class="pending-actions">${editBtn}${isSuperAdmin ? lockBtn : toggleBtn}</div>`;
        })();

        return `
            <tr class="users-row">
                <td>
                    <div class="users-identity">
                        <div class="users-avatar-wrap ${isOnline ? 'users-avatar-wrap--online' : ''}">
                            <img src="${avatarUrl}" alt="" class="users-avatar">
                        </div>
                        <div class="users-identity__info">
                            <strong class="users-identity__name">${U.escapeHtml(name)}</strong>
                            <span class="users-identity__email">${U.escapeHtml(email)}</span>
                            ${jobTitle ? `<span class="users-identity__title">${U.escapeHtml(jobTitle)}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td>${roleCell}</td>
                <td>
                    ${phone
                        ? `<span class="users-contact">${U.icon('phone')}${U.escapeHtml(phone)}</span>`
                        : '<span class="users-contact users-contact--empty">Non renseigné</span>'}
                </td>
                <td>
                    <span class="status-badge ${isActive ? 'badge-success' : 'badge-danger'}">${isActive ? 'Actif' : 'Inactif'}</span>
                </td>
                <td>
                    <span class="users-connection ${isOnline ? 'users-connection--online' : ''}">
                        <span class="users-connection__dot"></span>
                        ${isOnline ? 'En ligne' : 'Hors ligne'}
                    </span>
                </td>
                <td><span class="pending-date">${U.icon('calendar')}${createdAt}</span></td>
                <td class="pending-col-actions">${actionCell}</td>
            </tr>
        `;
    }

    function bindUserActions(container) {
        container.querySelectorAll('.btn-user-edit').forEach((btn) => {
            btn.addEventListener('click', () => openUserEditModal(btn.dataset.id));
        });

        container.querySelectorAll('.btn-user-toggle').forEach((btn) => {
            btn.addEventListener('click', () => toggleUserStatus(btn.dataset.id, btn.dataset.name, btn.dataset.active === '1'));
        });

        container.querySelectorAll('.users-role-select').forEach((select) => {
            select.addEventListener('focus', () => {
                select.dataset.previousRole = select.value;
            });
            select.addEventListener('change', () => changeUserRole(select));
        });
    }

    function renderUsersPagination(totalPages) {
        const container = document.getElementById('usersPagination');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination-controls">';

        if (usersPage > 1) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-users-page="${usersPage - 1}" aria-label="Page précédente">${U.icon('chevron-left')}</button>`;
        }

        for (let page = 1; page <= totalPages; page += 1) {
            if (page === 1 || page === totalPages || Math.abs(page - usersPage) <= 1) {
                html += `<button type="button" class="pagination-btn ${page === usersPage ? 'active' : ''}" data-users-page="${page}">${page}</button>`;
            } else if (page === usersPage - 2 || page === usersPage + 2) {
                html += '<span class="pagination-ellipsis">…</span>';
            }
        }

        if (usersPage < totalPages) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-users-page="${usersPage + 1}" aria-label="Page suivante">${U.icon('chevron-right')}</button>`;
        }

        html += '</div>';
        container.innerHTML = html;
        U.refreshIcons(container);

        container.querySelectorAll('[data-users-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                usersPage = Number(btn.dataset.usersPage);
                loadUsers();
            });
        });
    }

    function setupUsersFilters() {
        if (usersFiltersReady) return;
        usersFiltersReady = true;

        document.getElementById('usersRefreshBtn')?.addEventListener('click', () => loadUsers());

        document.getElementById('usersSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(usersSearchTimeout);
            usersSearchTimeout = setTimeout(() => {
                usersSearch = event.target.value.trim();
                usersPage = 1;
                loadUsers();
            }, 300);
        });

        document.getElementById('usersPerPageSelect')?.addEventListener('change', (event) => {
            usersPerPage = Number(event.target.value) || 10;
            usersPage = 1;
            loadUsers();
        });
    }

    async function loadPayments({ skipIfCached = false } = {}) {
        const container = document.getElementById('paymentsTable');
        const pagination = document.getElementById('paymentsPagination');
        const statsGrid = document.getElementById('paymentsStatsGrid');
        if (!container) return;
        if (skipIfCached && isViewLoaded('payments')) return;

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

            const data = await U.api(`${cfg.apiBase}/admin/payments?${params.toString()}`);
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
                markViewLoaded('payments');
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
            markViewLoaded('payments');
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function updatePaymentsCountLabel(count) {
        const label = document.getElementById('paymentsCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 transaction' : `${count} transactions`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function renderPaymentsStats(stats) {
        const grid = document.getElementById('paymentsStatsGrid');
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

        const amountEl = document.getElementById('paymentsChartAmount');
        const subtitleEl = document.getElementById('paymentsChartSubtitle');
        const donutAmountEl = document.getElementById('paymentsDonutAmount');
        const donutGrowthEl = document.getElementById('paymentsDonutGrowth');

        if (amountEl) amountEl.textContent = `$${succeededAmount.toFixed(2)}`;
        if (subtitleEl) {
            subtitleEl.textContent = `${stats.succeeded_count ?? 0} paiements réussis sur ${stats.total_count ?? 0} transactions`;
        }
        if (donutAmountEl) donutAmountEl.textContent = `$${totalAmount.toFixed(2)}`;
        if (donutGrowthEl) donutGrowthEl.textContent = `${successRate}% réussis`;

        renderLineChart('paymentsTrend', 'paymentsLineChart', {
            label: 'Montant réussi ($)',
            data: periodData.amounts || [],
            labels: periodData.labels || [],
            color: CHART_COLORS.blue,
            fillColor: 'rgba(30, 94, 255, 0.12)',
        });

        renderDoughnutChart('paymentsBreakdown', 'paymentsDonutChart', {
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

    const PAYMENT_METHOD_IMAGES = {
        carte_bancaire: '/assets/img/pictos/card.jpg',
        mpesa: '/assets/img/pictos/mpesa01.jpg',
        airtel_money: '/assets/img/pictos/airtel.jpg',
        orange_money: '/assets/img/pictos/orange3.jpg',
    };

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
                    <div class="payments-user__name">${U.escapeHtml(payment.user_name || 'Inconnu')}</div>
                </td>
                <td>${renderPaymentMethodIcon(payment.method)}</td>
                <td><span class="status-badge ${status.class}">${status.label}</span></td>
                <td><strong class="payments-amount">$${Number(payment.amount || 0).toFixed(2)}</strong></td>
            </tr>
        `;
    }

    function renderPaymentsPagination(totalPages) {
        const container = document.getElementById('paymentsPagination');
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
                loadPayments();
            });
        });
    }

    function setupPaymentsFilters() {
        if (paymentsFiltersReady) return;
        paymentsFiltersReady = true;

        document.getElementById('paymentsRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('payments');
            loadPayments();
        });

        document.getElementById('paymentsSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(paymentsSearchTimeout);
            paymentsSearchTimeout = setTimeout(() => {
                paymentsSearch = event.target.value.trim();
                paymentsPage = 1;
                loadPayments();
            }, 300);
        });

        document.getElementById('paymentsPerPageSelect')?.addEventListener('change', (event) => {
            paymentsPerPage = Number(event.target.value) || 10;
            paymentsPage = 1;
            loadPayments();
        });

        document.getElementById('paymentsStatusFilters')?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-status]');
            if (!btn) return;

            paymentsStatus = btn.dataset.status || 'all';
            paymentsPage = 1;

            document.querySelectorAll('#paymentsStatusFilters .payments-status-btn').forEach((el) => {
                el.classList.toggle('active', el.dataset.status === paymentsStatus);
            });

            loadPayments();
        });

        document.getElementById('paymentsChartPeriod')?.addEventListener('change', (event) => {
            paymentsChartPeriod = event.target.value || 'monthly';
            if (paymentsChartStats) {
                initPaymentsCharts(paymentsChartStats);
            }
        });
    }

    async function loadAds({ skipIfCached = false } = {}) {
        const container = document.getElementById('adsList');
        const pagination = document.getElementById('adsPagination');
        const statsGrid = document.getElementById('adsStatsGrid');
        if (!container) return;
        if (skipIfCached && isViewLoaded('ads')) return;

        container.innerHTML = U.loadingHtml();
        if (pagination) pagination.innerHTML = '';

        try {
            const params = new URLSearchParams({
                page: String(adsPage),
                per_page: String(adsPerPage),
            });
            if (adsFilters.validation) params.set('validation', adsFilters.validation);
            if (adsFilters.payment) params.set('payment', adsFilters.payment);
            if (adsFilters.broadcast) params.set('broadcast', adsFilters.broadcast);
            if (adsFilters.placement) params.set('placement', adsFilters.placement);
            if (adsSearch) params.set('search', adsSearch);

            const data = await U.api(`${cfg.apiBase}/admin/advertisements?${params.toString()}`);
            const items = data.data?.advertisements || [];
            const stats = data.data?.stats || {};
            const total = Number(data.data?.total ?? 0);
            const totalPages = Number(data.data?.totalPages ?? 1);

            updateAdsCountLabel(total);
            renderAdsStats(stats);

            if (!items.length) {
                container.innerHTML = `
                    <div class="pending-empty ads-empty">
                        <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">
                            ${U.icon('megaphone')}
                        </div>
                        <h3>Aucune publicité trouvée</h3>
                        <p>${adsSearch || Object.values(adsFilters).some(Boolean) ? 'Aucune campagne ne correspond à votre recherche.' : 'Aucune campagne ne correspond aux filtres sélectionnés.'}</p>
                    </div>
                `;
                U.refreshIcons(container);
                markViewLoaded('ads');
                return;
            }

            container.innerHTML = `
                <div class="ads-list">
                    ${items.map((ad, index) => renderAdRow(ad, index)).join('')}
                </div>
            `;

            bindAdActions(container);
            renderAdsPagination(totalPages);
            U.refreshIcons(container);
            markViewLoaded('ads');
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function updateAdsCountLabel(count) {
        const label = document.getElementById('adsCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 publicité' : `${count} publicités`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function renderAdsStats(stats) {
        const grid = document.getElementById('adsStatsGrid');
        if (!grid) return;

        const cards = [
            { color: 'stat-card-orange', icon: 'clock', label: 'En attente', value: stats.pending ?? 0 },
            { color: 'stat-card-green', icon: 'circle-check', label: 'Validées', value: stats.validated ?? 0 },
            { color: 'stat-card-blue', icon: 'megaphone', label: 'Actives', value: stats.active ?? 0 },
            { color: 'stat-card-teal', icon: 'banknote', label: 'Revenus', value: `$${Number(stats.revenue ?? 0).toFixed(2)}` },
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

    function adFormatLabel(format) {
        const map = {
            rectangle: 'Rectangle',
            portrait: 'Portrait',
            large_portrait: 'Grand portrait',
            large_rectangle: 'Grand rectangle',
            paysage_small: 'Paysage S',
            paysage_medium: 'Paysage M',
            paysage_large: 'Paysage L',
        };
        return map[format] || format || '—';
    }

    function adPlacementLabel(placement) {
        const map = {
            'pub-header': 'Header',
            'pub-modal': 'Modal',
            'pub-float': 'Flottant',
            'pub-body-1': 'Corps 1',
            'pub-body-2': 'Corps 2',
            'pub-body-3': 'Corps 3',
            'pub-body-sidebar-1': 'Sidebar 1',
            'pub-body-sidebar-2': 'Sidebar 2',
            'pub-footer': 'Footer',
        };
        return map[placement] || placement || 'Non défini';
    }

    function adStatusLabel(type, status) {
        const map = {
            payment: { gratuit: 'Gratuit', paye: 'Payé', en_attente: 'En attente' },
            validation: { valide: 'Validé', refuse: 'Refusé', en_attente: 'En attente' },
            broadcast: { active: 'Active', inactive: 'Inactive', terminee: 'Terminée' },
        };
        return map[type]?.[status] || status || '—';
    }

    function adDurationDays(start, end) {
        if (!start || !end) return '—';
        const debut = new Date(start);
        const fin = new Date(end);
        if (Number.isNaN(debut.getTime()) || Number.isNaN(fin.getTime())) return '—';
        const days = Math.ceil((fin - debut) / (1000 * 60 * 60 * 24)) + 1;
        return days > 0 ? days : '—';
    }

    function adDateStatus(start, end) {
        if (!start || !end) return '—';
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const debut = new Date(start);
        debut.setHours(0, 0, 0, 0);
        const fin = new Date(end);
        fin.setHours(0, 0, 0, 0);
        if (today < debut) return 'À venir';
        if (today > fin) return 'Expirée';
        return 'En cours';
    }

    function adStatusIcon(type, status) {
        const map = {
            payment: {
                gratuit: 'gift',
                paye: 'banknote',
                en_attente: 'clock',
            },
            validation: {
                valide: 'badge-check',
                refuse: 'circle-x',
                en_attente: 'clock',
            },
            broadcast: {
                active: 'radio',
                inactive: 'pause-circle',
                terminee: 'flag',
            },
        };
        return map[type]?.[status] || 'help-circle';
    }

    function renderAdStatusBadge(type, status) {
        const label = adStatusLabel(type, status);
        const icon = adStatusIcon(type, status);
        return `<span class="ads-status-icon ads-status-icon--${U.escapeHtml(status)}" title="${U.escapeHtml(label)}" aria-label="${U.escapeHtml(label)}">${U.icon(icon)}</span>`;
    }

    function adAttr(value) {
        return String(value ?? '').replace(/"/g, '&quot;');
    }

    function renderAdRow(ad, index) {
        const locked = ad.is_locked ? ' ads-row--locked' : '';
        const rejectBlock = ad.rejection_reason
            ? `<div class="ads-row-alert">${U.icon('info')}<strong>Motif du refus :</strong> ${U.escapeHtml(ad.rejection_reason)}</div>`
            : '';

        return `
            <div class="ads-row${locked}">
                <div class="ads-row__index">${ad.is_locked ? U.icon('lock') : index + 1}</div>
                <div class="ads-row__info">
                    <div class="ads-row__title">${U.escapeHtml(ad.title || 'Sans titre')}</div>
                    <div class="ads-row__subtitle">${U.escapeHtml(ad.user_name || 'Inconnu')}</div>
                </div>
                <div class="ads-row__col">
                    <div class="ads-row__label">${U.escapeHtml(adFormatLabel(ad.format))}</div>
                    <div class="ads-row__meta">${U.escapeHtml(adPlacementLabel(ad.placement))}</div>
                </div>
                <div class="ads-row__col">
                    <div class="ads-row__label">$${Number(ad.amount_paid || 0).toFixed(2)}</div>
                    <div class="ads-row__meta">${adDurationDays(ad.starts_at, ad.ends_at)} jours</div>
                </div>
                <div class="ads-row__col">
                    <div class="ads-row__label">${U.formatDateShort(ad.starts_at)}</div>
                    <div class="ads-row__meta">${adDateStatus(ad.starts_at, ad.ends_at)}</div>
                </div>
                <div class="ads-row__stats">
                    <span title="Clics">${U.icon('pointer')} ${ad.clicks ?? 0}</span>
                    <span title="Vues">${U.icon('eye')} ${ad.views ?? 0}</span>
                    <span title="Impressions">${U.icon('bar-chart-2')} ${ad.impressions ?? 0}</span>
                </div>
                <div class="ads-row__status">
                    ${renderAdStatusBadge('payment', ad.payment_status)}
                    ${renderAdStatusBadge('validation', ad.validation_status)}
                    ${renderAdStatusBadge('broadcast', ad.broadcast_status)}
                </div>
                <div class="ads-row__actions">
                    <button type="button" class="ads-action-btn" data-ad-action="image" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" data-ad-image="${adAttr(ad.image_url)}" title="Voir l'image">${U.icon('image')}</button>
                    <button type="button" class="ads-action-btn" data-ad-action="edit" data-ad-id="${ad.id}" title="Modifier">${U.icon('pencil')}</button>
                    ${ad.validation_status === 'en_attente' ? `
                        <button type="button" class="ads-action-btn ads-action-btn--success" data-ad-action="validate" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Valider">${U.icon('circle-check')}</button>
                        <button type="button" class="ads-action-btn ads-action-btn--danger" data-ad-action="refuse" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Refuser">${U.icon('circle-x')}</button>
                    ` : ''}
                    ${ad.validation_status === 'valide' && ad.broadcast_status === 'inactive' ? `
                        <button type="button" class="ads-action-btn ads-action-btn--success" data-ad-action="activate" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Activer">${U.icon('play')}</button>
                    ` : ''}
                    ${ad.broadcast_status === 'active' ? `
                        <button type="button" class="ads-action-btn ads-action-btn--warning" data-ad-action="deactivate" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Désactiver">${U.icon('pause')}</button>
                    ` : ''}
                    <button type="button" class="ads-action-btn ads-action-btn--danger" data-ad-action="delete" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Supprimer">${U.icon('trash-2')}</button>
                </div>
            </div>
            ${rejectBlock}
        `;
    }

    function bindAdActions(container) {
        container.querySelectorAll('[data-ad-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.adAction;
                const id = Number(btn.dataset.adId);
                const title = btn.dataset.adTitle || 'Publicité';

                if (action === 'image') openAdImageModal(title, btn.dataset.adImage);
                if (action === 'edit') window.DashboardAdminAdEdit?.open(id);
                if (action === 'validate') validateAd(id, title);
                if (action === 'refuse') openAdRefuseModal(id, title);
                if (action === 'activate') activateAd(id, title);
                if (action === 'deactivate') deactivateAd(id, title);
                if (action === 'delete') deleteAd(id, title);
            });
        });
    }

    function openAdImageModal(title, imagePath) {
        const modal = document.getElementById('adImageModal');
        const img = document.getElementById('adImageModalImg');
        const titleEl = document.getElementById('adImageModalTitle');
        if (!modal || !img) return;

        titleEl.textContent = title || 'Publicité';
        img.src = imagePath ? U.mediaUrl(imagePath) : '';
        img.alt = title || 'Publicité';
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
    }

    function closeAdImageModal() {
        const modal = document.getElementById('adImageModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function openAdRefuseModal(id, title) {
        adRefuseTargetId = id;
        const modal = document.getElementById('adRefuseModal');
        const message = document.getElementById('adRefuseMessage');
        const input = document.getElementById('adRefuseInput');
        if (!modal || !message || !input) return;

        message.textContent = `Refuser « ${title} » ?`;
        input.value = '';
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        input.focus();
    }

    function closeAdRefuseModal() {
        const modal = document.getElementById('adRefuseModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        adRefuseTargetId = null;
    }

    async function submitAdRefuse() {
        const input = document.getElementById('adRefuseInput');
        const reason = input?.value.trim() || '';
        if (!adRefuseTargetId) return;
        if (!reason) {
            U.showToast('Le motif du refus est requis', 'warning');
            return;
        }

        try {
            U.showLoader('Refus en cours…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${adRefuseTargetId}/refuse`, {
                method: 'POST',
                body: JSON.stringify({ reason }),
            });
            U.hideLoader();
            closeAdRefuseModal();
            U.showToast(data.message || 'Publicité refusée', 'success');
            await loadAds();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function validateAd(id, title) {
        if (!await U.confirm(`Valider « ${title} » ?`, { confirmText: 'Valider' })) return;

        try {
            U.showLoader('Validation…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${id}/validate`, { method: 'POST' });
            U.hideLoader();
            U.showToast(data.message || 'Publicité validée', 'success');
            await loadAds();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function activateAd(id, title) {
        if (!await U.confirm(`Activer la diffusion de « ${title} » ?`, { confirmText: 'Activer' })) return;

        try {
            U.showLoader('Activation…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${id}/activate`, { method: 'POST' });
            U.hideLoader();
            U.showToast(data.message || 'Publicité activée', 'success');
            await loadAds();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function deactivateAd(id, title) {
        if (!await U.confirm(`Désactiver « ${title} » ?`, { confirmText: 'Désactiver' })) return;

        try {
            U.showLoader('Désactivation…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${id}/deactivate`, { method: 'POST' });
            U.hideLoader();
            U.showToast(data.message || 'Publicité désactivée', 'success');
            await loadAds();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function deleteAd(id, title) {
        if (!await U.confirm(`Supprimer « ${title} » ?`, { confirmText: 'Supprimer' })) return;

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Publicité supprimée', 'success');
            await loadAds();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function renderAdsPagination(totalPages) {
        const container = document.getElementById('adsPagination');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination-controls">';

        if (adsPage > 1) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-ads-page="${adsPage - 1}" aria-label="Page précédente">${U.icon('chevron-left')}</button>`;
        }

        for (let page = 1; page <= totalPages; page += 1) {
            if (page === 1 || page === totalPages || Math.abs(page - adsPage) <= 1) {
                html += `<button type="button" class="pagination-btn ${page === adsPage ? 'active' : ''}" data-ads-page="${page}">${page}</button>`;
            } else if (page === adsPage - 2 || page === adsPage + 2) {
                html += '<span class="pagination-ellipsis">…</span>';
            }
        }

        if (adsPage < totalPages) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-ads-page="${adsPage + 1}" aria-label="Page suivante">${U.icon('chevron-right')}</button>`;
        }

        html += '</div>';
        container.innerHTML = html;
        U.refreshIcons(container);

        container.querySelectorAll('[data-ads-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                adsPage = Number(btn.dataset.adsPage);
                loadAds();
            });
        });
    }

    function setupAdsFilters() {
        if (adsFiltersReady) return;
        adsFiltersReady = true;

        document.getElementById('adsRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('ads');
            loadAds();
        });

        document.getElementById('adsSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(adsSearchTimeout);
            adsSearchTimeout = setTimeout(() => {
                adsSearch = event.target.value.trim();
                adsPage = 1;
                loadAds();
            }, 300);
        });

        const filterMap = {
            adsFilterValidation: 'validation',
            adsFilterPayment: 'payment',
            adsFilterBroadcast: 'broadcast',
            adsFilterPlacement: 'placement',
        };

        Object.entries(filterMap).forEach(([id, key]) => {
            document.getElementById(id)?.addEventListener('change', (event) => {
                adsFilters[key] = event.target.value;
                adsPage = 1;
                loadAds();
            });
        });

        document.getElementById('adsPerPageSelect')?.addEventListener('change', (event) => {
            adsPerPage = Number(event.target.value) || 10;
            adsPage = 1;
            loadAds();
        });

        document.getElementById('adRefuseCancel')?.addEventListener('click', closeAdRefuseModal);
        document.getElementById('adRefuseConfirm')?.addEventListener('click', submitAdRefuse);
        document.getElementById('adImageModalClose')?.addEventListener('click', closeAdImageModal);
        document.getElementById('adImageModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'adImageModal') closeAdImageModal();
        });
        document.getElementById('adRefuseModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'adRefuseModal') closeAdRefuseModal();
        });
    }

    async function toggleUserStatus(userId, userName, isActive) {
        const action = isActive ? 'désactiver' : 'activer';
        if (!await U.confirm(`${isActive ? 'Désactiver' : 'Activer'} « ${userName || 'cet utilisateur'} » ?`, {
            confirmText: isActive ? 'Désactiver' : 'Activer',
        })) {
            return;
        }

        try {
            U.showLoader('Mise à jour...');
            const data = await U.api(`${cfg.apiBase}/admin/users/toggle-status`, {
                method: 'POST',
                body: JSON.stringify({ user_id: Number(userId) }),
            });
            U.hideLoader();
            U.showToast(data.message || `Utilisateur ${action}`, 'success');
            await loadUsers();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function changeUserRole(select) {
        const userId = select.dataset.userId;
        const userName = select.dataset.userName || 'Utilisateur';
        const newRole = select.value;
        const previousRole = select.dataset.previousRole || newRole;
        const label = userRoleLabel(newRole);

        if (newRole === previousRole) return;

        if (!await U.confirm(`Attribuer le rôle « ${label} » à ${userName} ?`, { confirmText: 'Confirmer' })) {
            select.value = previousRole;
            return;
        }

        try {
            U.showLoader('Mise à jour du rôle...');
            const data = await U.api(`${cfg.apiBase}/admin/users/${userId}/role`, {
                method: 'PUT',
                body: JSON.stringify({ role: newRole }),
            });
            U.hideLoader();
            U.showToast(data.message || 'Rôle modifié', 'success');
            await loadUsers();
        } catch (error) {
            U.hideLoader();
            select.value = previousRole;
            U.showToast(error.message, 'error');
        }
    }

    function setupUserEditModal() {
        const modal = document.getElementById('userEditModal');
        const form = document.getElementById('userEditForm');
        if (!modal || !form || !cfg.isSuperAdmin) return;

        const close = () => {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };

        document.getElementById('userEditModalClose')?.addEventListener('click', close);
        document.getElementById('userEditCancel')?.addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            const cropperModal = document.getElementById('userAvatarCropperModal');
            if (cropperModal?.classList.contains('active')) {
                closeUserAvatarCropper();
                return;
            }
            if (modal.classList.contains('active')) close();
        });

        form.addEventListener('submit', submitUserEdit);
        setupUserAvatarCropper();
    }

    function updateUserEditAvatarPreview(coverPath) {
        const preview = document.getElementById('userEditAvatarPreview');
        if (preview) preview.src = U.mediaUrl(coverPath || null);
    }

    function setupUserAvatarCropper() {
        document.getElementById('userEditAvatarBtn')?.addEventListener('click', () => {
            document.getElementById('userEditAvatarInput')?.click();
        });

        document.getElementById('userEditAvatarInput')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                U.showToast('Veuillez sélectionner une image valide', 'warning');
                return;
            }

            userAvatarCropperFile = file;
            const reader = new FileReader();
            reader.onload = (loadEvent) => openUserAvatarCropper(loadEvent.target?.result);
            reader.readAsDataURL(file);
        });

        document.getElementById('userAvatarCropperClose')?.addEventListener('click', closeUserAvatarCropper);
        document.getElementById('userAvatarCropperCancel')?.addEventListener('click', closeUserAvatarCropper);
        document.getElementById('userAvatarCropperConfirm')?.addEventListener('click', applyUserAvatarCrop);

        document.getElementById('userAvatarCropperModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'userAvatarCropperModal') closeUserAvatarCropper();
        });
    }

    function openUserAvatarCropper(imageDataUrl) {
        const modal = document.getElementById('userAvatarCropperModal');
        const image = document.getElementById('userAvatarCropperImage');
        if (!modal || !image || typeof Cropper === 'undefined') {
            U.showToast('Recadrage indisponible', 'error');
            return;
        }

        if (userAvatarCropper) {
            userAvatarCropper.destroy();
            userAvatarCropper = null;
        }

        image.src = imageDataUrl;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        userAvatarCropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });

        U.refreshIcons(modal);
    }

    function closeUserAvatarCropper() {
        const modal = document.getElementById('userAvatarCropperModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');

        if (userAvatarCropper) {
            userAvatarCropper.destroy();
            userAvatarCropper = null;
        }

        userAvatarCropperFile = null;
    }

    async function uploadProfileBlob(blob) {
        const formData = new FormData();
        formData.append('file', blob, `profile-${Date.now()}.jpg`);
        formData.append('type', 'profile');

        const res = await fetch(`${cfg.apiBase}/upload`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': U.csrf,
            },
            body: formData,
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Erreur lors de l\'upload de la photo');
        }

        return data.url;
    }

    function applyUserAvatarCrop() {
        if (!userAvatarCropper || !userAvatarCropperFile) {
            U.showToast('Aucune image à traiter', 'warning');
            return;
        }

        const canvas = userAvatarCropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            U.showToast('Impossible de recadrer l\'image', 'error');
            return;
        }

        canvas.toBlob(async (blob) => {
            if (!blob) {
                U.showToast('Impossible de générer l\'image', 'error');
                return;
            }

            try {
                U.showLoader('Upload de la photo...');
                const url = await uploadProfileBlob(blob);
                userEditPendingCover = url;
                document.getElementById('userEditCover').value = url;
                updateUserEditAvatarPreview(url);
                closeUserAvatarCropper();
                U.hideLoader();
                U.showToast('Photo mise à jour — enregistrez pour confirmer', 'success');
            } catch (error) {
                U.hideLoader();
                U.showToast(error.message, 'error');
            }
        }, 'image/jpeg', 0.9);
    }

    async function openUserEditModal(userId) {
        const modal = document.getElementById('userEditModal');
        if (!modal || !cfg.isSuperAdmin) return;

        try {
            U.showLoader('Chargement...');
            const data = await U.api(`${cfg.apiBase}/admin/users/${userId}`);
            const user = data.user;
            U.hideLoader();

            if (!user) throw new Error('Utilisateur introuvable');

            const role = userRoleValue(user);
            const isTargetSuperAdmin = role === 'superadmin';
            const isSelf = Number(user.id) === Number(cfg.user?.id);

            document.getElementById('userEditModalTitle').textContent = `Modifier — ${user.nom || user.name || 'Utilisateur'}`;
            document.getElementById('userEditId').value = user.id;
            document.getElementById('userEditNom').value = user.nom || user.name || '';
            document.getElementById('userEditEmail').value = user.mail || user.email || '';
            document.getElementById('userEditPhone').value = user.telephone || user.phone || '';
            document.getElementById('userEditTitre').value = user.titre || user.Titre || user.job_title || '';
            document.getElementById('userEditBio').value = user.bio || '';
            document.getElementById('userEditFacebook').value = user.facebook || user.Facebook || '';
            document.getElementById('userEditYoutube').value = user.youtube || user.Youtube || '';
            document.getElementById('userEditTwitter').value = user.twitter || user.Twitter || '';
            document.getElementById('userEditInstagram').value = user.instagram || user.Instagram || '';

            userEditOriginalCover = user.cover || '';
            userEditPendingCover = null;
            document.getElementById('userEditCover').value = userEditOriginalCover;
            updateUserEditAvatarPreview(userEditOriginalCover);

            const roleSelect = document.getElementById('userEditRole');
            roleSelect.value = role;
            roleSelect.disabled = isTargetSuperAdmin || isSelf;

            const statusCheckbox = document.getElementById('userEditStatus');
            statusCheckbox.checked = Number(user.status ?? 0) === 1;
            statusCheckbox.disabled = isTargetSuperAdmin || isSelf;

            const hint = document.getElementById('userEditHint');
            if (hint) {
                if (isTargetSuperAdmin) {
                    hint.textContent = 'Compte super admin protégé : le rôle et la désactivation ne sont pas modifiables.';
                } else if (isSelf) {
                    hint.textContent = 'Vous modifiez votre propre compte : le rôle et la désactivation sont verrouillés.';
                } else {
                    hint.textContent = '';
                }
            }

            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            U.refreshIcons(modal);
            document.getElementById('userEditNom')?.focus();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function submitUserEdit(event) {
        event.preventDefault();
        const userId = document.getElementById('userEditId')?.value;
        if (!userId) return;

        const payload = {
            nom: document.getElementById('userEditNom')?.value.trim(),
            email: document.getElementById('userEditEmail')?.value.trim(),
            telephone: document.getElementById('userEditPhone')?.value.trim() || null,
            titre: document.getElementById('userEditTitre')?.value.trim() || null,
            bio: document.getElementById('userEditBio')?.value.trim() || null,
            facebook: document.getElementById('userEditFacebook')?.value.trim() || null,
            youtube: document.getElementById('userEditYoutube')?.value.trim() || null,
            twitter: document.getElementById('userEditTwitter')?.value.trim() || null,
            instagram: document.getElementById('userEditInstagram')?.value.trim() || null,
        };

        const roleSelect = document.getElementById('userEditRole');
        if (roleSelect && !roleSelect.disabled) {
            payload.role = roleSelect.value;
        }

        const statusCheckbox = document.getElementById('userEditStatus');
        if (statusCheckbox && !statusCheckbox.disabled) {
            payload.status = statusCheckbox.checked ? 1 : 0;
        }

        if (userEditPendingCover !== null && userEditPendingCover !== userEditOriginalCover) {
            payload.cover = userEditPendingCover;
        }

        try {
            U.showLoader('Enregistrement...');
            const data = await U.api(`${cfg.apiBase}/admin/users/${userId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
            U.hideLoader();
            U.showToast(data.message || 'Utilisateur mis à jour', 'success');

            document.getElementById('userEditModal')?.classList.remove('active');
            document.getElementById('userEditModal')?.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');

            await loadUsers();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function isVideoActive(video) {
        return video?.is_active === true || video?.is_active === 1 || video?.is_active === '1';
    }

    function updateHomeVideosCountLabel(videos) {
        const label = document.getElementById('homeVideosCountLabel');
        if (!label) return;
        const total = videos.length;
        const active = videos.filter((video) => isVideoActive(video)).length;
        if (!total) {
            label.textContent = '0 vidéo';
            label.className = 'pending-count-badge pending-count-badge--empty';
            return;
        }
        label.textContent = `${total} vidéo${total > 1 ? 's' : ''} · ${active} active${active > 1 ? 's' : ''}`;
        label.className = 'pending-count-badge';
    }

    function renderHomeVideoCard(video) {
        const active = isVideoActive(video);
        const website = video.website_url?.trim();
        const websiteBlock = website
            ? `<a href="${U.escapeHtml(website)}" class="home-video-card__link" target="_blank" rel="noopener noreferrer">${U.icon('external-link')} Site web</a>`
            : '';

        return `
            <article class="home-video-card">
                <div class="home-video-card__thumb">
                    <img src="https://img.youtube.com/vi/${U.escapeHtml(video.youtube_id)}/mqdefault.jpg" alt="${U.escapeHtml(video.title || 'Vidéo YouTube')}" loading="lazy">
                    <span class="home-video-card__badge ${active ? 'home-video-card__badge--active' : 'home-video-card__badge--inactive'}">${active ? 'Active' : 'Inactive'}</span>
                </div>
                <div class="home-video-card__body">
                    <h3 class="home-video-card__title">${U.escapeHtml(video.title || 'Sans titre')}</h3>
                    <p class="home-video-card__subtitle">${U.escapeHtml(video.subtitle || 'Aucun sous-titre')}</p>
                    ${websiteBlock}
                    <div class="home-video-card__footer">
                        <button type="button" class="home-video-toggle-btn ${active ? 'home-video-toggle-btn--hide' : 'home-video-toggle-btn--show'}" data-home-video-toggle="${video.id}" title="${active ? 'Masquer' : 'Publier'}">
                            ${U.icon(active ? 'eye-off' : 'eye')}
                            ${active ? 'Masquer' : 'Publier'}
                        </button>
                        <div class="home-video-card__actions">
                            <button type="button" class="ads-action-btn" data-home-video-edit="${video.id}" title="Modifier">${U.icon('pencil')}</button>
                            <button type="button" class="ads-action-btn ads-action-btn--danger" data-home-video-delete="${video.id}" data-home-video-title="${String(video.title || '').replace(/"/g, '&quot;')}" title="Supprimer">${U.icon('trash-2')}</button>
                        </div>
                    </div>
                </div>
            </article>
        `;
    }

    async function loadHomeVideos({ skipIfCached = false } = {}) {
        const grid = document.getElementById('homeVideosGrid');
        if (!grid) return;
        if (skipIfCached && isViewLoaded('home-video')) return;

        grid.innerHTML = U.loadingHtml();

        try {
            const data = await U.api(`${cfg.apiBase}/admin/home-videos`);
            const videos = data.videos || [];
            homeVideosCache = videos;
            updateHomeVideosCountLabel(videos);

            if (!videos.length) {
                grid.innerHTML = `
                    <div class="pending-empty home-videos-empty">
                        <div class="pending-empty__icon" style="background:#fef2f2;color:#E10600;">
                            ${U.icon('video-off')}
                        </div>
                        <h3>Aucune vidéo configurée</h3>
                        <p>Ajoutez une vidéo YouTube pour l'afficher sur la page d'accueil.</p>
                        <button type="button" class="btn btn-primary btn-sm" id="homeVideoEmptyAddBtn">
                            ${U.icon('plus')}
                            Ajouter une vidéo
                        </button>
                    </div>
                `;
                document.getElementById('homeVideoEmptyAddBtn')?.addEventListener('click', () => openHomeVideoModal());
                U.refreshIcons(grid);
                markViewLoaded('home-video');
                return;
            }

            grid.innerHTML = videos.map((video) => renderHomeVideoCard(video)).join('');
            bindHomeVideoActions(grid);
            U.refreshIcons(grid);
            markViewLoaded('home-video');
        } catch (error) {
            grid.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(grid);
        }
    }

    function bindHomeVideoActions(container) {
        container.querySelectorAll('[data-home-video-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => toggleHomeVideoStatus(Number(btn.dataset.homeVideoToggle)));
        });

        container.querySelectorAll('[data-home-video-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const video = homeVideosCache.find((item) => Number(item.id) === Number(btn.dataset.homeVideoEdit));
                if (video) openHomeVideoModal(video);
            });
        });

        container.querySelectorAll('[data-home-video-delete]').forEach((btn) => {
            btn.addEventListener('click', () => {
                deleteHomeVideo(Number(btn.dataset.homeVideoDelete), btn.dataset.homeVideoTitle || 'Vidéo');
            });
        });
    }

    function openHomeVideoModal(video = null) {
        const modal = document.getElementById('homeVideoModal');
        const form = document.getElementById('homeVideoForm');
        const titleEl = document.getElementById('homeVideoModalTitle');
        if (!modal || !form) return;

        form.reset();
        document.getElementById('homeVideoId').value = video?.id || '';
        document.getElementById('homeVideoYoutubeId').value = video?.youtube_id || '';
        document.getElementById('homeVideoTitle').value = video?.title || '';
        document.getElementById('homeVideoSubtitle').value = video?.subtitle || '';
        document.getElementById('homeVideoWebsiteUrl').value = video?.website_url || '';
        document.getElementById('homeVideoActive').checked = video ? isVideoActive(video) : true;

        if (titleEl) {
            titleEl.textContent = video ? 'Modifier la vidéo' : 'Ajouter une vidéo';
        }

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
        document.getElementById('homeVideoYoutubeId')?.focus();
    }

    function closeHomeVideoModal() {
        const modal = document.getElementById('homeVideoModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.getElementById('homeVideoForm')?.reset();
    }

    async function submitHomeVideoForm(event) {
        event.preventDefault();

        const id = document.getElementById('homeVideoId')?.value;
        const payload = {
            youtube_id: document.getElementById('homeVideoYoutubeId')?.value.trim(),
            title: document.getElementById('homeVideoTitle')?.value.trim(),
            subtitle: document.getElementById('homeVideoSubtitle')?.value.trim() || null,
            website_url: document.getElementById('homeVideoWebsiteUrl')?.value.trim() || null,
            is_active: document.getElementById('homeVideoActive')?.checked ?? false,
        };

        try {
            U.showLoader('Enregistrement…');
            const data = id
                ? await U.api(`${cfg.apiBase}/admin/home-videos/${id}`, { method: 'PUT', body: JSON.stringify(payload) })
                : await U.api(`${cfg.apiBase}/admin/home-videos`, { method: 'POST', body: JSON.stringify(payload) });
            U.hideLoader();
            U.showToast(data.message || 'Vidéo enregistrée', 'success');
            closeHomeVideoModal();
            await loadHomeVideos();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function toggleHomeVideoStatus(id) {
        try {
            const data = await U.api(`${cfg.apiBase}/admin/home-videos/${id}/toggle`, { method: 'POST' });
            U.showToast(data.message || 'Statut mis à jour', 'success');
            await loadHomeVideos();
        } catch (error) {
            U.showToast(error.message, 'error');
        }
    }

    async function deleteHomeVideo(id, title) {
        if (!await U.confirm(`Supprimer « ${title} » ?`, { confirmText: 'Supprimer' })) return;

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/admin/home-videos/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Vidéo supprimée', 'success');
            await loadHomeVideos();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupHomeVideoModal() {
        if (homeVideosReady) return;
        homeVideosReady = true;

        document.getElementById('homeVideoAddBtn')?.addEventListener('click', () => openHomeVideoModal());
        document.getElementById('homeVideoRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('home-video');
            loadHomeVideos();
        });
        document.getElementById('homeVideoModalClose')?.addEventListener('click', () => closeHomeVideoModal());
        document.getElementById('homeVideoCancel')?.addEventListener('click', () => closeHomeVideoModal());
        document.getElementById('homeVideoForm')?.addEventListener('submit', submitHomeVideoForm);

        document.getElementById('homeVideoModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'homeVideoModal') closeHomeVideoModal();
        });
    }

    function formatUsd(amount) {
        return Number(amount ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderAdsRateCard(rate) {
        return `
            <article class="ads-rate-card">
                <div class="ads-rate-card__head">
                    <h3>${U.escapeHtml(rate.label || rate.format)}</h3>
                    <span class="ads-rate-card__dimensions">${U.icon('maximize-2')}${U.escapeHtml(rate.dimensions || '—')}</span>
                </div>
                <ul class="ads-rate-card__prices">
                    <li><span>${U.icon('calendar')}7 jours</span><strong>$${formatUsd(rate.price_7_days)}</strong></li>
                    <li><span>${U.icon('calendar')}15 jours</span><strong>$${formatUsd(rate.price_15_days)}</strong></li>
                    <li><span>${U.icon('calendar')}30 jours</span><strong>$${formatUsd(rate.price_30_days)}</strong></li>
                </ul>
                <button type="button" class="btn btn-secondary btn-sm ads-rate-card__edit" data-ads-rate-edit="${rate.id}">
                    ${U.icon('pencil')}
                    Modifier
                </button>
            </article>
        `;
    }

    async function loadAdsPricing({ skipIfCached = false } = {}) {
        const grid = document.getElementById('adsPricingGrid');
        if (!grid) return;
        if (skipIfCached && isViewLoaded('ads-pricing')) return;

        if (!cfg.access?.adRatesEdit && !cfg.isSuperAdmin) {
            grid.innerHTML = `<div class="empty-state">${U.icon('lock')}<p>Accès réservé aux super administrateurs.</p></div>`;
            U.refreshIcons(grid);
            markViewLoaded('ads-pricing');
            return;
        }

        grid.innerHTML = U.loadingHtml();

        try {
            const data = await U.api(`${cfg.apiBase}/advertisement-rates`);
            const rates = data.rates || [];
            adsPricingCache = rates;

            if (!rates.length) {
                grid.innerHTML = `
                    <div class="pending-empty ads-rates-empty">
                        <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">
                            ${U.icon('tags')}
                        </div>
                        <h3>Aucun tarif configuré</h3>
                        <p>Les tarifs publicitaires apparaîtront ici une fois définis en base.</p>
                    </div>
                `;
                U.refreshIcons(grid);
                markViewLoaded('ads-pricing');
                return;
            }

            grid.innerHTML = rates.map((rate) => renderAdsRateCard(rate)).join('');
            grid.querySelectorAll('[data-ads-rate-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const rate = adsPricingCache.find((item) => Number(item.id) === Number(btn.dataset.adsRateEdit));
                    if (rate) openAdsPricingModal(rate);
                });
            });
            U.refreshIcons(grid);
            markViewLoaded('ads-pricing');
        } catch (error) {
            grid.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(grid);
        }
    }

    function openAdsPricingModal(rate) {
        const modal = document.getElementById('adsPricingModal');
        if (!modal || !rate) return;

        document.getElementById('adsPricingId').value = rate.id;
        document.getElementById('adsPricingFormat').value = rate.label || rate.format;
        document.getElementById('adsPricingDimensions').value = rate.dimensions || '';
        document.getElementById('adsPricing7').value = rate.price_7_days;
        document.getElementById('adsPricing15').value = rate.price_15_days;
        document.getElementById('adsPricing30').value = rate.price_30_days;

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
        document.getElementById('adsPricing7')?.focus();
    }

    function closeAdsPricingModal() {
        const modal = document.getElementById('adsPricingModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.getElementById('adsPricingForm')?.reset();
    }

    async function submitAdsPricingForm(event) {
        event.preventDefault();
        const id = document.getElementById('adsPricingId')?.value;
        if (!id) return;

        const payload = {
            price_7_days: Number(document.getElementById('adsPricing7')?.value),
            price_15_days: Number(document.getElementById('adsPricing15')?.value),
            price_30_days: Number(document.getElementById('adsPricing30')?.value),
        };

        try {
            U.showLoader('Enregistrement…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisement-rates/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
            U.hideLoader();
            U.showToast(data.message || 'Tarif enregistré', 'success');
            closeAdsPricingModal();
            await loadAdsPricing();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupAdsPricingModal() {
        if (adsPricingReady) return;
        adsPricingReady = true;

        document.getElementById('adsPricingRefreshBtn')?.addEventListener('click', () => {
            resetViewLoaded('ads-pricing');
            loadAdsPricing();
        });
        document.getElementById('adsPricingModalClose')?.addEventListener('click', () => closeAdsPricingModal());
        document.getElementById('adsPricingCancel')?.addEventListener('click', () => closeAdsPricingModal());
        document.getElementById('adsPricingForm')?.addEventListener('submit', submitAdsPricingForm);
        document.getElementById('adsPricingModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'adsPricingModal') closeAdsPricingModal();
        });
    }

    function isPlanActive(plan) {
        return plan?.is_active === true || plan?.is_active === 1 || plan?.is_active === '1';
    }

    function isSocialNetworkActive(row) {
        return Boolean(String(row?.url ?? '').trim());
    }

    function buildSocialMediaPayload() {
        const payload = {};

        Object.keys(socialCatalogCache).forEach((network) => {
            const row = socialMediaCache[network] || {};
            payload[network] = {
                url: String(row.url ?? '').trim(),
                title: String(row.title ?? '').trim(),
                count: String(row.count ?? '').trim(),
                count_label: String(row.count_label ?? '').trim(),
            };
        });

        return payload;
    }

    function renderSettingsSocialCard(network, meta, row, canEdit) {
        const active = isSocialNetworkActive(row);
        const url = String(row?.url ?? '').trim();
        const count = String(row?.count ?? '').trim();
        const countLabel = String(row?.count_label ?? meta?.default_count_label ?? '').trim();
        const counter = count ? `${count}${countLabel ? ` ${countLabel}` : ''}` : '—';
        const urlPreview = url || 'Aucune URL configurée';
        const actions = canEdit ? `
            <div class="settings-plan-card__actions">
                <button type="button" class="ads-action-btn" data-social-media-edit="${U.escapeHtml(network)}" title="Modifier">${U.icon('pencil')}</button>
            </div>
        ` : '';

        return `
            <article class="settings-plan-card ${active ? '' : 'settings-plan-card--inactive'}">
                <div class="settings-plan-card__top">
                    <div>
                        <div class="settings-plan-card__name">${U.escapeHtml(meta?.label || network)}</div>
                        <div class="settings-plan-card__price settings-social-card__counter">${U.escapeHtml(counter)}</div>
                        <div class="settings-plan-card__meta settings-social-card__url">${U.escapeHtml(urlPreview)}</div>
                    </div>
                    <span class="settings-plan-card__status">${active ? 'Actif' : 'Inactif'}</span>
                </div>
                ${actions}
            </article>
        `;
    }

    function renderSettingsPlanCard(plan, canEdit) {
        const active = isPlanActive(plan);
        const actions = canEdit ? `
            <div class="settings-plan-card__actions">
                <button type="button" class="ads-action-btn" data-subscription-plan-edit="${plan.id}" title="Modifier">${U.icon('pencil')}</button>
                <button type="button" class="ads-action-btn ads-action-btn--danger" data-subscription-plan-delete="${plan.id}" data-subscription-plan-name="${String(plan.name || '').replace(/"/g, '&quot;')}" title="Supprimer">${U.icon('trash-2')}</button>
            </div>
        ` : '';

        return `
            <article class="settings-plan-card ${active ? '' : 'settings-plan-card--inactive'}">
                <div class="settings-plan-card__top">
                    <div>
                        <div class="settings-plan-card__name">${U.escapeHtml(plan.name || 'Plan')}</div>
                        <div class="settings-plan-card__price">$${formatUsd(plan.price)}</div>
                        <div class="settings-plan-card__meta">${Number(plan.duration_days ?? 0)} jour${Number(plan.duration_days ?? 0) > 1 ? 's' : ''}</div>
                    </div>
                    <span class="settings-plan-card__status">${active ? 'Actif' : 'Inactif'}</span>
                </div>
                <p class="settings-plan-card__desc">${U.escapeHtml(plan.description || '—')}</p>
                ${actions}
            </article>
        `;
    }

    async function loadSettings({ skipIfCached = false } = {}) {
        const container = document.getElementById('settingsContent');
        if (!container) return;
        if (skipIfCached && isViewLoaded('settings')) return;

        container.innerHTML = U.loadingHtml();

        try {
            const settingsData = await U.api(`${cfg.apiBase}/admin/settings`);
            const settings = settingsData.settings || {};
            const defaultPrice = settings.default_article_price ?? '0.00';
            const breakingNewsEnabled = !['0', 'false', 'off', 'no'].includes(
                String(settings.breaking_news_enabled ?? '1').toLowerCase(),
            );
            const plans = settingsData.subscription_plans || [];
            const socialMedia = settingsData.social_media || {};
            const socialCatalog = settingsData.social_media_catalog || {};
            socialMediaCache = socialMedia;
            socialCatalogCache = socialCatalog;
            subscriptionPlansCache = plans;
            const canEdit = cfg.access?.settingsEdit ?? cfg.isSuperAdmin;
            const socialCards = Object.entries(socialCatalog)
                .map(([network, meta]) => renderSettingsSocialCard(network, meta, socialMedia[network] || {}, canEdit))
                .join('');

            container.innerHTML = `
                <section class="settings-card">
                    <div class="settings-card__head">
                        <div class="settings-card__icon settings-card__icon--red">${U.icon('dollar-sign')}</div>
                        <div>
                            <h3>Configuration globale</h3>
                            <p>Prix par défaut appliqué aux articles payants sans montant spécifié.</p>
                        </div>
                    </div>
                    <form id="settingsGlobalForm" class="settings-global-form">
                        <label class="settings-price-field" for="defaultArticlePrice">
                            <span>Prix par défaut d'un article ($)</span>
                            <div class="settings-price-input">
                                <span class="settings-price-input__prefix">$</span>
                                <input type="number" id="defaultArticlePrice" name="default_article_price" min="0" step="0.01" value="${U.escapeHtml(defaultPrice)}" ${canEdit ? '' : 'disabled'}>
                            </div>
                        </label>
                        ${canEdit ? `
                            <button type="submit" class="btn btn-primary btn-sm" id="settingsSaveBtn">
                                ${U.icon('save')}
                                Enregistrer
                            </button>
                        ` : `<p class="settings-readonly-hint">${U.icon('lock')} Modification réservée aux super administrateurs.</p>`}
                    </form>
                </section>

                <section class="settings-card">
                    <div class="settings-card__head">
                        <div class="settings-card__icon settings-card__icon--red">${U.icon('radio')}</div>
                        <div>
                            <h3>Affichage du site</h3>
                            <p>Éléments visibles sur le front public.</p>
                        </div>
                    </div>
                    <label class="toggle-switch settings-feature-toggle" for="breakingNewsEnabled">
                        <span class="settings-feature-toggle__copy">
                            <span class="toggle-text">Bandeau Breaking News</span>
                            <span class="settings-feature-toggle__hint">Affiche sous le menu les derniers titres d'articles en défilement.</span>
                        </span>
                        <input
                            type="checkbox"
                            id="breakingNewsEnabled"
                            name="breaking_news_enabled"
                            ${breakingNewsEnabled ? 'checked' : ''}
                            ${canEdit ? '' : 'disabled'}
                        >
                        <span class="toggle-slider" aria-hidden="true"></span>
                    </label>
                    ${canEdit ? '' : `<p class="settings-readonly-hint">${U.icon('lock')} Modification réservée aux super administrateurs.</p>`}
                </section>

                <section class="settings-card">
                    <div class="settings-card__head settings-card__head--split">
                        <div class="settings-card__head-main">
                            <div class="settings-card__icon settings-card__icon--blue">${U.icon('credit-card')}</div>
                            <div>
                                <h3>Plans d'abonnement</h3>
                                <p>Offres d'accès illimité proposées aux utilisateurs.</p>
                            </div>
                        </div>
                        ${canEdit ? `
                            <button type="button" class="btn btn-primary btn-sm" id="subscriptionPlanAddBtn">
                                ${U.icon('plus')}
                                Nouveau plan
                            </button>
                        ` : ''}
                    </div>
                    <div class="settings-plans-grid" id="settingsPlansGrid">
                        ${plans.length ? plans.map((plan) => renderSettingsPlanCard(plan, canEdit)).join('') : `
                            <div class="settings-plans-empty">
                                <p class="settings-empty">Aucun plan d'abonnement configuré.</p>
                                ${canEdit ? `<button type="button" class="btn btn-secondary btn-sm" id="subscriptionPlanEmptyAddBtn">${U.icon('plus')} Ajouter un plan</button>` : ''}
                            </div>
                        `}
                    </div>
                </section>

                <section class="settings-card">
                    <div class="settings-card__head">
                        <div class="settings-card__icon settings-card__icon--blue">${U.icon('share-2')}</div>
                        <div>
                            <h3>Réseaux sociaux</h3>
                            <p>Liens affichés dans l'en-tête, le pied de page, la sidebar mobile et les pages statiques.</p>
                        </div>
                    </div>
                    <div class="settings-plans-grid" id="settingsSocialGrid">
                        ${socialCards || `<p class="settings-empty">Aucun réseau social configuré.</p>`}
                    </div>
                    ${canEdit ? '' : `<p class="settings-readonly-hint">${U.icon('lock')} Modification réservée aux super administrateurs.</p>`}
                </section>
            `;

            bindSubscriptionPlanActions(container);
            bindSocialMediaActions(container);
            bindBreakingNewsToggle(container, canEdit);
            U.refreshIcons(container);
            markViewLoaded('settings');
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function bindBreakingNewsToggle(container, canEdit) {
        const input = container.querySelector('#breakingNewsEnabled');
        if (!input || !canEdit) return;

        input.addEventListener('change', async () => {
            const enabled = input.checked;
            const previous = !enabled;

            try {
                U.showLoader('Enregistrement…');
                const data = await U.api(`${cfg.apiBase}/admin/settings`, {
                    method: 'PUT',
                    body: JSON.stringify({ breaking_news_enabled: enabled }),
                });
                U.hideLoader();
                U.showToast(data.message || 'Paramètre enregistré', 'success');
            } catch (error) {
                input.checked = previous;
                U.hideLoader();
                U.showToast(error.message, 'error');
            }
        });
    }

    function bindSocialMediaActions(container) {
        container.querySelectorAll('[data-social-media-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openSocialMediaModal(btn.dataset.socialMediaEdit);
            });
        });
    }

    function openSocialMediaModal(network) {
        if (!cfg.isSuperAdmin || !network) return;

        const meta = socialCatalogCache[network];
        const row = socialMediaCache[network] || {};
        const modal = document.getElementById('socialMediaModal');
        const form = document.getElementById('socialMediaForm');
        const titleEl = document.getElementById('socialMediaModalTitle');
        if (!modal || !form || !meta) return;

        form.reset();
        document.getElementById('socialMediaNetwork').value = network;
        document.getElementById('socialMediaUrl').value = row.url ?? '';
        document.getElementById('socialMediaTitle').value = row.title ?? meta.label ?? '';
        document.getElementById('socialMediaCount').value = row.count ?? '';
        document.getElementById('socialMediaCountLabel').value = row.count_label ?? meta.default_count_label ?? '';

        if (titleEl) {
            titleEl.textContent = `Modifier ${meta.label || network}`;
        }

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
        document.getElementById('socialMediaUrl')?.focus();
    }

    function closeSocialMediaModal() {
        const modal = document.getElementById('socialMediaModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.getElementById('socialMediaForm')?.reset();
    }

    async function submitSocialMediaForm(event) {
        event.preventDefault();
        if (!cfg.isSuperAdmin) return;

        const network = document.getElementById('socialMediaNetwork')?.value;
        if (!network) return;

        socialMediaCache[network] = {
            url: document.getElementById('socialMediaUrl')?.value.trim() || '',
            title: document.getElementById('socialMediaTitle')?.value.trim() || '',
            count: document.getElementById('socialMediaCount')?.value.trim() || '',
            count_label: document.getElementById('socialMediaCountLabel')?.value.trim() || '',
        };

        try {
            U.showLoader('Enregistrement…');
            const data = await U.api(`${cfg.apiBase}/admin/settings`, {
                method: 'PUT',
                body: JSON.stringify({ social_media: buildSocialMediaPayload() }),
            });
            U.hideLoader();
            U.showToast(data.message || 'Réseau social enregistré', 'success');
            if (data.social_media) {
                socialMediaCache = data.social_media;
            }
            closeSocialMediaModal();
            await loadSettings();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupSocialMediaModal() {
        if (socialMediaModalReady) return;
        socialMediaModalReady = true;

        document.getElementById('socialMediaModalClose')?.addEventListener('click', () => closeSocialMediaModal());
        document.getElementById('socialMediaCancel')?.addEventListener('click', () => closeSocialMediaModal());
        document.getElementById('socialMediaForm')?.addEventListener('submit', submitSocialMediaForm);
        document.getElementById('socialMediaModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'socialMediaModal') closeSocialMediaModal();
        });
    }

    function bindSubscriptionPlanActions(container) {
        container.querySelector('#subscriptionPlanAddBtn')?.addEventListener('click', () => openSubscriptionPlanModal());
        container.querySelector('#subscriptionPlanEmptyAddBtn')?.addEventListener('click', () => openSubscriptionPlanModal());

        container.querySelectorAll('[data-subscription-plan-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const plan = subscriptionPlansCache.find((item) => Number(item.id) === Number(btn.dataset.subscriptionPlanEdit));
                if (plan) openSubscriptionPlanModal(plan);
            });
        });

        container.querySelectorAll('[data-subscription-plan-delete]').forEach((btn) => {
            btn.addEventListener('click', () => {
                deleteSubscriptionPlan(Number(btn.dataset.subscriptionPlanDelete), btn.dataset.subscriptionPlanName || 'Plan');
            });
        });
    }

    function openSubscriptionPlanModal(plan = null) {
        if (!cfg.isSuperAdmin) return;

        const modal = document.getElementById('subscriptionPlanModal');
        const form = document.getElementById('subscriptionPlanForm');
        const titleEl = document.getElementById('subscriptionPlanModalTitle');
        if (!modal || !form) return;

        form.reset();
        document.getElementById('subscriptionPlanId').value = plan?.id || '';
        document.getElementById('subscriptionPlanName').value = plan?.name || '';
        document.getElementById('subscriptionPlanDuration').value = plan?.duration_days ?? '';
        document.getElementById('subscriptionPlanPrice').value = plan?.price ?? '';
        document.getElementById('subscriptionPlanDescription').value = plan?.description || '';
        document.getElementById('subscriptionPlanActive').checked = plan ? isPlanActive(plan) : true;

        if (titleEl) {
            titleEl.textContent = plan ? 'Modifier le plan' : 'Ajouter un plan';
        }

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
        document.getElementById('subscriptionPlanName')?.focus();
    }

    function closeSubscriptionPlanModal() {
        const modal = document.getElementById('subscriptionPlanModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.getElementById('subscriptionPlanForm')?.reset();
    }

    async function submitSubscriptionPlanForm(event) {
        event.preventDefault();
        if (!cfg.isSuperAdmin) return;

        const id = document.getElementById('subscriptionPlanId')?.value;
        const payload = {
            name: document.getElementById('subscriptionPlanName')?.value.trim(),
            duration_days: Number(document.getElementById('subscriptionPlanDuration')?.value),
            price: Number(document.getElementById('subscriptionPlanPrice')?.value),
            description: document.getElementById('subscriptionPlanDescription')?.value.trim() || null,
            is_active: document.getElementById('subscriptionPlanActive')?.checked ?? false,
        };

        try {
            U.showLoader('Enregistrement…');
            const data = id
                ? await U.api(`${cfg.apiBase}/admin/subscription-plans/${id}`, { method: 'PUT', body: JSON.stringify(payload) })
                : await U.api(`${cfg.apiBase}/admin/subscription-plans`, { method: 'POST', body: JSON.stringify(payload) });
            U.hideLoader();
            U.showToast(data.message || 'Plan enregistré', 'success');
            closeSubscriptionPlanModal();
            await loadSettings();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function deleteSubscriptionPlan(id, name) {
        if (!cfg.isSuperAdmin) return;
        if (!await U.confirm(`Supprimer le plan « ${name} » ?`, { confirmText: 'Supprimer' })) return;

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/admin/subscription-plans/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Plan supprimé', 'success');
            await loadSettings();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupSubscriptionPlanModal() {
        if (subscriptionPlanModalReady) return;
        subscriptionPlanModalReady = true;

        document.getElementById('subscriptionPlanModalClose')?.addEventListener('click', () => closeSubscriptionPlanModal());
        document.getElementById('subscriptionPlanCancel')?.addEventListener('click', () => closeSubscriptionPlanModal());
        document.getElementById('subscriptionPlanForm')?.addEventListener('submit', submitSubscriptionPlanForm);
        document.getElementById('subscriptionPlanModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'subscriptionPlanModal') closeSubscriptionPlanModal();
        });
    }

    async function submitSettingsForm(event) {
        event.preventDefault();
        if (!cfg.isSuperAdmin) return;

        const price = document.getElementById('defaultArticlePrice')?.value;
        if (price === undefined || price === '') return;

        try {
            U.showLoader('Enregistrement…');
            const data = await U.api(`${cfg.apiBase}/admin/settings`, {
                method: 'PUT',
                body: JSON.stringify({ default_article_price: Number(price) }),
            });
            U.hideLoader();
            U.showToast(data.message || 'Paramètres enregistrés', 'success');
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupSettingsForm() {
        if (settingsReady) return;
        settingsReady = true;

        document.getElementById('settingsContent')?.addEventListener('submit', (event) => {
            if (event.target.id === 'settingsGlobalForm') {
                submitSettingsForm(event);
            }
        });
    }
})();
