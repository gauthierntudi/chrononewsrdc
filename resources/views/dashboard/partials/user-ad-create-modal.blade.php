<div id="userAdModal" class="modal app-dialog-modal user-ad-modal" aria-hidden="true" role="dialog" aria-labelledby="userAdModalTitle">
    <div class="modal-content user-ad-modal__content">
        <div class="user-ad-modal__header">
            <h3 id="userAdModalTitle">Nouvelle publicité</h3>
            <button type="button" class="user-ad-modal__close" id="userAdModalClose" aria-label="Fermer">
                <i data-lucide="x" class="lucide-icon" aria-hidden="true"></i>
            </button>
        </div>
        <form id="userAdForm" class="user-ad-form">
            <input type="hidden" id="userAdId" value="">
            <label class="user-ad-form__field" for="userAdTitle">
                <span>Titre de la campagne *</span>
                <input type="text" id="userAdTitle" required maxlength="255">
            </label>

            @if($access['globalAds'] ?? false)
            <label class="user-ad-form__field" for="userAdPlacement">
                <span>Emplacement *</span>
                <select id="userAdPlacement" required>
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
                <span class="user-ad-form__help user-ad-form__help--error" id="userAdPlacementError" hidden></span>
            </label>
            @endif

            <label class="user-ad-form__field" for="userAdFormat">
                <span>Format @if($access['globalAds'] ?? false)(automatique)@else*@endif</span>
                <select id="userAdFormat" @if($access['globalAds'] ?? false) disabled @else required @endif></select>
                <span class="user-ad-form__help" id="userAdFormatHelp"></span>
            </label>

            @if($access['globalAds'] ?? false)
            <div class="user-ad-form__lock-field">
                <label class="user-ad-form__lock-label" for="userAdIsLocked">
                    <span class="switch user-ad-form__switch">
                        <input type="checkbox" id="userAdIsLocked" value="1">
                        <span class="slider round"></span>
                    </span>
                    <span class="user-ad-form__lock-text">Verrouiller cet emplacement pour cette publicité uniquement</span>
                </label>
            </div>
            @endif

            <div class="user-ad-form__field">
                <span>Bannière publicitaire *</span>
                <div class="user-ad-upload-zone" id="userAdUploadZone" role="button" tabindex="0" aria-label="Choisir une image">
                    <div class="user-ad-upload-zone__placeholder" id="userAdUploadPlaceholder">
                        <span class="user-ad-upload-zone__glyph" aria-hidden="true">
                            <i data-lucide="cloud-upload" class="lucide-icon user-ad-upload-zone__icon"></i>
                        </span>
                        <p>Cliquez pour choisir une image</p>
                    </div>
                    <img id="userAdUploadPreview" class="user-ad-upload-zone__preview" alt="Aperçu bannière" hidden>
                </div>
                <input type="file" id="userAdImageInput" accept="image/*" class="user-ad-form__file-input" hidden>
            </div>
            <label class="user-ad-form__field" for="userAdTargetUrl">
                <span>URL de destination *</span>
                <input type="url" id="userAdTargetUrl" required placeholder="https://example.com">
                <span class="user-ad-form__help">L'URL vers laquelle les utilisateurs seront redirigés</span>
            </label>
            <div class="user-ad-form__dates">
                <label class="user-ad-form__field" for="userAdStartsAt">
                    <span>Date de début *</span>
                    <input type="text" id="userAdStartsAt" required placeholder="jj/mm/aaaa" autocomplete="off">
                </label>
                <label class="user-ad-form__field" for="userAdEndsAt">
                    <span>Date de fin *</span>
                    <input type="text" id="userAdEndsAt" required placeholder="jj/mm/aaaa" autocomplete="off">
                </label>
            </div>
            <div class="user-ad-pricing-info" id="userAdPricingInfo" hidden>
                <div class="user-ad-pricing-info__inner">
                    <div>
                        <div class="user-ad-pricing-info__label">Montant à payer</div>
                        <div class="user-ad-pricing-info__amount" id="userAdPriceAmount">0 USD</div>
                        <div class="user-ad-pricing-info__duration" id="userAdPriceDuration"></div>
                    </div>
                    <i data-lucide="banknote" class="lucide-icon user-ad-pricing-info__icon" aria-hidden="true"></i>
                </div>
            </div>
            <p class="user-ad-form__price user-ad-form__price--free" id="userAdPriceHint" hidden></p>
            <div class="user-ad-modal__footer">
                <button type="button" class="btn btn-secondary" id="userAdCancel">Annuler</button>
                <button type="submit" class="btn btn-primary" id="userAdSubmit">
                    <i data-lucide="check" class="lucide-icon" aria-hidden="true"></i>
                    <span id="userAdSubmitLabel">Créer la publicité</span>
                </button>
            </div>
        </form>
    </div>
</div>
