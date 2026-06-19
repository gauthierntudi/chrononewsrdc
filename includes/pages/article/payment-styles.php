<style>
    /* Styles du Modal Custom - Design Exact */
    .custom-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 100000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif; /* Assurer une font moderne */
    }

    .custom-modal[hidden] {
        display: none !important;
    }
    
    .custom-modal.active {
        display: flex !important;
        opacity: 1;
        visibility: visible;
    }
    
    .custom-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6); /* Fond foncé */
        backdrop-filter: blur(5px);
    }
    
    .custom-modal-container {
        background: white;
        width: 90%;
        max-width: 480px;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
        transform: translateY(20px);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        padding: 40px;
    }
    
    .custom-modal.active .custom-modal-container {
        transform: translateY(0);
    }
    
    .price-display {
        font-size: 64px;
        font-weight: 900;
        color: #1e293b;
        text-align: center;
        margin-bottom: 10px;
        letter-spacing: -2px;
        line-height: 1;
    }
    
    .payment-context {
        text-align: center;
        font-size: 16px;
        color: #475569;
        margin-bottom: 5px;
    }
    
    .context-highlight {
        font-weight: 700;
        color: #ef4444; /* Rouge comme sur l'image pour le prix/info */
    }
    
    .payment-instruction {
        text-align: center;
        font-size: 14px;
        color: #94a3b8;
        margin-bottom: 30px;
    }
    
    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .payment-method-card {
        position: relative;
        cursor: pointer;
    }
    
    .payment-method-card input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .method-content {
        border: 2px solid #e2e8f0; /* Bordure grise par défaut */
        border-radius: 16px;
        aspect-ratio: 1/1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        transition: all 0.2s;
        background: white;
    }
    
    .method-content img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px; /* Coins arrondis pour les logos */
    }
    
    /* Style sélectionné : bordure bleue épaisse */
    .payment-method-card input:checked + .method-content {
        border: 3px solid #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    
    .form-input {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #cbd5e1; /* Gris plus visible */
        border-radius: 20px; /* Arrondi plus prononcé */
        font-size: 16px;
        color: #1e293b;
        outline: none;
        text-align: center;
        margin-bottom: 8px;
        transition: all 0.2s ease-in-out;
        background-color: #ffffff;
    }
    
    .form-input:hover {
        border-color: #94a3b8;
    }
    
    .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
    
    .input-helper {
        text-align: center;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 30px;
    }
    
    .submit-btn {
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
        transition: transform 0.1s;
    }
    
    .btn-primary {
        background: #3ec106; /* Vert vif */
        color: white;
        box-shadow: 0 4px 6px -1px rgba(62, 193, 6, 0.2);
    }
    
    .btn-primary:hover {
        background: #32a802;
    }
    
    .btn-secondary {
        background: #64748b; /* Gris foncé */
        color: white;
    }
    
    .btn-secondary:hover {
        background: #475569;
    }
    
    .submit-btn:active {
        transform: scale(0.98);
    }
    
    .secure-checkout {
        text-align: center;
        color: #2563eb;
        font-size: 13px;
        font-weight: 600;
        margin-top: 20px;
    }

    /* Overlay Loading */
    .payment-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 100001;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        transition: opacity 0.3s ease;
    }

    .payment-loader-overlay[hidden] {
        display: none !important;
    }
    
    .payment-loader-overlay.flex {
        display: flex !important;
    }
    
    .loader-content {
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 90%;
        border: 1px solid #e2e8f0;
    }
    
    .loader-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid #e2e8f0;
        border-top: 5px solid #3b82f6;
        border-radius: 50%;
        margin: 0 auto 24px;
        animation: spin 1s linear infinite;
    }
    
    .loader-title {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }
    
    .loader-desc {
        color: #64748b;
        font-size: 15px;
        line-height: 1.5;
        margin-bottom: 24px;
    }
    
    .loader-status {
        display: inline-block;
        padding: 8px 16px;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        animation: pulse 2s infinite;
    }

    .loader-cancel-btn {
        display: none;
        margin-top: 24px;
        width: 100%;
        padding: 14px 20px;
        border-radius: 12px;
        border: none;
        background-color: #64748b !important;
        color: #ffffff !important;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        height: auto;
        text-transform: none;
        letter-spacing: normal;
        transition: background-color 0.2s;
    }

    .loader-cancel-btn.is-visible {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loader-cancel-btn:hover {
        background-color: #475569 !important;
        color: #ffffff !important;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    /* --- MODE SOMBRE --- */
    body.options_dark_skin .payment-loader-overlay {
        background: rgba(15, 23, 42, 0.95);
    }
    
    body.options_dark_skin .loader-content {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    
    body.options_dark_skin .loader-spinner {
        border-color: #334155;
        border-top-color: #3b82f6;
    }
    
    body.options_dark_skin .loader-title {
        color: #ffffff;
    }
    
    body.options_dark_skin .loader-desc {
        color: #94a3b8;
    }
    
    body.options_dark_skin .loader-status {
        background: #1e3a8a;
        color: #60a5fa;
    }

    body.options_dark_skin .loader-cancel-btn {
        background-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.options_dark_skin .loader-cancel-btn:hover {
        background-color: #475569 !important;
        color: #ffffff !important;
    }

    body.options_dark_skin .custom-modal-container {
        background: #1e293b;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    
    body.options_dark_skin .custom-modal-overlay {
        background: rgba(0, 0, 0, 0.8);
    }

    body.options_dark_skin .price-display {
        color: #ffffff;
    }

    body.options_dark_skin .payment-context {
        color: #94a3b8;
    }
    
    body.options_dark_skin .context-highlight {
        color: #f87171;
    }

    body.options_dark_skin .payment-instruction {
        color: #64748b;
    }

    body.options_dark_skin .method-content {
        background: #0f172a;
        border-color: #334155;
    }

    body.options_dark_skin .payment-method-card input:checked + .method-content {
        border-color: #3b82f6;
        background: #1e3a8a; /* Bleu très foncé */
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    body.options_dark_skin .form-input {
        background-color: #0f172a;
        border-color: #334155;
        color: #ffffff;
    }

    body.options_dark_skin .form-input:hover {
        border-color: #475569;
    }

    body.options_dark_skin .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
    }

    body.options_dark_skin .btn-secondary {
        background: #334155;
        color: #e2e8f0;
    }

    body.options_dark_skin .btn-secondary:hover {
        background: #475569;
    }
    
    body.options_dark_skin .secure-checkout {
        color: #60a5fa;
    }
    
    /* Overlay Lock Premium en Dark Mode */
    body.options_dark_skin .premium-lock-card {
        background: #1e293b;
        border-color: #334155;
    }
    
    body.options_dark_skin .premium-lock-card h3 {
        color: #ffffff !important;
    }
    
    body.options_dark_skin .premium-lock-card p {
        color: #94a3b8 !important;
    }
    
    body.options_dark_skin .premium-lock-icon {
        background: rgba(209, 24, 16, 0.18);
        color: #f87171;
    }

    body.options_dark_skin .premium-lock-title {
        color: #ffffff !important;
    }

    body.options_dark_skin .premium-lock-desc,
    body.options_dark_skin .premium-lock-login {
        color: #94a3b8 !important;
    }

    body.options_dark_skin .btn-buy {
        background: var(--cn-blue);
        color: #ffffff;
    }

    body.options_dark_skin .btn-buy:hover {
        background: #184bcc;
    }

    body.options_dark_skin .btn-sub {
        background: var(--cn-red);
        color: #ffffff;
    }

    body.options_dark_skin .btn-sub:hover {
        background: var(--cn-red-hover);
    }
</style>
