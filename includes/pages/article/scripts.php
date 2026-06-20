<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

$is_logged_in = (bool) ($is_logged_in ?? false);
$cnFrontBase = cn_site_url();

?>
<script>
    const FLOAT_CLOSE_TO_VIDEO_DELAY_MS = 50000;
    let floatCloseVideoTimer = null;

    function adSlotHasContent(slot) {
        if (!slot) return false;
        return !!slot.querySelector('[data-ad-link="1"], iframe, video, .ad-video-wrap');
    }

    function scheduleVideoAdAfterFloatClose() {
        if (sessionStorage.getItem('adModalDismissed') === '1' || floatCloseVideoTimer) {
            return;
        }

        const hasVideoContent = document.querySelector('.post_content iframe[src*="youtube"], .post_content iframe[src*="vimeo"], .post_content video, .elementor-widget-video');
        floatCloseVideoTimer = setTimeout(() => {
            floatCloseVideoTimer = null;
            triggerNextAd(hasVideoContent);
        }, FLOAT_CLOSE_TO_VIDEO_DELAY_MS);
    }

    window.scheduleVideoAdAfterFloatClose = scheduleVideoAdAfterFloatClose;

    function waitForAdSlot(slot, timeoutMs) {
        return new Promise((resolve) => {
            if (adSlotHasContent(slot)) {
                resolve(true);
                return;
            }
            const observer = new MutationObserver(() => {
                if (adSlotHasContent(slot)) {
                    observer.disconnect();
                    resolve(true);
                }
            });
            observer.observe(slot, { childList: true, subtree: true });
            setTimeout(() => {
                observer.disconnect();
                resolve(adSlotHasContent(slot));
            }, timeoutMs);
        });
    }

    // Chaîne publicitaire : uniquement si une pub est réellement chargée
    document.addEventListener('DOMContentLoaded', async function() {
        if (sessionStorage.getItem('adModalDismissed') === '1') {
            return;
        }

        const adsFloat = document.getElementById('adsFloat');
        const floatSlot = adsFloat ? adsFloat.querySelector('.ad-slot') : null;
        const hasVideoContent = document.querySelector('.post_content iframe[src*="youtube"], .post_content iframe[src*="vimeo"], .post_content video, .elementor-widget-video');

        const floatReady = floatSlot ? await waitForAdSlot(floatSlot, 6000) : false;
        const floatVisible = adsFloat && adsFloat.classList.contains('is-visible');

        if (floatReady && floatVisible) {
            return;
        }

        if (!floatReady) {
            setTimeout(() => triggerNextAd(hasVideoContent), 3000);
        }
    });

    async function tryOpenAdModal() {
        const modal = document.getElementById('modalAdWithTimer');
        if (!modal || sessionStorage.getItem('adModalDismissed') === '1') return;

        const slot = modal.querySelector('.ad-slot');
        if (!slot) return;

        if (!adSlotHasContent(slot)) {
            const loaded = await waitForAdSlot(slot, 5000);
            if (!loaded) return;
        }

        openAdModal();
    }

    function triggerNextAd(isVideoArticle) {
        if (sessionStorage.getItem('adModalDismissed') === '1') {
            return;
        }

        if (isVideoArticle) {
            tryOpenAdModal();
            return;
        }

        const floatVideo = document.getElementById('floatingVideoAd');
        const adSlot = floatVideo ? floatVideo.querySelector('.ad-slot') : null;

        if (floatVideo && adSlotHasContent(adSlot)) {
            floatVideo.style.display = 'block';
            return;
        }

        tryOpenAdModal();
    }

    let adTimerInterval;
    
    function openAdModal() {
        const modal = document.getElementById('modalAdWithTimer');
        if (!modal || sessionStorage.getItem('adModalDismissed') === '1') return;

        const slot = modal.querySelector('.ad-slot');
        if (!adSlotHasContent(slot)) {
            return;
        }

        modal.classList.add('active');
        modal.removeAttribute('hidden');
        modal.setAttribute('aria-hidden', 'false');
        
        let timeLeft = 15;
        const countdownEl = document.getElementById('adCountdown');
        const closeBtn = document.getElementById('closeModalAdBtn');
        
        if(countdownEl) countdownEl.textContent = timeLeft;
        if(closeBtn) {
            closeBtn.disabled = true;
            closeBtn.classList.remove('enabled');
        }
        
        if (adTimerInterval) clearInterval(adTimerInterval);
        
        adTimerInterval = setInterval(() => {
            timeLeft--;
            if(countdownEl) countdownEl.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(adTimerInterval);
                if(closeBtn) {
                    closeBtn.disabled = false;
                    closeBtn.classList.add('enabled');
                }
                if(countdownEl) countdownEl.parentElement.innerHTML = 'Vous pouvez fermer';
            }
        }, 1000);
    }
    
    function closeAdModal() {
        const modal = document.getElementById('modalAdWithTimer');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            modal.setAttribute('aria-hidden', 'true');
        }
        sessionStorage.setItem('adModalDismissed', '1');
        if (adTimerInterval) clearInterval(adTimerInterval);
    }
    
    // Fonction globale pour gérer le son
    window.toggleAdMute = function(overlay, type, videoId) {
        const icon = overlay.querySelector('i');
        
        if (type === 'local') {
            const container = overlay.parentElement;
            const video = container.querySelector('video');
            if (video) {
                video.muted = !video.muted;
                if (video.muted) {
                    icon.className = 'bi bi-volume-mute-fill';
                } else {
                    icon.className = 'bi bi-volume-up-fill';
                }
            }
        } else if (type === 'youtube') {
            // Pour YouTube, on masque l'overlay pour permettre l'interaction avec le player natif
            overlay.style.display = 'none';
         }
     };

     window.closeFloatingVideo = function() {
        const container = document.getElementById('floatingVideoAd');
        if (!container) return;
        
        container.style.display = 'none';
        
        // Arrêter la vidéo locale
        const video = container.querySelector('video');
        if (video) {
            video.pause();
        }
        
        // Arrêter l'iframe (YouTube)
        const iframe = container.querySelector('iframe');
        if (iframe) {
            // Vider le src arrête immédiatement la lecture
            iframe.src = '';
        }
        
        // Vider le contenu pour être sûr
        const slot = container.querySelector('.ad-slot');
        if (slot) slot.innerHTML = '';
     };
 </script>

