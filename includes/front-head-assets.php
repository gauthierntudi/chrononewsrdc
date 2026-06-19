<?php
/** Assets CSS/JS communs au front (extrait de index.php). */
require_once __DIR__.'/brand.php';
require_once __DIR__.'/front-asset-paths.php';
$frontStylesheet = $frontStylesheet ?? '/css/styles-home.css';
$frontExtraStylesheets = $frontExtraStylesheets ?? [];
?>
      <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="https://fonts.googleapis.com/css?family=Roboto:700|Oxygen:400,700|Roboto:700,500,400&display=swap">
      <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:700|Oxygen:400,700|Roboto:700,500,400&display=swap"></noscript>
      <style>img:is([sizes="auto" i], [sizes^="auto," i]) { contain-intrinsic-size: 3000px 1500px }</style>
      <link rel="dns-prefetch" href="https://fonts.googleapis.com">
      <style id='wp-emoji-styles-inline-css'>
         img.wp-smiley, img.emoji {
            display: inline !important;
            border: none !important;
            box-shadow: none !important;
            height: 1em !important;
            width: 1em !important;
            margin: 0 0.07em !important;
            vertical-align: -0.1em !important;
            background: none !important;
            padding: 0 !important;
        }
      </style>
         <style id='classic-theme-styles-inline-css'>
            /*! This file is auto-generated */
            .wp-block-button__link{
               color:#fff;
               background-color:#32373c;
               border-radius:9999px;
               box-shadow:none;
               text-decoration:none;
               padding:calc(.667em + 2px) calc(1.333em + 2px);
               font-size:1.125em
            }
            .wp-block-file__button{
               background:#32373c;
               color:#fff;
               text-decoration:none
            }
         </style>

         <style id='global-styles-inline-css'>
            :root {
             --wp--preset--aspect-ratio--square: 1;
             --wp--preset--aspect-ratio--4-3: 4/3;
             --wp--preset--aspect-ratio--3-4: 3/4;
             --wp--preset--aspect-ratio--3-2: 3/2;
             --wp--preset--aspect-ratio--2-3: 2/3;
             --wp--preset--aspect-ratio--16-9: 16/9;
             --wp--preset--aspect-ratio--9-16: 9/16;
             --wp--preset--color--black: #000000;
             --wp--preset--color--cyan-bluish-gray: #abb8c3;
             --wp--preset--color--white: #ffffff;
             --wp--preset--color--pale-pink: #f78da7;
             --wp--preset--color--vivid-red: <?= CN_PRIMARY_COLOR ?>;
             --wp--preset--color--luminous-vivid-orange: #ff6900;
             --wp--preset--color--luminous-vivid-amber: #fcb900;
             --wp--preset--color--light-green-cyan: #7bdcb5;
             --wp--preset--color--vivid-green-cyan: #00d084;
             --wp--preset--color--pale-cyan-blue: #8ed1fc;
             --wp--preset--color--vivid-cyan-blue: #0693e3;
             --wp--preset--color--vivid-purple: #9b51e0;
             --wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(
                 135deg,
                 rgba(6, 147, 227, 1) 0%,
                 rgb(155, 81, 224) 100%
             );
             --wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(
                 135deg,
                 rgb(122, 220, 180) 0%,
                 rgb(0, 208, 130) 100%
             );
             --wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(
                 135deg,
                 rgba(252, 185, 0, 1) 0%,
                 rgba(255, 105, 0, 1) 100%
             );
             --wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(
                 135deg,
                 rgba(255, 105, 0, 1) 0%,
                 rgb(207, 46, 46) 100%
             );
             --wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(
                 135deg,
                 rgb(238, 238, 238) 0%,
                 rgb(169, 184, 195) 100%
             );
             --wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(
                 135deg,
                 rgb(74, 234, 220) 0%,
                 rgb(151, 120, 209) 20%,
                 rgb(207, 42, 186) 40%,
                 rgb(238, 44, 130) 60%,
                 rgb(251, 105, 98) 80%,
                 rgb(254, 248, 76) 100%
             );
             --wp--preset--gradient--blush-light-purple: linear-gradient(135deg, rgb(255, 206, 236) 0%, rgb(152, 150, 240) 100%);
             --wp--preset--gradient--blush-bordeaux: linear-gradient(
                 135deg,
                 rgb(254, 205, 165) 0%,
                 rgb(254, 45, 45) 50%,
                 rgb(107, 0, 62) 100%
             );
             --wp--preset--gradient--luminous-dusk: linear-gradient(
                 135deg,
                 rgb(255, 203, 112) 0%,
                 rgb(199, 81, 192) 50%,
                 rgb(65, 88, 208) 100%
             );
             --wp--preset--gradient--pale-ocean: linear-gradient(
                 135deg,
                 rgb(255, 245, 203) 0%,
                 rgb(182, 227, 212) 50%,
                 rgb(51, 167, 181) 100%
             );
             --wp--preset--gradient--electric-grass: linear-gradient(135deg, rgb(202, 248, 128) 0%, rgb(113, 206, 126) 100%);
             --wp--preset--gradient--midnight: linear-gradient(135deg, rgb(2, 3, 129) 0%, rgb(40, 116, 252) 100%);
             --wp--preset--font-size--small: 13px;
             --wp--preset--font-size--medium: 20px;
             --wp--preset--font-size--large: 36px;
             --wp--preset--font-size--x-large: 42px;
             --wp--preset--spacing--20: 0.44rem;
             --wp--preset--spacing--30: 0.67rem;
             --wp--preset--spacing--40: 1rem;
             --wp--preset--spacing--50: 1.5rem;
             --wp--preset--spacing--60: 2.25rem;
             --wp--preset--spacing--70: 3.38rem;
             --wp--preset--spacing--80: 5.06rem;
             --wp--preset--shadow--natural: 6px 6px 9px rgba(0, 0, 0, 0.2);
             --wp--preset--shadow--deep: 12px 12px 50px rgba(0, 0, 0, 0.4);
             --wp--preset--shadow--sharp: 6px 6px 0px rgba(0, 0, 0, 0.2);
             --wp--preset--shadow--outlined: 6px 6px 0px -3px rgba(255, 255, 255, 1), 6px 6px rgba(0, 0, 0, 1);
             --wp--preset--shadow--crisp: 6px 6px 0px rgba(0, 0, 0, 1);
         }
         :where(.is-layout-flex) {
             gap: 0.5em;
         }
         :where(.is-layout-grid) {
             gap: 0.5em;
         }
         body .is-layout-flex {
             display: flex;
         }
         .is-layout-flex {
             flex-wrap: wrap;
             align-items: center;
         }
         .is-layout-flex > :is(*, div) {
             margin: 0;
         }
         body .is-layout-grid {
             display: grid;
         }
         .is-layout-grid > :is(*, div) {
             margin: 0;
         }
         :where(.wp-block-columns.is-layout-flex) {
             gap: 2em;
         }
         :where(.wp-block-columns.is-layout-grid) {
             gap: 2em;
         }
         :where(.wp-block-post-template.is-layout-flex) {
             gap: 1.25em;
         }
         :where(.wp-block-post-template.is-layout-grid) {
             gap: 1.25em;
         }
         .has-black-color {
             color: var(--wp--preset--color--black) !important;
         }
         .has-cyan-bluish-gray-color {
             color: var(--wp--preset--color--cyan-bluish-gray) !important;
         }
         .has-white-color {
             color: var(--wp--preset--color--white) !important;
         }
         .has-pale-pink-color {
             color: var(--wp--preset--color--pale-pink) !important;
         }
         .has-vivid-red-color {
             color: var(--wp--preset--color--vivid-red) !important;
         }
         .has-luminous-vivid-orange-color {
             color: var(--wp--preset--color--luminous-vivid-orange) !important;
         }
         .has-luminous-vivid-amber-color {
             color: var(--wp--preset--color--luminous-vivid-amber) !important;
         }
         .has-light-green-cyan-color {
             color: var(--wp--preset--color--light-green-cyan) !important;
         }
         .has-vivid-green-cyan-color {
             color: var(--wp--preset--color--vivid-green-cyan) !important;
         }
         .has-pale-cyan-blue-color {
             color: var(--wp--preset--color--pale-cyan-blue) !important;
         }
         .has-vivid-cyan-blue-color {
             color: var(--wp--preset--color--vivid-cyan-blue) !important;
         }
         .has-vivid-purple-color {
             color: var(--wp--preset--color--vivid-purple) !important;
         }
         .has-black-background-color {
             background-color: var(--wp--preset--color--black) !important;
         }
         .has-cyan-bluish-gray-background-color {
             background-color: var(--wp--preset--color--cyan-bluish-gray) !important;
         }
         .has-white-background-color {
             background-color: var(--wp--preset--color--white) !important;
         }
         .has-pale-pink-background-color {
             background-color: var(--wp--preset--color--pale-pink) !important;
         }
         .has-vivid-red-background-color {
             background-color: var(--wp--preset--color--vivid-red) !important;
         }
         .has-luminous-vivid-orange-background-color {
             background-color: var(--wp--preset--color--luminous-vivid-orange) !important;
         }
         .has-luminous-vivid-amber-background-color {
             background-color: var(--wp--preset--color--luminous-vivid-amber) !important;
         }
         .has-light-green-cyan-background-color {
             background-color: var(--wp--preset--color--light-green-cyan) !important;
         }
         .has-vivid-green-cyan-background-color {
             background-color: var(--wp--preset--color--vivid-green-cyan) !important;
         }
         .has-pale-cyan-blue-background-color {
             background-color: var(--wp--preset--color--pale-cyan-blue) !important;
         }
         .has-vivid-cyan-blue-background-color {
             background-color: var(--wp--preset--color--vivid-cyan-blue) !important;
         }
         .has-vivid-purple-background-color {
             background-color: var(--wp--preset--color--vivid-purple) !important;
         }
         .has-black-border-color {
             border-color: var(--wp--preset--color--black) !important;
         }
         .has-cyan-bluish-gray-border-color {
             border-color: var(--wp--preset--color--cyan-bluish-gray) !important;
         }
         .has-white-border-color {
             border-color: var(--wp--preset--color--white) !important;
         }
         .has-pale-pink-border-color {
             border-color: var(--wp--preset--color--pale-pink) !important;
         }
         .has-vivid-red-border-color {
             border-color: var(--wp--preset--color--vivid-red) !important;
         }
         .has-luminous-vivid-orange-border-color {
             border-color: var(--wp--preset--color--luminous-vivid-orange) !important;
         }
         .has-luminous-vivid-amber-border-color {
             border-color: var(--wp--preset--color--luminous-vivid-amber) !important;
         }
         .has-light-green-cyan-border-color {
             border-color: var(--wp--preset--color--light-green-cyan) !important;
         }
         .has-vivid-green-cyan-border-color {
             border-color: var(--wp--preset--color--vivid-green-cyan) !important;
         }
         .has-pale-cyan-blue-border-color {
             border-color: var(--wp--preset--color--pale-cyan-blue) !important;
         }
         .has-vivid-cyan-blue-border-color {
             border-color: var(--wp--preset--color--vivid-cyan-blue) !important;
         }
         .has-vivid-purple-border-color {
             border-color: var(--wp--preset--color--vivid-purple) !important;
         }
         .has-vivid-cyan-blue-to-vivid-purple-gradient-background {
             background: var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple) !important;
         }
         .has-light-green-cyan-to-vivid-green-cyan-gradient-background {
             background: var(--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan) !important;
         }
         .has-luminous-vivid-amber-to-luminous-vivid-orange-gradient-background {
             background: var(--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange) !important;
         }
         .has-luminous-vivid-orange-to-vivid-red-gradient-background {
             background: var(--wp--preset--gradient--luminous-vivid-orange-to-vivid-red) !important;
         }
         .has-very-light-gray-to-cyan-bluish-gray-gradient-background {
             background: var(--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray) !important;
         }
         .has-cool-to-warm-spectrum-gradient-background {
             background: var(--wp--preset--gradient--cool-to-warm-spectrum) !important;
         }
         .has-blush-light-purple-gradient-background {
             background: var(--wp--preset--gradient--blush-light-purple) !important;
         }
         .has-blush-bordeaux-gradient-background {
             background: var(--wp--preset--gradient--blush-bordeaux) !important;
         }
         .has-luminous-dusk-gradient-background {
             background: var(--wp--preset--gradient--luminous-dusk) !important;
         }
         .has-pale-ocean-gradient-background {
             background: var(--wp--preset--gradient--pale-ocean) !important;
         }
         .has-electric-grass-gradient-background {
             background: var(--wp--preset--gradient--electric-grass) !important;
         }
         .has-midnight-gradient-background {
             background: var(--wp--preset--gradient--midnight) !important;
         }
         .has-small-font-size {
             font-size: var(--wp--preset--font-size--small) !important;
         }
         .has-medium-font-size {
             font-size: var(--wp--preset--font-size--medium) !important;
         }
         .has-large-font-size {
             font-size: var(--wp--preset--font-size--large) !important;
         }
         .has-x-large-font-size {
             font-size: var(--wp--preset--font-size--x-large) !important;
         }
         :where(.wp-block-post-template.is-layout-flex) {
             gap: 1.25em;
         }
         :where(.wp-block-post-template.is-layout-grid) {
             gap: 1.25em;
         }
         :where(.wp-block-columns.is-layout-flex) {
             gap: 2em;
         }
         :where(.wp-block-columns.is-layout-grid) {
             gap: 2em;
         }
         :root :where(.wp-block-pullquote) {
             font-size: 1.5em;
             line-height: 1.6;
         }

      </style>
      <link
          rel="stylesheet"
          id="contact-form-7-css"
          href="<?= cn_front_plugin('contact-form-7/includes/css/styles.css') ?>"
          media="all"
      />

      <link rel="stylesheet" id="bopea_layout-css" href="<?= cn_front_theme('bopea/css/layout.css') ?>" media="all" />
      <link rel="stylesheet" id="bopea_style-css" href="<?= cn_front_theme('bopea/style.css') ?>" media="all" />
      <link rel="stylesheet" id="bopea_style-inline-css" href="<?= htmlspecialchars($frontStylesheet, ENT_QUOTES, 'UTF-8') ?>" media="all" />
      <link rel="stylesheet" id="chrononews-drop-cap-css" href="/css/chrononews-drop-cap.css" media="all" />
      <link rel="stylesheet" id="chrononews-breaking-news-css" href="/css/breaking-news.css" media="all" />
