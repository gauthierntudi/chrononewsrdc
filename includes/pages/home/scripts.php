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
  const GET_AD_URL = '/publication/ajax/get_ad.php';
  const TRACK_URL  = '/publication/ajax/track_ad.php';
  const DEFAULT_ROTATE_MS = 20000;

  // Tes dimensions (source de vérité front)
  const AD_SIZES = {
    rectangle:      { w: 672,  h: 560 },
    portrait:       { w: 512,  h: 562 },
    large_portrait: { w: 768,  h: 1024 },
    large_rectangle:{ w: 1024, h: 768 },
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

      if (!ad) {
        slot.innerHTML = '';
        slot.classList.remove('is-visible', 'is-fading');
        return;
      }

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

      requestAnimationFrame(() => slot.classList.add('is-visible'));
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

  // === GESTION DE LA PUBLICITÉ MODALE (Large Rectangle) ===
   async function checkAndShowModalAd() {
       // 1. Vérifier si on a déjà affiché le modal récemment (sessionStorage)
       const lastShown = sessionStorage.getItem('modalAdShown');
       const now = Date.now();
       const FIVE_MINUTES = 5 * 60 * 1000;

       if (lastShown) {
           const lastTime = parseInt(lastShown, 10);
           // Si affiché il y a moins de 5 minutes, on stop
           if (!isNaN(lastTime) && (now - lastTime < FIVE_MINUTES)) {
               // Pas de modal affiché -> démarrer vidéo flottante directement
               if (window.startFloatingVideoTimer) window.startFloatingVideoTimer();
               return;
           }
           // Sinon (plus de 5 min ou invalide), on continue (le sessionStorage sera écrasé)
       }

       const format = 'large_rectangle';
       const emplacement = 'pub-modal';
       
       try {
          // Utilise le même endpoint que les slots
          const url = `${GET_AD_URL}?format=${encodeURIComponent(format)}&emplacement=${encodeURIComponent(emplacement)}&_=${Date.now()}`;
          const res = await fetch(url, { cache: 'no-store' });
          const data = await res.json();
          
          if (data && data.ok && data.ad) {
              showModalAd(data.ad);
          } else {
              // Pas de pub disponible -> démarrer vidéo flottante directement
              if (window.startFloatingVideoTimer) window.startFloatingVideoTimer();
          }
      } catch (e) {
          console.error('Erreur chargement pub modal:', e);
          if (window.startFloatingVideoTimer) window.startFloatingVideoTimer();
      }
  }

  function showModalAd(ad) {
      // Création du HTML du modal
      const modalHtml = `
          <div id="ad-modal-overlay" style="
              position: fixed; top: 0; left: 0; width: 100%; height: 100%;
              background: rgba(0,0,0,0.7); z-index: 9999;
              display: flex; align-items: center; justify-content: center;
              opacity: 0; transition: opacity 0.3s ease;">
              
              <div id="ad-modal-content" style="
                  position: relative; background: transparent; 
                  max-width: 90%; max-height: 90%;
                  display: flex; flex-direction: column; align-items: center;
                  transform: scale(0.9); transition: transform 0.3s ease;">
                  
                  <div style="position: relative;">
                      <button id="ad-modal-close" style="
                          position: absolute; top: -40px; right: 0;
                          background: #666; border: none; color: white;
                          width: 40px; height: 30px; min-height: 30px; border-radius: 0; padding: 0; line-height: 30px;
                          font-size: 14px; cursor: not-allowed; font-weight: bold; font-family: sans-serif;
                          display: flex; align-items: center; justify-content: center;
                          z-index: 10; opacity: 1; transition: opacity 0.3s ease;">
                          <span id="ad-timer-val" style="display:inline-block; width:100%; text-align:center;">15s</span>
                          <span id="ad-close-text" style="display:none; margin-left:0; background:#00e600; color:#000; padding:0 10px; height:100%; align-items:center;">Fermer</span>
                      </button>
                      
                      <a href="${escapeAttr(ad.url)}" target="_blank" rel="sponsored noopener" data-ad-link="1">
                          <img src="${escapeAttr(ad.img)}" 
                               style="display: block; max-width: 600px; max-height: 60vh; width: 100%; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);"
                               alt="${escapeAttr(ad.title || 'Publicité')}" />
                      </a>
                  </div>
              </div>
          </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);

      const overlay = document.getElementById('ad-modal-overlay');
      const content = document.getElementById('ad-modal-content');
      const closeBtn = document.getElementById('ad-modal-close');
      const link = content.querySelector('a');

      // Gestion du compte à rebours
      let timeLeft = 15;
      closeBtn.disabled = true;
      const timerVal = document.getElementById('ad-timer-val');
      const closeText = document.getElementById('ad-close-text');
      
      // Gérer l'affichage au survol
      // NOTE: Le bouton est initialement masqué par opacity: 0 dans le CSS inline du bouton
      // On le rend visible au survol de la zone parente
      const showBtn = () => { closeBtn.style.opacity = '1'; };
      const hideBtn = () => { if(closeBtn.disabled) closeBtn.style.opacity = '0'; };
      
      // Zone sensible autour du bouton pour le hover (le bouton lui-même + un peu autour)
      // On attache l'event sur le parent (la div relative qui contient le bouton)
      if (closeBtn.parentElement) {
          closeBtn.parentElement.addEventListener('mouseenter', showBtn);
          closeBtn.parentElement.addEventListener('mouseleave', hideBtn);
      }
      
      // Force l'affichage initial si on veut qu'il soit visible tout le temps (mais la demande précédente était "au survol")
      // Si la demande actuelle est "s'afficher depuis le début" sans survol :
      closeBtn.style.opacity = '1'; 
      // Et on supprime les écouteurs de hover si on veut qu'il reste visible
      if (closeBtn.parentElement) {
          closeBtn.parentElement.removeEventListener('mouseenter', showBtn);
          closeBtn.parentElement.removeEventListener('mouseleave', hideBtn);
      }
      
      const countdownTimer = setInterval(() => {
          timeLeft--;
          if (timeLeft > 0) {
              // Ajoute le zéro devant si < 10 (ex: 09s, 08s)
              const displayTime = timeLeft < 10 ? `0${timeLeft}` : timeLeft;
              timerVal.textContent = displayTime + 's';
          } else {
              clearInterval(countdownTimer);
              closeBtn.disabled = false;
              closeBtn.style.cursor = 'pointer';
              closeBtn.style.opacity = '1'; // Rendre visible à la fin
              closeBtn.style.padding = '0'; // Reset padding pour le layout flex
              closeBtn.style.width = 'auto'; // Laisser le bouton s'agrandir pour contenir "Fermer"
              closeBtn.style.background = 'transparent'; // Fond transparent car géré par les enfants
              
              // Mise à jour visuelle : timer fond gris, bouton fermer vert
              timerVal.textContent = '00s';
              timerVal.style.background = '#666';
              timerVal.style.padding = '0 10px';
              timerVal.style.width = 'auto'; // Reset width fixe du timer
              timerVal.style.height = '100%';
              timerVal.style.display = 'inline-flex';
              timerVal.style.alignItems = 'center';
              
              closeText.style.display = 'inline-flex';
              
              // Désactiver les écouteurs de hover car le bouton doit rester visible
              closeBtn.parentElement.removeEventListener('mouseleave', hideBtn);
          }
      }, 1000);

      // Tracking vue
      postTrack(ad.id, 'view');

      // Tracking clic
      if (link) {
          link.addEventListener('click', () => {
              postTrack(ad.id, 'click');
              closeModal();
          });
      }

      // Affichage avec animation
      requestAnimationFrame(() => {
          overlay.style.opacity = '1';
          content.style.transform = 'scale(1)';
      });

      // Marquer comme vu pour la session (timestamp)
      const now = Date.now();
      sessionStorage.setItem('modalAdShown', now.toString());

      // Fermeture
      function closeModal() {
          // Ne rien faire si le bouton est désactivé (sauf si clic sur la pub qui ferme aussi)
          if (closeBtn.disabled && (event && event.target === closeBtn)) return;

          overlay.style.opacity = '0';
          content.style.transform = 'scale(0.9)';
          setTimeout(() => {
              overlay.remove();
          }, 300);

          // Démarrer la vidéo flottante après fermeture du modal
          if (window.startFloatingVideoTimer) {
              window.startFloatingVideoTimer();
          }
      }

      closeBtn.addEventListener('click', closeModal);
      // On empêche la fermeture au clic sur l'overlay tant que le timer n'est pas fini
      overlay.addEventListener('click', (e) => {
          if (e.target === overlay && !closeBtn.disabled) closeModal();
      });
  }

  // Logique de déclenchement : 
  // Soit au chargement (avec petit délai), soit après X secondes
  // Ici on combine : on lance le check après 2 secondes pour ne pas bloquer le rendu initial
  setTimeout(checkAndShowModalAd, 3000);

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
    var action = $form.data('action') || '/publication/ajax/newsletter_subscribe.php';

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