<script>
    const lazyloadRunObserver = () => {
        const lazyloadBackgrounds = document.querySelectorAll( `.e-con.e-parent:not(.e-lazyloaded)` );
        const lazyloadBackgroundObserver = new IntersectionObserver( ( entries ) => {
            entries.forEach( ( entry ) => {
                if ( entry.isIntersecting ) {
                    let lazyloadBackground = entry.target;
                    if( lazyloadBackground ) {
                        lazyloadBackground.classList.add( 'e-lazyloaded' );
                    }
                    lazyloadBackgroundObserver.unobserve( entry.target );
                }
            });
        }, { rootMargin: '200px 0px 200px 0px' } );
        lazyloadBackgrounds.forEach( ( lazyloadBackground ) => {
            lazyloadBackgroundObserver.observe( lazyloadBackground );
        } );
    };
    const events = [
        'DOMContentLoaded',
        'elementor/lazyload/observe',
    ];
    events.forEach( ( event ) => {
        document.addEventListener( event, lazyloadRunObserver );
    } );
</script>
<script>
(function () {
  const GET_AD_URL = <?= json_encode(cn_ajax_url('get_ad'), JSON_UNESCAPED_SLASHES) ?>;
  const TRACK_URL  = <?= json_encode(cn_ajax_url('track_ad'), JSON_UNESCAPED_SLASHES) ?>;
  const NL_SUBSCRIBE_URL = <?= json_encode(cn_ajax_url('newsletter_subscribe'), JSON_UNESCAPED_SLASHES) ?>;
  const DEFAULT_ROTATE_MS = 20000;

  // Tes dimensions (source de vérité front)
  const AD_SIZES = {
    rectangle:      { w: 672,  h: 560 },
    portrait:       { w: 512,  h: 562 },
    paysage_small:  { w: 1456, h: 180 },
    paysage_medium: { w: 1920, h: 400 },
    paysage_large:  { w: 3456, h: 502 }
  };

  // Anti-doublon simultané par format (pour toute la page)
  const usedByFormat = {}; // {format: Set(ids)}
  function getExcludeCsv(format) {
    if (!usedByFormat[format]) usedByFormat[format] = new Set();
    return Array.from(usedByFormat[format]).join(',');
  }
  function markUsed(format, id) {
    if (!usedByFormat[format]) usedByFormat[format] = new Set();
    if (id) usedByFormat[format].add(id);
  }
  function unmarkUsed(format, id) {
    if (!usedByFormat[format]) usedByFormat[format] = new Set();
    if (id) usedByFormat[format].delete(id);
  }

  function escapeAttr(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');
  }

  function postTrack(adId, eventName) {
    if (!adId) return;

    const body = new URLSearchParams();
    body.set('ad_id', adId);
    body.set('event', eventName);

    if (navigator.sendBeacon) {
      const blob = new Blob([body.toString()], { type: 'application/x-www-form-urlencoded' });
      navigator.sendBeacon(TRACK_URL, blob);
      return;
    }

    fetch(TRACK_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
      keepalive: true
    }).catch(() => {});
  }

  function createSlotController(slot) {
    const format = slot.dataset.format || 'paysage_small';
    const emplacement = slot.dataset.emplacement || '';
    const rotateMs = Math.max(5000, parseInt(slot.dataset.rotate || DEFAULT_ROTATE_MS, 10));

    const size = AD_SIZES[format] || {};
    const w = size.w || '';
    const h = size.h || '';

    let currentAdId = null;
    let viewTrackedForCurrent = false;
    let observer = null;

    function setupObserver() {
      if (observer) observer.disconnect();

      observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (!currentAdId || viewTrackedForCurrent) return;
          if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
            viewTrackedForCurrent = true;
            postTrack(currentAdId, 'view');
          }
        });
      }, { threshold: [0.5] });

      observer.observe(slot);
    }

    function bindClick() {
      const link = slot.querySelector('[data-ad-link="1"]');
      if (!link) return;
      link.addEventListener('click', () => postTrack(currentAdId, 'click'));
    }

    function render(ad) {
      // libère l’ancienne pub du registre (anti-doublon simultané)
      unmarkUsed(format, currentAdId);

      currentAdId = ad?.id ?? null;
      markUsed(format, currentAdId);

      viewTrackedForCurrent = false;

      const isFloat = (emplacement === 'pub-float');
      const floatContainer = isFloat ? document.getElementById('adsFloat') : null;

      if (!ad) {
        slot.innerHTML = '';
        slot.classList.remove('is-visible', 'is-fading');
        if (floatContainer) {
          floatContainer.classList.remove('is-visible');
          floatContainer.setAttribute('aria-hidden', 'true');
        }
        return;
      }

      // Gestion spécifique pour la vidéo
      if (ad.format === 'video-outstream') {
          console.log('Rendu Video Outstream. URL:', ad.img);
          let videoContent = '';
          // Regex plus robuste pour YouTube (inclut shorts, embed, watch)
          const youtubeMatch = ad.img.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);

          if (youtubeMatch && youtubeMatch[1]) {
              const videoId = youtubeMatch[1];
              console.log('YouTube Video ID:', videoId);
              videoContent = `
                  <iframe 
                      id="yt-player-${videoId}"
                      src="https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=1&loop=1&playlist=${videoId}&modestbranding=1&showinfo=0&rel=0&enablejsapi=1" 
                      frameborder="0" 
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                      allowfullscreen
                      class="jl_ads_video"
                      style="width: 100%; height: 100%; border: none;">
                  </iframe>
                  <div class="video-click-overlay" onclick="window.toggleAdMute(this, 'youtube', '${videoId}')">
                       <div class="volume-icon"><i class="bi bi-volume-mute-fill"></i></div>
                  </div>`;
          } else {
              console.log('Video locale ou inconnue');
              videoContent = `
                  <video autoplay muted loop playsinline class="jl_ads_video" style="width: 100%; height: 100%; object-fit: cover;">
                      <source src="${escapeAttr(ad.img)}" type="video/mp4">
                      Votre navigateur ne supporte pas la vidéo.
                  </video>
                  <div class="video-click-overlay" onclick="window.toggleAdMute(this, 'local')">
                       <div class="volume-icon"><i class="bi bi-volume-mute-fill"></i></div>
                  </div>`;
          }

          slot.innerHTML = `
            <div class="ad-wrap ad-video-wrap">
              ${videoContent}
              <a href="${escapeAttr(ad.url)}" target="_blank" rel="sponsored noopener" class="ad-cta-btn">En savoir plus</a>
            </div>
          `;
      } else {
          // Rendu image standard
          slot.innerHTML = `
            <div class="ad-wrap">
              <a href="${escapeAttr(ad.url)}" target="_blank" rel="sponsored noopener" data-ad-link="1">
                <img loading="lazy" decoding="async"
                     class="jl_ads_img lazyload"
                     src="${escapeAttr(ad.img)}"
                     data-src="${escapeAttr(ad.img)}"
                     ${w ? `width="${escapeAttr(w)}"` : ''}
                     ${h ? `height="${escapeAttr(h)}"` : ''}
                     alt="${escapeAttr(ad.title || 'Sponsored')}" />
              </a>
            </div>
          `;
      }

      requestAnimationFrame(() => {
        slot.classList.add('is-visible');
        if (floatContainer) {
          floatContainer.classList.add('is-visible');
          floatContainer.setAttribute('aria-hidden', 'false');
        }
      });
      bindClick();
      setupObserver();
    }

    async function fetchAd() {
      const exclude = getExcludeCsv(format);
      const url = `${GET_AD_URL}?format=${encodeURIComponent(format)}&emplacement=${encodeURIComponent(emplacement)}&exclude=${encodeURIComponent(exclude)}&_=${Date.now()}`;
      const res = await fetch(url, { cache: 'no-store' });
      const data = await res.json();
      if (!data || !data.ok) return null;
      return data.ad;
    }

    async function loadWithFade() {
      try {
        // 1) On récupère d'abord une pub
        const ad = await fetchAd();

        // Si aucune pub retournée => on garde l'actuelle (pas de vide)
        if (!ad) return;

        // Si c'est la même pub que celle déjà affichée => on garde (pas de flicker)
        if (ad.id && ad.id === currentAdId) return;

        // 2) Fade out (sans vider)
        slot.classList.add('is-fading');
        slot.classList.remove('is-visible');

        // attendre fin du fade out
        await new Promise(r => setTimeout(r, 450));

        // 3) Swap (remplacement) puis fade in
        render(ad);
        slot.classList.remove('is-fading');
      } catch (e) {
        // En cas d'erreur réseau => garder l'affichage actuel
      }
    }

    let timer = null;
    function start() {
      loadWithFade();
      timer = setInterval(loadWithFade, rotateMs);
    }
    function stop() {
      if (timer) clearInterval(timer);
      timer = null;
      if (observer) observer.disconnect();
      observer = null;
      // libère aussi la pub si tu stop
      unmarkUsed(format, currentAdId);
      currentAdId = null;
    }

    return { start, stop };
  }

  // Init all slots
  const slots = Array.from(document.querySelectorAll('.ad-slot'));
  slots.forEach(slot => createSlotController(slot).start());

})();
</script>

