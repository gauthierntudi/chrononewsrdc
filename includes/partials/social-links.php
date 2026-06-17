<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/social-links.php';

/** @var string $socialVariant header|footer|widget|mobile */
$socialVariant = $socialVariant ?? 'widget';
$links = cn_social_links(cn_social_resolve_db());
$styles = cn_social_network_styles();

if ($links === []) {
    return;
}

$ulClass = match ($socialVariant) {
    'header' => 'jl_wr_soci jl_sh_ctericons jlshcolor',
    'footer' => 'jl_wr_soci jl_sh_ctericons_bg',
    'widget' => 'jl_wr_soci jl_sh_cter jl_sh1',
    'mobile' => 'jl_sh_ic_li',
    default => 'jl_wr_soci jl_sh_cter jl_sh1',
};

$colorKey = match ($socialVariant) {
    'header' => 'color_header',
    'footer' => 'color_footer',
    default => 'color_rich',
};

echo '<ul class="'.cn_social_h($ulClass).'">';

foreach ($links as $network => $row) {
    $meta = $styles[$network] ?? null;
    if ($meta === null) {
        continue;
    }

    $url = trim((string) ($row['url'] ?? ''));
    if ($url === '') {
        continue;
    }

    if ($socialVariant === 'mobile') {
        echo '<li class="'.cn_social_h($meta['mobile_li_class']).'">';
        echo '<a href="'.cn_social_h($url).'" target="_blank" aria-label="'.cn_social_h($meta['aria']).'" rel="noopener nofollow">';
        echo '<i class="'.cn_social_h($meta['icon']).'"></i>';
        echo '</a></li>';

        continue;
    }

    $liClasses = trim($meta['li_class'].' '.($socialVariant === 'header' ? ($meta['extra_li_class'] ?? '') : ''));
    $color = $meta[$colorKey] ?? '#4080FF';
    $title = trim((string) ($row['title'] ?? ''));
    $count = trim((string) ($row['count'] ?? ''));
    $countLabel = trim((string) ($row['count_label'] ?? ''));

    echo '<li class="'.cn_social_h($liClasses).'" style="--jl-social-color:'.cn_social_h($color).';">';
    echo '<a aria-label="'.cn_social_h($meta['aria']).'" href="'.cn_social_h($url).'" target="_blank" rel="nofollow">';
    echo '<span class="jl_sh_i"><i class="'.cn_social_h($meta['icon']).'"></i></span>';
    echo '<span class="jl_sh_t">'.cn_social_h($title).'</span>';
    echo '<span class="jl_sh_w">';
    if ($count !== '') {
        echo '<span class="jl_sh_c">'.cn_social_h($count).'</span>';
    }
    if ($countLabel !== '') {
        echo '<span class="jl_sh_l">'.cn_social_h($countLabel).'</span>';
    }
    echo '</span></a></li>';
}

echo '</ul>';
