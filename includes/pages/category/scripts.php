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
(function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    function initScrollAnimations() {
        const elements = document.querySelectorAll('.jl_sidebar_w > div, .jl_cgrid_layout');
        elements.forEach(el => {
            if (!el.classList.contains('revealed')) {
                el.classList.add('scroll-reveal');
                observer.observe(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollAnimations);
    } else {
        initScrollAnimations();
    }

    document.querySelectorAll('.jl_clist_layout, .jl_cgrid_layout').forEach((card, index) => {
        card.style.setProperty('--card-index', index);
    });

    const goTopBtn = document.querySelector('#go-top');
    if (goTopBtn) {
        goTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
})();
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

      // Gestion spécifique pour le conteneur flottant
      const isFloat = (emplacement === 'pub-float');
      const floatContainer = isFloat ? document.getElementById('adsFloat') : null;

      if (!ad) {
        slot.innerHTML = '';
        slot.classList.remove('is-visible', 'is-fading');
        if (floatContainer) floatContainer.classList.remove('is-visible');
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

      requestAnimationFrame(() => {
          slot.classList.add('is-visible');
          if (floatContainer) floatContainer.classList.add('is-visible');
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
        // SAUF si c'est le flottant, on veut peut-être le cacher si plus de pub ? 
        // Ici on garde la logique "pas de vide" sauf si explicitement null renvoyé par le render en cas d'init
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

  // Gestion fermeture pub flottante
  const closeFloat = document.querySelector('.ads-float__close');
  if(closeFloat) {
      closeFloat.addEventListener('click', function(e){
          e.preventDefault();
          const parent = document.getElementById('adsFloat');
          if(parent) {
              parent.classList.remove('is-visible');
          }
      });
  }
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

</body>
</html>