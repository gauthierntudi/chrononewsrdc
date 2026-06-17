@php
    use App\Support\FrontHelper;
    $title = FrontHelper::cleanTitle($article['title'] ?? '');
    $cover = FrontHelper::coverUrl($article['cover'] ?? null);
    $category = $article['category'] ?? $article['categorie'] ?? null;
    $author = $article['auteur_nom'] ?? ($article['author']['nom'] ?? 'Rédaction');
    $date = FrontHelper::formatDate($article['date_add'] ?? $article['created_at'] ?? null);
    $url = FrontHelper::articleUrl($article);
    $isVideo = FrontHelper::hasVideo($article);
@endphp
<div class="{{ $variant === 'hero' ? 'jl_p_fr7 jl_m_fr7 jl_frsha' : 'jl_p_fr7 jl_cgrid_layout jl_frsha jl_sm_mt' }}">
    <div class="{{ $variant === 'hero' ? 'jl_m_fr7_inner' : '' }}">
        <div class="jl_imgw jl_radus_e">
            <div class="jl_imgin">
                @if ($cover)
                    <img decoding="async"
                         @if ($variant === 'hero') fetchpriority="high" width="1100" height="734" @else width="680" height="510" @endif
                         src="{{ $cover }}"
                         class="attachment-bopea_medium size-bopea_medium wp-post-image"
                         alt="{{ $title }}">
                @else
                    <div style="width:100%;height:{{ $variant === 'hero' ? '260' : '160' }}px;background:#111;border-radius:12px;"></div>
                @endif
            </div>
            @if ($isVideo)
                <div class="jl_video_badge" aria-hidden="true">
                    <div class="jl_play"><i class="bi bi-youtube"></i></div>
                </div>
            @endif
            <a class="jl_imgl" aria-label="{{ $title }}" href="{{ $url }}"></a>
            @if ($variant === 'hero' && $category)
                <span class="jl_f_cat jl_lb7">
                    <a class="jl_cat_lbl jl_cat70" style="background:{{ \App\Support\Categories::color($category) }}!important"
                       href="{{ \App\Support\Categories::url($category) }}">
                        <span>{{ $category }}</span>
                    </a>
                    <a class="jl_cat_lbl jl_cat70" style="background:#212121!important;margin-left:6px;"
                       href="{{ \App\Support\Categories::url($category) }}">
                        <span>A LA UNE</span>
                    </a>
                </span>
            @endif
        </div>
        <div class="jl_fe_text">
            <h2 class="{{ $variant === 'hero' ? 'h2 jl_fe_title jl_txt_2row' : 'jl_fe_title jl_txt_2row' }}">
                <a href="{{ $url }}">{{ $title }}</a>
            </h2>
            @if ($variant === 'hero')
                <p class="jl_fe_des">{{ FrontHelper::excerpt($article['content'] ?? $article['contenu'] ?? '', 220) }}</p>
            @endif
            <span class="jl_post_meta">
                <span class="jl_author_img_w">Par <a href="#!" rel="author">{{ $author }}</a></span>
                <span class="post-date">{{ $date }}</span>
            </span>
        </div>
    </div>
</div>
