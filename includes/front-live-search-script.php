<script>
(function () {
  document.querySelectorAll('.jl-live-search').forEach(initLiveSearch);

  function initLiveSearch(root) {
    const input = root.querySelector('.js-live-search-input');
    const box = root.querySelector('.js-live-search-box');
    const results = root.querySelector('.js-live-search-results');

    if (!input || !box || !results) return;

    let timer = null;
    let controller = null;

    const escapeHtml = (str) =>
      String(str ?? '').replace(/[&<>"']/g, (m) => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
      }[m]));

    function titreLimit(text, limit = 70) {
      text = String(text || '').trim();
      if (text.length <= limit) return text;
      let cut = text.slice(0, limit);
      cut = cut.replace(/\s+\S*$/, '');
      return cut + '…';
    }

    const highlight = (safeText, q) => {
      q = String(q || '').trim();
      if (q.length < 2) return safeText;
      const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'ig');
      return safeText.replace(re, (m) => `<mark>${m}</mark>`);
    };

    function hideBox() {
      box.style.display = 'none';
      results.innerHTML = '';
    }

    function showBox() {
      box.style.display = 'block';
    }

    async function fetchResults(q) {
      if (controller) controller.abort();
      controller = new AbortController();

      const url = <?= json_encode(cn_ajax_url('live_search'), JSON_UNESCAPED_SLASHES) ?> + '?q=' + encodeURIComponent(q) + '&limit=6';

      const res = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        signal: controller.signal
      });

      if (!res.ok) throw new Error('HTTP ' + res.status);
      return await res.json();
    }

    function render(items, q) {
      if (!items || items.length === 0) {
        results.innerHTML = '<div style="padding:12px;">Aucun résultat.</div>';
        showBox();
        return;
      }

      results.innerHTML = items.map((it) => {
        const limited = titreLimit(it.title || '', 70);
        const safeTitle = escapeHtml(limited);
        const titleHtml = highlight(safeTitle, q);
        const safeUrl = escapeHtml(it.url || '#');
        const safeDate = escapeHtml(it.date || '');

        const imgHtml = it.image
          ? `<img width="200" height="150"
                  src="${escapeHtml(it.image)}"
                  class="attachment-bopea_small size-bopea_small jl-lazyload wp-post-image"
                  alt="${safeTitle}" loading="lazy">`
          : '<div style="width:200px;height:150px;background:#111;border-radius:12px;"></div>';

        return `
        <div class="jl_cslist_layout jl_lisep">
          <div class="jl_li_in">
            <div class="jl_img_holder">
              <div class="jl_imgw jl_radus_e">
                <div class="jl_imgin">${imgHtml}</div>
                <a class="jl_imgl" href="${safeUrl}"></a>
              </div>
            </div>
            <div class="jl_fe_text">
              <h3 class="jl_fe_title">
                <a href="${safeUrl}">${titleHtml}</a>
              </h3>
              <span class="jl_post_meta">
                <span class="post-date">${safeDate}</span>
              </span>
            </div>
          </div>
        </div>`;
      }).join('');

      showBox();
    }

    input.addEventListener('input', function () {
      const q = input.value.trim();
      if (q.length < 2) { hideBox(); return; }

      clearTimeout(timer);
      timer = setTimeout(async () => {
        try {
          const data = await fetchResults(q);
          render(data.items || [], q);
        } catch (e) {
          if (e.name !== 'AbortError') console.warn(e);
        }
      }, 250);
    });

    document.addEventListener('click', function (e) {
      if (!root.contains(e.target)) hideBox();
    });

    input.addEventListener('focus', function () {
      const q = input.value.trim();
      if (q.length >= 2) input.dispatchEvent(new Event('input'));
    });
  }
})();
</script>
