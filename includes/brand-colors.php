<?php
/**
 * Couleur primaire — surcharge variables thème Bopea / WP.
 * Source : includes/brand.php (CN_PRIMARY_COLOR)
 */
require_once __DIR__.'/brand.php';
?>
<style id="cn-brand-colors">
:root,
body {
    --wp--preset--color--vivid-red: <?= CN_PRIMARY_COLOR ?>;
    --jl-main-color: <?= CN_PRIMARY_COLOR ?>;
    --jl-menu-ac-color: <?= CN_PRIMARY_COLOR ?>;
    --jl-cat-sk: <?= CN_PRIMARY_COLOR ?>;
    --jl-ha-skin: <?= CN_PRIMARY_COLOR ?>;
    --jl-catc-bg: <?= CN_PRIMARY_COLOR ?>;
    --jl-catb-bg: <?= CN_PRIMARY_COLOR ?>;
}
body.options_dark_skin {
    --jl-main-color: <?= CN_PRIMARY_COLOR ?>;
    --jl-menu-ac-color: <?= CN_PRIMARY_COLOR ?>;
    --jl-cat-sk: <?= CN_PRIMARY_COLOR ?>;
}

/* Legacy palette : #34af03 → #d11810, #081838/#021455 → #000, #006ff6 → #E10600 */
.elementor-13574 .elementor-element-a193639 .block-section .jl_m_fr7 {
    --jl-pbg: <?= CN_PRIMARY_COLOR ?> !important;
}
.elementor-12224 .elementor-element-21e1ec9:not(.elementor-motion-effects-element-type-background),
.elementor-12224 .elementor-element-21e1ec9 > .elementor-motion-effects-container > .elementor-motion-effects-layer {
    background-color: #000000 !important;
}
.elementor-12224 .elementor-element-cfc296e {
    --jlc7-btnbg: #E10600 !important;
    --jlc7-hbtnbg: #E10600 !important;
}
</style>
