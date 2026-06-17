(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const access = cfg.access || {};

    const hasUserAdsPanel = !!document.getElementById('userAdsList');
    const isStaffForm = !!(access.globalAds && document.getElementById('userAdPlacement'));

    if (!access.ownAds || !document.getElementById('userAdModal')) return;

    const PLACEMENT_FORMAT_MAP = {
        'pub-header': 'paysage_large',
        'pub-modal': 'large_rectangle',
        'pub-float': 'portrait',
        'pub-body-1': 'paysage_small',
        'pub-body-2': 'paysage_medium',
        'pub-body-3': 'paysage_small',
        'pub-body-sidebar-1': 'large_portrait',
        'pub-body-sidebar-2': 'rectangle',
        'pub-footer': 'paysage_large',
    };

    const FORMAT_DIMENSIONS = {
        rectangle: '672×560 pixels',
        portrait: '512×562 pixels',
        large_portrait: '768×1024 pixels',
        large_rectangle: '1024×768 pixels',
        paysage_small: '1456×180 pixels',
        paysage_medium: '1920×400 pixels',
        paysage_large: '3456×502 pixels',
    };

    let currentAds = [];
    let rates = [];
    let adsPage = 1;
    let adsPerPage = 10;
    let adsSearch = '';
    let adsSearchTimeout = null;
    let adsFilters = { validation: '', payment: '', broadcast: '', placement: '' };
    let editingAdId = null;
    let originalAdFormat = null;
    let imageFile = null;
    let adsReady = false;
    let adsLoaded = false;
    let startsAtPicker = null;
    let endsAtPicker = null;
    let paymentPollingActive = false;
    let paymentPollingOrder = null;

    document.addEventListener('DOMContentLoaded', () => {
        setupUserAds();
        checkForNewAd();
        checkPaymentReturn();
        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'ads' && hasUserAdsPanel) {
                loadUserAds({ skipIfCached: true });
            }
        });
    });

    function setupUserAds() {
        if (adsReady) return;
        adsReady = true;

        initDatePickers();

        document.getElementById('userAdAddBtn')?.addEventListener('click', () => openUserAdModal());
        if (hasUserAdsPanel) {
            document.getElementById('userAdsRefreshBtn')?.addEventListener('click', () => {
                adsLoaded = false;
                loadUserAds({ force: true });
            });
        }
        document.getElementById('userAdModalClose')?.addEventListener('click', closeUserAdModal);
        document.getElementById('userAdCancel')?.addEventListener('click', closeUserAdModal);
        document.getElementById('userAdForm')?.addEventListener('submit', submitUserAdForm);
        document.getElementById('userAdModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'userAdModal') closeUserAdModal();
        });

        document.getElementById('userAdImageModalClose')?.addEventListener('click', closeUserAdImageModal);
        document.getElementById('userAdImageModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'userAdImageModal') closeUserAdImageModal();
        });

        document.getElementById('userAdsSearchInput')?.addEventListener('input', (event) => {
            clearTimeout(adsSearchTimeout);
            adsSearchTimeout = setTimeout(() => {
                adsSearch = event.target.value.trim();
                adsPage = 1;
                loadUserAds({ force: true });
            }, 300);
        });

        const filterMap = {
            userAdsFilterValidation: 'validation',
            userAdsFilterPayment: 'payment',
            userAdsFilterBroadcast: 'broadcast',
            userAdsFilterPlacement: 'placement',
        };

        Object.entries(filterMap).forEach(([id, key]) => {
            document.getElementById(id)?.addEventListener('change', (event) => {
                adsFilters[key] = event.target.value;
                adsPage = 1;
                loadUserAds({ force: true });
            });
        });

        document.getElementById('userAdsPerPageSelect')?.addEventListener('change', (event) => {
            adsPerPage = Number(event.target.value) || 10;
            adsPage = 1;
            loadUserAds({ force: true });
        });

        document.getElementById('userAdFormat')?.addEventListener('change', () => {
            updateFormatHelp();
            updatePriceHint();
        });
        document.getElementById('userAdPlacement')?.addEventListener('change', handlePlacementChange);

        setupImageUpload();
        setupPaymentForm();
    }

    function initDatePickers() {
        if (typeof flatpickr === 'undefined') return;

        flatpickr.localize(flatpickr.l10ns.fr);

        const startsInput = document.getElementById('userAdStartsAt');
        const endsInput = document.getElementById('userAdEndsAt');
        if (!startsInput || !endsInput) return;

        startsAtPicker = flatpickr(startsInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            locale: 'fr',
            onChange(selectedDates) {
                if (selectedDates.length > 0 && endsAtPicker) {
                    endsAtPicker.set('minDate', selectedDates[0]);
                }
                updatePriceHint();
            },
        });

        endsAtPicker = flatpickr(endsInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            locale: 'fr',
            onChange: updatePriceHint,
        });
    }

    function setupImageUpload() {
        const zone = document.getElementById('userAdUploadZone');
        const input = document.getElementById('userAdImageInput');
        const preview = document.getElementById('userAdUploadPreview');
        const placeholder = document.getElementById('userAdUploadPlaceholder');
        if (!zone || !input) return;

        const openPicker = () => input.click();
        zone.addEventListener('click', openPicker);
        zone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPicker();
            }
        });

        input.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            imageFile = file || null;
            if (!file) {
                resetUploadPreview();
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                if (preview) {
                    preview.src = e.target?.result || '';
                    preview.hidden = false;
                }
                if (placeholder) placeholder.hidden = true;
                zone.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    }

    function resetUploadPreview(existingUrl = null) {
        const zone = document.getElementById('userAdUploadZone');
        const preview = document.getElementById('userAdUploadPreview');
        const placeholder = document.getElementById('userAdUploadPlaceholder');
        const input = document.getElementById('userAdImageInput');

        if (input) input.value = '';
        imageFile = null;

        if (existingUrl && preview) {
            preview.src = U.mediaUrl(existingUrl);
            preview.hidden = false;
            if (placeholder) placeholder.hidden = true;
            zone?.classList.add('has-image');
            return;
        }

        if (preview) {
            preview.removeAttribute('src');
            preview.hidden = true;
        }
        if (placeholder) placeholder.hidden = false;
        zone?.classList.remove('has-image');
        if (zone) U.refreshIcons(zone);
    }

    function handlePlacementChange() {
        const placement = document.getElementById('userAdPlacement')?.value || '';
        const formatSelect = document.getElementById('userAdFormat');
        const errorEl = document.getElementById('userAdPlacementError');
        if (!formatSelect || !isStaffForm) return;

        const expectedFormat = PLACEMENT_FORMAT_MAP[placement] || '';
        if (expectedFormat) {
            formatSelect.value = expectedFormat;
            if (errorEl) errorEl.hidden = true;
        } else {
            formatSelect.value = '';
            if (errorEl) errorEl.hidden = true;
        }

        updateFormatHelp();
        updatePriceHint();
    }

    function setPaymentLoaderVisible(visible, { title, subtitle } = {}) {
        const loader = document.getElementById('userAdPaymentLoader');
        if (!loader) return;

        const titleEl = loader.querySelector('.loader-text');
        const subtitleEl = loader.querySelector('.loader-subtext');
        if (titleEl) {
            titleEl.textContent = title || 'Veuillez patienter s\'il vous plaît';
        }
        if (subtitleEl) {
            subtitleEl.textContent = subtitle || 'Ne quittez pas cette page pendant le traitement…';
        }

        loader.hidden = !visible;
        loader.classList.toggle('is-hidden', !visible);
        loader.style.display = visible ? 'flex' : 'none';
    }

    function setPaymentFormVisible(visible) {
        const form = document.getElementById('userAdPaymentForm');
        const subtitle = document.querySelector('#userAdPaymentModal .payment-subtitle');
        const closeBtn = document.getElementById('userAdPaymentModalClose');
        const cancelBtn = document.getElementById('userAdPaymentCancel');

        if (form) form.hidden = !visible;
        if (subtitle) subtitle.hidden = !visible;
        if (closeBtn) closeBtn.hidden = !visible;
        if (cancelBtn) cancelBtn.hidden = !visible;
    }

    function resetPaymentModalUi() {
        paymentPollingActive = false;
        paymentPollingOrder = null;
        setPaymentFormVisible(true);
        setPaymentLoaderVisible(false);
    }

    function showPaymentWaitingState(orderNumber) {
        paymentPollingActive = true;
        paymentPollingOrder = orderNumber;
        setPaymentFormVisible(false);
        setPaymentLoaderVisible(true, {
            title: 'Validation en cours',
            subtitle: 'Veuillez valider le paiement USSD sur votre téléphone. Ne quittez pas cette page.',
        });
    }

    function setupPaymentForm() {
        document.getElementById('userAdPaymentModalClose')?.addEventListener('click', () => {
            if (paymentPollingActive) {
                U.showToast('Validation USSD en cours. Attendez la confirmation ou l\'échec du paiement.', 'warning');
                return;
            }
            closePaymentModal();
        });
        document.getElementById('userAdPaymentCancel')?.addEventListener('click', () => {
            if (paymentPollingActive) {
                U.showToast('Validation USSD en cours. Attendez la confirmation ou l\'échec du paiement.', 'warning');
                return;
            }
            closePaymentModal();
        });
        document.getElementById('userAdPaymentModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'userAdPaymentModal' && !paymentPollingActive) closePaymentModal();
        });
        document.getElementById('userAdPaymentForm')?.addEventListener('submit', handlePaymentSubmit);

        document.querySelectorAll('input[name="userAdPaymentMethod"]').forEach((radio) => {
            radio.addEventListener('change', () => updatePhonePlaceholder(radio.value));
        });
    }

    function updateFormatHelp() {
        const format = document.getElementById('userAdFormat')?.value;
        const help = document.getElementById('userAdFormatHelp');
        if (!help) return;
        help.textContent = format ? `Dimensions requises : ${FORMAT_DIMENSIONS[format] || '—'}` : '';
    }

    async function loadUserAds({ skipIfCached = false, force = false } = {}) {
        const list = document.getElementById('userAdsList');
        const pagination = document.getElementById('userAdsPagination');
        if (!list) return;
        if (skipIfCached && adsLoaded && !force) return;

        list.innerHTML = U.loadingHtml();
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

            const data = await U.api(`${cfg.apiBase}/advertisements?${params.toString()}`);
            const items = data.data?.advertisements || [];
            const stats = data.data?.stats || {};
            const total = Number(data.data?.total ?? 0);
            const totalPages = Number(data.data?.totalPages ?? 1);

            currentAds = items;
            updateUserAdsCountLabel(total);
            renderUserAdsStats(stats);

            if (!items.length) {
                list.innerHTML = `
                    <div class="pending-empty ads-empty">
                        <div class="pending-empty__icon" style="background:#fff7ed;color:#f59e0b;">${U.icon('megaphone')}</div>
                        <h3>Aucune publicité trouvée</h3>
                        <p>${adsSearch || Object.values(adsFilters).some(Boolean)
                            ? 'Aucune campagne ne correspond à votre recherche.'
                            : 'Créez votre première campagne publicitaire.'}</p>
                        ${!adsSearch && !Object.values(adsFilters).some(Boolean)
                            ? `<button type="button" class="btn btn-primary btn-sm" id="userAdsEmptyAddBtn">${U.icon('plus')} Nouvelle publicité</button>`
                            : ''}
                    </div>
                `;
                document.getElementById('userAdsEmptyAddBtn')?.addEventListener('click', () => openUserAdModal());
                U.refreshIcons(list);
                adsLoaded = true;
                return;
            }

            list.innerHTML = `
                <div class="ads-list">
                    ${items.map((ad, index) => renderUserAdRow(ad, index)).join('')}
                </div>
            `;

            bindUserAdActions(list);
            renderUserAdsPagination(totalPages);
            U.refreshIcons(list);
            adsLoaded = true;
        } catch (error) {
            list.innerHTML = `<div class="empty-state">${U.icon('circle-alert')}<p>${U.escapeHtml(error.message)}</p></div>`;
            U.refreshIcons(list);
        }
    }

    function updateUserAdsCountLabel(count) {
        const label = document.getElementById('userAdsCountLabel');
        if (!label) return;
        label.textContent = count === 1 ? '1 publicité' : `${count} publicités`;
        label.className = count ? 'pending-count-badge' : 'pending-count-badge pending-count-badge--empty';
    }

    function renderUserAdsStats(stats) {
        const grid = document.getElementById('userAdsStatsGrid');
        if (!grid) return;

        const spentLabel = access.adsFree ? 'Gratuites' : 'Dépensé';
        const spentValue = access.adsFree
            ? (stats.total ?? 0)
            : `$${Number(stats.revenue ?? 0).toFixed(2)}`;

        const cards = [
            { color: 'stat-card-orange', icon: 'clock', label: 'En attente', value: stats.pending ?? 0 },
            { color: 'stat-card-green', icon: 'circle-check', label: 'Validées', value: stats.validated ?? 0 },
            { color: 'stat-card-blue', icon: 'megaphone', label: 'Actives', value: stats.active ?? 0 },
            { color: 'stat-card-teal', icon: access.adsFree ? 'gift' : 'banknote', label: spentLabel, value: spentValue },
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
            payment: { gratuit: 'gift', paye: 'banknote', en_attente: 'clock' },
            validation: { valide: 'badge-check', refuse: 'circle-x', en_attente: 'clock' },
            broadcast: { active: 'radio', inactive: 'pause-circle', terminee: 'flag' },
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

    function renderUserAdRow(ad, index) {
        const locked = ad.is_locked ? ' ads-row--locked' : '';
        const canPay = !access.adsFree
            && ad.payment_status !== 'paye'
            && ad.payment_status !== 'gratuit';
        const rejectBlock = ad.rejection_reason
            ? `<div class="ads-row-alert">${U.icon('info')}<strong>Motif du refus :</strong> ${U.escapeHtml(ad.rejection_reason)}</div>`
            : '';

        return `
            <div class="ads-row${locked}">
                <div class="ads-row__index">${ad.is_locked ? U.icon('lock') : index + 1}</div>
                <div class="ads-row__info">
                    <div class="ads-row__title">${U.escapeHtml(ad.title || 'Sans titre')}</div>
                    <div class="ads-row__subtitle">${U.escapeHtml(adPlacementLabel(ad.placement))}</div>
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
                    <button type="button" class="ads-action-btn" data-user-ad-action="image" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" data-ad-image="${adAttr(ad.image_url)}" title="Voir l'image">${U.icon('image')}</button>
                    <button type="button" class="ads-action-btn" data-user-ad-action="edit" data-ad-id="${ad.id}" title="Modifier">${U.icon('pencil')}</button>
                    ${canPay ? `<button type="button" class="ads-action-btn ads-action-btn--pay" data-user-ad-action="pay" data-ad-id="${ad.id}" title="Payer">${U.icon('credit-card')}</button>` : ''}
                    <button type="button" class="ads-action-btn ads-action-btn--danger" data-user-ad-action="delete" data-ad-id="${ad.id}" data-ad-title="${adAttr(ad.title)}" title="Supprimer">${U.icon('trash-2')}</button>
                </div>
            </div>
            ${rejectBlock}
        `;
    }

    function bindUserAdActions(container) {
        container.querySelectorAll('[data-user-ad-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.userAdAction;
                const id = Number(btn.dataset.adId);
                const title = btn.dataset.adTitle || 'Publicité';

                if (action === 'image') openUserAdImageModal(title, btn.dataset.adImage);
                if (action === 'edit') {
                    const ad = currentAds.find((item) => Number(item.id) === id);
                    if (ad) openUserAdModal(ad);
                }
                if (action === 'pay') payUserAd(id);
                if (action === 'delete') deleteUserAd(id, title);
            });
        });
    }

    function openUserAdImageModal(title, imagePath) {
        const modal = document.getElementById('userAdImageModal');
        const img = document.getElementById('userAdImageModalImg');
        const titleEl = document.getElementById('userAdImageModalTitle');
        if (!modal || !img) return;

        if (titleEl) titleEl.textContent = title || 'Publicité';
        img.src = imagePath ? U.mediaUrl(imagePath) : '';
        img.alt = title || 'Publicité';
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
    }

    function closeUserAdImageModal() {
        const modal = document.getElementById('userAdImageModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function renderUserAdsPagination(totalPages) {
        const container = document.getElementById('userAdsPagination');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="pagination-controls">';

        if (adsPage > 1) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-user-ads-page="${adsPage - 1}" aria-label="Page précédente">${U.icon('chevron-left')}</button>`;
        }

        for (let page = 1; page <= totalPages; page += 1) {
            if (page === 1 || page === totalPages || Math.abs(page - adsPage) <= 1) {
                html += `<button type="button" class="pagination-btn ${page === adsPage ? 'active' : ''}" data-user-ads-page="${page}">${page}</button>`;
            } else if (page === adsPage - 2 || page === adsPage + 2) {
                html += '<span class="pagination-ellipsis">…</span>';
            }
        }

        if (adsPage < totalPages) {
            html += `<button type="button" class="pagination-btn pagination-btn--nav" data-user-ads-page="${adsPage + 1}" aria-label="Page suivante">${U.icon('chevron-right')}</button>`;
        }

        html += '</div>';
        container.innerHTML = html;
        U.refreshIcons(container);

        container.querySelectorAll('[data-user-ads-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                adsPage = Number(btn.dataset.userAdsPage);
                loadUserAds({ force: true });
            });
        });
    }

    async function ensureRates() {
        if (rates.length) return rates;
        const data = await U.api(`${cfg.apiBase}/advertisement-rates`);
        rates = data.rates || [];
        return rates;
    }

    async function openUserAdModal(ad = null) {
        const modal = document.getElementById('userAdModal');
        const form = document.getElementById('userAdForm');
        const titleEl = document.getElementById('userAdModalTitle');
        const submitLabel = document.getElementById('userAdSubmitLabel');
        const formatSelect = document.getElementById('userAdFormat');
        if (!modal || !form || !formatSelect) return;

        await ensureRates();
        formatSelect.innerHTML = isStaffForm
            ? rates.map((rate) => `
                <option value="${U.escapeHtml(rate.format)}">${U.escapeHtml(rate.label || rate.format)} (${U.escapeHtml(rate.dimensions || '')})</option>
            `).join('')
            : rates.map((rate) => `
                <option value="${U.escapeHtml(rate.format)}">${U.escapeHtml(rate.label || rate.format)} (${U.escapeHtml(rate.dimensions || '')})</option>
            `).join('');

        editingAdId = ad?.id || null;
        originalAdFormat = ad?.format || null;
        form.reset();
        document.getElementById('userAdId').value = ad?.id || '';
        document.getElementById('userAdTitle').value = ad?.title || '';
        document.getElementById('userAdTargetUrl').value = ad?.target_url || '';

        const placementSelect = document.getElementById('userAdPlacement');
        const lockInput = document.getElementById('userAdIsLocked');
        if (isStaffForm && placementSelect) {
            placementSelect.value = ad?.placement || '';
            if (lockInput) lockInput.checked = !!ad?.is_locked;
            handlePlacementChange();
        } else {
            document.getElementById('userAdFormat').value = ad?.format || rates[0]?.format || '';
        }

        if (startsAtPicker) startsAtPicker.clear();
        if (endsAtPicker) endsAtPicker.clear();
        if (ad?.starts_at && startsAtPicker) startsAtPicker.setDate(String(ad.starts_at).slice(0, 10), false);
        if (ad?.ends_at && endsAtPicker) endsAtPicker.setDate(String(ad.ends_at).slice(0, 10), false);

        resetUploadPreview(ad?.image_url || null);

        if (titleEl) titleEl.textContent = ad ? 'Modifier la publicité' : 'Nouvelle publicité';
        if (submitLabel) submitLabel.textContent = ad ? 'Modifier la publicité' : 'Créer la publicité';
        updateFormatHelp();
        updatePriceHint();

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        U.refreshIcons(modal);
    }

    function closeUserAdModal() {
        const modal = document.getElementById('userAdModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        editingAdId = null;
        originalAdFormat = null;
        imageFile = null;
    }

    function resolveDurationDays(startsAt, endsAt) {
        const start = new Date(startsAt);
        const end = new Date(endsAt);
        const diff = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
        if (diff <= 7) return 7;
        if (diff <= 15) return 15;
        return 30;
    }

    function updatePriceHint() {
        const pricingInfo = document.getElementById('userAdPricingInfo');
        const priceAmount = document.getElementById('userAdPriceAmount');
        const priceDuration = document.getElementById('userAdPriceDuration');
        const freeHint = document.getElementById('userAdPriceHint');

        if (access.adsFree) {
            if (pricingInfo) pricingInfo.hidden = true;
            if (freeHint) {
                freeHint.hidden = false;
                freeHint.textContent = 'Publicité gratuite pour votre rôle.';
            }
            return;
        }

        if (freeHint) freeHint.hidden = true;

        const format = document.getElementById('userAdFormat')?.value;
        const startsAt = document.getElementById('userAdStartsAt')?.value;
        const endsAt = document.getElementById('userAdEndsAt')?.value;
        const rate = rates.find((item) => item.format === format);

        if (!pricingInfo || !priceAmount || !priceDuration) return;

        if (!rate || !startsAt || !endsAt) {
            pricingInfo.hidden = true;
            return;
        }

        const start = new Date(startsAt);
        const end = new Date(endsAt);
        const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
        if (diffDays <= 0) {
            pricingInfo.hidden = true;
            return;
        }

        const duration = resolveDurationDays(startsAt, endsAt);
        const amount = duration === 7 ? rate.price_7_days : duration === 15 ? rate.price_15_days : rate.price_30_days;
        const periodLabel = duration === 7 ? '7 jours' : duration === 15 ? '15 jours' : '30 jours';

        priceAmount.textContent = `${Number(amount ?? 0).toFixed(2)} USD`;
        priceDuration.textContent = `Pour ${diffDays} jour${diffDays > 1 ? 's' : ''} (Tarif ${periodLabel})`;
        pricingInfo.hidden = false;
    }

    async function uploadAdImage(file, format) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'ad');
        formData.append('ad_format', format);

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
            throw new Error(data.message || 'Erreur lors de l\'upload de l\'image');
        }

        return data.url;
    }

    async function submitUserAdForm(event) {
        event.preventDefault();

        const title = document.getElementById('userAdTitle')?.value.trim();
        const placement = document.getElementById('userAdPlacement')?.value || '';
        const format = document.getElementById('userAdFormat')?.value;
        const targetUrl = document.getElementById('userAdTargetUrl')?.value.trim();
        const startsAt = document.getElementById('userAdStartsAt')?.value;
        const endsAt = document.getElementById('userAdEndsAt')?.value;
        const isLocked = document.getElementById('userAdIsLocked')?.checked || false;
        const existingAd = editingAdId ? currentAds.find((item) => Number(item.id) === Number(editingAdId)) : null;

        if (!title || !format || !targetUrl || !startsAt || !endsAt || (isStaffForm && !placement)) {
            U.showToast('Veuillez remplir tous les champs obligatoires', 'warning');
            return;
        }

        try {
            U.showLoader('Enregistrement…');
            let imageUrl = existingAd?.image_url || null;

            if (imageFile) {
                imageUrl = await uploadAdImage(imageFile, format);
            } else if (!imageUrl) {
                U.hideLoader();
                U.showToast('Veuillez sélectionner une image', 'warning');
                return;
            }

            const payload = {
                title,
                format,
                target_url: targetUrl,
                starts_at: startsAt,
                ends_at: endsAt,
                image_url: imageUrl,
            };

            if (isStaffForm) {
                payload.placement = placement;
                payload.is_locked = isLocked;
            }

            const data = editingAdId
                ? await U.api(`${cfg.apiBase}/advertisements/${editingAdId}`, { method: 'PUT', body: JSON.stringify(payload) })
                : await U.api(`${cfg.apiBase}/advertisements`, { method: 'POST', body: JSON.stringify(payload) });

            U.hideLoader();
            U.showToast(data.message || 'Publicité enregistrée', 'success');
            closeUserAdModal();

            if (hasUserAdsPanel) {
                adsLoaded = false;
                await loadUserAds({ force: true });
            } else {
                window.dispatchEvent(new CustomEvent('dashboard:ads-created'));
            }

            const newAdId = data.advertisement_id || editingAdId;
            if (data.requires_payment && newAdId) {
                setTimeout(() => showPaymentModal(newAdId), 500);
            }
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function deleteUserAd(id, title) {
        if (!await U.confirm(`Supprimer « ${title || 'cette publicité'} » ?`, { confirmText: 'Supprimer' })) return;

        try {
            U.showLoader('Suppression…');
            const data = await U.api(`${cfg.apiBase}/advertisements/${id}`, { method: 'DELETE' });
            U.hideLoader();
            U.showToast(data.message || 'Publicité supprimée', 'success');
            adsLoaded = false;
            await loadUserAds({ force: true });
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    async function payUserAd(id) {
        await showPaymentModal(id);
    }

    async function showPaymentModal(adId) {
        if (access.adsFree) {
            U.showToast('Les publicités sont gratuites pour votre rôle.', 'info');
            return;
        }

        try {
            const data = await U.api(`${cfg.apiBase}/advertisements/${adId}`);
            const ad = data.data?.advertisement;
            if (!ad) {
                U.showToast('Impossible de charger la publicité', 'error');
                return;
            }

            const amount = parseFloat(ad.amount_paid || 0);
            const adIdInput = document.getElementById('userAdPaymentAdId');
            if (adIdInput) {
                adIdInput.value = String(adId);
                adIdInput.dataset.amount = String(amount);
            }

            const amountDisplay = document.getElementById('userAdPaymentAmountDisplay');
            const amountText = document.getElementById('userAdPaymentAmountText');
            if (amountDisplay) amountDisplay.textContent = `$${amount.toFixed(2)}`;
            if (amountText) amountText.textContent = amount.toFixed(0);

            const cardOption = document.querySelector('input[name="userAdPaymentMethod"][value="carte_bancaire"]')?.closest('.payment-method');
            if (cardOption) {
                cardOption.style.display = amount <= 1 ? 'none' : 'flex';
                if (amount <= 1) {
                    const mpesa = document.querySelector('input[name="userAdPaymentMethod"][value="mpesa"]');
                    if (mpesa) {
                        mpesa.checked = true;
                        updatePhonePlaceholder('mpesa');
                    }
                }
            }

            const selected = document.querySelector('input[name="userAdPaymentMethod"]:checked');
            if (selected) updatePhonePlaceholder(selected.value);

            resetPaymentModalUi();

            const modal = document.getElementById('userAdPaymentModal');
            modal?.classList.add('active');
            modal?.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            U.refreshIcons(modal);
        } catch (error) {
            U.showToast(error.message, 'error');
        }
    }

    function closePaymentModal() {
        if (paymentPollingActive) return;

        const modal = document.getElementById('userAdPaymentModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
        resetPaymentModalUi();
        if (!document.getElementById('userAdModal')?.classList.contains('active')
            && !document.getElementById('userAdImageModal')?.classList.contains('active')) {
            document.body.classList.remove('modal-open');
        }
    }

    function updatePhonePlaceholder(method) {
        const phoneInput = document.getElementById('userAdPaymentPhone');
        if (!phoneInput) return;
        const placeholders = {
            carte_bancaire: 'Ex: 243820000000 ou 081XXXXXXX',
            mpesa: 'Ex: 243820000000 ou 081XXXXXXX (M-Pesa)',
            airtel_money: 'Ex: 243910000000 ou 091XXXXXXX (Airtel)',
            orange_money: 'Ex: 243840000000 ou 084XXXXXXX (Orange)',
        };
        phoneInput.placeholder = placeholders[method] || placeholders.carte_bancaire;
    }

    function validatePhoneNumber(phone, method) {
        const airtelPrefixes = ['091', '092', '093', '094', '095', '096', '097', '098', '099'];
        const mpesaPrefixes = ['080', '081', '082', '083'];
        const orangePrefixes = ['084', '085', '086', '087', '088', '089'];

        phone = phone.replace(/\s+/g, '');

        if (method === 'carte_bancaire') {
            if (/^243\d{9}$/.test(phone)) return { valid: true, phone };
            if (/^0\d{9}$/.test(phone)) return { valid: true, phone: `243${phone.substring(1)}` };
            return { valid: false, phone, error: 'Format invalide. Utilisez 243XXXXXXXXX ou 0XXXXXXXXX' };
        }

        const checkPrefix = (prefix) => {
            if (method === 'airtel_money' && !airtelPrefixes.includes(prefix)) {
                return { valid: false, phone, error: 'Ce numéro n\'est pas un numéro Airtel Money valide' };
            }
            if (method === 'mpesa' && !mpesaPrefixes.includes(prefix)) {
                return { valid: false, phone, error: 'Ce numéro n\'est pas un numéro M-Pesa valide' };
            }
            if (method === 'orange_money' && !orangePrefixes.includes(prefix)) {
                return { valid: false, phone, error: 'Ce numéro n\'est pas un numéro Orange Money valide' };
            }
            return null;
        };

        if (/^243\d{9}$/.test(phone)) {
            const prefixError = checkPrefix(`0${phone.substring(3, 5)}`);
            if (prefixError) return prefixError;
            return { valid: true, phone };
        }

        if (/^0\d{9}$/.test(phone)) {
            const prefixError = checkPrefix(phone.substring(0, 3));
            if (prefixError) return prefixError;
            return { valid: true, phone: `243${phone.substring(1)}` };
        }

        return { valid: false, phone, error: 'Format invalide. Utilisez 243XXXXXXXXX ou 0XXXXXXXXX' };
    }

    async function handlePaymentSubmit(event) {
        event.preventDefault();

        const adIdInput = document.getElementById('userAdPaymentAdId');
        const adId = adIdInput?.value;
        const amount = parseFloat(adIdInput?.dataset.amount || '0');
        const method = document.querySelector('input[name="userAdPaymentMethod"]:checked')?.value;
        const phone = document.getElementById('userAdPaymentPhone')?.value.trim() || '';
        if (!adId || !method) return;

        const validation = validatePhoneNumber(phone, method);
        if (!validation.valid) {
            U.showToast(validation.error, 'error');
            return;
        }

        setPaymentLoaderVisible(true);

        try {
            const data = await U.api(`${cfg.apiBase}/advertisements/${adId}/initiate-payment`, {
                method: 'POST',
                body: JSON.stringify({
                    amount,
                    method,
                    phone: validation.phone,
                }),
            });

            if (data.success) {
                if (data.is_redirect && data.payment_url) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = data.payment_url;
                    if (data.params) {
                        Object.entries(data.params).forEach(([key, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        });
                    }
                    document.body.appendChild(form);
                    form.submit();
                    return;
                }

                if (data.direct_success && data.orderNumber) {
                    showPaymentWaitingState(data.orderNumber);
                    pollPaymentStatus(data.orderNumber);
                    return;
                }
            }

            setPaymentLoaderVisible(false);
            U.showToast(data.message || 'Erreur lors de l\'initialisation du paiement', 'error');
        } catch (error) {
            setPaymentLoaderVisible(false);
            U.showToast(error.message, 'error');
        }
    }

    function pollPaymentStatus(orderNumber, attempt = 1) {
        if (!paymentPollingActive || paymentPollingOrder !== orderNumber) return;

        if (attempt > 60) {
            paymentPollingActive = false;
            paymentPollingOrder = null;
            setPaymentLoaderVisible(false);
            setPaymentFormVisible(true);
            U.showToast('Délai dépassé. Vérifiez votre téléphone ou réessayez.', 'warning');
            return;
        }

        setTimeout(async () => {
            if (!paymentPollingActive || paymentPollingOrder !== orderNumber) return;

            try {
                const res = await fetch(`${cfg.apiBase}/payments/check-status?orderNumber=${encodeURIComponent(orderNumber)}`, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': U.csrf },
                });
                const data = await res.json();

                if (data.success && data.statut === 'reussi') {
                    paymentPollingActive = false;
                    paymentPollingOrder = null;
                    setPaymentLoaderVisible(false);
                    closePaymentModal();
                    U.showToast('Paiement confirmé ! Votre publicité est en attente de validation.', 'success');
                    if (hasUserAdsPanel) {
                        adsLoaded = false;
                        await loadUserAds({ force: true });
                    } else {
                        window.dispatchEvent(new CustomEvent('dashboard:ads-created'));
                    }
                } else if (data.success && data.statut === 'echoue') {
                    paymentPollingActive = false;
                    paymentPollingOrder = null;
                    resetPaymentModalUi();
                    U.showToast('Le paiement a échoué. Vous pouvez réessayer.', 'error');
                } else {
                    pollPaymentStatus(orderNumber, attempt + 1);
                }
            } catch {
                pollPaymentStatus(orderNumber, attempt + 1);
            }
        }, 3000);
    }

    function checkForNewAd() {
        const params = new URLSearchParams(window.location.search);
        const newAdId = params.get('new_ad');
        if (!newAdId) return;

        params.delete('new_ad');
        const newUrl = `${window.location.pathname}${params.toString() ? `?${params}` : ''}`;
        window.history.replaceState({}, document.title, newUrl);

        setTimeout(() => showPaymentModal(Number(newAdId)), 600);
    }

    function checkPaymentReturn() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('payment_status') !== 'success') return;

        params.delete('payment_status');
        params.delete('payment_ref');
        const newUrl = `${window.location.pathname}${params.toString() ? `?${params}` : ''}`;
        window.history.replaceState({}, document.title, newUrl);

        U.showToast('Paiement réussi ! Votre publicité est en attente de validation.', 'success');
        adsLoaded = false;
        loadUserAds({ force: true });
    }

    window.DashboardUserAds = {
        load: hasUserAdsPanel ? loadUserAds : null,
        openModal: openUserAdModal,
        showPaymentModal,
    };
})();
