<!-- Modal Paiement Custom -->
<div id="paymentModal" class="custom-modal" hidden aria-hidden="true">
    <div class="custom-modal-overlay"></div>
    <div class="custom-modal-container">
        <div class="custom-modal-body">
            
            <div class="price-display" id="paymentAmountDisplay">$0.00</div>
            
            <p class="payment-context">
                Achat Article : <span class="context-highlight"><?php echo htmlspecialchars($currentArticle['titre']); ?></span>
            </p>
            
            <p class="payment-instruction">
                Sélectionnez votre méthode de paiement préférée
            </p>

            <form id="paymentForm" class="payment-form">
                <input type="hidden" id="paymentArticleId">
                
                <div class="payment-methods-grid">
                    <label class="payment-method-card">
                        <input type="radio" name="methode" value="carte_bancaire" checked>
                        <div class="method-content">
                            <img src="/publication/img/card.jpg" alt="Visa Mastercard">
                        </div>
                    </label>
                    <label class="payment-method-card">
                        <input type="radio" name="methode" value="mpesa">
                        <div class="method-content">
                            <img src="/publication/img/mpesa01.jpg" alt="M-Pesa">
                        </div>
                    </label>
                    <label class="payment-method-card">
                        <input type="radio" name="methode" value="airtel_money">
                        <div class="method-content">
                            <img src="/publication/img/airtel.jpg" alt="Airtel">
                        </div>
                    </label>
                    <label class="payment-method-card">
                        <input type="radio" name="methode" value="orange_money">
                        <div class="method-content">
                            <img src="/publication/img/orange3.jpg" alt="Orange">
                        </div>
                    </label>
                </div>

                <div class="form-group" id="phoneFieldContainer">
                    <input type="tel" id="paymentPhone" class="form-input" placeholder="Ex: 243820000000 ou 081XXXXXXX (M-Pesa)">
                    <p class="input-helper">Format: Code pays + numéro (ex: 243820000000)</p>
                </div>

                <button type="submit" class="submit-btn btn-primary">
                    <i class="fa-solid fa-lock"></i> Procédez au paiement
                </button>
                
                <button type="button" class="submit-btn btn-secondary" onclick="closePaymentModal()">
                    Annuler
                </button>
                
                <p class="secure-checkout">Guaranteed safe & secure checkout</p>
            </form>
        </div>
    </div>
</div>

<!-- Overlay Loading Paiement -->
<div id="paymentLoadingOverlay" class="payment-loader-overlay" hidden aria-hidden="true">
    <div class="loader-content">
        <div class="loader-spinner"></div>
        <h3 class="loader-title">Validation en cours...</h3>
        <p class="loader-desc">Veuillez valider la transaction sur votre téléphone. Ne fermez pas cette page.</p>
        <div class="loader-status">En attente de confirmation...</div>
        <button type="button" id="paymentLoaderCancelBtn" class="loader-cancel-btn" hidden onclick="cancelPaymentValidation()">
            Annuler
        </button>
    </div>
</div>
