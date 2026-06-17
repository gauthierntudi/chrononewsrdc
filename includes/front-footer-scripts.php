<?php
/** Scripts footer communs au front (Elementor, Bopea). */
?>
<link rel='stylesheet' id='elementor-post-22454-css' href='/wp-content/uploads/sites/5/elementor/css/post-22454.css' media='all' />
<link rel='stylesheet' id='elementor-post-22143-css' href='/wp-content/uploads/sites/5/elementor/css/post-22143.css' media='all' />
<link rel='stylesheet' id='widget-divider-css' href='/wp-content/plugins/elementor/assets/css/widget-divider.min.css' media='all' />
<link rel='stylesheet' id='elementor-post-12224-css' href='/wp-content/uploads/sites/5/elementor/css/post-12224.css' media='all' />

<script src="/wp-includes/js/imagesloaded.min.js" id="imagesloaded-js"></script>
<script src="/wp-includes/js/dist/hooks.min.js" id="wp-hooks-js"></script>

<script src="/wp-content/plugins/elementor/assets/lib/swiper/v8/swiper.min.js" id="swiper-js"></script>
<script src="/wp-content/themes/bopea/js/jquery.waypoints.min.js" id="waypoints-js"></script>
<script src="/wp-content/themes/bopea/js/cookie.min.js" id="cookie-js"></script>
<script src="/wp-content/themes/bopea/js/lazysizes.min.js" id="lazysizes-js"></script>
<script src="/wp-content/themes/bopea/js/glightbox.min.js" id="glightbox-js"></script>
<script id="bopea-custom-js-extra">
<?php include __DIR__.'/jl-params.php'; ?>
</script>
<script src="/wp-content/themes/bopea/js/customs.js" id="bopea-custom-js"></script>
<script src="/wp-content/plugins/elementor/assets/js/webpack.runtime.min.js" id="elementor-webpack-runtime-js"></script>
<script src="/wp-content/plugins/elementor/assets/js/frontend-modules.min.js" id="elementor-frontend-modules-js"></script>
<script src="/wp-includes/js/jquery/ui/core.min.js" id="jquery-ui-core-js"></script>
<script id="elementor-frontend-js-before">
var elementorFrontendConfig = {
    environmentMode: { edit: false, wpPreview: false, isScriptDebug: false },
    i18n: {
        shareOnFacebook: "Share on Facebook",
        shareOnTwitter: "Share on Twitter",
        pinIt: "Pin it",
        download: "Download",
        downloadImage: "Download image",
        fullscreen: "Fullscreen",
        zoom: "Zoom",
        share: "Share",
        playVideo: "Play Video",
        previous: "Previous",
        next: "Next",
        close: "Close",
        a11yCarouselPrevSlideMessage: "Previous slide",
        a11yCarouselNextSlideMessage: "Next slide",
        a11yCarouselFirstSlideMessage: "This is the first slide",
        a11yCarouselLastSlideMessage: "This is the last slide",
        a11yCarouselPaginationBulletMessage: "Go to slide",
    },
    is_rtl: false,
    breakpoints: { xs: 0, sm: 480, md: 768, lg: 1025, xl: 1440, xxl: 1600 },
    responsive: {
        breakpoints: {
            mobile: { label: "Mobile Actualités", value: 767, default_value: 767, direction: "max", is_enabled: true },
            mobile_extra: {
                label: "Mobile Landscape",
                value: 880,
                default_value: 880,
                direction: "max",
                is_enabled: false,
            },
            tablet: { label: "Tablet Actualités", value: 1024, default_value: 1024, direction: "max", is_enabled: true },
            tablet_extra: {
                label: "Tablet Landscape",
                value: 1200,
                default_value: 1200,
                direction: "max",
                is_enabled: false,
            },
            laptop: { label: "Laptop", value: 1366, default_value: 1366, direction: "max", is_enabled: false },
            widescreen: { label: "Widescreen", value: 2400, default_value: 2400, direction: "min", is_enabled: false },
        },
        hasCustomBreakpoints: false,
    },
    version: "3.33.0",
    is_static: false,
    experimentalFeatures: {
        e_font_icon_svg: true,
        additional_custom_breakpoints: true,
        container: true,
        "nested-elements": true,
        home_screen: true,
        global_classes_should_enforce_capabilities: true,
        e_variables: true,
        "cloud-library": true,
        e_opt_in_v4_page: true,
        "import-export-customization": true,
    },
    urls: {
        assets: "/wp-content/plugins/elementor/assets/",
        ajaxurl: "",
        uploadUrl: "",
    },
    nonces: { floatingButtonsClickTracking: "0c295fc16d" },
    swiperClass: "swiper",
    settings: { page: [], editorPreferences: [] },
    kit: {
        active_breakpoints: ["viewport_mobile", "viewport_tablet"],
        global_image_lightbox: "yes",
        lightbox_enable_counter: "yes",
        lightbox_enable_fullscreen: "yes",
        lightbox_enable_zoom: "yes",
        lightbox_enable_share: "yes",
        lightbox_title_src: "title",
        lightbox_description_src: "description",
    },
    post: { id: 13574, title: "Bopea2%20%E2%80%93%20WordPress%20theme", excerpt: "", featuredImage: false },
};

</script>
<script src="/wp-content/plugins/elementor/assets/js/frontend.min.js" id="elementor-frontend-js"></script>

<script>
   // À mettre avant que les scripts frontend problematiques se déclenchent
window.addEventListener('unhandledrejection', function (event) {
    const reason = event.reason;

    // Cas typique Webpack
    if (reason && reason.name === 'ChunkLoadError') {
        // On empêche l'erreur d'apparaître en rouge dans la console
        event.preventDefault();
        console.warn('ChunkLoadError ignoré :', reason);
    }
});
</script>

<?php include __DIR__.'/front-live-search-script.php'; ?>

