<div id="subscriptionPaymentModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="subscriptionPaymentModalTitle">
    <div class="modal-content payment-modal-content">
        <button type="button" class="modal-close" id="subscriptionPaymentModalClose" aria-label="Fermer">&times;</button>
        <div id="subscriptionPaymentLoader" class="payment-loader-overlay is-hidden" hidden style="display: none;">
            <div class="payment-loader-content">
                <div class="spinner"></div>
                <p class="loader-text">Validation en cours…</p>
                <p class="loader-subtext">Validez la transaction sur votre téléphone. Ne quittez pas cette page.</p>
            </div>
        </div>
        <div class="modal-body">
            <h3 id="subscriptionPaymentModalTitle" class="visually-hidden">Paiement abonnement</h3>
            <div class="payment-amount" id="subscriptionPaymentAmountDisplay">$0.00</div>
            <div class="payment-description">
                <p id="subscriptionPaymentDescription">Abonnement</p>
            </div>
            <div class="payment-subtitle">Sélectionnez votre méthode de paiement préférée</div>
            <form id="subscriptionPaymentForm">
                <input type="hidden" id="subscriptionPaymentPlanId" value="">
                <input type="hidden" id="subscriptionPaymentAmount" value="">
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="subscriptionPaymentMethod" value="carte_bancaire" checked>
                        <div class="payment-method-content">
                            <img src="/assets/img/pictos/card.jpg" alt="Carte Bancaire" class="payment-method-img">
                        </div>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="subscriptionPaymentMethod" value="mpesa">
                        <div class="payment-method-content">
                            <img src="/assets/img/pictos/mpesa01.jpg" alt="M-Pesa" class="payment-method-img">
                        </div>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="subscriptionPaymentMethod" value="airtel_money">
                        <div class="payment-method-content">
                            <img src="/assets/img/pictos/airtel.jpg" alt="Airtel Money" class="payment-method-img">
                        </div>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="subscriptionPaymentMethod" value="orange_money">
                        <div class="payment-method-content">
                            <img src="/assets/img/pictos/orange3.jpg" alt="Orange Money" class="payment-method-img">
                        </div>
                    </label>
                </div>
                <div class="phone-field-container" id="subscriptionPhoneFieldContainer">
                    <input type="tel" id="subscriptionPaymentPhone" placeholder="Ex: 243820000000 ou 081XXXXXXX" class="payment-phone-input" required>
                    <p class="phone-field-hint">Format : code pays + numéro (ex: 243820000000)</p>
                </div>
                <div class="payment-actions">
                    <button type="submit" class="btn btn-payment-primary">
                        <i data-lucide="lock" class="lucide-icon" aria-hidden="true"></i>
                        Procédez au paiement
                    </button>
                    <button type="button" class="btn btn-payment-cancel" id="subscriptionPaymentCancel">Annuler</button>
                </div>
                <div class="payment-security">Guaranteed safe &amp; secure checkout</div>
            </form>
        </div>
    </div>
</div>
