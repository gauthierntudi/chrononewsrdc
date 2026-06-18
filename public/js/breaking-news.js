(function () {
    function initBreakingNewsSwiper() {
        const el = document.querySelector('.cn-breaking-swiper');
        if (!el || el.dataset.cnBreakingInit === '1' || typeof Swiper === 'undefined') {
            return;
        }

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const slideCount = el.querySelectorAll('.swiper-slide').length;

        if (slideCount < 2) {
            return;
        }

        el.dataset.cnBreakingInit = '1';

        const isMobile = window.matchMedia('(max-width: 767px)').matches;

        new Swiper(el, {
            direction: 'vertical',
            loop: true,
            speed: isMobile ? 480 : 600,
            slidesPerView: 1,
            spaceBetween: 0,
            allowTouchMove: !reducedMotion,
            autoplay: reducedMotion
                ? false
                : {
                    delay: isMobile ? 3500 : 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBreakingNewsSwiper);
    } else {
        initBreakingNewsSwiper();
    }
})();
