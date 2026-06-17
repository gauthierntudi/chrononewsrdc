(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const access = cfg.access || {};

    let pendingArticles = [];
    let pendingCategory = '';
    let pendingLoaded = false;
    let currentPreviewArticleId = null;
    let previewReady = false;
    let pendingActionsReady = false;

    document.addEventListener('DOMContentLoaded', () => {
        setupArticlePreviewModal();
        setupPendingTableActions();

        document.getElementById('pendingRefreshBtn')?.addEventListener('click', () => {
            loadUserPending({ force: true });
        });

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'validation' && access.pendingOwn) {
                loadUserPending({ skipIfCached: true });
            }
        });

        if (access.pendingOwn) {
            const urlView = new URLSearchParams(window.location.search).get('view');
            const savedView = localStorage.getItem('activeView');
            if ((urlView || savedView) === 'validation') {
                loadUserPending({ skipIfCached: true });
            }
        }
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

            const editBtn = event.target.closest('.btn-pending-edit');
            if (editBtn?.dataset?.id) {
                event.preventDefault();
                window.location.href = `/dashboard/publish?id=${editBtn.dataset.id}`;
            }
        });
    }

    function isPendingArticle(article) {
        const status = article.validation_status?.value ?? article.validation_status ?? article.statut_validation;
        return status === 'en_attente' || status === 'pending';
    }

    async function loadUserPending({ skipIfCached = false, force = false } = {}) {
        if (!access.pendingOwn) return;

        const table = document.getElementById('pendingArticlesTable');
        if (!table) return;
        if (skipIfCached && pendingLoaded && !force) return;

        table.innerHTML = U.loadingHtml();
        document.getElementById('pendingCategoriesContainer')?.replaceChildren();

        try {
            const data = await U.api(`${cfg.apiBase}/articles`);
            pendingArticles = U.extractArticlesList(data).filter(isPendingArticle);
            pendingCategory = '';
            updatePendingCountLabel(pendingArticles.length);
            syncPendingSidebarBadge(pendingArticles.length);
            renderPendingCategoryFilters();
            renderPendingTable();
            pendingLoaded = true;
        } catch (error) {
            pendingArticles = [];
            pendingCategory = '';
            document.getElementById('pendingCategoriesContainer')?.replaceChildren();
            table.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(table);
        }
    }

    function articleTitle(article) {
        return article.title || article.titre || 'Sans titre';
    }

    function articleCategory(article) {
        return U.normalizeCategory(article.category || article.categorie || '—');
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

    function validationBadge(article) {
        const status = article.validation_status?.value ?? article.validation_status ?? article.statut_validation;
        const formatted = U.formatValidation(status);
        return `<span class="status-badge ${formatted.class}">${formatted.label}</span>`;
    }

    function paymentBadge(article) {
        const status = article.payment_status?.value ?? article.payment_status ?? article.statut_paiement;
        const formatted = U.formatPayment(status);
        return `<span class="status-badge ${formatted.class}">${formatted.label}</span>`;
    }

    function renderPendingAuthor(article) {
        const name = article.author?.nom || article.auteur_nom || cfg.user?.nom || '—';
        const email = article.author?.mail || article.auteur_email || cfg.user?.mail || '';
        const avatar = article.author?.cover || article.auteur_cover || cfg.user?.cover;
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
        const title = articleTitle(article);
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
                        <button type="button" class="action-btn-modern edit btn-pending-edit" data-id="${article.id}" title="Modifier">
                            ${U.icon('pencil')}
                        </button>
                        <button type="button" class="action-btn-modern validate btn-article-view" data-id="${article.id}" title="Voir">
                            ${U.icon('eye')}
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncPendingSidebarBadge(count) {
        const badge = document.getElementById('pendingBadge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-flex';
            return;
        }

        badge.style.display = 'none';
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

    function setupArticlePreviewModal() {
        if (previewReady) return;
        previewReady = true;

        const modal = document.getElementById('articlePreviewModal');
        if (!modal) return;

        const close = () => closeArticleModal();

        document.getElementById('articlePreviewModalClose')?.addEventListener('click', close);
        document.getElementById('articlePreviewModalCloseBtn')?.addEventListener('click', close);
        document.getElementById('articlePreviewModalEditBtn')?.addEventListener('click', () => {
            if (currentPreviewArticleId) {
                window.location.href = `/dashboard/publish?id=${currentPreviewArticleId}`;
            }
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });

        modal.querySelector('.modal-content')?.addEventListener('click', (event) => {
            event.stopPropagation();
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
            const articleData = await U.api(`${cfg.apiBase}/articles/${id}`);
            const article = extractArticleFromResponse(articleData);
            if (!article) {
                throw new Error('Article introuvable ou réponse invalide');
            }

            let blocks = [];
            try {
                const blocksData = await U.api(`${cfg.apiBase}/articles/${id}/blocks`);
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
        const authorName = article.author?.nom || article.auteur_nom || cfg.user?.nom || '—';
        const authorEmail = article.author?.mail || article.auteur_email || cfg.user?.mail || '';
        const authorAvatar = article.author?.cover || article.auteur_cover || cfg.user?.cover;
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

    window.DashboardUserPending = {
        load: loadUserPending,
        showPreview: showArticleModal,
    };
})();
