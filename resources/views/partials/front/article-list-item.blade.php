@php
    use App\Support\FrontHelper;
@endphp
<div class="jl_m_right jl_sm_list jl_numjl_lis jl_numjl_list jl_lisep">
    <div class="jl_img_holder">
        <div class="jl_imgw">
            <div class="jl_imgin">
                @if ($cover = FrontHelper::coverUrl($article['cover'] ?? null))
                    <img width="680" height="510" src="{{ $cover }}" alt="{{ FrontHelper::cleanTitle($article['title'] ?? '') }}">
                @endif
            </div>
            <a class="jl_imgl" href="{{ FrontHelper::articleUrl($article) }}"></a>
        </div>
    </div>
    <div class="jl_item_content">
        @if ($cat = $article['category'] ?? $article['categorie'] ?? null)
            <span class="jl_f_cat">
                <a class="jl_cat_lbl" style="background:{{ \App\Support\Categories::color($cat) }}!important"
                   href="{{ \App\Support\Categories::url($cat) }}">{{ $cat }}</a>
            </span>
        @endif
        <h3 class="jl_fe_title">
            <a href="{{ FrontHelper::articleUrl($article) }}">{{ FrontHelper::cleanTitle($article['title'] ?? '') }}</a>
        </h3>
        <span class="jl_post_meta">
            <span class="post-date">{{ FrontHelper::formatDate($article['date_add'] ?? $article['created_at'] ?? null) }}</span>
        </span>
    </div>
</div>
