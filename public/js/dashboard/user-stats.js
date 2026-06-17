(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const access = cfg.access || {};

    if (!access.stats) return;

    const CHART_COLORS = {
        red: '#E10600',
        blue: '#1E5EFF',
        green: '#10b981',
        orange: '#f59e0b',
        slate: '#393e41',
        indigo: '#6366f1',
    };

    const chartInstances = {};
    let statsLoaded = false;

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('userStatsRefreshBtn')?.addEventListener('click', () => loadUserStats({ force: true }));

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'stats') loadUserStats({ skipIfCached: true });
        });
    });

    async function loadUserStats({ skipIfCached = false, force = false } = {}) {
        const container = document.getElementById('statsContent');
        if (!container) return;
        if (skipIfCached && statsLoaded && !force) return;

        container.innerHTML = U.loadingHtml();

        try {
            const data = await U.api(`${cfg.apiBase}/stats`);
            const stats = data.stats || {};
            renderUserStats(container, stats);
            statsLoaded = true;
        } catch (error) {
            container.innerHTML = `
                <div class="empty-state">
                    ${U.icon('circle-alert')}
                    <p>${U.escapeHtml(error.message)}</p>
                </div>
            `;
            U.refreshIcons(container);
        }
    }

    function renderUserStats(container, stats) {
        const articles = stats.articles || {};
        const ads = stats.ads || {};
        const showAds = access.ownAds;

        container.innerHTML = `
            <section class="author-stats-section" aria-labelledby="authorStatsArticlesTitle">
                <h3 class="author-stats-section__title" id="authorStatsArticlesTitle">Articles</h3>
                <div class="stats-grid author-stats-grid">
                    ${renderStatCard('stat-card-blue', 'newspaper', 'Total Articles', articles.total ?? 0)}
                    ${renderStatCard('stat-card-green', 'circle-check', 'Articles Publiés', articles.published ?? 0)}
                    ${renderStatCard('stat-card-orange', 'clock', 'En Attente', articles.pending ?? 0)}
                    ${renderStatCard('stat-card-purple', 'eye', 'Total Vues', formatNumber(articles.views ?? 0))}
                </div>
            </section>

            ${showAds ? `
                <section class="author-stats-section" aria-labelledby="authorStatsAdsTitle">
                    <h3 class="author-stats-section__title" id="authorStatsAdsTitle">Publicité</h3>
                    <div class="stats-grid author-stats-grid">
                        ${renderStatCard('stat-card-blue', 'megaphone', 'Total Publicités', ads.total ?? 0)}
                        ${renderStatCard('stat-card-orange', 'clock', 'En Attente', ads.pending ?? 0)}
                        ${renderStatCard('stat-card-green', 'radio', 'Actives', ads.active ?? 0)}
                        ${renderStatCard('stat-card-purple', 'eye', 'Total Vues', formatNumber(ads.views ?? 0))}
                    </div>
                </section>
            ` : ''}

            <div class="stats-charts-row">
                <div class="stats-chart-card">
                    <div class="stats-chart-card__head">
                        <div>
                            <h4>Statistiques des Articles</h4>
                            <p class="stats-chart-card__subtitle">Évolution des vues et répartition du catalogue</p>
                        </div>
                        <span class="stats-chart-kpi" id="authorArticlesChartKpi">—</span>
                    </div>
                    <div class="stats-chart-card__body">
                        <div class="stats-chart-canvas stats-chart-canvas--line">
                            <canvas id="authorArticlesTrendChart"></canvas>
                        </div>
                        <div class="stats-chart-canvas stats-chart-canvas--donut">
                            <canvas id="authorArticlesBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>
                ${showAds ? `
                    <div class="stats-chart-card">
                        <div class="stats-chart-card__head">
                            <div>
                                <h4>Statistiques des Publicités</h4>
                                <p class="stats-chart-card__subtitle">Impressions mensuelles et statut des campagnes</p>
                            </div>
                            <span class="stats-chart-kpi" id="authorAdsChartKpi">—</span>
                        </div>
                        <div class="stats-chart-card__body">
                            <div class="stats-chart-canvas stats-chart-canvas--line">
                                <canvas id="authorAdsTrendChart"></canvas>
                            </div>
                            <div class="stats-chart-canvas stats-chart-canvas--donut">
                                <canvas id="authorAdsBreakdownChart"></canvas>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        U.refreshIcons(container);
        initCharts(articles, showAds ? ads : null);
    }

    function renderStatCard(color, icon, label, value) {
        return `
            <div class="stat-card ${color}">
                <div class="stat-icon">${U.icon(icon)}</div>
                <div class="stat-content">
                    <div class="stat-label">${label}</div>
                    <div class="stat-value">${value}</div>
                </div>
            </div>
        `;
    }

    function initCharts(articles, ads) {
        if (typeof Chart === 'undefined') return;

        const articlesKpi = document.getElementById('authorArticlesChartKpi');
        if (articlesKpi) {
            articlesKpi.textContent = `${formatNumber(articles.views ?? 0)} vues`;
        }

        const monthLabels = last6MonthLabels();
        const viewsHistory = normalizeHistory(articles.views_history, articles.views ?? 0);

        const published = Number(articles.published ?? 0);
        const pending = Number(articles.pending ?? 0);
        const totalArticles = Number(articles.total ?? 0);
        const otherArticles = Math.max(0, totalArticles - published - pending);

        renderLineChart('authorArticlesTrend', 'authorArticlesTrendChart', {
            label: 'Vues articles',
            data: viewsHistory,
            labels: monthLabels,
            color: CHART_COLORS.blue,
            fillColor: 'rgba(30, 94, 255, 0.12)',
        });

        renderDoughnutChart('authorArticlesBreakdown', 'authorArticlesBreakdownChart', {
            labels: ['Publiés', 'En attente', 'Autres'],
            data: [published, pending, otherArticles],
            colors: [CHART_COLORS.green, CHART_COLORS.orange, CHART_COLORS.slate],
        });

        if (!ads) return;

        const adsKpi = document.getElementById('authorAdsChartKpi');
        if (adsKpi) {
            adsKpi.textContent = `${formatNumber(ads.views ?? 0)} vues`;
        }

        const adsHistory = normalizeHistory(ads.views_history, ads.views ?? 0);
        const adsActive = Number(ads.active ?? 0);
        const adsPending = Number(ads.pending ?? 0);
        const totalAds = Number(ads.total ?? 0);
        const adsOther = Math.max(0, totalAds - adsActive - adsPending);

        renderLineChart('authorAdsTrend', 'authorAdsTrendChart', {
            label: 'Vues publicités',
            data: adsHistory,
            labels: monthLabels,
            color: CHART_COLORS.red,
            fillColor: 'rgba(225, 6, 0, 0.1)',
        });

        renderDoughnutChart('authorAdsBreakdown', 'authorAdsBreakdownChart', {
            labels: ['Actives', 'En attente', 'Autres'],
            data: [adsActive, adsPending, adsOther],
            colors: [CHART_COLORS.green, CHART_COLORS.orange, CHART_COLORS.indigo],
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

    window.DashboardUserStats = { load: loadUserStats };
})();