<script>
  function toastDark(type, message, title) {

  const icons = {
    success: 'bi bi-check-circle-fill',
    info: 'bi bi-info-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill',
    error: 'bi bi-x-circle-fill'
  };

  const colors = {
    success: '#32c704',
    info: '#2490e2',
    warning: '#f2c94c',
    error: '#ed2228'
  };

  iziToast.show({
    class: 'iziToast-color-dark',
    theme: 'dark',
    title: title || '',
    message: message || '',

    backgroundColor: '#11112E',
    titleColor: '#ffffff',
    messageColor: '#ffffff',

    icon: icons[type] || icons.info,
    iconColor: '#ffffff',

    progressBar: true,
    progressBarColor: colors[type] || '#ffffff',

    position: 'topRight',
    timeout: 4500,
    close: true,

    transitionIn: 'fadeInDown',
    transitionOut: 'fadeOutUp'
  });
}
</script>

<script>
(function($){

  function setLoading($form, on){
    var $btn = $form.find('[type="submit"]');
    $btn.prop('disabled', !!on);
    $btn.toggleClass('is-loading', !!on);
  }

  function setMsg($form, type, text){
    var $box = $form.find('.wpcf7-response-output');
    if(!$box.length){
      $box = $('<div class="wpcf7-response-output"></div>').appendTo($form);
    }
    $box.show().attr('aria-hidden','false');

    $box.removeClass('is-success is-error is-info');
    if(type === 'success') $box.addClass('is-success');
    else if(type === 'error') $box.addClass('is-error');
    else $box.addClass('is-info');

    $box.text(text || '');
  }

  // ✅ Interception submit (même si CF7/Elementor est présent)
  $(document).on('submit', 'form.js-nl-form', function(e){
    e.preventDefault();
    e.stopPropagation();

    var $form = $(this);
    var action = $form.data('action') || (typeof NL_SUBSCRIBE_URL !== 'undefined' ? NL_SUBSCRIBE_URL : '/publication/ajax/newsletter-subscribe');

    // Serialize + source
    var payload = $form.serializeArray();
    if($form.data('source')){
      payload.push({ name:'source', value:String($form.data('source')) });
    }

    setLoading($form, true);
    setMsg($form, 'info', 'Envoi en cours...');

    $.ajax({
      url: action,
      method: 'POST',
      data: $.param(payload),
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .done(function(resp){
      if(resp && resp.ok){
        setMsg($form, 'success', resp.message || 'Abonnement confirmé. Merci !');
        toastDark('success', resp.message, 'Newsletter');
        $form[0].reset();
      } else {
        setMsg($form, 'error', (resp && resp.message) ? resp.message : 'Vérifiez l’e-mail et le consentement.');
        toastDark('error', resp.message, 'Newsletter');
      }
    })
    .fail(function(xhr){
      setMsg($form, 'error', 'Erreur serveur (' + xhr.status + '). Réessayez.');
    })
    .always(function(){
      setLoading($form, false);
    });

    return false;
  });

})(jQuery);
</script>

<!-- Floating Video Feature -->
<style>
#main-youtube-video.floating {
    position: fixed !important;
    bottom: 20px;
    right: 20px;
    width: 400px !important;
    height: 225px !important;
    z-index: 9999;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    border-radius: 12px;
    transition: all 0.3s ease;
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

#video-close-btn {
    display: none;
    position: fixed;
    z-index: 10000;
    width: 32px!important;
    height: 32px!important;
    background: rgba(0,0,0,0.8);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background 0.2s;
}

#video-close-btn.show {
    display: flex;
    bottom: calc(20px + 225px - 12px);
    right: 30px;
}

