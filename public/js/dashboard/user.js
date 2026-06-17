(() => {
    const U = window.DashboardUtils;
    const cfg = U.cfg;
    const access = cfg.access || {};

    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('dashboard:view', (e) => {
            const view = e.detail.view;
            if (view === 'stats') window.DashboardUserStats?.load({ skipIfCached: true });
            if (view === 'validation') window.DashboardUserPending?.load({ skipIfCached: true });
            if (view === 'articles') window.DashboardUserArticles?.load({ skipIfCached: true });
            if (view === 'create') window.location.href = '/dashboard/publish';
            if (view === 'payments') window.DashboardUserPayments?.load({ skipIfCached: true });
            if (view === 'ads') window.DashboardUserAds?.load({ skipIfCached: true });
            if (view === 'ads-pricing') window.DashboardUserAdsPricing?.load({ skipIfCached: true });
            if (view === 'subscriptions') window.DashboardUserSubscriptions?.load({ skipIfCached: true });
        });

        if (access.pendingOwn) loadPendingCount();
        U.restoreView(access.stats ? 'stats' : 'articles');
    });

    async function loadPendingCount() {
        try {
            const data = await U.api(`${cfg.apiBase}/articles/pending-count`);
            const badge = document.getElementById('pendingBadge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
            }
        } catch (error) {
            console.error(error);
        }
    }

})();
