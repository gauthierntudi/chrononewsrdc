<form id="profileForm" class="profile-form">
    <div class="profile-photo-section">
        <h4><i data-lucide="image" class="lucide-icon" aria-hidden="true"></i> Photo de profil</h4>
        <div class="profile-photo-content">
            <div class="profile-photo-preview">
                <img src="{{ \App\Support\Media::url($user->avatar) }}" alt="Photo de profil" class="profile-photo-avatar" id="profileAvatar">
            </div>
            <div class="profile-photo-actions">
                <button type="button" class="btn-change-photo" id="profilePhotoBtn">
                    <i data-lucide="camera" class="lucide-icon" aria-hidden="true"></i>
                    Modifier la photo
                </button>
                <button type="button" class="btn-delete-photo" id="profilePhotoDeleteBtn">
                    Supprimer
                </button>
                <input type="file" id="profilePhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <div class="profile-section-title">
            <i data-lucide="user" class="lucide-icon" aria-hidden="true"></i>
            Informations personnelles
        </div>
        <div class="profile-meta-row">
            <span class="profile-meta-badge" id="profileRoleBadge">{{ $user->role?->label() }}</span>
            @if($user->user_number)
                <span class="profile-meta-badge profile-meta-badge--muted">N° {{ $user->user_number }}</span>
            @endif
        </div>
        <div class="profile-grid">
            <div class="form-group">
                <label for="profileNom">Nom complet *</label>
                <input type="text" id="profileNom" name="nom" required maxlength="255" placeholder="Votre nom complet">
            </div>
            <div class="form-group">
                <label for="profileEmail">Email</label>
                <input type="email" id="profileEmail" name="email" readonly>
            </div>
            <div class="form-group">
                <label for="profileTelephone">Téléphone</label>
                <input type="tel" id="profileTelephone" name="telephone" maxlength="50" placeholder="+243 XXX XXX XXX">
            </div>
            <div class="form-group">
                <label for="profileTitre">Titre / Profession</label>
                <input type="text" id="profileTitre" name="titre" maxlength="255" placeholder="Ex. Journaliste, Administrateur…">
            </div>
        </div>
        <div class="form-group">
            <label for="profileBio">Biographie</label>
            <textarea id="profileBio" name="bio" rows="3" maxlength="200" placeholder="Parlez-nous un peu de vous…"></textarea>
            <div class="profile-char-counter"><span id="profileBioCount">0</span> / 200</div>
        </div>
    </div>

    <div class="profile-section">
        <div class="profile-section-title">
            <i data-lucide="share-2" class="lucide-icon" aria-hidden="true"></i>
            Réseaux sociaux
        </div>
        <div class="profile-grid">
            <div class="form-group">
                <label for="profileFacebook"><i data-lucide="facebook" class="lucide-icon" aria-hidden="true"></i> Facebook</label>
                <input type="url" id="profileFacebook" name="facebook" maxlength="500" placeholder="https://facebook.com/…">
            </div>
            <div class="form-group">
                <label for="profileYoutube"><i data-lucide="youtube" class="lucide-icon" aria-hidden="true"></i> YouTube</label>
                <input type="url" id="profileYoutube" name="youtube" maxlength="500" placeholder="https://youtube.com/@…">
            </div>
            <div class="form-group">
                <label for="profileTwitter"><i data-lucide="twitter" class="lucide-icon" aria-hidden="true"></i> Twitter / X</label>
                <input type="url" id="profileTwitter" name="twitter" maxlength="500" placeholder="https://twitter.com/…">
            </div>
            <div class="form-group">
                <label for="profileInstagram"><i data-lucide="instagram" class="lucide-icon" aria-hidden="true"></i> Instagram</label>
                <input type="url" id="profileInstagram" name="instagram" maxlength="500" placeholder="https://instagram.com/…">
            </div>
        </div>
    </div>

    <div class="form-actions profile-form-actions">
        <button type="button" class="btn-discard" id="profileDiscardBtn">Annuler</button>
        <button type="submit" class="btn btn-primary" id="profileSaveBtn">
            <i data-lucide="save" class="lucide-icon" aria-hidden="true"></i>
            Enregistrer
        </button>
    </div>
</form>
