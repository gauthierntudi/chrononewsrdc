<style>
    /* Floating Video Ad */
    .floating-video-ad {
        position: fixed;
        bottom: 20px;
        right: 20px; /* Positionné à droite */
        width: 320px; /* Un peu plus large */
        height: 180px;
        z-index: 9999;
        background: #000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        border-radius: 12px;
        overflow: hidden;
        display: none; /* Caché par défaut */
        animation: slideInRight 0.5s ease-out;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .close-video-ad {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(0,0,0,0.6);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        width: 28px;
        height: 28px;
        border-radius: 50%; /* Parfaitement rond */
        cursor: pointer;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
        padding: 0;
    }
    
    .close-video-ad:hover {
        background: #ef4444;
        border-color: #ef4444;
        transform: scale(1.1);
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .video-ad-wrapper {
        width: 100%;
        height: 100%;
        display: block;
        position: relative;
        background: #000;
        overflow: hidden;
    }
    
    /* Force l'iframe à tout remplir */
    .video-ad-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .jl_ads_video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .ad-overlay-text {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 2px 6px;
        font-size: 10px;
        border-radius: 4px;
        pointer-events: none;
        z-index: 20;
    }
    
    .video-click-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        background: transparent;
        cursor: pointer;
    }

    .volume-icon {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: rgba(0,0,0,0.6);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        pointer-events: none; /* Le clic est géré par l'overlay */
    }
    
    .ad-cta-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: #3b82f6;
        color: white;
        padding: 4px 12px;
        font-size: 12px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        z-index: 20;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .ad-cta-btn:hover {
        background: #2563eb;
        color: white;
    }

    /* Modal Ad */
    .modal-ad-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85);
        z-index: 100002; /* Au-dessus de tout */
        display: none;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }
    
    .modal-ad-overlay.active {
        display: flex;
    }
    
    .modal-ad-container {
        background: white;
        padding: 0; /* Supprimé le padding global pour gérer header/body */
        border-radius: 12px;
        max-width: 500px; /* Largeur max plus standard */
        width: 90%;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden; /* Pour les coins arrondis */
    }
    
    .modal-ad-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .ad-label {
        font-size: 14px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .modal-ad-body {
        min-height: 250px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    /* Dark Mode Support */
    body.options_dark_skin .modal-ad-container {
        background: #1e293b;
        border: 1px solid #334155;
    }
    
    body.options_dark_skin .modal-ad-header {
        background: #0f172a;
        border-bottom-color: #334155;
    }
    
    body.options_dark_skin .modal-ad-body {
        background: #1e293b;
    }
    
    body.options_dark_skin .ad-label {
        color: #94a3b8;
    }
</style>
