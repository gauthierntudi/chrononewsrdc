<?php
/**
 * Ajustements layout header — espace menu / icônes (lune, search).
 */
?>
<style id="cn-header-layout">
@media (min-width: 1025px) {
    /* Colonne menu : s'étend sans réserver 70 % fixes */
    .elementor-22453 .elementor-element-670991ed > .e-con-inner,
    .elementor-22454 .elementor-element-376bc8c3 > .e-con-inner {
        gap: 12px;
        justify-content: space-between;
    }

    .elementor-22453 .elementor-element-738986c7,
    .elementor-22454 .elementor-element-32b3ef37 {
        width: auto !important;
        --width: auto !important;
        flex: 1 1 auto !important;
        min-width: 0;
    }

    /* Colonne icônes : largeur au contenu seulement */
    .elementor-22453 .elementor-element-55773142,
    .elementor-22454 .elementor-element-60fbaf0b {
        width: auto !important;
        --width: auto !important;
        flex: 0 0 auto !important;
        --gap: 10px 10px;
        --row-gap: 10px;
        --column-gap: 10px;
    }

    /* Menu collé vers les icônes */
    .elementor-22453 .elementor-element-74e59b79,
    .elementor-22454 .elementor-element-3420e1f4 {
        flex: 1 1 auto !important;
        min-width: 0;
    }

    .jlc-hmain-w .elementor-element-738986c7 .navigation_wrapper,
    .jlc-stick-main-w .elementor-element-32b3ef37 .navigation_wrapper {
        flex: 1 1 auto;
        justify-content: flex-end;
        margin-left: 8px;
        padding-right: 28px;
    }

    /* « Plus d'infos » + flèche dropdown : ne pas chevaucher l'icône lune */
    .jlc-hmain-w .navigation_wrapper .jl_main_menu > .menu-item:last-child,
    .jlc-stick-main-w .navigation_wrapper .jl_main_menu > .menu-item:last-child {
        margin-right: 16px !important;
    }

    .elementor-22453 .elementor-element-55773142,
    .elementor-22454 .elementor-element-60fbaf0b {
        padding-left: 12px;
    }

    .jlc-hmain-w .elementor-element-738986c7 .elementor-element-47fe1331,
    .jlc-stick-main-w .elementor-element-32b3ef37 .elementor-element-2cf0189e {
        flex: 0 0 auto;
    }
}
</style>
