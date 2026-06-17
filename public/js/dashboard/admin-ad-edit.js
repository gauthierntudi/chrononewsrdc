(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};

    if (!document.getElementById('adminAdEditModal')) return;

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

    const FORMAT_LABELS = {
        rectangle: 'Rectangle',
        portrait: 'Portrait',
        large_portrait: 'Grand portrait',
        large_rectangle: 'Grand rectangle',
        paysage_small: 'Paysage S',
        paysage_medium: 'Paysage M',
        paysage_large: 'Paysage L',
    };

    let editingAdId = null;
    let currentImageUrl = null;
    let imageFile = null;
    let startsAtPicker = null;
    let endsAtPicker = null;
    let ready = false;

    document.addEventListener('DOMContentLoaded', setup);

    function setup() {
        if (ready) return;
        ready = true;

        initDatePickers();
        populateFormatOptions();

        document.getElementById('adminAdEditModalClose')?.addEventListener('click', closeModal);
        document.getElementById('adminAdEditCancel')?.addEventListener('click', closeModal);
        document.getElementById('adminAdEditForm')?.addEventListener('submit', submitForm);
        document.getElementById('adminAdEditModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'adminAdEditModal') closeModal();
        });
        document.getElementById('adminAdEditPlacement')?.addEventListener('change', handlePlacementChange);
        setupImageUpload();
    }

    function initDatePickers() {
        if (typeof flatpickr === 'undefined') return;

        flatpickr.localize(flatpickr.l10ns.fr);

        const startsInput = document.getElementById('adminAdEditStartsAt');
        const endsInput = document.getElementById('adminAdEditEndsAt');
        if (!startsInput || !endsInput) return;

        startsAtPicker = flatpickr(startsInput, {
            dateFormat: 'Y-m-d',
            locale: 'fr',
            allowInput: true,
            onChange(selectedDates) {
                if (selectedDates.length > 0 && endsAtPicker) {
                    endsAtPicker.set('minDate', selectedDates[0]);
                }
            },
        });

        endsAtPicker = flatpickr(endsInput, {
            dateFormat: 'Y-m-d',
            locale: 'fr',
            allowInput: true,
        });
    }

    function populateFormatOptions() {
        const select = document.getElementById('adminAdEditFormat');
        if (!select) return;

        select.innerHTML = Object.entries(FORMAT_LABELS)
            .map(([value, label]) => `<option value="${value}">${label}</option>`)
            .join('');
    }

    function handlePlacementChange() {
        const placement = document.getElementById('adminAdEditPlacement')?.value || '';
        const formatSelect = document.getElementById('adminAdEditFormat');
        if (!formatSelect) return;

        const expectedFormat = PLACEMENT_FORMAT_MAP[placement] || '';
        if (expectedFormat) {
            formatSelect.value = expectedFormat;
        }
        updateFormatHelp();
    }

    function updateFormatHelp() {
        const format = document.getElementById('adminAdEditFormat')?.value || '';
        const help = document.getElementById('adminAdEditFormatHelp');
        if (!help) return;
        help.textContent = FORMAT_DIMENSIONS[format] || '';
    }

    function setupImageUpload() {
        const zone = document.getElementById('adminAdEditUploadZone');
        const input = document.getElementById('adminAdEditImageInput');
        const preview = document.getElementById('adminAdEditUploadPreview');
        const placeholder = document.getElementById('adminAdEditUploadPlaceholder');
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
                resetUploadPreview(currentImageUrl);
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
        const zone = document.getElementById('adminAdEditUploadZone');
        const preview = document.getElementById('adminAdEditUploadPreview');
        const placeholder = document.getElementById('adminAdEditUploadPlaceholder');
        const input = document.getElementById('adminAdEditImageInput');

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

    function formatDateForPicker(value) {
        if (!value) return '';
        const normalized = String(value).trim().slice(0, 10);
        return /^\d{4}-\d{2}-\d{2}$/.test(normalized) ? normalized : '';
    }

    async function openModal(id) {
        if (!id) return;

        try {
            U.showLoader('Chargement…');
            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${id}`);
            U.hideLoader();

            const ad = data.data?.advertisement;
            if (!ad) {
                U.showToast('Publicité introuvable', 'error');
                return;
            }

            editingAdId = ad.id;
            currentImageUrl = ad.image_url || null;

            document.getElementById('adminAdEditId').value = String(ad.id);
            document.getElementById('adminAdEditTitle').value = ad.title || '';
            document.getElementById('adminAdEditPlacement').value = ad.placement || '';
            document.getElementById('adminAdEditFormat').value = ad.format || '';
            document.getElementById('adminAdEditTargetUrl').value = ad.target_url || '';
            document.getElementById('adminAdEditIsLocked').checked = !!ad.is_locked;

            const subtitle = document.getElementById('adminAdEditSubtitle');
            if (subtitle) {
                subtitle.textContent = `Modifier la campagne « ${ad.title || 'Sans titre'} » (${ad.user_name || 'Annonceur inconnu'}).`;
            }

            handlePlacementChange();
            resetUploadPreview(currentImageUrl);
            updateFormatHelp();

            const startValue = formatDateForPicker(ad.starts_at);
            const endValue = formatDateForPicker(ad.ends_at);

            if (startsAtPicker) {
                startsAtPicker.set('minDate', null);
                startsAtPicker.setDate(startValue || null, false);
            }
            if (endsAtPicker) {
                endsAtPicker.set('minDate', null);
                endsAtPicker.setDate(endValue || null, false);
            }

            const modal = document.getElementById('adminAdEditModal');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            U.refreshIcons(modal);
            document.getElementById('adminAdEditTitle')?.focus();
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function closeModal() {
        const modal = document.getElementById('adminAdEditModal');
        if (!modal) return;

        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        editingAdId = null;
        currentImageUrl = null;
        imageFile = null;
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

    async function submitForm(event) {
        event.preventDefault();
        if (!editingAdId) return;

        const title = document.getElementById('adminAdEditTitle')?.value.trim();
        const placement = document.getElementById('adminAdEditPlacement')?.value || '';
        const format = document.getElementById('adminAdEditFormat')?.value;
        const targetUrl = document.getElementById('adminAdEditTargetUrl')?.value.trim();
        const startsAt = document.getElementById('adminAdEditStartsAt')?.value;
        const endsAt = document.getElementById('adminAdEditEndsAt')?.value;
        const isLocked = document.getElementById('adminAdEditIsLocked')?.checked || false;

        if (!title || !placement || !format || !targetUrl || !startsAt || !endsAt) {
            U.showToast('Veuillez remplir tous les champs obligatoires', 'warning');
            return;
        }

        try {
            U.showLoader('Enregistrement…');
            let imageUrl = currentImageUrl;

            if (imageFile) {
                imageUrl = await uploadAdImage(imageFile, format);
            } else if (!imageUrl) {
                U.hideLoader();
                U.showToast('Veuillez sélectionner une image', 'warning');
                return;
            }

            const payload = {
                title,
                placement,
                format,
                target_url: targetUrl,
                starts_at: startsAt,
                ends_at: endsAt,
                image_url: imageUrl,
                is_locked: isLocked,
            };

            const data = await U.api(`${cfg.apiBase}/admin/advertisements/${editingAdId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });

            U.hideLoader();
            closeModal();
            U.showToast(data.message || 'Publicité mise à jour', 'success');
            window.dispatchEvent(new CustomEvent('dashboard:ads-updated'));
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    window.DashboardAdminAdEdit = { open: openModal };
})();