#video-close-btn:hover {
    background: rgba(255,0,0,0.9);
}

@media (max-width: 768px) {
    #main-youtube-video.floating {
        width: calc(100vw - 40px) !important;
        height: calc((100vw - 40px) * 0.5625) !important;
        right: 20px;
        bottom: 20px;
    }

    #video-close-btn.show {
        bottom: calc(20px + ((100vw - 40px) * 0.5625) - 42px);
    }
}
</style>

<script>
(function() {
    const mainWrapper = document.getElementById('main-video-wrapper');
    const mainVideo = document.getElementById('main-youtube-video');

    if (!mainWrapper || !mainVideo) return;

    let isFloating = false;
    let userClosed = false;
    let closeBtn = null;
    let originalParent = mainVideo.parentElement;
    let placeholder = null;

    function createCloseButton() {
        if (closeBtn) return;

        closeBtn = document.createElement('a');
        closeBtn.id = 'video-close-btn';
        closeBtn.innerHTML = '×';
        closeBtn.setAttribute('aria-label', 'Fermer la vidéo flottante');
        closeBtn.onclick = function() {
            hideFloatingVideo();
            userClosed = true;
        };

        document.body.appendChild(closeBtn);
    }

    function showFloatingVideo() {
        if (isFloating || userClosed) return;

        createCloseButton();

        // Créer un placeholder pour maintenir l'espace
        placeholder = document.createElement('div');
        placeholder.style.paddingBottom = '56.25%';
        placeholder.style.width = '100%';

        // Insérer le placeholder et déplacer la vidéo vers le body
        mainWrapper.insertBefore(placeholder, mainVideo);
        document.body.appendChild(mainVideo);

        mainVideo.classList.add('floating');
        mainVideo.style.top = 'auto';
        mainVideo.style.left = 'auto';
        if (closeBtn) closeBtn.classList.add('show');
        isFloating = true;
    }

    function hideFloatingVideo() {
        if (!isFloating) return;

        mainVideo.classList.remove('floating');
        if (closeBtn) closeBtn.classList.remove('show');

        // Remettre la vidéo à sa place originale
        if (placeholder && placeholder.parentElement) {
            mainWrapper.insertBefore(mainVideo, placeholder);
            placeholder.remove();
            placeholder = null;
        }

        mainVideo.style.top = '0';
        mainVideo.style.left = '0';
        isFloating = false;
    }

    function checkVideoPosition() {
        const rect = mainWrapper.getBoundingClientRect();
        const videoScrolledPast = rect.bottom < 0;

        if (videoScrolledPast && !userClosed) {
            showFloatingVideo();
        } else if (!videoScrolledPast && isFloating) {
            hideFloatingVideo();
        }
    }

    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(checkVideoPosition, 100);
    });

    window.addEventListener('resize', function() {
        checkVideoPosition();
    });

    checkVideoPosition();
})();
</script>
<script>
(function () {
  const ads = document.getElementById('adsFloat');
  if (!ads) return;

  if (ads.parentElement !== document.body) {
    document.body.appendChild(ads);
  }

  const closeBtn = ads.querySelector('.ads-float__close');
  if (!closeBtn) return;

  function show() {
    ads.classList.add('is-visible');
    ads.setAttribute('aria-hidden', 'false');
  }

  function hide() {
    ads.classList.remove('is-visible');
    ads.setAttribute('aria-hidden', 'true');
  }

  function closeFloatAd() {
    const slot = ads.querySelector('.ad-slot');
    const hadContent = !!slot?.querySelector('[data-ad-link="1"], iframe, video, .ad-video-wrap');
    const wasVisible = ads.classList.contains('is-visible');
    hide();
    if (hadContent && wasVisible) {
      window.scheduleVideoAdAfterFloatClose?.();
    }
  }

  // 🔥 Fonction globale demandée
  window.displayAdsFloat = function () {
    show();
  };

  closeBtn.addEventListener('click', function (e) {
      e.preventDefault();
      closeFloatAd();
    });

  // ESC pour fermer
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && ads.classList.contains('is-visible')) closeFloatAd();
  });
})();
</script>
    <script>
        const API_BASE = '/publication/api';
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const CN_FRONT_BASE = <?php echo json_encode($cnFrontBase, JSON_UNESCAPED_SLASHES); ?>;

        function showToast(message, type = 'success') {
            iziToast[type]({
                title: type === 'error' ? 'Erreur' : 'Succès',
                message: message,
                position: 'topRight'
            });
        }

        function initiatePurchase(articleId, price) {
            if (!isLoggedIn) {
                // Rediriger vers login/register avec return url sur le sous-domaine
                window.location.href = `${CN_FRONT_BASE}/connexion/?redirect=${encodeURIComponent(window.location.href)}&action=buy`;
                return;
            }
            openPaymentModal(articleId, price);
        }

        function redirectToSubscription() {
            if (!isLoggedIn) {
                // Si pas connecté, on redirige vers le login
                window.location.href = `${CN_FRONT_BASE}/connexion/?redirect=${encodeURIComponent(window.location.href)}`;
            } else {
                // Si connecté, on va vers la page d'abonnement sur le sous-domaine
                const returnUrl = encodeURIComponent(window.location.href);
                window.location.href = `${CN_FRONT_BASE}/dashboard/?view=subscriptions&return_url=${returnUrl}`;
            }
        }

        function openPaymentModal(articleId, price) {
            const modal = document.getElementById('paymentModal');
            document.getElementById('paymentArticleId').value = articleId;
            document.getElementById('paymentArticleId').setAttribute('data-amount', price);
            document.getElementById('paymentAmountDisplay').textContent = '$' + parseFloat(price).toFixed(2);
            modal.classList.add('active');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            modal.setAttribute('aria-hidden', 'true');
            sessionStorage.removeItem('paymentModalOpen');
            sessionStorage.removeItem('paymentArticleId');
            sessionStorage.removeItem('paymentOrderNumber');
        }

        function showLoginModal() {
             window.location.href = `${CN_FRONT_BASE}/connexion/?redirect=${encodeURIComponent(window.location.href)}`;
        }

        let paymentPollTimer = null;
        let paymentPollActive = false;
        let paymentCancelButtonTimer = null;

        function resetPaymentLoaderCancelButton() {
            const cancelBtn = document.getElementById('paymentLoaderCancelBtn');
            if (!cancelBtn) return;
            cancelBtn.hidden = true;
            cancelBtn.classList.remove('is-visible');
        }

        function showPaymentLoaderCancelButton() {
            const cancelBtn = document.getElementById('paymentLoaderCancelBtn');
            if (!cancelBtn || !paymentPollActive) return;
            cancelBtn.hidden = false;
            cancelBtn.classList.add('is-visible');
        }

        function showPaymentLoader() {
            paymentPollActive = true;
            const overlay = document.getElementById('paymentLoadingOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            overlay.removeAttribute('hidden');
            overlay.setAttribute('aria-hidden', 'false');
            resetPaymentLoaderCancelButton();
            clearTimeout(paymentCancelButtonTimer);
            paymentCancelButtonTimer = setTimeout(showPaymentLoaderCancelButton, 15000);
        }

        function hidePaymentLoader() {
            paymentPollActive = false;
            clearTimeout(paymentCancelButtonTimer);
            if (paymentPollTimer) {
                clearTimeout(paymentPollTimer);
                paymentPollTimer = null;
            }
            resetPaymentLoaderCancelButton();
            const overlay = document.getElementById('paymentLoadingOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            overlay.setAttribute('hidden', '');
            overlay.setAttribute('aria-hidden', 'true');
        }

        function cancelPaymentValidation() {
            hidePaymentLoader();
            sessionStorage.removeItem('paymentOrderNumber');
            window.__paymentPollErrors = 0;
            const submitBtn = document.getElementById('paymentForm')?.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Procédez au paiement';
            }
            showToast('Vérification annulée. Si vous avez validé sur votre téléphone, rechargez la page.', 'info');
        }
        window.cancelPaymentValidation = cancelPaymentValidation;

        function pollPaymentStatus(orderNumber, attempt = 1) {
            if (!paymentPollActive) {
                return;
            }

            if (attempt > 100) {
                hidePaymentLoader();
                sessionStorage.removeItem('paymentOrderNumber');
                showToast('Délai d\'attente dépassé. Veuillez vérifier vos messages ou réessayer.', 'warning');
                document.getElementById('paymentForm').querySelector('button[type="submit"]').disabled = false;
                document.getElementById('paymentForm').querySelector('button[type="submit"]').innerHTML = '<i class="fa-solid fa-lock"></i> Procédez au paiement';
                return;
            }

            const networkErrors = window.__paymentPollErrors || 0;
            const delay = networkErrors > 0 ? Math.min(12000, 3000 + networkErrors * 1000) : 3000;

            paymentPollTimer = setTimeout(async () => {
                if (!paymentPollActive) {
                    return;
                }

                try {
                    const url = `${API_BASE}/payments.php?action=check_status&orderNumber=${encodeURIComponent(orderNumber)}&attempt=${attempt}`;
                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    window.__paymentPollErrors = 0;

                    if (data.success && data.statut === 'reussi') {
                        // Paiement réussi
                        hidePaymentLoader();
                        sessionStorage.removeItem('paymentModalOpen');
                        sessionStorage.removeItem('paymentArticleId');
                        sessionStorage.removeItem('paymentOrderNumber');
                        
                        showToast('Paiement confirmé avec succès !', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else if (data.success && (data.statut === 'echoue' || data.statut === 'annule')) {
                        // Paiement échoué
                        hidePaymentLoader();
                        sessionStorage.removeItem('paymentOrderNumber');
                        showToast('Le paiement a échoué ou a été annulé.', 'error');
                        document.getElementById('paymentForm').querySelector('button[type="submit"]').disabled = false;
                        document.getElementById('paymentForm').querySelector('button[type="submit"]').innerHTML = '<i class="fa-solid fa-lock"></i> Procédez au paiement';
                    } else {
                        // Continuer d'attendre (statut 'en_attente' ou autre)
                        pollPaymentStatus(orderNumber, attempt + 1);
                    }
                } catch (error) {
                    window.__paymentPollErrors = (window.__paymentPollErrors || 0) + 1;
                    if (window.__paymentPollErrors === 3 || window.__paymentPollErrors === 8) {
                        showToast('Connexion instable. Vérification du paiement en cours…', 'warning');
                    }
                    pollPaymentStatus(orderNumber, attempt + 1);
                }
            }, delay);
        }

        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const articleId = document.getElementById('paymentArticleId').value;
            const montant = document.getElementById('paymentArticleId').getAttribute('data-amount');
            const methode = document.querySelector('input[name="methode"]:checked').value;
            const telephone = document.getElementById('paymentPhone').value;

            if(!telephone) {
                showToast('Veuillez entrer un numéro de téléphone', 'error');
                return;
            }

            const btn = e.target.querySelector('button[type="submit"]');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Traitement...';

            try {
                const response = await fetch(`${API_BASE}/payments.php?action=initiate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        article_id: articleId,
                        montant: montant,
                        methode: methode,
                        telephone: telephone
                    })
                });

                const data = await response.json();

                if(data.success) {
                    if(data.direct_success && data.orderNumber) {
                        // Paiement initié, on lance le polling
                        sessionStorage.setItem('paymentOrderNumber', data.orderNumber);
                        closePaymentModal(); // On ferme le modal de saisie
                        showPaymentLoader(); // On affiche l'overlay d'attente
                        pollPaymentStatus(data.orderNumber);
                    } else if(data.payment_url) {
                        // Redirection externe
                        sessionStorage.removeItem('paymentModalOpen'); 
                        window.location.href = data.payment_url;
                    } else {
                        // Cas fallback si pas d'orderNumber (ne devrait pas arriver avec direct_success)
                        showToast('Veuillez valider le paiement sur votre téléphone', 'info');
                        setTimeout(() => window.location.reload(), 5000);
                    }
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            } catch(error) {
                console.error(error);
                showToast('Erreur de communication', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });

        document.querySelectorAll('input[name="methode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const phoneInput = document.getElementById('paymentPhone');
                const placeholders = {
                    'carte_bancaire': 'Ex: 243820000000',
                    'mpesa': 'Ex: 0812345678 (Vodacom)',
                    'airtel_money': 'Ex: 0991234567 (Airtel)',
                    'orange_money': 'Ex: 0841234567 (Orange)'
                };
                if(placeholders[this.value]) phoneInput.placeholder = placeholders[this.value];
            });
        });

        // Ouverture automatique : uniquement retour connexion ou reprise paiement en cours
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const shouldOpenBuy = urlParams.get('action') === 'buy';
            const currentArticleId = <?php echo $article_id; ?>;
            const hasAccess = <?php echo $has_access ? 'true' : 'false'; ?>;

            // Flags obsolètes : le modal ne doit jamais se rouvrir tout seul
            sessionStorage.removeItem('paymentModalOpen');
            sessionStorage.removeItem('paymentArticleId');

            if (hasAccess) {
                sessionStorage.removeItem('paymentOrderNumber');
                return;
            }

            const pendingOrder = sessionStorage.getItem('paymentOrderNumber');
            if (pendingOrder) {
                showPaymentLoader();
                pollPaymentStatus(pendingOrder);
                return;
            }

            if (shouldOpenBuy && isLoggedIn) {
                const newUrl = window.location.pathname + window.location.hash;
                window.history.replaceState({}, document.title, newUrl);
                initiatePurchase(currentArticleId, <?php echo $article_price; ?>);
            }
        });
    </script>
