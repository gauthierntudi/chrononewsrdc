(() => {
    const cfg = window.CHRONONEWS_DASHBOARD || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    window.DashboardUtils = {
        cfg,
        csrf,

        async api(url, options = {}) {
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(options.method && options.method !== 'GET' ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
                ...options,
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const firstError = data.errors
                    ? Object.values(data.errors).flat().find(Boolean)
                    : null;
                throw new Error(firstError || data.message || `Erreur ${res.status}`);
            }

            return data;
        },

        showToast(message, type = 'info') {
            const text = message != null && String(message).trim() !== ''
                ? String(message)
                : 'Notification';

            if (typeof iziToast === 'undefined') {
                void this.alert(text);
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
                void this.alert(text);
            }
        },

        showLoader(text = 'Chargement...') {
            const loader = document.getElementById('viewLoader');
            const label = loader?.querySelector('.view-loader-text');
            if (label) label.textContent = text;
            loader?.classList.add('active');
        },

        hideLoader() {
            document.getElementById('viewLoader')?.classList.remove('active');
        },

        showView(viewId) {
            document.querySelectorAll('.view').forEach((el) => el.classList.remove('active'));
            const view = document.getElementById(`${viewId}-view`);
            if (view) view.classList.add('active');

            document.querySelectorAll('.sidebar-menu a[data-view]').forEach((link) => {
                link.classList.toggle('active', link.dataset.view === viewId);
            });

            document.querySelectorAll('.sidebar-menu a[data-sidebar="publish"]').forEach((link) => {
                link.classList.remove('active');
            });

            localStorage.setItem('activeView', viewId);

            if (viewId) {
                const url = new URL(window.location.href);
                url.searchParams.set('view', viewId);
                window.history.replaceState({ view: viewId }, '', url);
            }

            window.dispatchEvent(new CustomEvent('dashboard:view', { detail: { view: viewId } }));
        },

        redirectToDashboardView(viewId) {
            const base = cfg.isAdmin
                ? (cfg.adminDashboardUrl || '/dashboard/admin')
                : (cfg.dashboardUrl || '/dashboard');
            window.location.href = `${base}?view=${encodeURIComponent(viewId)}`;
        },

        setupNavigation() {
            document.querySelectorAll('.sidebar-menu a[data-view]').forEach((link) => {
                link.addEventListener('click', (e) => {
                    const view = link.dataset.view;
                    if (document.getElementById(`${view}-view`)) {
                        e.preventDefault();
                        this.showView(view);
                        return;
                    }
                    const href = link.getAttribute('href');
                    if (!href || href === '#') {
                        e.preventDefault();
                        this.redirectToDashboardView(view);
                    }
                });
            });

            document.querySelectorAll('[data-view-link]').forEach((el) => {
                el.addEventListener('click', (e) => {
                    const view = el.dataset.viewLink;
                    if (document.getElementById(`${view}-view`)) {
                        e.preventDefault();
                        this.showView(view);
                    } else {
                        e.preventDefault();
                        this.redirectToDashboardView(view);
                    }
                });
            });
        },

        setupSidebarToggle() {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            if (!toggle || !sidebar) return;

            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            });
        },

        setupLogout() {
            const link = document.getElementById('logoutLink');
            if (!link) return;

            link.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    await this.api('/auth/logout', { method: 'POST' });
                } catch (error) {
                    console.error(error);
                }
                window.location.href = cfg.loginUrl || '/connexion';
            });
        },

        restoreView(defaultView = 'articles') {
            const urlView = new URLSearchParams(window.location.search).get('view');
            const saved = localStorage.getItem('activeView');
            this.showView(urlView || saved || defaultView);
        },

        comingSoon(title) {
            const hammer = typeof this.icon === 'function' ? this.icon('hammer') : '';
            return `
                <div class="coming-soon-banner">
                    ${hammer}
                    <h3 style="margin:0 0 .5rem;font-family:var(--cn-font-display);">${title}</h3>
                    <p style="margin:0;">Cette section sera disponible prochainement (Phase 3 — paiements & modules avancés).</p>
                </div>
            `;
        },

        formatValidation(status) {
            const map = {
                pending: { label: 'En attente', class: 'badge-warning' },
                approved: { label: 'Validé', class: 'badge-success' },
                rejected: { label: 'Rejeté', class: 'badge-danger' },
                en_attente: { label: 'En attente', class: 'badge-warning' },
                valide: { label: 'Validé', class: 'badge-success' },
                rejete: { label: 'Rejeté', class: 'badge-danger' },
            };
            return map[status] || { label: status || '—', class: 'badge-secondary' };
        },

        formatPayment(status) {
            const map = {
                pending: { label: 'Paiement requis', class: 'badge-warning' },
                paid: { label: 'Payé', class: 'badge-success' },
                free: { label: 'Gratuit', class: 'badge-info' },
                en_attente: { label: 'Paiement requis', class: 'badge-warning' },
                paye: { label: 'Payé', class: 'badge-success' },
                gratuit: { label: 'Gratuit', class: 'badge-info' },
            };
            return map[status] || { label: status || '—', class: 'badge-secondary' };
        },

        formatTransactionStatus(status) {
            const map = {
                reussi: { label: 'Réussi', class: 'badge-success' },
                succeeded: { label: 'Réussi', class: 'badge-success' },
                en_attente: { label: 'En attente', class: 'badge-warning' },
                pending: { label: 'En attente', class: 'badge-warning' },
                echoue: { label: 'Échoué', class: 'badge-danger' },
                failed: { label: 'Échoué', class: 'badge-danger' },
            };
            return map[status] || { label: status || '—', class: 'badge-secondary' };
        },

        escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        formatArticleContent(content) {
            const raw = String(content ?? '');
            if (!raw.trim()) return '';

            if (/<[a-z][\s\S]*>/i.test(raw)) {
                return raw;
            }

            return this.escapeHtml(raw).replace(/\r\n|\r|\n/g, '<br>');
        },

        normalizeCategory(category) {
            const cat = String(category ?? '').trim();
            if (!cat || cat === '—') return '—';

            const legacy = {
                Interview: 'Interviews',
                Economie: 'Économie',
                "Int'l": 'International',
                'Int\u2019l': 'International',
                Portrait: 'Société',
                Entreprise: 'Institutions',
                Events: 'Société',
            };

            return legacy[cat] || cat;
        },

        getCategoryColorClass(category) {
            const normalized = this.normalizeCategory(category);
            const map = {
                'Actualités': 'color-1',
                'Institutions': 'color-2',
                'Politique': 'color-3',
                'Économie': 'color-4',
                'Justice & Sécurité': 'color-5',
                'Développement & Infrastructures': 'color-6',
                'Société': 'color-7',
                'International': 'color-8',
                'Sport': 'color-9',
                'Interviews': 'color-10',
                'Opinions': 'color-11',
            };
            return map[normalized] || '';
        },

        getInitials(name) {
            if (!name) return '??';
            const parts = String(name).trim().split(/\s+/);
            if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
            if (parts[0]?.length >= 2) return parts[0].substring(0, 2).toUpperCase();
            return parts[0]?.[0]?.toUpperCase() || '??';
        },

        formatDateShort(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) return '—';
            return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        mediaUrl(path) {
            if (!path || !String(path).trim()) return cfg.defaultAvatar || '/assets/img/user.jpg';
            if (path.startsWith('http://') || path.startsWith('https://')) return path;

            let relative = String(path).trim().replace(/^\/+/, '');
            if (relative.startsWith('publication/')) {
                relative = relative.slice('publication/'.length);
            }

            const base = String(cfg.mediaBaseUrl || '').replace(/\/$/, '');
            if (base) {
                return `${base}/${relative}`;
            }

            // Fallback : /uploads/* est redirigé vers S3 en production (MediaRedirectController)
            return `/${relative}`;
        },

        extractArticlesList(data) {
            if (!data || typeof data !== 'object') return [];

            const candidates = [
                data.articles,
                data.data?.articles,
                data.data,
            ];

            for (const candidate of candidates) {
                if (Array.isArray(candidate)) return candidate;
                if (candidate && Array.isArray(candidate.data)) return candidate.data;
            }

            return [];
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        DashboardUtils.setupNavigation();
        DashboardUtils.setupSidebarToggle();
        DashboardUtils.setupLogout();

        window.addEventListener('popstate', () => {
            const view = new URLSearchParams(window.location.search).get('view')
                || localStorage.getItem('activeView')
                || (DashboardUtils.cfg.isAdmin ? 'stats' : 'articles');
            DashboardUtils.showView(view);
        });
    });
})();
