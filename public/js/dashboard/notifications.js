(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || window.CHRONONEWS_DASHBOARD || {};

    class NotificationsManager {
        constructor() {
            this.dropdown = document.getElementById('notificationsDropdown');
            this.notificationsList = document.getElementById('notificationsList');
            this.badge = document.getElementById('notificationBadge');
            this.isOpen = false;
            this.init();
        }

        init() {
            const btn = document.getElementById('notificationsBtn');
            if (!btn || !this.dropdown) return;

            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.toggle();
            });

            btn.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.toggle();
                }
            });

            document.addEventListener('click', (event) => {
                if (this.isOpen && !btn.contains(event.target)) {
                    this.close();
                }
            });

            document.getElementById('notificationsViewAllBtn')?.addEventListener('click', () => {
                this.viewAll();
            });

            this.loadNotifications();
            setInterval(() => this.loadNotifications(), 30000);
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        open() {
            this.dropdown?.classList.add('active');
            this.isOpen = true;
            this.loadNotifications();
        }

        close() {
            this.dropdown?.classList.remove('active');
            this.isOpen = false;
        }

        async loadNotifications() {
            if (!U?.api) return;

            try {
                const data = await U.api(`${cfg.apiBase}/notifications`);
                const articles = data.articles || [];
                this.updateBadge(articles.length);
                this.renderNotifications(articles.slice(0, 6));
            } catch (error) {
                console.error('Notifications:', error);
            }
        }

        updateBadge(count) {
            if (!this.badge) return;

            if (count > 0) {
                this.badge.textContent = count > 99 ? '99+' : String(count);
                this.badge.style.display = 'block';
            } else {
                this.badge.style.display = 'none';
            }
        }

        renderNotifications(articles) {
            if (!this.notificationsList) return;

            if (!articles.length) {
                this.notificationsList.innerHTML = `
                    <div class="notifications-empty">
                        ${U.icon('bell-off')}
                        <p>Aucune notification</p>
                    </div>
                `;
                U.refreshIcons(this.notificationsList);
                return;
            }

            this.notificationsList.innerHTML = articles.map((article) => {
                const status = article.validation_status?.value ?? article.validation_status;
                const isUnread = ['pending', 'en_attente'].includes(status);
                const title = article.title || article.titre || 'Sans titre';
                const date = article.created_at || article.date_add || article.dateAdd;

                return `
                    <div class="notification-item ${isUnread ? 'unread' : ''}" data-article-id="${article.id}">
                        <img src="${this.getArticleCover(article)}" alt="" class="notification-cover">
                        <div class="notification-content">
                            <div class="notification-title">${U.escapeHtml(title)}</div>
                            <div class="notification-time">${this.getRelativeTime(date)}</div>
                        </div>
                    </div>
                `;
            }).join('');

            this.notificationsList.querySelectorAll('.notification-item').forEach((item) => {
                item.addEventListener('click', () => this.viewArticle());
            });

            U.refreshIcons(this.notificationsList);
        }

        getArticleCover(article) {
            const cover = article.cover;
            if (cover && String(cover).trim() !== '') {
                if (cover.startsWith('http://') || cover.startsWith('https://')) {
                    return cover;
                }
                return cover.startsWith('/') ? cover : `/${cover}`;
            }

            return cfg.defaultAvatar || '/assets/img/user.jpg';
        }

        getRelativeTime(dateString) {
            if (!dateString) return '—';

            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) return '—';

            const diffSeconds = Math.floor((Date.now() - date.getTime()) / 1000);
            if (diffSeconds < 60) return 'À l\'instant';

            const diffMinutes = Math.floor(diffSeconds / 60);
            if (diffMinutes < 60) {
                return `Il y a ${diffMinutes} min`;
            }

            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) {
                return `Il y a ${diffHours} h`;
            }

            const diffDays = Math.floor(diffHours / 24);
            if (diffDays === 1) return 'Hier';
            if (diffDays < 30) return `Il y a ${diffDays} j`;

            return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
        }

        viewArticle() {
            this.close();
            U.showView(cfg.isAdmin ? 'validation' : 'articles');
        }

        viewAll() {
            this.close();
            U.showView(cfg.isAdmin ? 'validation' : 'articles');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('notificationsBtn')) {
            window.notificationsManager = new NotificationsManager();
        }
    });
})();
