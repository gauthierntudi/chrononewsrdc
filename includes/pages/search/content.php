<div class="jl_block_content">
<div class="jlc-container">
    <div class="jlc-row">
    <div class="jlc-col-md-8 jl_main_achv jl_hide_line jl_achv_tpl_list">
        <div class="jl_breadcrumbs">
            <span class="jl_item_bread">
            <a href="/">
               Accueil
            </a>
         </span>
         <i class="jli-right-chevron"></i>
         <span class="jl_item_bread">
            <a href="#!">
               <?php echo htmlspecialchars($q); ?>
            </a>
         </span>
      </div>
        <div class="jl_ache_head">
            <div class="jl_pc_sec_title">
                <h1 class="jl_pc_sec_h">
                     Résultats pour : "<?php echo htmlspecialchars($q); ?>"
                </h1>
                <div class="post_subtitle_text">
                    <p>
                        <?php 
                        echo $q === '' 
                            ? "Tape un mot-clé pour lancer la recherche." 
                            : $total_articles . " résultat(s) trouvé(s)."; 
                        ?>
                    </p>
                </div>
            </div>
            <span class="jl_auth_numw">
                <span class="jl_auth_num h1">
                <?php echo $total_articles; ?>
                </span>
                <span class="jl_auth_txt">Articles</span>
            </span>
        </div>      

        <!-- publicités -->
           <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                <div class="elementor-widget-container">
                    <div class="jl_ads_img_w">
                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-body-1" data-format="paysage_small" data-rotate="10000"></div>                       
                    </div>
              </div>
            </div>
        <!-- publicités -->
                  
        <div class="jl_clear_at block-section jl-main-block jl_wrapper_cat">
            <div class="jl_clear_at">
                <div class="jl_main_list_cw jl_wrap_eb jl_clear_at jl_lm_list">
                <div class="jl_fli_wrap jl-roww jl_contain jl-col-row">
                    <?php if (empty($articles)): ?>
                        <div class="jl_no_articles">
                            <p>Aucun résultat pour cette recherche.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $index => $article):
                            $t = clean_title($article['titre'] ?? '');
                            $article_slug = slugify($t);
                            $article_url = "/article/" . (int)$article['id'] . "/" . $article_slug;
                            $cat_url = category_url($article['categorie'] ?? '');
                            $author = $article['auteur_nom'] ?? 'Rédaction';
                            $date = fmt_date($article['date_add'] ?: $article['created_at']);

                            $covers = parse_cover_images($article['cover']);
                            $first_cover = !empty($covers) ? $covers[0] : '';
                            $has_video_flag = has_video($article);
                            $thumb = $has_video_flag ? youtube_thumb($article['videos']) : null;
                            $article_image = $first_cover;
                            $cat_color = category_color($article['categorie']);

                            $isVideo = !empty(trim((string)($article['videos'] ?? '')));
                        ?>
                        <div class="jl_clist_layout jl_frsha">
                            <div class="jl_li_in">
                                <div class="jl_img_holder">
                                    <div class="jl_imgw jl_radus_e">
                                        <div class="jl_imgin">
                                            <?php if ($article_image): ?>
                                                <img
                                                    width="680"
                                                    height="453"
                                                    src="<?php echo htmlspecialchars(cn_media_url($article_image) ?? ''); ?>"
                                                    class="attachment-bopea_layouts size-bopea_layouts wp-post-image"
                                                    alt="<?php echo htmlspecialchars($article['titre']); ?>"
                                                    loading="lazy"
                                                />
                                            <?php endif; ?>
                                        </div>
                                        <a class="jl_imgl" href="<?php echo htmlspecialchars($article_url); ?>"></a>

                                        <?php if (!empty($isVideo)): ?>
                                    <div class="jl_video_badge" aria-hidden="true">
                                        <div class="jl_play">
                                            <i class="bi bi-youtube"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                    </div>
                                </div>

                                <div class="jl_fe_text">
                                    <span class="jl_f_cat jl_lb3">
                                        <a
                                            class="jl_cat_lbl"
                                            href="<?php echo htmlspecialchars($cat_url); ?>"
                                            style="background-color: <?php echo $cat_color; ?>;"
                                        >
                                            <span><?php echo htmlspecialchars($article['categorie']); ?></span>
                                        </a>
                                    </span>
                                    <h2 class="h2 jl_fe_title">
                                        <a href="<?php echo htmlspecialchars($article_url); ?>">
                                            <?php echo titre_limit($t, 100); ?>
                                        </a>
                                    </h2>
                                    <p class="jl_fe_des">
                                        <?php echo excerpt($article['contenu'], 160); ?>
                                    </p>
                                    <span class="jl_post_meta">
                                        <span class="jl_author_img_w">
                                            Par <a href="#!" rel="author"><?php echo htmlspecialchars($author); ?></a>
                                        </span>
                                        <span class="post-date">
                                            <?php echo htmlspecialchars($date); ?>
                                        </span>
                                        <?php if (vues_int($article['vues']) > 0): ?>
                                            <span class="post-views">
                                                <i class="jli-eye"></i> 
                                            <?php echo vues_format($article['vues']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if ($index === 3): // Après le 4ème article ?>
                            <div class="jl_clist_layout jl_frsha">
                                <div class="jl_li_in">
                                     <div class="jl_ads_img_w" style="width: 100%; display: flex; justify-content: center;">
                                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-body-1" data-format="paysage_small" data-rotate="10000"></div>                       
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>

                <?php if ($total_pages > 1 && $q !== ''): ?>
                <div class="jl_pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo htmlspecialchars(cn_search_page_url($q, $page - 1)); ?>" class="jl_page_prev">
                            <i class="jli-left-chevron"></i> Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="jl_page_current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars(cn_search_page_url($q, $i)); ?>" class="jl_page_link">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo htmlspecialchars(cn_search_page_url($q, $page + 1)); ?>" class="jl_page_next">
                            Suivant <i class="jli-right-chevron"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
<div class="jlc-col-md-4 jl_smmain_side">
<div class="jl_sidebar_w">
<div id="bopea_widget_add_layouts-1" class="widget jl_cus_layouts_widget">
<div class="widget_jl_wrapper jl-cuslayouts-wrapper">
<div class="jl_cuslayouts_inner">

<div data-elementor-type="wp-post" data-elementor-id="12383" class="elementor elementor-12383">

<div class="elementor-element elementor-element-8cf430a e-flex e-con-boxed e-con e-parent" data-id="8cf430a" data-element_type="container">
    <div class="e-con-inner">

        <!-- publicités -->
           <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                <div class="elementor-widget-container">
                    <div class="jl_ads_img_w">
                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-body-sidebar-2" data-format="rectangle" data-rotate="10000"></div>                       
                    </div>
              </div>
            </div>
        <!-- publicités -->


        

    <div class="elementor-element elementor-element-2ff2179f elementor-widget elementor-widget-bopea-section-title" data-id="2ff2179f" data-element_type="widget" data-widget_type="bopea-section-title.default">
        <div class="elementor-widget-container">
            <div class="jlcus_sec_title jl_sec_style13 jl_secf_title  ">
                <div class="jlcus_sect_inner">
                    <h2 class="jl-heading-text">
                        <span class="jl_ttw">
                            Ne manquez pas
                        </span>                     
                    </h2>
                </div>                    
            </div>                    
        </div>
    </div>
    

    <div class="elementor-element elementor-element-3fa9d3f1 elementor-widget elementor-widget-bopea-grid-post" data-id="3fa9d3f1" data-element_type="widget" data-widget_type="bopea-grid-post.default">
        <div class="elementor-widget-container">
            <div class="jl_clear_at block-section jl-main-block jl_hide_meta jl_hide_desc jl_hide_line jl_hide_col_line jl_sh_num jl_num_top">
                <div class="jl_grid_wrap_f jl_wrap_eb jl_clear_at">
                    <div class="jl-roww jl_contain jl_cgrid_wrap jl-col-row">


                        <?php foreach ($mustRows as $row): ?>
                  <?php
                    $t = clean_title($row['titre'] ?? '');
                    $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                    $uCat = category_url($row['categorie'] ?? '');
                    $date = fmt_date($row['date_add'] ?: $row['created_at']);
                    $author = $row['auteur_nom'] ?? 'Rédaction';

                    $imgs = parse_cover_images($row['cover'] ?? '');
                    $img = $imgs[0] ?? null;

                    $isVideo = !empty(trim((string)($row['videos'] ?? '')));

                    $vues = (int)($row['vues_int'] ?? 0);

                    // ✅ ta fonction (base global fixée par DONUT_MAX_GLOBAL)
                    $p = donut_percent($vues);
                    $deg = (int)round(($p / 100) * 360);
                  ?>

                  <div class="jl_cgrid_layout jl_frsha jl_in_num jl_numl">
                    <div class="jl_img_holder">
                      <div class="jl_imgw jl_radus_e">
                        <div class="jl_imgin">
                          <?php if ($img): ?>
                            <img loading="lazy" decoding="async" width="680" height="453"
                                 src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                 class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                                 alt="<?= htmlspecialchars($t) ?>"
                                 data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                          <?php else: ?>
                            <div style="width:100%;height:220px;background:#111;border-radius:12px;"></div>
                          <?php endif; ?>
                        </div>

                        <?php if ($isVideo): ?>
                          <div class="jl_video_badge" aria-hidden="true">
                            <div class="jl_play"><i class="bi bi-youtube"></i></div>
                          </div>
                        <?php endif; ?>

                        <?php if ($p > 0): ?>
                          <span class="jl_none"></span>
                          <div class="container-donut jl-donut-front">
                            <div class="jl-renut-container">
                              <div class="jl-renut">
                                <div class="jl-renut-sections" style="transform: rotate(0deg);">
                                  <div class="jl-renut-section jl-renut-section-right" style="transform: rotate(0deg);">
                                    <div class="jl-renut-filler" style="background-color:#2490e2; transform: rotate(0deg);"></div>
                                  </div>
                                  <div class="jl-renut-section jl-renut-section-left" style="transform: rotate(0deg);">
                                    <div class="jl-renut-filler"
                                         style="background-color:#2490e2; transform: rotate(<?= (int)$deg ?>deg);"></div>
                                  </div>
                                </div>
                                <div class="jl-renut-overlay">
                                  <div class="jl-renut-text">
                                    <div>
                                      <span><?= (int)$p ?><span class="jl_score_sign">%</span></span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>

                        <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                        <?php if (!empty($row['categorie'])): ?>
                          <span class="jl_f_cat jl_lb7">
                            <a class="jl_cat_lbl jl_cat65"
                               style="background:<?= htmlspecialchars(category_color($row['categorie'])) ?>!important"
                               href="<?= htmlspecialchars($uCat) ?>">
                              <span><?= htmlspecialchars($row['categorie']) ?></span>
                            </a>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="jl_fe_text jl_nun_i">
                      <span class="jl_nun_d"></span>
                      <h2 class="h2 jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h2>
                      <p class="jl_fe_des"><?= htmlspecialchars(excerpt($row['contenu'] ?? '', 180)) ?></p>

                      <span class="jl_post_meta">
                        <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>
                  </div>

                <?php endforeach; ?>





                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>              
</div>
</div>
</div>
</div>
</div>  


<!-- Floating Ads (bottom-right) -->
<div id="adsFloat" class="ads-float" aria-hidden="true">
  <div class="ads-float__content">
    <div class="ads-float__title">Publicité</div>
    <div class="ads-float__body">
      <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-float" data-format="portrait" data-rotate="10000"></div>
    </div>
  </div>
  <a href="#"
     class="ads-float__close"
     aria-label="Fermer"
     role="button">
    <i class="bi bi-x-lg"></i>
  </a>
</div>
