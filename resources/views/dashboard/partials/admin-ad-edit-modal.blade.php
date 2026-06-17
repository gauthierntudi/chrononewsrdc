<div id="adminAdEditModal" class="modal app-dialog-modal user-ad-modal" aria-hidden="true" role="dialog" aria-labelledby="adminAdEditModalTitle">
    <div class="modal-content user-ad-modal__content">
        <div class="user-ad-modal__header">
            <h3 id="adminAdEditModalTitle">Modifier la publicité</h3>
            <button type="button" class="user-ad-modal__close" id="adminAdEditModalClose" aria-label="Fermer">
                <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
            </button>
        </div>
        <form id="adminAdEditForm" class="user-ad-form">
            <input type="hidden" id="adminAdEditId" value="">
            <p class="app-dialog__hint" id="adminAdEditSubtitle">Modifiez les informations de la campagne.</p>

            <label class="user-ad-form__field" for="adminAdEditTitle">
                <span>Titre de la campagne *</span>
                <input type="text" id="adminAdEditTitle" required maxlength="255">
            </label>

            <label class="user-ad-form__field" for="adminAdEditPlacement">
                <span>Emplacement *</span>
                <select id="adminAdEditPlacement" required>
                    <option value="">Sélectionner un emplacement</option>
                    <option value="pub-header">Header (Paysage Large)</option>
                    <option value="pub-modal">Modal (Grand rectangle)</option>
                    <option value="pub-float">Flottant (Portrait)</option>
                    <option value="pub-body-1">Corps 1 (Paysage S)</option>
                    <option value="pub-body-2">Corps 2 (Paysage M)</option>
                    <option value="pub-body-3">Corps 3 (Paysage S)</option>
                    <option value="pub-body-sidebar-1">Sidebar 1 (Grand portrait)</option>
                    <option value="pub-body-sidebar-2">Sidebar 2 (Rectangle)</option>
                    <option value="pub-footer">Footer (Paysage L)</option>
                </select>
            </label>

            <label class="user-ad-form__field" for="adminAdEditFormat">
                <span>Format (automatique)</span>
                <select id="adminAdEditFormat" disabled></select>
                <span class="user-ad-form__help" id="adminAdEditFormatHelp"></span>
            </label>

            <div class="user-ad-form__lock-field">
                <label class="user-ad-form__lock-label" for="adminAdEditIsLocked">
                    <span class="switch user-ad-form__switch">
                        <input type="checkbox" id="adminAdEditIsLocked" value="1">
                        <span class="slider round"></span>
                    </span>
                    <span class="user-ad-form__lock-text">Verrouiller cet emplacement pour cette publicité uniquement</span>
                </label>
            </div>

            <div class="user-ad-form__field">
                <span>Bannière publicitaire *</span>
                <div class="user-ad-upload-zone" id="adminAdEditUploadZone" role="button" tabindex="0" aria-label="Choisir une image">
                    <div class="user-ad-upload-zone__placeholder" id="adminAdEditUploadPlaceholder">
                        <span class="user-ad-upload-zone__glyph" aria-hidden="true">
                            <i data-lucide="cloud-upload" class="lucide-icon user-ad-upload-zone__icon"></i>
                        </span>
                        <p>Cliquez pour choisir une image</p>
                    </div>
                    <img id="adminAdEditUploadPreview" class="user-ad-upload-zone__preview" alt="Aperçu bannière" hidden>
                </div>
                <input type="file" id="adminAdEditImageInput" accept="image/*" class="user-ad-form__file-input" hidden>
            </div>

            <label class="user-ad-form__field" for="adminAdEditTargetUrl">
                <span>URL de destination *</span>
                <input type="url" id="adminAdEditTargetUrl" required placeholder="https://example.com">
            </label>

            <div class="user-ad-form__dates">
                <label class="user-ad-form__field" for="adminAdEditStartsAt">
                    <span>Date de début *</span>
                    <input type="text" id="adminAdEditStartsAt" required placeholder="jj/mm/aaaa" autocomplete="off">
                </label>
                <label class="user-ad-form__field" for="adminAdEditEndsAt">
                    <span>Date de fin *</span>
                    <input type="text" id="adminAdEditEndsAt" required placeholder="jj/mm/aaaa" autocomplete="off">
                </label>
            </div>

            <div class="user-ad-modal__footer">
                <button type="button" class="btn btn-secondary" id="adminAdEditCancel">Annuler</button>
                <button type="submit" class="btn btn-primary" id="adminAdEditSubmit">
                    <i data-lucide="check" class="lucide-icon" aria-hidden="true"></i>
                    <span>Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>
