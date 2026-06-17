<?php
/**
 * Footer — fond noir fixe (#000000) + logo logo-front-02.png, tous thèmes.
 * Surcharge post-22143.css (Elementor) sans modifier les assets WP.
 */
require_once __DIR__.'/brand.php';
?>
<style id="cn-footer-styles">
#jl-footer-custpl,
.elementor-22143 .elementor-element.elementor-element-39faf867:not(.elementor-motion-effects-element-type-background),
.elementor-22143 .elementor-element.elementor-element-39faf867 > .elementor-motion-effects-container > .elementor-motion-effects-layer,
.elementor-22143 .elementor-element.elementor-element-6cf4787:not(.elementor-motion-effects-element-type-background),
.elementor-22143 .elementor-element.elementor-element-6cf4787 > .elementor-motion-effects-container > .elementor-motion-effects-layer,
.elementor-22143 .elementor-element.elementor-element-440c9edb:not(.elementor-motion-effects-element-type-background),
.elementor-22143 .elementor-element.elementor-element-440c9edb > .elementor-motion-effects-container > .elementor-motion-effects-layer {
    background-color: <?= CN_FOOTER_BG ?> !important;
}

/* Textes & liens (lisibles sur fond noir) */
.elementor-22143 .elementor-element.elementor-element-54536c6e,
.elementor-22143 .elementor-element.elementor-element-c29ecfa,
.elementor-22143 .elementor-element.elementor-element-434c995f {
    --jl-ct-color: <?= CN_FOOTER_TEXT_MUTED ?>;
    color: <?= CN_FOOTER_TEXT_MUTED ?>;
}
.elementor-22143 .elementor-element.elementor-element-20eaa4c8 .jlcus_sec_title,
.elementor-22143 .elementor-element.elementor-element-a6af4a0 .jlcus_sec_title,
.options_dark_skin .elementor-22143 .elementor-element.elementor-element-20eaa4c8 .jlcus_sec_title,
.options_dark_skin .elementor-22143 .elementor-element.elementor-element-a6af4a0 .jlcus_sec_title {
    --sect-t-color: <?= CN_FOOTER_TEXT ?>;
}
.elementor-22143 .elementor-element.elementor-element-6933093 .jlcm-main-jl_menu_list.widget_nav_menu ul li a,
.elementor-22143 .elementor-element.elementor-element-6933093 .jlcm-main-jl_menu_inline.widget_nav_menu ul li a,
.elementor-22143 .elementor-element.elementor-element-78521e0a .jlcm-main-jl_menu_list.widget_nav_menu ul li a,
.elementor-22143 .elementor-element.elementor-element-78521e0a .jlcm-main-jl_menu_inline.widget_nav_menu ul li a {
    color: <?= CN_FOOTER_TEXT_MUTED ?> !important;
}
.elementor-22143 .elementor-element.elementor-element-73d9500 .block-section {
    --jl-txt-color: <?= CN_FOOTER_TEXT_MUTED ?>;
    --jl-main-color: <?= CN_FOOTER_TEXT_MUTED ?>;
    --jl-meta-color: <?= CN_FOOTER_TEXT ?>;
    --jl-post-line-color: rgba(255, 255, 255, 0.08);
}
.elementor-22143 .elementor-element.elementor-element-6b7aeb3 {
    --divider-color: rgba(255, 255, 255, 0.12);
}
.elementor-22143 .elementor-element.elementor-element-78521e0a,
.options_dark_skin .elementor-22143 .elementor-element.elementor-element-78521e0a {
    --jl-m-divider: rgba(255, 255, 255, 0.2);
}

/* Logo footer unique — jamais basculé par le thème jour/nuit */
#jl-footer-custpl .logo_link .cn-footer-logo {
    opacity: 1 !important;
    visibility: visible !important;
    position: relative !important;
}
#jl-footer-custpl .logo_link .jl_logo_n,
#jl-footer-custpl .logo_link .jl_logo_w {
    display: none !important;
}
</style>
