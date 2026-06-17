<?php include dirname(__DIR__, 2).'/partials/ads-float.php'; ?>

<!-- Floating Video Ad -->
<div id="floatingVideoAd" class="floating-video-ad">
    <button class="close-video-ad" onclick="closeFloatingVideo()"><i class="bi bi-x-lg"></i></button>
    <div class="video-ad-wrapper">
        <div class="jl_ads_inner jl_ads_video ad-slot" data-emplacement="video-float" data-format="video-outstream"></div>
    </div>
</div>

<!-- Modal Ad avec Compteur -->
<div id="modalAdWithTimer" class="modal-ad-overlay" hidden aria-hidden="true">
    <div class="modal-ad-container">
        <div class="modal-ad-header">
            <span class="ad-label">Publicité</span>
            <div class="ad-timer-badge">Fermeture dans <span id="adCountdown">15</span>s</div>
            <button id="closeModalAdBtn" class="close-modal-ad" disabled onclick="closeAdModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-ad-body">
             <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="modal-popup" data-format="rectangle"></div>
        </div>
    </div>
</div>
