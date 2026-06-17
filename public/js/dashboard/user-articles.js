(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};

    let allArticles = [];
    let articlesCategory = '';
    let articlesSearch = '';
    let articlesPage = 1;
    let articlesPerPage = 12;
    let articlesViewMode = 'grid';
    let articlesFiltersReady = false;
    let articlesSearchTimeout = null;
    let selectedArticles = new Set();
    let articlesLoaded = false;

    document.addEventListener('DOMContentLoaded', () => {
        setupUserArticlesFilters();

        document.getElementById('userArticlesRefreshBtn')?.addEventListener('click', () => {
            loadUserArticles({ force: true });
        });

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'articles') loadUserArticles({ skipIfCached: true });
        });
    });

    async function loadUserArticles({ skipIfCached = false, force = false } = {}) {
        const container = document.getElementById('userArticlesPanel');
        if (!container) return;
        if (skipIfCached && articlesLoaded && !force) return;

        container.innerHTML = U.loadingHtml();
        selectedArticles.clear();
        updateSelectionBar();

        try {
            document.getElementById('userArticlesCategoriesContainer')?.replaceChildren();
            document.getElementById('userArticlesPagination')?.replaceChildren();

            const data = await U.api(`${cfg.apiBase}/articles`);
            allArticles = U.extractArticlesList(data);

            articlesCategory = '';
            articlesSearch = '';
            articlesPage = 1;
            const searchInput = document.getElementById('userArticlesSearchInput');
            if (searchInput) searchInput.value = '';

            updateArticlesCountLabel(allArticles.length);
            renderCategoryFilters();

            if (!allArticles.length) {
                container.innerHTML = `
                    <div class="pending-empty">
                        <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">
                            ${U.icon('inbox')}
                        </div>
                        <h3>Aucun article</h3>
                        <p>Vous n'avez pas encore publié d'article.</p>
                        <button type="button" class="btn btn-primary btn-sm" id="userArticlesEmptyCreateBtn">
                            ${U.icon('plus')} Créer un article
                        </button>
                    </div>
                `;
                document.getElementById('userArticlesEmptyCreateBtn')?.addEventListener('click', () => {
                    window.location.href = '/dashboard/publish';
                });
                U.refreshIcons(container);
                articlesLoaded = true;
                return;
            }

            renderArticlesView();
            articlesLoaded = true;
        } catch (error) {
            container.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(container);
        }
    }

    function setupUserArticlesFilters() {
        if (articlesFiltersReady) return;
        articlesFiltersReady = true;

        document.getElementById('userArticlesSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(articlesSearchTimeout);
            articlesSearchTimeout = setTimeout(() => {
                articlesSearch = event.target.value.trim().toLowerCase();
                articlesPage = 1;
                renderArticlesView();
            }, 250);
        });

        document.getElementById('userArticlesPerPageSelect')?.addEventListener('change', (event) => {
            articlesPerPage = Number(event.target.value) || 12;
            articlesPage = 1;
            renderArticlesView();
        });

        document.getElementById('userArticlesViewToggle')?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-mode]');
            if (!btn) return;
            articlesViewMode = btn.dataset.mode;
            document.querySelectorAll('#userArticlesViewToggle .view-toggle-btn').forEach((toggle) => {
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
        return article.author?.nom || article.auteur_nom || cfg.user?.nom || '—';
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

    function updateArticlesCountLabel(count) {
        const label = document.getElementById('userArticlesCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 article' : `${count} articles`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
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
        renderCategoryFilterBar(document.getElementById('userArticlesCategoriesContainer'), {
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
        const selectAll = document.getElementById('selectAllUserArticles');
        if (!selectAll) return;

        const pageIds = getPaginatedArticleIds();
        selectAll.checked = pageIds.length > 0 && pageIds.every((id) => selectedArticles.has(id));
        selectAll.indeterminate = pageIds.some((id) => selectedArticles.has(id)) && !selectAll.checked;
    }

    function updateSelectionBar() {
        let bar = document.getElementById('userArticlesSelectionBar');

        if (selectedArticles.size === 0) {
            bar?.remove();
            return;
        }

        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'userArticlesSelectionBar';
            bar.className = 'articles-selection-bar';
            document.body.appendChild(bar);
        }

        const count = selectedArticles.size;
        bar.innerHTML = `
            <span class="articles-selection-bar__count">
                ${U.icon('square-check')}
                ${count} article${count > 1 ? 's' : ''} sélectionné${count > 1 ? 's' : ''}
            </span>
            <button type="button" class="btn btn-danger btn-sm" id="userArticlesSelectionDeleteBtn">
                ${U.icon('trash-2')}
                Supprimer
            </button>
            <button type="button" class="articles-selection-bar__clear" id="userArticlesSelectionClearBtn" title="Annuler la sélection">
                ${U.icon('x')}
            </button>
        `;

        bar.querySelector('#userArticlesSelectionDeleteBtn')?.addEventListener('click', deleteSelectedArticles);
        bar.querySelector('#userArticlesSelectionClearBtn')?.addEventListener('click', clearArticleSelection);
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
            const ids = Array.from(selectedArticles);
            const results = await Promise.allSettled(
                ids.map((id) => U.api(`${cfg.apiBase}/articles/${id}`, { method: 'DELETE' })),
            );
            U.hideLoader();

            const deleted = results.filter((result) => result.status === 'fulfilled').length;
            if (deleted > 0) {
                U.showToast(`${deleted} article${deleted > 1 ? 's' : ''} supprimé${deleted > 1 ? 's' : ''}`, 'success');
                selectedArticles.clear();
                updateSelectionBar();
                articlesLoaded = false;
                await loadUserArticles({ force: true });
            } else {
                U.showToast('Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function renderArticlesPagination(totalPages) {
        const container = document.getElementById('userArticlesPagination');
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
                document.getElementById('articles-view')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function renderArticleGridCard(article) {
        const title = articleTitle(article);
        const category = articleCategory(article);
        const coverUrl = articleCoverUrl(article);
        const featured = isFeatured(article);
        const isPaid = article.is_paid === true || article.is_paid === 1 || article.is_paid === '1';
        const price = article.price ? `${Number(article.price).toFixed(2)} $` : 'Premium';
        const authorName = articleAuthorName(article);
        const authorAvatar = article.author?.cover || article.auteur_cover || cfg.user?.cover;
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
        const authorName = articleAuthorName(article);
        const authorAvatar = article.author?.cover || article.auteur_cover || cfg.user?.cover;
        const avatarUrl = authorAvatar ? U.mediaUrl(authorAvatar) : U.mediaUrl(null);

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
                <td>
                    <div class="article-admin-card__author article-admin-card__author--inline">
                        <img src="${avatarUrl}" alt="">
                        <span>${U.escapeHtml(authorName)}</span>
                    </div>
                </td>
                <td><span class="category-badge ${U.getCategoryColorClass(category)}">${U.escapeHtml(category)}</span></td>
                <td><span class="blocks-badge">${article.blocks_count ?? 0}</span></td>
                <td>${paymentBadge(article)}</td>
                <td>${validationBadge(article)}</td>
                <td>${article.is_published || article.status === 1 ? '<span class="status-badge badge-success">Oui</span>' : '<span class="status-badge badge-secondary">Non</span>'}</td>
                <td>${Number(article.views ?? 0).toLocaleString('fr-FR')}</td>
                <td><span class="pending-date">${U.icon('calendar')}${U.formatDateShort(article.created_at || article.date_add)}</span></td>
                <td class="pending-col-actions">
                    <div class="pending-actions">
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
                </td>
            </tr>
        `;
    }

    function renderArticlesView() {
        const container = document.getElementById('userArticlesPanel');
        if (!container) return;

        const filtered = getFilteredArticles();
        updateArticlesCountLabel(filtered.length);

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
            U.refreshIcons(container);
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
                                    <input type="checkbox" id="selectAllUserArticles" aria-label="Tout sélectionner sur cette page" title="Tout sélectionner">
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
        U.refreshIcons(container);
    }

    function bindArticleActions(container) {
        if (!container) return;

        container.querySelectorAll('.btn-article-select').forEach((input) => {
            input.addEventListener('change', () => {
                toggleArticleSelection(input.dataset.id, input.checked);
            });
        });

        document.getElementById('selectAllUserArticles')?.addEventListener('change', (event) => {
            selectAllArticlesOnPage(event.target.checked);
        });

        container.querySelectorAll('.btn-article-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.location.href = `/dashboard/publish?id=${btn.dataset.id}`;
            });
        });
        container.querySelectorAll('.btn-article-delete').forEach((btn) => {
            btn.addEventListener('click', () => deleteArticle(btn.dataset.id, btn.dataset.title));
        });
        container.querySelectorAll('.btn-article-view').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.DashboardUserPending?.showPreview?.(btn.dataset.id);
            });
        });
    }

    async function deleteArticle(id, title = '') {
        const label = title || articleTitle(allArticles.find((article) => String(article.id) === String(id)) || {});

        if (!await U.confirm(`Supprimer « ${label || 'Sans titre'} » ?`, { confirmText: 'Supprimer' })) {
            return;
        }

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/articles/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Article supprimé', 'success');
            selectedArticles.delete(Number(id));
            updateSelectionBar();
            articlesLoaded = false;
            await loadUserArticles({ force: true });
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    window.DashboardUserArticles = { load: loadUserArticles };
})();
