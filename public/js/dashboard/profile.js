(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};

    let profileReady = false;
    let profileLoaded = false;
    let profileSnapshot = null;
    let profileCropper = null;
    let profilePhotoFile = null;

    const roleLabels = {
        user: 'Utilisateur',
        journaliste: 'Journaliste',
        admin: 'Administrateur',
        superadmin: 'Super administrateur',
    };

    function roleLabel(role) {
        return roleLabels[role] || role || 'Utilisateur';
    }

    function setField(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
    }

    function getField(id) {
        return document.getElementById(id)?.value?.trim() ?? '';
    }

    function updateBioCounter() {
        const bio = document.getElementById('profileBio');
        const counter = document.getElementById('profileBioCount');
        if (bio && counter) counter.textContent = String(bio.value.length);
    }

    function updateAvatars(coverPath) {
        const url = U.mediaUrl(coverPath);
        ['navbarAvatar', 'sidebarAvatar', 'profileAvatar'].forEach((id) => {
            const img = document.getElementById(id);
            if (img) img.src = url;
        });
    }

    function updateUserLabels(user) {
        const name = user?.nom || user?.name || 'Utilisateur';
        const subtitle = user?.titre || roleLabel(user?.role);

        ['navbarUserName', 'sidebarUserName'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = name;
        });

        ['navbarUserRole', 'sidebarUserRole'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = subtitle;
        });

        const roleBadge = document.getElementById('profileRoleBadge');
        if (roleBadge && user?.role) roleBadge.textContent = roleLabel(user.role);

        if (cfg.user) {
            Object.assign(cfg.user, user);
        }
    }

    function applySnapshot(data) {
        if (!data) return;
        setField('profileNom', data.nom);
        setField('profileEmail', data.email);
        setField('profileTelephone', data.telephone);
        setField('profileTitre', data.titre);
        setField('profileBio', data.bio);
        setField('profileFacebook', data.facebook);
        setField('profileYoutube', data.youtube);
        setField('profileTwitter', data.twitter);
        setField('profileInstagram', data.instagram);
        updateAvatars(data.cover);
        updateBioCounter();
    }

    async function loadProfile({ skipIfCached = false } = {}) {
        const form = document.getElementById('profileForm');
        if (!form) return;
        if (skipIfCached && profileLoaded) return;

        try {
            const data = await U.api(`${cfg.apiBase}/auth/me`);
            const user = data.user || cfg.user;
            profileSnapshot = {
                nom: user.nom || '',
                email: user.email || '',
                telephone: user.telephone || '',
                titre: user.titre || '',
                bio: user.bio || '',
                facebook: user.facebook || '',
                youtube: user.youtube || '',
                twitter: user.twitter || '',
                instagram: user.instagram || '',
                cover: user.cover || null,
                role: user.role,
            };
            applySnapshot(profileSnapshot);
            updateUserLabels(user);
            U.refreshIcons(form);
            profileLoaded = true;
        } catch (error) {
            if (cfg.user) {
                applySnapshot(cfg.user);
                profileLoaded = true;
            }
            U.showToast(error.message, 'error');
        }
    }

    async function saveProfile(payload) {
        const data = await U.api(`${cfg.apiBase}/auth/profile`, {
            method: 'PUT',
            body: JSON.stringify(payload),
        });
        profileSnapshot = {
            ...profileSnapshot,
            ...payload,
            cover: data.user?.cover ?? payload.cover ?? profileSnapshot?.cover,
        };
        updateUserLabels(data.user);
        updateAvatars(data.user?.cover);
        return data;
    }

    async function handleProfileSubmit(event) {
        event.preventDefault();

        const payload = {
            nom: getField('profileNom'),
            telephone: getField('profileTelephone') || null,
            titre: getField('profileTitre') || null,
            bio: getField('profileBio') || null,
            facebook: getField('profileFacebook') || null,
            youtube: getField('profileYoutube') || null,
            twitter: getField('profileTwitter') || null,
            instagram: getField('profileInstagram') || null,
        };

        try {
            U.showLoader('Enregistrement…');
            const data = await saveProfile(payload);
            U.hideLoader();
            U.showToast(data.message || 'Profil mis à jour', 'success');
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function openProfileCropper(imageDataUrl) {
        const modal = document.getElementById('profileCropperModal');
        const image = document.getElementById('profileCropperImage');
        if (!modal || !image || typeof Cropper === 'undefined') {
            U.showToast('Recadrage indisponible', 'error');
            return;
        }

        if (profileCropper) {
            profileCropper.destroy();
            profileCropper = null;
        }

        image.src = imageDataUrl;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        profileCropper = new Cropper(image, {
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

    function closeProfileCropper() {
        const modal = document.getElementById('profileCropperModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        if (profileCropper) {
            profileCropper.destroy();
            profileCropper = null;
        }
        profilePhotoFile = null;
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

    function applyProfileCrop() {
        if (!profileCropper || !profilePhotoFile) {
            U.showToast('Aucune image à traiter', 'warning');
            return;
        }

        const canvas = profileCropper.getCroppedCanvas({
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
                U.showLoader('Upload de la photo…');
                const url = await uploadProfileBlob(blob);
                const data = await saveProfile({ cover: url });
                closeProfileCropper();
                U.hideLoader();
                U.showToast(data.message || 'Photo de profil mise à jour', 'success');
            } catch (error) {
                U.hideLoader();
                U.showToast(error.message, 'error');
            }
        }, 'image/jpeg', 0.9);
    }

    async function deleteProfilePhoto() {
        if (!await U.confirm('Supprimer votre photo de profil ?', { confirmText: 'Supprimer' })) return;

        try {
            U.showLoader('Suppression…');
            const data = await saveProfile({ cover: '' });
            U.hideLoader();
            U.showToast(data.message || 'Photo supprimée', 'success');
        } catch (error) {
            U.hideLoader();
            U.showToast(error.message, 'error');
        }
    }

    function setupProfile() {
        if (profileReady) return;
        profileReady = true;

        document.getElementById('profileForm')?.addEventListener('submit', handleProfileSubmit);
        document.getElementById('profileDiscardBtn')?.addEventListener('click', () => applySnapshot(profileSnapshot));
        document.getElementById('profileBio')?.addEventListener('input', updateBioCounter);

        document.getElementById('profilePhotoBtn')?.addEventListener('click', () => {
            document.getElementById('profilePhotoInput')?.click();
        });

        document.getElementById('profilePhotoDeleteBtn')?.addEventListener('click', deleteProfilePhoto);

        document.getElementById('profilePhotoInput')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                U.showToast('Veuillez sélectionner une image valide', 'error');
                return;
            }

            profilePhotoFile = file;
            const reader = new FileReader();
            reader.onload = (e) => openProfileCropper(e.target?.result);
            reader.readAsDataURL(file);
        });

        document.getElementById('profileCropperClose')?.addEventListener('click', closeProfileCropper);
        document.getElementById('profileCropperCancel')?.addEventListener('click', closeProfileCropper);
        document.getElementById('profileCropperConfirm')?.addEventListener('click', applyProfileCrop);
        document.getElementById('profileCropperModal')?.addEventListener('click', (event) => {
            if (event.target.id === 'profileCropperModal') closeProfileCropper();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        setupProfile();

        window.addEventListener('dashboard:view', (event) => {
            if (event.detail?.view === 'profile') loadProfile({ skipIfCached: true });
        });

        if (document.getElementById('profile-view')?.classList.contains('active')) {
            loadProfile({ skipIfCached: true });
        }
    });

    window.DashboardProfile = {
        load: loadProfile,
        reload: () => {
            profileLoaded = false;
            return loadProfile();
        },
    };
})();