<?php foreach ($frontExtraStylesheets as $extraStylesheet): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($extraStylesheet, ENT_QUOTES, 'UTF-8') ?>" media="all" />
<?php endforeach; ?>
      <link rel="stylesheet" id="glightbox-css" href="<?= cn_front_theme('bopea/css/glightbox.min.css') ?>" media="all" />
      <link rel="stylesheet" id="swiper-css" href="<?= cn_front_plugin('elementor/assets/lib/swiper/v8/css/swiper.min.css') ?>" />
      <link
          rel="stylesheet"
          id="elementor-frontend-css"
          href="<?= cn_front_plugin('elementor/assets/css/frontend.min.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-post-5-css"
          href="<?= cn_front_upload('sites/5/elementor/css/post-5.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-post-13574-css"
          href="<?= cn_front_upload('sites/5/elementor/css/post-13574.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-post-22453-css"
          href="<?= cn_front_upload('sites/5/elementor/css/post-22453.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-post-12383-css"
          href="<?= cn_front_upload('sites/5/elementor/css/post-12383.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-gf-local-roboto-css"
          href="<?= cn_front_upload('sites/5/elementor/google-fonts/css/roboto.css') ?>"
          media="all"
      />
      <link
          rel="stylesheet"
          id="elementor-gf-local-robotoslab-css"
          href="<?= cn_front_upload('sites/5/elementor/google-fonts/css/robotoslab.css') ?>"
          media="all"
      />
      <script src="<?= cn_front_core('js/jquery/jquery.min.js') ?>" id="jquery-core-js"></script>
      <script src="<?= cn_front_core('js/jquery/jquery-migrate.min.js') ?>" id="jquery-migrate-js"></script>

      <meta
          name="generator"
          content="Elementor 3.33.0; features: e_font_icon_svg, additional_custom_breakpoints; settings: css_print_method-external, google_font-enabled, font_display-swap"
      />

        <style>
         .e-con.e-parent:nth-of-type(n + 4):not(.e-lazyloaded):not(.e-no-lazyload),
         .e-con.e-parent:nth-of-type(n + 4):not(.e-lazyloaded):not(.e-no-lazyload) * {
             background-image: none !important;
         }
         @media screen and (max-height: 1024px) {
             .e-con.e-parent:nth-of-type(n + 3):not(.e-lazyloaded):not(.e-no-lazyload),
             .e-con.e-parent:nth-of-type(n + 3):not(.e-lazyloaded):not(.e-no-lazyload) * {
                 background-image: none !important;
             }
         }
         @media screen and (max-height: 640px) {
             .e-con.e-parent:nth-of-type(n + 2):not(.e-lazyloaded):not(.e-no-lazyload),
             .e-con.e-parent:nth-of-type(n + 2):not(.e-lazyloaded):not(.e-no-lazyload) * {
                 background-image: none !important;
             }
         }

    </style>
    <style id="wp-custom-css">
         .wp-block-image,
         .wp-block-embed,
         .wp-block-gallery {
             margin-top: calc(30px + 0.25vw);
             margin-bottom: calc(30px + 0.25vw) !important;
         }
         .logo_small_wrapper_table .logo_small_wrapper .logo_link > h1,
         .logo_small_wrapper_table .logo_small_wrapper .logo_link > span {
             display: flex;
             margin: 0px;
             padding: 0px;
         } /* elementor category */
         .elementor-widget-wp-widget-categories h5 {
             display: none;
         }
         .elementor-widget-wp-widget-categories ul {
             list-style: none;
             padding: 0px 0px 0px 15px !important;
             margin: 0px;
             display: flex;
             flex-direction: column;
             gap: 7px;
         }
         .elementor-widget-wp-widget-categories ul li {
             margin-bottom: 0 !important;
             list-style: none;
             font-family: var(--jl-menu-font);
             font-size: 14px;
             font-weight: var(--jl-cat-font-weight);
             display: flex;
             flex-direction: column;
             gap: 7px;
         }
         .elementor-widget-wp-widget-categories ul li a {
             display: inline-flex;
             align-items: center;
             width: 100%;
         }
         .elementor-widget-wp-widget-categories ul li a:before {
             content: "";
             position: absolute;
             margin-left: -15px;
             border: solid currentcolor;
             border-width: 0 1px 1px 0;
             display: inline-block;
             padding: 2px;
             vertical-align: middle;
             transform: rotate(-45deg);
             -webkit-transform: rotate(-45deg);
         }
         .elementor-widget-wp-widget-categories span {
             margin-right: 0px;
             margin-left: auto;
             color: #fff;
             text-align: center;
             min-width: 24px;
             height: 24px;
             line-height: 24px;
             border-radius: 4px;
             padding: 0px 5px;
             font-size: 80%;
         }


         /* style publicités */
        .ad-slot { position: relative; }
        .ad-slot .ad-wrap { opacity: 0; transition: opacity .45s ease; will-change: opacity; }
        .ad-slot.is-visible .ad-wrap { opacity: 1; }
        .ad-slot.is-fading .ad-wrap { opacity: 0; }

        /* Pub flottante (bottom-right) */
        .ads-float {
            position: fixed;
            right: 18px;
            bottom: 18px;
            width: 320px;
            max-width: calc(100vw - 24px);
            background: #ffffff;
            color: #212121;
            border-radius: 0;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
            z-index: 99998;
            overflow: visible;
            opacity: 0;
            transform: translateY(14px);
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .ads-float.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .ads-float__content {
            position: relative;
            z-index: 1;
            padding: 0;
        }

        .ads-float__title {
            display: none;
        }

        .ads-float__body .ad-wrap,
        .ads-float__body .ad-wrap img {
            display: block;
            width: 100%;
            height: auto;
        }

        .ads-float__close {
            position: absolute;
            top: -10px;
            right: -10px;
            z-index: 100;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50px;
            background: rgba(234, 37, 40, 1);
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
            pointer-events: auto;
        }

        .ads-float__close i {
            font-size: 1em;
            line-height: 1;
        }

        .ads-float__close:hover {
            background: rgba(234, 37, 40, 0.88);
            color: #fff;
        }
        /* style publicités */

        /* Overlay vidéo sur cover */
        .jl_video_badge{
          position:absolute;
          inset:0;
          display:flex;
          align-items:center;
          justify-content:center;
          pointer-events:none; /* laisse le clic passer sur le lien de l’article */
          border-radius: inherit;
          z-index: 1;
        }

        .jl_video_badge::before{
          content:"";
          position:absolute;
          inset:0;
          background: linear-gradient(to top, rgba(0,0,0,.45), rgba(0,0,0,.05));
          border-radius: inherit;
        }

        .jl_video_badge .jl_play{
          position:relative;
          width:65px;
          height:65px;
          border-radius:999px;
          background: rgba(0,0,0,0);
          backdrop-filter: blur(0px);
          display:flex;
          align-items:center;
          justify-content:center;
          box-shadow: 0 10px 30px rgba(0,0,0,0);
          transition: .8s ease;
          transform: scale(1);
        }

        .jl_video_badge .jl_play i{
          font-size: 3em;
          margin-left:2px;
          color:rgba(255, 255, 255, 1);
        }

        .jl_video_badge .jl_play i:hover{
          color: #ff0000;
        }

        /* Overlay vidéo sur cover */

        .jl_imgw { position: relative; }

        .jl_imgw:hover .jl_play {transform: scale(1.3); transition: .8s ease; }


        .wpcf7-response-output.is-success { border-left: 4px solid #32c704; padding:10px; }
        .wpcf7-response-output.is-error { border-left: 4px solid #ed2228; padding:10px; }
        .wpcf7-submit.is-loading { opacity:.7; cursor:wait; }

        /* ✅ iziToast dark only + texte blanc */
          .iziToast.iziToast-color-dark{
            background: #11112E !important;
            color: #fff !important;
            z-index: 99999999;
          }
          .iziToast.iziToast-color-dark .iziToast-title,
          .iziToast.iziToast-color-dark .iziToast-message{
            color:#fff !important;
          }
          .iziToast.iziToast-color-dark .iziToast-icon{
            color:#fff !important;
          }
          .iziToast.iziToast-color-dark .iziToast-close{
            color:#fff !important;
            opacity:1;
          }

          mark {
            /*color: #09b960!important;*/
          }
      </style>

      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
      <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
