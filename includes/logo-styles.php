<?php
/**
 * CSS centralisé des logos header + footer.
 * Images : includes/brand.php (CN_LOGO_LIGHT, CN_LOGO_DARK)
 * Tailles : includes/brand.php (CN_LOGO_HEADER_*, CN_LOGO_FOOTER_*)
 */
require_once __DIR__.'/brand.php';
?>
<style id="cn-logo-styles">
/* Header principal + barre sticky */
header.jlc-hmain-w .logo_link span,
.jlc-stick-main-w .logo_link span {
    position: relative !important;
    width: <?= CN_LOGO_HEADER_WRAP ?> !important;
    max-width: <?= CN_LOGO_HEADER_WRAP ?> !important;
    display: inline-flex;
    align-items: center;
}
header.jlc-hmain-w .logo_link span .jl_logo_n,
header.jlc-hmain-w .logo_link span .jl_logo_w,
.jlc-stick-main-w .logo_link span .jl_logo_n,
.jlc-stick-main-w .logo_link span .jl_logo_w {
    width: <?= CN_LOGO_HEADER_IMG ?> !important;
    max-width: <?= CN_LOGO_HEADER_IMG ?> !important;
    height: auto !important;
}

/* Footer */
#jl-footer-custpl .logo_link span {
    position: relative !important;
    width: <?= CN_LOGO_FOOTER_WRAP ?> !important;
    max-width: <?= CN_LOGO_FOOTER_WRAP ?> !important;
    display: inline-flex;
    align-items: center;
}
#jl-footer-custpl .logo_link span .jl_logo_n,
#jl-footer-custpl .logo_link span .jl_logo_w,
#jl-footer-custpl .logo_link span .cn-footer-logo {
    width: <?= CN_LOGO_FOOTER_IMG ?> !important;
    max-width: <?= CN_LOGO_FOOTER_IMG ?> !important;
    height: auto !important;
}

@media (max-width: 768px) {
    header.jlc-hmain-w .logo_link span,
    .jlc-stick-main-w .logo_link span {
        width: <?= CN_LOGO_HEADER_WRAP_MOBILE ?> !important;
        max-width: <?= CN_LOGO_HEADER_WRAP_MOBILE ?> !important;
    }
    header.jlc-hmain-w .logo_link span .jl_logo_n,
    header.jlc-hmain-w .logo_link span .jl_logo_w,
    .jlc-stick-main-w .logo_link span .jl_logo_n,
    .jlc-stick-main-w .logo_link span .jl_logo_w {
        width: <?= CN_LOGO_HEADER_IMG_MOBILE ?> !important;
        max-width: <?= CN_LOGO_HEADER_IMG_MOBILE ?> !important;
    }
    #jl-footer-custpl .logo_link span {
        width: <?= CN_LOGO_FOOTER_WRAP_MOBILE ?> !important;
        max-width: <?= CN_LOGO_FOOTER_WRAP_MOBILE ?> !important;
    }
    #jl-footer-custpl .logo_link span .jl_logo_n,
    #jl-footer-custpl .logo_link span .jl_logo_w,
    #jl-footer-custpl .logo_link span .cn-footer-logo {
        width: <?= CN_LOGO_FOOTER_IMG_MOBILE ?> !important;
        max-width: <?= CN_LOGO_FOOTER_IMG_MOBILE ?> !important;
    }
}
</style>
