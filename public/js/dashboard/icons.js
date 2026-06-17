(() => {
    const U = window.DashboardUtils;
    if (!U) return;

    U.icon = (name, className = '') =>
        `<i data-lucide="${name}" class="lucide-icon${className ? ` ${className}` : ''}" aria-hidden="true"></i>`;

    U.loadingHtml = (text = 'Chargement...') =>
        `<div class="loading">${U.icon('loader-circle', 'lucide-spin')} ${text}</div>`;

    U.refreshIcons = (root) => {
        if (!window.lucide?.createIcons) return;

        window.lucide.createIcons({
            attrs: {
                'stroke-width': 2,
                'aria-hidden': 'true',
            },
            nameAttr: 'data-lucide',
            ...(root ? { root } : {}),
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        U.refreshIcons();
    });
})();
