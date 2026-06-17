<div class="jl_home_bw">
   <div data-elementor-type="wp-page" data-elementor-id="13574" class="elementor elementor-13574">



<!-- home Article recents -->
<div class="elementor-element elementor-element-394f5a09 e-flex e-con-boxed e-con e-parent" data-id="394f5a09" data-element_type="container">


<!-- publicités -->
<div class="e-con-inner">
    <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="paysage_large" data-emplacement="pub-header" data-rotate="15000"></div>                       
</div>
<!-- publicités -->
<br>

  <div class="e-con-inner">
    <div class="elementor-element elementor-element-a193639 elementor-widget elementor-widget-bopea-feature-layout7" data-id="a193639" data-element_type="widget" data-widget_type="bopea-feature-layout7.default">
      <div class="elementor-widget-container">
        <div id="blockid_a193639" class="block-section jl-main-block jl_hide_col_line jl_en_fr jl_frg">
          <div class="jl_mb_wrap_f jl_clear_at">
            <div class="jl-roww jl_contain jl-col-row">
              <div class="jl_fr7_wrap">
                <div class="jl_fr7_inner">

                  <!-- large article à la une : juste 1 seul article -->
                  <?php if (!empty($homeHero)): ?>
                    <?php
                      $t = clean_title($homeHero['titre'] ?? '');
                      $uArticle = "/article/" . (int)$homeHero['id'] . "/" . slugify($t);
                      $uCat = category_url($homeHero['categorie'] ?? '');
                      $date = fmt_date($homeHero['date_add'] ?: $homeHero['created_at']);
                      $author = $homeHero['auteur_nom'] ?? 'Rédaction';

                      $imgs = parse_cover_images($homeHero['cover'] ?? '');
                      $img = $imgs[0] ?? null;

                      $finalImg = $img; // ✅ on garde cover uniquement
                      $isVideo = !empty(trim((string)($homeHero['videos'] ?? '')));
                    ?>
                    <div class="jl_p_fr7 jl_m_fr7 jl_frsha">
                      <div class="jl_m_fr7_inner">
                        <div class="jl_imgw jl_radus_e">
                          <div class="jl_imgin">
                            <?php if ($finalImg): ?>
                              <img fetchpriority="high" decoding="async"
                                   width="1100" height="734"
                                   src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>"
                                   class="attachment-bopea_medium size-bopea_medium jl-lazyload lazyload wp-post-image"
                                   alt="<?= htmlspecialchars($t) ?>"
                                   data-src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>" />
                            <?php else: ?>
                              <div style="width:100%;height:260px;background:#111;border-radius:12px;"></div>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($isVideo)): ?>
                            <div class="jl_video_badge" aria-hidden="true">
                                <div class="jl_play">
                                    <i class="bi bi-youtube"></i>
                                </div>
                            </div>
                          <?php endif; ?>

                          <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                        <?php if (!empty($homeHero['categorie'])): ?>
                          <span class="jl_f_cat jl_lb7">
                            <!-- Badge catégorie -->
                            <a class="jl_cat_lbl jl_cat70"
                               style="background:<?= htmlspecialchars(category_color($homeHero['categorie'] ?? '')) ?>!important"
                               href="<?= htmlspecialchars($uCat) ?>">
                              <span><?= htmlspecialchars($homeHero['categorie']) ?></span>
                            </a>

                            <!-- Badge A LA UNE (même lien catégorie) -->
                            <a class="jl_cat_lbl jl_cat70"
                               style="background:#212121!important; margin-left:6px;"
                               href="<?= htmlspecialchars($uCat) ?>">
                              <span>A LA UNE</span>
                            </a>
                          </span>
                        <?php endif; ?>

                        </div>

                        <div class="jl_fe_text">
                          <h2 class="h2 jl_fe_title jl_txt_2row">
                            <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                          </h2>
                          <p class="jl_fe_des"><?= htmlspecialchars(excerpt($homeHero['contenu'] ?? '', 220)) ?></p>

                          <span class="jl_post_meta">
                            <span class="jl_author_img_w">
                              Par <a href="#!" title="author" rel="author"><?= htmlspecialchars($author) ?></a>
                            </span>
                            <span class="post-date"><?= htmlspecialchars($date) ?></span>
                          </span>
                        </div>

                      </div>
                    </div>
                  <?php endif; ?>
                  <!-- /large article à la une -->

                  <!-- minimalistes articles récents : 4 articles -->
                  <?php foreach ($homeSmalls as $item): ?>
                    <?php
                      $t = clean_title($item['titre'] ?? '');
                      $uArticle = "/article/" . (int)$item['id'] . "/" . slugify($t);
                      $uCat = category_url($item['categorie'] ?? '');
                      $date = fmt_date($item['date_add'] ?: $item['created_at']);
                      $author = $item['auteur_nom'] ?? 'Rédaction';

                      $imgs = parse_cover_images($item['cover'] ?? '');
                      $img = $imgs[0] ?? null;

                      $finalImg = $img; // cover uniquement
                      $isVideo = !empty(trim((string)($item['videos'] ?? '')));


                      $vues = vues_int($item['vues'] ?? 0);
                      $p = donut_percent($vues);
                      $deg = (int)round(($p / 100) * 360);
                    ?>

                    <div class="jl_p_fr7 jl_cgrid_layout jl_frsha jl_sm_mt">
                      <div class="jl_imgw jl_radus_e">
                        <div class="jl_imgin">
                          <?php if ($finalImg): ?>
                            <img decoding="async" width="680" height="510"
                                 src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>"
                                 class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                                 alt="<?= htmlspecialchars($t) ?>"
                                 data-src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>" />
                          <?php else: ?>
                            <div style="width:100%;height:160px;background:#111;border-radius:12px;"></div>
                          <?php endif; ?>
                        </div>

                        <?php if (!empty($isVideo)): ?>
                            <div class="jl_video_badge" aria-hidden="true">
                                <div class="jl_play">
                                    <i class="bi bi-youtube"></i>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($vues > 100 && $p > 0): ?>
                          <span class="jl_none"></span>
                          <!-- indicateur de meilleurs articles en terme de vue : top parmi ces 4 -->
                          <div class="container-donut jl-donut-front">
                            <div class="jl-renut-container">
                              <div class="jl-renut">
                                <div class="jl-renut-sections" style="transform: rotate(0deg);">
                                  <div class="jl-renut-section jl-renut-section-right" style="transform: rotate(0deg);">
                                    <div class="jl-renut-filler" style="background-color:#f2ff00; transform: rotate(0deg);"></div>
                                  </div>
                                  <div class="jl-renut-section jl-renut-section-left" style="transform: rotate(0deg);background-color:#939830">
                                    <div class="jl-renut-filler" style="background-color:#f2ff00; transform: rotate(<?= (int)$deg ?>deg);"></div>
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
                          <!-- /donut -->
                        <?php endif; ?>

                        <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                        <?php if (!empty($item['categorie'])): ?>
                          <span class="jl_f_cat jl_lb7">
                            <a class="jl_cat_lbl jl_cat70"
                               style="background:<?= htmlspecialchars(category_color($item['categorie'] ?? '')) ?>!important"
                               href="<?= htmlspecialchars($uCat) ?>">
                              <span><?= htmlspecialchars($item['categorie']) ?></span>
                            </a>
                          </span>
                        <?php endif; ?>
                      </div>

                      <div class="jl_fe_text">
                        <h3 class="h3 jl_fe_title jl_txt_2row">
                          <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                        </h3>
                        <span class="jl_post_meta">
                          <span class="jl_author_img_w">
                            Par <a href="#!" title="Posts" rel="author"><?= htmlspecialchars($author) ?></a>
                          </span>
                          <span class="post-date"><?= htmlspecialchars($date) ?></span>
                        </span>
                      </div>
                    </div>

                  <?php endforeach; ?>
                  <!-- /minimalistes articles récents -->

                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- widget container -->
    </div>
  </div>
</div>
<!-- /home Article recents -->

<?php
add_exclude_ids($excludeHomeIds, $homeHero);
add_exclude_ids($excludeHomeIds, $homeSmalls);
?>



<!-- publicités -->
<div class="elementor-element elementor-element-29d6572 e-flex e-con-boxed e-con e-parent" data-id="29d6572" data-element_type="container">
  <div class="e-con-inner">
    <div class="elementor-element elementor-element-123dddb5 elementor-widget elementor-widget-bopea-section-ads-img" data-id="123dddb5" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
      <div class="elementor-widget-container">
        <div class="jl_ads_img_w">
          <!-- <span class="jl_ads_title">-Sponsored-</span> -->
          <div class="jl_ads_inner ad-slot" data-format="paysage_small" data-emplacement="pub-body-1" data-rotate="20000"></div>
            <!-- JS injecte la pub ici -->
          </div>
        </div>
      </div>
    </div>
</div>
<!-- /publicités -->




<!--  Juste pour vous -->
<div class="elementor-element elementor-element-dcf1f9 e-flex e-con-boxed e-con e-parent" data-id="dcf1f9" data-element_type="container">
    <div class="e-con-inner">
        <div class="elementor-element elementor-element-438b9a e-con-full e-flex e-con e-child" data-id="438b9a" data-element_type="container">
            
      <!-- header Juste pour vous -->  
         
         <div class="elementor-element elementor-element-6e47718 elementor-widget elementor-widget-bopea-section-title" data-id="6e47718" data-element_type="widget" data-widget_type="bopea-section-title.default">
                <div class="elementor-widget-container">
                <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
                  <div class="jlcus_sect_inner">
                     <h2 class="jl-heading-text">
                        <span class="jl_ttw">
                           Juste <span> pour vous</span>
                        </span>                       
                     </h2>
                     <p class="jl_sct_bt">
                        <a href="/juste-pour-vous" class="jlopt-text-link">                        
                           <span class="jl_bt_inner">
                              <span>En savoir plus</span>
                              <i class="jli-carrow"></i>                        
                           </span>
                        </a>                  
                     </p>
                  </div>                    
               </div>                    
                </div>
            </div>
        <!-- /header Juste pour vous -->    


<!-- artiles Juste pour vous -->        
         
<div class="elementor-element elementor-element-2d9ca41 elementor-widget elementor-widget-bopea-feature-layout15" data-id="2d9ca41" data-element_type="widget" data-widget_type="bopea-feature-layout15.default">
    <div class="elementor-widget-container">
                
      <div id="blockid_2d9ca41" class="block-section jl-main-block" data-blockid="blockid_2d9ca41" data-section_style="jl_feature_15" data-post_type="post" data-post_type_tax="none" data-page_max="10" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="5" data-offset="6" data-tabs_link="none" >
            <div class="jl_mb_wrap_f jl_wrap_eb jl_clear_at">
               <div class="jl-roww jl_contain jl_contain_mix jl-col-row">       

            <div class="jl_fr15_wrap"> 
            <div class="jl_fr15_inner">     
                  

         <!-- grand article juste 1 seul -->
            <?php if (!empty($forYouBig)): ?>
            <?php
                $t = clean_title($forYouBig['titre'] ?? '');
                $uArticle = "/article/" . (int)$forYouBig['id'] . "/" . slugify($t);
                $uCat = category_url($forYouBig['categorie'] ?? '');
                $date = fmt_date($forYouBig['date_add'] ?: $forYouBig['created_at']);
                $author = $forYouBig['auteur_nom'] ?? 'Rédaction';

                $imgs = parse_cover_images($forYouBig['cover'] ?? '');
                $img = $imgs[0] ?? null;
                
                $finalImg = $img; // cover uniquement
                $isVideoForYou = !empty(trim((string)($forYouBig['videos'] ?? '')));
            ?>

              <div class="jl_en_lfr">
                <div class="jl_cgrid_layout jl_frsha">
                  <div class="jl_imgw jl_radus_e">
                    <div class="jl_imgin">
                      <?php if ($finalImg): ?>
                        <img loading="lazy" decoding="async" width="680" height="453"
                             src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>"
                             class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                             alt="<?= htmlspecialchars($t) ?>"
                             data-src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>" />
                      <?php else: ?>
                        <div style="width:100%;height:220px;background:#111;border-radius:12px;"></div>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($isVideoForYou)): ?>
                        <div class="jl_video_badge" aria-hidden="true">
                            <div class="jl_play">
                                <i class="bi bi-youtube"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                    <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                    <?php if (!empty($forYouBig['categorie'])): ?>
                      <span class="jl_f_cat jl_lb7">
                        <a class="jl_cat_lbl jl_cat70"
                           style="background:<?= htmlspecialchars(category_color($forYouBig['categorie'])) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($forYouBig['categorie']) ?></span>
                        </a>
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="jl_fe_text">
                    <h2 class="h3 jl_fe_title jl_txt_2row">
                      <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                    </h2>
                    <p class="jl_fe_des"><?= htmlspecialchars(excerpt($forYouBig['contenu'] ?? '', 220)) ?></p>

                    <span class="jl_post_meta">
                      <span class="jl_author_img_w">
                        Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a>
                      </span>
                      <span class="post-date"><?= htmlspecialchars($date) ?></span>
                    </span>
                  </div>
                </div>
              </div>
            <?php endif; ?>
         <!-- /grand article juste 1 seul -->


         <div class="jl_fli_con">
         <div class="jl_fli_wrap">  


            <!-- patit article : il en faut 4 -->
            <?php foreach ($forYouSmalls as $it): ?>
            <?php
                $t = clean_title($it['titre'] ?? '');
                $uArticle = "/article/" . (int)$it['id'] . "/" . slugify($t);
                $uCat = category_url($it['categorie'] ?? '');
                $date = fmt_date($it['date_add'] ?: $it['created_at']);

                $imgs = parse_cover_images($it['cover'] ?? '');
                $img = $imgs[0] ?? null;
                
                $finalImg = $img; // cover uniquement
                $isVideoForYouSmall = !empty(trim((string)($it['videos'] ?? '')));
            ?>

              <div class="jl_mmlistc">
                <div class="jl_mmlist_layout jl_lisep">
                  <div class="jl_li_in">

                    <div class="jl_img_holder jl_smi">
                      <div class="jl_imgw jl_radus_e">
                        <div class="jl_imgin">
                          <?php if ($finalImg): ?>
                            <img loading="lazy" decoding="async" width="200" height="133"
                                 src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>"
                                 class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                                 alt="<?= htmlspecialchars($t) ?>"
                                 data-src="<?= htmlspecialchars(cn_media_url($finalImg) ?? '') ?>" />
                          <?php else: ?>
                            <div style="width:200px;height:133px;background:#111;border-radius:10px;"></div>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($isVideoForYouSmall)): ?>
                        <div class="jl_video_badge" aria-hidden="true">
                            <div class="jl_play">
                                <i class="bi bi-youtube"></i>
                            </div>
                        </div>
                        <?php endif; ?>
                        <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                      </div>
                    </div>

                    <div class="jl_fe_text">
                      <?php if (!empty($it['categorie'])): ?>
                        <span class="jl_f_cat jl_lb2">
                          <a class="jl_cat_txt jl_cat70"
                             style="border-bottom: 2px solid <?= htmlspecialchars(category_color($it['categorie'])) ?>!important;text-decoration: none!important;"
                             href="<?= htmlspecialchars($uCat) ?>">
                            <span><?= htmlspecialchars($it['categorie']) ?></span>
                          </a>
                        </span>
                      <?php endif; ?>

                      <h3 class="h3 jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>

            <?php endforeach; ?>
            <!-- /patit article : il en faut 4 -->






         </div>
      </div>
   </div>
</div>
</div>
</div>
</div>      
</div>
</div>

<!-- /artiles Juste pour vous -->




<!-- start bloc la Actualités -->

<!-- header la Actualités --> 
<?php $uActualités = category_url('Actualités'); ?>

<div class="elementor-element elementor-element-767ec9e elementor-widget elementor-widget-bopea-section-title" data-id="767ec9e" data-element_type="widget" data-widget_type="bopea-section-title.default">
    <div class="elementor-widget-container">
        <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
         <div class="jlcus_sect_inner">
            <h2 class="jl-heading-text">
               <span class="jl_ttw">
                  <span>Actualités</span>
               </span>                   
            </h2>
            
            <p class="jl_sct_bt">
               <a href="<?= htmlspecialchars($uActualités) ?>" class="jlopt-text-link">                
                  <span class="jl_bt_inner">
                     <span>En savoir plus</span>
                     <i class="jli-carrow"></i>                        
                  </span>
               </a>                  
            </p>
         </div>                    
      </div>                    
   </div>
</div>
<!-- /header la Actualités --> 


<div class="elementor-element elementor-element-7204c00 elementor-widget elementor-widget-bopea-grid-post" data-id="7204c00" data-element_type="widget" data-widget_type="bopea-grid-post.default">
    <div class="elementor-widget-container">
        <div class="jl_clear_at block-section jl-main-block jl_hide_desc jl_hide_line jl_hide_col_line jl_num_top" data-blockid="blockid_7204c00" data-section_style="jl_mgrid" data-post_type="post" data-post_type_tax="none" data-page_max="9" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="6" data-offset="11" data-tabs_link="none" >      
            <div class="jl_grid_wrap_f jl_wrap_eb jl_clear_at">
            <div class="jl-roww jl_contain jl_cgrid_wrap jl-col-row">

        <?php 
        
        foreach ($ActualitésPosts as $Actualités): ?>
            <?php
                $t = clean_title($Actualités['titre'] ?? '');
                $uArticle = "/article/" . (int)$Actualités['id'] . "/" . slugify($t);
                $uCat = category_url($Actualités['categorie'] ?? 'Actualités');
                $date = fmt_date($Actualités['date_add'] ?: $Actualités['created_at']);
                $author = $Actualités['auteur_nom'] ?? 'Rédaction';

                $imgs = parse_cover_images($Actualités['cover'] ?? '');
                $img = $imgs[0] ?? null;

                $isVideoActualités = !empty(trim((string)($Actualités['videos'] ?? '')));

                $vues = (int)($Actualités['vues_int'] ?? 0);
                $p = donut_percent($vues);
                $deg = (int)round(($p / 100) * 360);

            ?>
                
         
            <div class="jl_cgrid_layout jl_frsha jl_in_num jl_numl">
                <div class="jl_img_holder">
                  <div class="jl_imgw jl_radus_e">
                    <div class="jl_imgin">
                      <?php if ($img): ?>
                        <img loading="lazy" decoding="async" width="680" height="454"
                             src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                             class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                             alt="<?= htmlspecialchars($t) ?>"
                             data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                      <?php else: ?>
                        <div style="width:100%;height:220px;background:#111;border-radius:12px;"></div>
                      <?php endif; ?>
                    </div>

                    <?php if ($isVideoActualités): ?>
                        <div class="jl_video_badge" aria-hidden="true">
                            <div class="jl_play">
                                <i class="bi bi-youtube"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($vues > 100 && $p > 0): ?>
                      <span class="jl_none"></span>
                          <!-- indicateur de meilleurs articles en terme de vue : top parmi ces 4 -->
                          <div class="container-donut jl-donut-front">
                            <div class="jl-renut-container">
                              <div class="jl-renut">
                                <div class="jl-renut-sections" style="transform: rotate(0deg);">
                                  <div class="jl-renut-section jl-renut-section-right" style="transform: rotate(0deg);">
                                    <div class="jl-renut-filler" style="background-color:#f2ff00; transform: rotate(0deg);"></div>
                                  </div>
                                  <div class="jl-renut-section jl-renut-section-left" style="transform: rotate(0deg);background-color:#939830">
                                    <div class="jl-renut-filler" style="background-color:#f2ff00; transform: rotate(<?= (int)$deg ?>deg);"></div>
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

                    <span class="jl_f_cat jl_lb7">
                      <a class="jl_cat_lbl jl_cat66"
                         style="background:<?= htmlspecialchars(category_color($Actualités['categorie'] ?? 'Actualités')) ?>!important"
                         href="<?= htmlspecialchars($uCat) ?>">
                        <span><?= htmlspecialchars($Actualités['categorie'] ?? 'Actualités') ?></span>
                      </a>
                    </span>
                  </div>
                </div>

                <div class="jl_fe_text jl_nun_i">
                  <span class="jl_nun_d"></span>
                  <h2 class="h2 jl_fe_title jl_txt_2row">
                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                  </h2>
                  <p class="jl_fe_des"><?= htmlspecialchars(excerpt($Actualités['contenu'] ?? '', 180)) ?></p>

                  <span class="jl_post_meta">
                    <span class="jl_author_img_w">
                      Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a>
                    </span>
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

<!-- end bloc la Actualités -->


<!-- publicités -->

<div class="elementor-widget-container" style="margin-top: 15px;">
    <div class="jl_ads_img_w">
        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="paysage_medium" data-emplacement="pub-body-2" data-rotate="10000"></div>                       
    </div>
</div>

<!-- publicités -->



<!-- start bloc Int’l -->

<!-- header Int’l--> 
<div class="elementor-element elementor-element-bc35985 elementor-widget elementor-widget-bopea-section-title" data-id="bc35985" data-element_type="widget" data-widget_type="bopea-section-title.default">
    <div class="elementor-widget-container">
        <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
         <div class="jlcus_sect_inner">
            <h2 class="jl-heading-text">
               <span class="jl_ttw"> 
                  <span>Interviews</span>
               </span>                                          
            </h2>
            <p class="jl_sct_bt">
               <a href="<?= category_url('Interviews') ?>" class="jlopt-text-link">
                  <span class="jl_bt_inner">
                     <span>En savoir plus</span>
                     <i class="jli-carrow"></i>                        
                  </span>
               </a>                  
            </p>
         </div>                    
      </div>                    
   </div>
</div>



<!-- start bloc Interview global -->

<div class="elementor-element elementor-element-b68e934 e-grid e-con-full e-con e-child" data-id="b68e934" data-element_type="container">
    
<!-- start bloc Interview 2 -->
   <div class="elementor-element elementor-element-16aaa0c elementor-widget elementor-widget-bopea-main-ov-sm-li" data-id="16aaa0c" data-element_type="widget" data-widget_type="bopea-main-ov-sm-li.default">
        <div class="elementor-widget-container">
            <div id="blockid_16aaa0c" class="block-section jl-main-block" data-blockid="blockid_16aaa0c" data-section_style="jl_main_ov_sm_li" data-post_type="post" data-post_type_tax="none" data-page_max="13" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="4" data-offset="17" data-tabs_link="none" >
            <div class="jl_mb_wrap_f jl_wrap_eb jl_clear_at">
            <div class="jl-roww jl_contain jl_contain_mix jl-col-row">              
            <div class="jl_fr16_wrap">


         <div class="jl_fr16_inner">        
            

         <!-- un large article Interview 1 -->
        <?php if (!empty($lic1Big)): ?>
          <?php
            $t = clean_title($lic1Big['titre'] ?? '');
            $uArticle = "/article/" . (int)$lic1Big['id'] . "/" . slugify($t);
            $uCat = category_url($lic1Big['categorie'] ?? 'Interviews');
            $date = fmt_date($lic1Big['date_add'] ?: $lic1Big['created_at']);
            $author = $lic1Big['auteur_nom'] ?? 'Rédaction';

            $imgs = parse_cover_images($lic1Big['cover'] ?? '');
            $img = $imgs[0] ?? null;

            $isVideo = !empty(trim((string)($lic1Big['videos'] ?? '')));
          ?>

          <div class="jl_mini_ov_sb">
            <div class="jl_ov_layout jl_ov_mix_opt jl_ov_el jl_ov_mh jl_sm_mt">
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

                  <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                </div>
              </div>

              <div class="jl_fe_text">
                <div class="jl_fe_inner">
                  <span class="jl_f_cat jl_lb3">
                    <a class="jl_cat_lbl jl_cat70"
                       style="background:<?= htmlspecialchars(category_color($lic1Big['categorie'] ?? 'Interviews')) ?>!important"
                       href="<?= htmlspecialchars($uCat) ?>">
                      <span><?= htmlspecialchars($lic1Big['categorie'] ?? 'Interviews') ?></span>
                    </a>
                  </span>

                  <h3 class="h3 jl_fe_title jl_txt_2row">
                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                  </h3>

                  <span class="jl_post_meta">
                    <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                    <span class="post-date"><?= htmlspecialchars($date) ?></span>
                  </span>

                  <a href="<?= htmlspecialchars($uArticle) ?>" aria-label="<?= htmlspecialchars($t) ?>" class="jl_cap_ov"></a>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- /un large article Interview 1 -->

      <!-- 3 minimalistes articles Interview 1-->
         <?php foreach ($lic1Smalls as $row): ?>
          <?php
            $t = clean_title($row['titre'] ?? '');
            $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
            $uCat = category_url($row['categorie'] ?? 'Interviews');
            $date = fmt_date($row['date_add'] ?: $row['created_at']);
            $imgs = parse_cover_images($row['cover'] ?? '');
            $img = $imgs[0] ?? null;
            $isVideo = !empty(trim((string)($row['videos'] ?? '')));
          ?>
          <div class="jl_mmlistc">
            <div class="jl_mmlist_layout jl_lisep">
              <div class="jl_li_in">
                <div class="jl_img_holder">
                  <div class="jl_imgw jl_radus_e">
                    <div class="jl_imgin">
                      <?php if ($img): ?>
                        <img loading="lazy" decoding="async" width="200" height="150"
                             src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                             class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                             alt="<?= htmlspecialchars($t) ?>"
                             data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                      <?php else: ?>
                        <div style="width:200px;height:150px;background:#111;border-radius:12px;"></div>
                      <?php endif; ?>
                    </div>

                    <?php if ($isVideo): ?>
                      <div class="jl_video_badge" aria-hidden="true">
                        <div class="jl_play"><i class="bi bi-youtube"></i></div>
                      </div>
                    <?php endif; ?>

                    <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                  </div>
                </div>

                <div class="jl_fe_text">
                  <span class="jl_f_cat jl_lb">
                    <a class="jl_cat_txt jl_cat66"
                       style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? 'Interviews')) ?>!important"
                       href="<?= htmlspecialchars($uCat) ?>">
                      <span><?= htmlspecialchars($row['categorie'] ?? 'Interviews') ?></span>
                    </a>
                  </span>

                  <h3 class="h3 jl_fe_title jl_txt_2row">
                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                  </h3>

                  <span class="jl_post_meta">
                    <span class="post-date"><?= htmlspecialchars($date) ?></span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <!-- /3 minimalistes articles Interview 1-->

        </div>
   </div>                               
</div>
</div>
</div>              
</div>
</div>
<!-- end bloc Interview 1 -->


<!-- start bloc Interview 2 -->
<div class="elementor-element elementor-element-d45667b elementor-widget elementor-widget-bopea-main-ov-sm-li" data-id="d45667b" data-element_type="widget" data-widget_type="bopea-main-ov-sm-li.default">
    <div class="elementor-widget-container">
        <div id="blockid_d45667b" class="block-section jl-main-block" data-blockid="blockid_d45667b" data-section_style="jl_main_ov_sm_li" data-post_type="post" data-post_type_tax="none" data-page_max="13" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="4" data-offset="21" data-tabs_link="none" >
            <div class="jl_mb_wrap_f jl_wrap_eb jl_clear_at">
            <div class="jl-roww jl_contain jl_contain_mix jl-col-row">              
                <div class="jl_fr16_wrap"> 
               <div class="jl_fr16_inner">      
                  

         <!-- un large article Interview 2 -->
               <?php if (!empty($lic2Big)): ?>
          <?php
            $t = clean_title($lic2Big['titre'] ?? '');
            $uArticle = "/article/" . (int)$lic2Big['id'] . "/" . slugify($t);
            $uCat = category_url($lic2Big['categorie'] ?? 'Interviews');
            $date = fmt_date($lic2Big['date_add'] ?: $lic2Big['created_at']);
            $author = $lic2Big['auteur_nom'] ?? 'Rédaction';

            $imgs = parse_cover_images($lic2Big['cover'] ?? '');
            $img = $imgs[0] ?? null;

            $isVideo = !empty(trim((string)($lic2Big['videos'] ?? '')));
          ?>

          <div class="jl_mini_ov_sb">
            <div class="jl_ov_layout jl_ov_mix_opt jl_ov_el jl_ov_mh jl_sm_mt">
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

                  <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                </div>
              </div>

              <div class="jl_fe_text">
                <div class="jl_fe_inner">
                  <span class="jl_f_cat jl_lb3">
                    <a class="jl_cat_lbl jl_cat70"
                       style="background:<?= htmlspecialchars(category_color($lic2Big['categorie'] ?? 'Interviews')) ?>!important"
                       href="<?= htmlspecialchars($uCat) ?>">
                      <span><?= htmlspecialchars($lic2Big['categorie'] ?? 'Interviews') ?></span>
                    </a>
                  </span>

                  <h3 class="h3 jl_fe_title jl_txt_2row">
                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                  </h3>

                  <span class="jl_post_meta">
                    <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                    <span class="post-date"><?= htmlspecialchars($date) ?></span>
                  </span>

                  <a href="<?= htmlspecialchars($uArticle) ?>" aria-label="<?= htmlspecialchars($t) ?>" class="jl_cap_ov"></a>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
    <!-- /un large article Interview 2 -->


   <!-- /3 minimalistes articles Interview 2-->
      <?php foreach ($lic2Smalls as $row): ?>
          <?php
            $t = clean_title($row['titre'] ?? '');
            $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
            $uCat = category_url($row['categorie'] ?? 'Interviews');
            $date = fmt_date($row['date_add'] ?: $row['created_at']);
            $imgs = parse_cover_images($row['cover'] ?? '');
            $img = $imgs[0] ?? null;
            $isVideo = !empty(trim((string)($row['videos'] ?? '')));
          ?>
          <div class="jl_mmlistc">
            <div class="jl_mmlist_layout jl_lisep">
              <div class="jl_li_in">
                <div class="jl_img_holder">
                  <div class="jl_imgw jl_radus_e">
                    <div class="jl_imgin">
                      <?php if ($img): ?>
                        <img loading="lazy" decoding="async" width="200" height="150"
                             src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                             class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                             alt="<?= htmlspecialchars($t) ?>"
                             data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                      <?php else: ?>
                        <div style="width:200px;height:150px;background:#111;border-radius:12px;"></div>
                      <?php endif; ?>
                    </div>

                    <?php if ($isVideo): ?>
                      <div class="jl_video_badge" aria-hidden="true">
                        <div class="jl_play"><i class="bi bi-youtube"></i></div>
                      </div>
                    <?php endif; ?>

                    <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                  </div>
                </div>

                <div class="jl_fe_text">
                  <span class="jl_f_cat jl_lb">
                    <a class="jl_cat_txt jl_cat66"
                       style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? 'Interviews')) ?>!important"
                       href="<?= htmlspecialchars($uCat) ?>">
                      <span><?= htmlspecialchars($row['categorie'] ?? 'Interviews') ?></span>
                    </a>
                  </span>

                  <h3 class="h3 jl_fe_title jl_txt_2row">
                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                  </h3>

                  <span class="jl_post_meta">
                    <span class="post-date"><?= htmlspecialchars($date) ?></span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
    <!-- /3 minimalistes articles Interview 2-->

    </div>
</div>                              
</div>
</div>
</div>              
</div>
</div>

<!-- end bloc Interview 2 -->

</div>
<!-- end bloc Interview global -->

<!-- start bloc Interview -->

</div>



<div class="elementor-element elementor-element-5fd8a8df e-con-full e-flex e-con e-child" data-id="5fd8a8df" data-element_type="container">

<!-- publicités -->
<div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
    <div class="elementor-widget-container">
        <div class="jl_ads_img_w">
            <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="large_portrait" data-emplacement="pub-body-sidebar-1" data-rotate="10000"></div>                       
        </div>
  </div>
</div>
<!-- publicités -->

<!-- start bloc À ne pas manquer -->    
   <div class="elementor-element elementor-element-7b0263dc elementor-widget elementor-widget-bopea-section-title" data-id="7b0263dc" data-element_type="widget" data-widget_type="bopea-section-title.default">
        <div class="elementor-widget-container">
            <div class="jlcus_sec_title jl_sec_style13 jl_secf_title  ">
            <div class="jlcus_sect_inner">
               <h2 class="jl-heading-text">
                  <span class="jl_ttw">À ne pas manquer</span>
               </h2>
            </div>
         </div>
      </div>
   </div>
    
   

   <div class="elementor-element elementor-element-98acca3 elementor-widget elementor-widget-bopea-grid-post" data-id="98acca3" data-element_type="widget" data-widget_type="bopea-grid-post.default">
        <div class="elementor-widget-container">
            <div class="jl_clear_at block-section jl-main-block jl_hide_meta jl_hide_desc jl_hide_line jl_hide_col_line jl_sh_num jl_num_top" data-blockid="blockid_98acca3" data-section_style="jl_mgrid" data-post_type="post" data-post_type_tax="none" data-page_max="25" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="2" data-offset="40" data-tabs_link="none" >      
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

<!-- end bloc À ne pas manquer -->

</div>
</div>
</div>



<div class="elementor-element elementor-element-4bd45cf e-flex e-con-boxed e-con e-parent" data-id="4bd45cf" data-element_type="container">


<!-- start video product digital presentation -->
<?php
// Récupérer toutes les vidéos actives configurées en base de données
$videoStmt = $db->query("SELECT * FROM home_video WHERE is_active = 1 ORDER BY created_at DESC");
$homeVideos = $videoStmt->fetchAll(PDO::FETCH_ASSOC);

// On prend la première vidéo comme vidéo principale par défaut
$mainVideo = !empty($homeVideos) ? $homeVideos[0] : null;

if ($mainVideo):
?>
<div class="e-con-inner" id="video-section-wrapper">
  <div style="width: 100%; max-width: 100%;">
    <link rel="stylesheet" href="/assets/css/video-section.css">
    
    <div class="video-presentation-container">
        <div class="video-header">
            <h2><?php echo htmlspecialchars($mainVideo['title']); ?> <span class="arrow"><i class="fas fa-chevron-right"></i></span></h2>
            <span class="video-subtitle"><?php echo htmlspecialchars($mainVideo['subtitle']); ?></span>
        </div>
        <div class="video-content-wrapper">
            <div class="main-video-area">
                <div class="floating-close-btn" onclick="toggleFloatingVideo(false)"><i class="bi bi-x-lg"></i></div>
                <div id="youtube-player"></div>
                <a href="<?php echo !empty($mainVideo['website_url']) ? htmlspecialchars($mainVideo['website_url']) : '#'; ?>" class="video-cta-btn" target="_blank">En savoir plus</a>
            </div>
            <div class="video-placeholder"></div>
        </div>
    </div>

    <script>
        // Passer la liste des IDs des vidéos à JS pour la playlist
        window.homeVideoIds = <?php echo json_encode(array_column($homeVideos, 'youtube_id')); ?>;
    </script>
    <script src="/assets/js/video-section.js"></script>
  </div>
</div>
<?php endif; ?>
<!-- end video product digital presentation -->
<br>
<br>

    <div class="e-con-inner">
        <div class="elementor-element elementor-element-6ca1f08e e-flex e-con-boxed e-con e-child" data-id="6ca1f08e" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
            <div class="e-con-inner">
                <div class="elementor-element elementor-element-679eb4a5 elementor-widget elementor-widget-bopea-section-title" data-id="679eb4a5" data-element_type="widget" data-widget_type="bopea-section-title.default">
                <div class="elementor-widget-container">
                
               <div class="jlcus_sec_title jl_sec_style8 jl_secf_title  ">
                  <div class="jlcus_sect_inner">
                     <h2 class="jl-heading-text">
                        <span class="jl_ttw">Restons en contact</span>
                     </h2>
                  </div>
               </div>                    
                </div>
                </div>
            
            <div class="elementor-element elementor-element-1f21d569 elementor-widget elementor-widget-bopea-text" data-id="1f21d569" data-element_type="widget" data-widget_type="bopea-text.default">
                <div class="elementor-widget-container">
                    <div class="jlc-ctw">
                     <p class="jl-cust">
                        Abonnez-vous à notre newsletter pour recevoir instantanément nos nouveaux articles !
                     </p>
                  </div>
                </div>
                </div>

                <div class="elementor-element elementor-element-732e702a elementor-widget__width-initial elementor-widget elementor-widget-bopea-contact-form" data-id="732e702a" data-element_type="widget" data-widget_type="bopea-contact-form.default">
                  <div class="elementor-widget-container">
                       <div class="jl-cf7 jl_c7in">
        
               

               <div class="wpcf7 no-js" id="wpcf7-f20925-p13574-o1" lang="en-US" dir="ltr" data-wpcf7-id="20925">
               <div class="screen-reader-response">
                  <p role="status" aria-live="polite" aria-atomic="true"></p> 
                  <ul></ul>
               </div>

                <?php

                    if (empty($_SESSION['csrf_newsletter'])) $_SESSION['csrf_newsletter'] = bin2hex(random_bytes(16));
                ?>
               
                <form action="#wpcf7-f20925-p13574-o1"
                  method="post"
                  class="wpcf7-form init js-nl-form"
                  data-action="/publication/ajax/newsletter_subscribe.php"
                  data-source="home_newsletter">

                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_newsletter']) ?>">
                  <fieldset class="hidden-fields-container">
                     <input type="hidden" name="localisation" value="en_US" />
                     <input type="hidden" name="data_hash" value="" />
                  </fieldset>
                  <div class="jl-c7sub">
                     <span class="wpcf7-form-control-wrap" data-name="your-email">
                        <input size="40" maxlength="400" class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-email" aria-required="true" aria-invalid="false" placeholder="Adresse e-mail" value="" type="email" name="your-email" />
                     </span>
                     <input class="wpcf7-form-control wpcf7-submit has-spinner" type="submit" value="Je m'abonne" style="text-transform: uppercase;" />
                  </div>
                  <span class="wpcf7-form-control-wrap" data-name="your-consent">
                     <span class="wpcf7-form-control wpcf7-acceptance">
                        <span class="wpcf7-list-item">
                           <label>
                              <input type="checkbox" name="your-consent" value="1" aria-invalid="false" />
                              <span class="wpcf7-list-item-label">
                                 J'accepte les termes et conditions
                              </span>
                           </label>
                        </span>
                     </span>
                  </span>
                  <div class="wpcf7-response-output" aria-hidden="true"></div>
               </form>
            </div>




         </div>
      </div>
   </div>
</div>
</div>
</div>
</div>


<div class="elementor-element elementor-element-c06d649 e-flex e-con-boxed e-con e-parent" data-id="c06d649" data-element_type="container">
<div class="e-con-inner">
<div class="elementor-element elementor-element-2613695 e-con-full e-flex e-con e-child" data-id="2613695" data-element_type="container">


<!-- start bloc Economie -->

<!-- header Economie -->
   <div class="elementor-element elementor-element-a0e4048 elementor-widget elementor-widget-bopea-section-title" data-id="a0e4048" data-element_type="widget" data-widget_type="bopea-section-title.default">
        <div class="elementor-widget-container">
            <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
            <div class="jlcus_sect_inner">
               <h2 class="jl-heading-text">
                  <span class="jl_ttw">
                     <span>Économie</span>
                  </span>
               </h2>
               <p class="jl_sct_bt">
                  <a href="<?= category_url('Économie') ?>" class="jlopt-text-link">                        
                     <span class="jl_bt_inner">
                        <span>En savoir plus</span>
                        <i class="jli-carrow"></i>                        
                     </span>
                  </a> 
               </p>
            </div>                    
         </div>                    
            </div>
    </div>
<!-- /header Economie -->



   <div class="elementor-element elementor-element-8883eb4 elementor-widget elementor-widget-bopea-feature-layout6" data-id="8883eb4" data-element_type="widget" data-widget_type="bopea-feature-layout6.default">
        <div class="elementor-widget-container">
            <div id="blockid_8883eb4" class="block-section jl-main-block jl_hide_desc jl_hide_col_line" >
            <div class="jl_mb_wrap_f jl_clear_at">
               <div class="jl-roww jl_contain jl-col-row">
                  <div class="jl_fr6_wrap">
                     <div class="jl_fr6_inner">     

                        <!-- large article Economie : 1 seul article -->
                        <?php if (!empty($ecoBig)): ?>
                          <?php
                            $t = clean_title($ecoBig['titre'] ?? '');
                            $uArticle = "/article/" . (int)$ecoBig['id'] . "/" . slugify($t);
                            $uCat = category_url($ecoBig['categorie'] ?? 'Économie');
                            $date = fmt_date($ecoBig['date_add'] ?: $ecoBig['created_at']);
                            $author = $ecoBig['auteur_nom'] ?? 'Rédaction';

                            $imgs = parse_cover_images($ecoBig['cover'] ?? '');
                            $img = $imgs[0] ?? null;

                            $isVideo = !empty(trim((string)($ecoBig['videos'] ?? '')));
                          ?>

                          <div class="jl_p_fr6 jl_m_fr6 jl_ov_el">
                            <div class="jl_img_holder">
                              <div class="jl_imgw jl_radus_e">
                                <div class="jl_imgin">
                                  <?php if ($img): ?>
                                    <img loading="lazy" decoding="async" width="1100" height="733"
                                         src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                         class="attachment-bopea_medium size-bopea_medium jl-lazyload lazyload wp-post-image"
                                         alt="<?= htmlspecialchars($t) ?>"
                                         data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                                  <?php else: ?>
                                    <div style="width:100%;height:260px;background:#111;border-radius:12px;"></div>
                                  <?php endif; ?>
                                </div>

                                <?php if ($isVideo): ?>
                                  <div class="jl_video_badge" aria-hidden="true">
                                    <div class="jl_play"><i class="bi bi-youtube"></i></div>
                                  </div>
                                <?php endif; ?>

                                <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                              </div>
                            </div>

                            <div class="jl_fe_text">
                              <div class="jl_fe_inner">

                                <span class="jl_f_cat jl_lb3">
                                  <a class="jl_cat_lbl jl_cat68"
                                     style="background:<?= htmlspecialchars(category_color('Économie')) ?>!important"
                                     href="<?= htmlspecialchars($uCat) ?>">
                                    <span>Économie</span>
                                  </a>
                                </span>

                                <h2 class="h2 jl_fe_title jl_txt_2row">
                                  <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                                </h2>

                                <p class="jl_fe_des"><?= htmlspecialchars(excerpt($ecoBig['contenu'] ?? '', 220)) ?></p>

                                <span class="jl_post_meta">
                                  <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                                  <span class="post-date"><?= htmlspecialchars($date) ?></span>
                                </span>

                                <a href="<?= htmlspecialchars($uArticle) ?>" aria-label="<?= htmlspecialchars($t) ?>" class="jl_cap_ov"></a>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>
                    <!-- /large article Economie -->


                    <!-- minimalist articles Economie : 4 articles -->
                        <?php foreach ($ecoSmalls as $row): ?>
                          <?php
                            $t = clean_title($row['titre'] ?? '');
                            $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                            $uCat = category_url($row['categorie'] ?? 'Économie');
                            $date = fmt_date($row['date_add'] ?: $row['created_at']);
                            $author = $row['auteur_nom'] ?? 'Rédaction';

                            $imgs = parse_cover_images($row['cover'] ?? '');
                            $img = $imgs[0] ?? null;

                            $isVideo = !empty(trim((string)($row['videos'] ?? '')));
                          ?>

                          <div class="jl_p_fr6 jl_cgrid_layout jl_frsha jl_sm_mt">
                            <div class="jl_imgw jl_radus_e">
                              <div class="jl_imgin">
                                <?php if ($img): ?>
                                  <img loading="lazy" decoding="async" width="680" height="453"
                                       src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                       class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                                       alt="<?= htmlspecialchars($t) ?>"
                                       data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                                <?php else: ?>
                                  <div style="width:100%;height:160px;background:#111;border-radius:12px;"></div>
                                <?php endif; ?>
                              </div>

                              <?php if ($isVideo): ?>
                                <div class="jl_video_badge" aria-hidden="true">
                                  <div class="jl_play"><i class="bi bi-youtube"></i></div>
                                </div>
                              <?php endif; ?>

                              <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                              <span class="jl_f_cat jl_lb7">
                                <a class="jl_cat_lbl jl_cat68"
                                   style="background:<?= htmlspecialchars(category_color('Économie')) ?>!important"
                                   href="<?= htmlspecialchars($uCat) ?>">
                                  <span>Économie</span>
                                </a>
                              </span>
                            </div>

                            <div class="jl_fe_text">
                              <h3 class="h3 jl_fe_title jl_txt_2row">
                                <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                              </h3>

                              <span class="jl_post_meta">
                                <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                                <span class="post-date"><?= htmlspecialchars($date) ?></span>
                              </span>
                            </div>
                          </div>

                        <?php endforeach; ?>
                        <!-- /minimalist articles Economie -->
         </div>
      </div>
   </div>
</div>
</div>              
</div>
</div>
<!-- end bloc économia -->


<!-- start bloc Politique -->

<!-- header Politique -->
<div class="elementor-element elementor-element-22ac040 elementor-widget elementor-widget-bopea-section-title" data-id="22ac040" data-element_type="widget" data-widget_type="bopea-section-title.default">
    <div class="elementor-widget-container">
        <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
         <div class="jlcus_sect_inner">
            <h2 class="jl-heading-text">
               <span class="jl_ttw">
                  <span>Politique</span>
               </span>
            </h2>
            <p class="jl_sct_bt">
                <?php $uCatInnov = category_url($catInnov); ?>
               <a href="<?= htmlspecialchars($uCatInnov) ?>" class="jlopt-text-link">
                  <span class="jl_bt_inner">
                     <span>En savoir plus</span>
                     <i class="jli-carrow"></i>                        
                  </span>
               </a>                  
            </p>
         </div>
      </div>
   </div>
</div>
<!-- /header Politique -->

<div class="elementor-element elementor-element-71abc77 elementor-widget elementor-widget-bopea-list-post" data-id="71abc77" data-element_type="widget" data-widget_type="bopea-list-post.default">
    <div class="elementor-widget-container">
        <div class="block-section jl-main-block jl_en_fr jl_frli" data-blockid="blockid_71abc77" data-section_style="jl_m_list" data-post_type="post" data-post_type_tax="none" data-page_max="50" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="1" data-offset="31" data-tabs_link="none" >     
            <div class="jl_cw_wrap_f jl_clear_at">
                <div class="jl_main_list_cw jl_wrap_eb jl_clear_at jl_lm_list">
                    <div class="jl-roww jl_contain jl_fli_wrap jl-col-row">           

               <!-- large article Politique : 1 seul article -->
                <?php if (!empty($invBig)): ?>
                  <?php
                    $t = clean_title($invBig['titre'] ?? '');
                    $uArticle = "/article/" . (int)$invBig['id'] . "/" . slugify($t);
                    $uCat = category_url($catInnov);
                    $date = fmt_date($invBig['date_add'] ?: $invBig['created_at']);
                    $author = $invBig['auteur_nom'] ?? 'Rédaction';

                    $imgs = parse_cover_images($invBig['cover'] ?? '');
                    $img = $imgs[0] ?? null;

                    $isVideo = !empty(trim((string)($invBig['videos'] ?? '')));
                  ?>

                  <div class="jl_clist_layout jl_lisep jl_frsha">
                    <div class="jl_li_in">
                      <div class="jl_img_holder">
                        <div class="jl_imgw jl_radus_e">
                          <div class="jl_imgin">
                            <?php if ($img): ?>
                              <img loading="lazy" decoding="async" width="680" height="425"
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

                          <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                        </div>
                      </div>

                      <div class="jl_fe_text">
                        <span class="jl_f_cat jl_lb3">
                          <a class="jl_cat_lbl jl_cat65"
                             style="background:<?= htmlspecialchars(category_color($catInnov)) ?>!important"
                             href="<?= htmlspecialchars($uCat) ?>">
                            <span><?= htmlspecialchars($labelInnov) ?></span>
                          </a>
                        </span>

                        <h2 class="h3 jl_fe_title jl_txt_2row">
                          <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                        </h2>

                        <p class="jl_fe_des"><?= htmlspecialchars(excerpt($invBig['contenu'] ?? '', 220)) ?></p>

                        <span class="jl_post_meta">
                          <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                          <span class="post-date"><?= htmlspecialchars($date) ?></span>
                        </span>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
                <!-- /large article Politique -->


            </div>
            </div>
        </div>      
    </div>              
</div>
</div>



<div class="elementor-element elementor-element-0298454 elementor-widget elementor-widget-bopea-grid-post" data-id="0298454" data-element_type="widget" data-widget_type="bopea-grid-post.default">
    <div class="elementor-widget-container">
        <div class="jl_clear_at block-section jl-main-block jl_hide_desc jl_hide_line jl_hide_col_line jl_num_top" data-blockid="blockid_0298454" data-section_style="jl_mgrid" data-post_type="post" data-post_type_tax="none" data-page_max="17" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="3" data-offset="32" data-tabs_link="none" >     
            <div class="jl_grid_wrap_f jl_wrap_eb jl_clear_at">
               <div class="jl-roww jl_contain jl_cgrid_wrap jl-col-row">
                    

            <!-- minimalistes articles Politique : 3 articles -->
            <?php foreach ($invSmalls as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($catInnov);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);
                $author = $row['auteur_nom'] ?? 'Rédaction';

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;

                $isVideo = !empty(trim((string)($row['videos'] ?? '')));
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

                    <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                    <span class="jl_f_cat jl_lb7">
                      <a class="jl_cat_lbl jl_cat65"
                         style="background:<?= htmlspecialchars(category_color($catInnov)) ?>!important"
                         href="<?= htmlspecialchars($uCat) ?>">
                        <span><?= htmlspecialchars($labelInnov) ?></span>
                      </a>
                    </span>
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
            <!-- /minimalistes articles Politique : 3 articles -->
    
            </div>
      </div>
   </div>               
</div>
</div>

<!-- end bloc Politique -->




<!-- start bloc Int’l -->

<!-- header Int’l -->
<div class="elementor-element elementor-element-770c4ea elementor-widget elementor-widget-bopea-section-title" data-id="770c4ea" data-element_type="widget" data-widget_type="bopea-section-title.default">
    <div class="elementor-widget-container">
        <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
         <div class="jlcus_sect_inner">
            <h2 class="jl-heading-text">
               <span class="jl_ttw">
                  <span><?= htmlspecialchars($labelEnergy) ?></span>
               </span>                                          
            </h2>
            <p class="jl_sct_bt">
                <?php $uCatEnergy = category_url($catEnergy); ?>
               <a href="<?= htmlspecialchars($uCatEnergy) ?>" class="jlopt-text-link">               
                  <span class="jl_bt_inner">
                     <span>En savoir plus</span>
                     <i class="jli-carrow"></i>                        
                  </span>
               </a>                  
            </p>
         </div>                    
      </div>                    
    </div>
</div>
<!-- /header Int’l -->

<div class="elementor-element elementor-element-22310aa elementor-widget elementor-widget-bopea-grid-post" data-id="22310aa" data-element_type="widget" data-widget_type="bopea-grid-post.default">
    <div class="elementor-widget-container">
        <div class="jl_clear_at block-section jl-main-block jl_hide_line jl_hide_col_line jl_num_top" data-blockid="blockid_22310aa" data-section_style="jl_mgrid" data-post_type="post" data-post_type_tax="none" data-page_max="13" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="4" data-offset="35" data-tabs_link="none" >      
            <div class="jl_grid_wrap_f jl_wrap_eb jl_clear_at">
               <div class="jl-roww jl_contain jl_cgrid_wrap jl-col-row">
                    

            <!-- large article Int’l : 4 articles -->
            <?php foreach ($energyPosts as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = $uCatEnergy;
                $date = fmt_date($row['date_add'] ?: $row['created_at']);
                $author = $row['auteur_nom'] ?? 'Rédaction';

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;

                $isVideo = !empty(trim((string)($row['videos'] ?? '')));

                // Donut global (DONUT_MAX_GLOBAL = 2000). Afficher seulement si > 0
                $vues = (int)($row['vues_int'] ?? 0);
                $p = donut_percent($vues); // ✅ 1 seul param (ton nouveau helper)
                $deg = (int)round(($p / 100) * 360);
              ?>

              <div class="jl_cgrid_layout jl_frsha jl_in_num jl_numl">
                <div class="jl_img_holder">
                  <div class="jl_imgw jl_radus_e">
                    <div class="jl_imgin">
                      <?php if ($img): ?>
                        <img loading="lazy" decoding="async" width="680" height="383"
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
                                <div class="jl-renut-filler" style="background-color:#09b960; transform: rotate(0deg);"></div>
                              </div>
                              <div class="jl-renut-section jl-renut-section-left" style="transform: rotate(0deg);">
                                <div class="jl-renut-filler" style="background-color:#09b960; transform: rotate(<?= (int)$deg ?>deg);"></div>
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

                    <span class="jl_f_cat jl_lb7">
                      <a class="jl_cat_lbl jl_cat69"
                         style="background:<?= htmlspecialchars(category_color($catEnergy)) ?>!important"
                         href="<?= htmlspecialchars($uCat) ?>">
                        <span><?= htmlspecialchars($labelEnergy) ?></span>
                      </a>
                    </span>
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
            <!-- /large article Int’l : 4 articles -->



                  </div>
               </div>
            </div>              
         </div>
      </div>
        
<!-- end bloc Int’l -->

      
<!-- publicités  -->
      <div class="elementor-element elementor-element-2f8b39d elementor-widget elementor-widget-bopea-section-ads-img" data-id="2f8b39d" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
            <div class="elementor-widget-container">
                <div class="jl_ads_img_w">
                    <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="paysage_small" data-emplacement="pub-body-3" data-rotate="25000">
                    </div>
                </div>
            </div>
        </div>
<!-- /publicités  -->



<!-- start bloc Events -->

<!-- header  Events-->
<div class="elementor-element elementor-element-66a2256 elementor-widget elementor-widget-bopea-section-title" data-id="66a2256" data-element_type="widget" data-widget_type="bopea-section-title.default">
    <div class="elementor-widget-container">
        <div class="jlcus_sec_title jl_sec_style13 jl_secf_title jl_secb_menu no">
         <div class="jlcus_sect_inner">
            <h2 class="jl-heading-text">
               <span class="jl_ttw">
                  <span>Société</span>
               </span>                                          
            </h2>
            <p class="jl_sct_bt">
               <a href="<?= htmlspecialchars($uCatCulture) ?>" class="jlopt-text-link">                        
                  <span class="jl_bt_inner">
                     <span>En savoir plus</span>
                     <i class="jli-carrow"></i>                        
                  </span>
               </a>                  
            </p>
         </div>                    
      </div>                    
   </div>
</div>

<!-- /header  Events-->

<div class="elementor-element elementor-element-4169abc elementor-widget elementor-widget-bopea-feature-layout6" data-id="4169abc" data-element_type="widget" data-widget_type="bopea-feature-layout6.default">
    <div class="elementor-widget-container">
        <div id="blockid_4169abc" class="block-section jl-main-block jl_hide_desc jl_hide_col_line" >
            <div class="jl_mb_wrap_f jl_clear_at">
               <div class="jl-roww jl_contain jl-col-row">
                  <div class="jl_fr6_wrap">
                  <div class="jl_fr6_inner">        
                     
                  <!-- large article Events : 1 seul article -->
                     <?php if (!empty($cultureBig)): ?>
                      <?php
                        $t = clean_title($cultureBig['titre'] ?? '');
                        $uArticle = "/article/" . (int)$cultureBig['id'] . "/" . slugify($t);
                        $date = fmt_date($cultureBig['date_add'] ?: $cultureBig['created_at']);
                        $author = $cultureBig['auteur_nom'] ?? 'Rédaction';

                        $imgs = parse_cover_images($cultureBig['cover'] ?? '');
                        $img = $imgs[0] ?? null;

                        $isVideo = !empty(trim((string)($cultureBig['videos'] ?? '')));
                      ?>

                      <div class="jl_p_fr6 jl_m_fr6 jl_ov_el">
                        <div class="jl_img_holder">
                          <div class="jl_imgw jl_radus_e">
                            <div class="jl_imgin">
                              <?php if ($img): ?>
                                <img loading="lazy" decoding="async" width="1100" height="734"
                                     src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                     class="attachment-bopea_medium size-bopea_medium jl-lazyload lazyload wp-post-image"
                                     alt="<?= htmlspecialchars($t) ?>"
                                     data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                              <?php else: ?>
                                <div style="width:100%;height:260px;background:#111;border-radius:12px;"></div>
                              <?php endif; ?>
                            </div>

                            <?php if ($isVideo): ?>
                              <div class="jl_video_badge" aria-hidden="true">
                                <div class="jl_play"><i class="bi bi-youtube"></i></div>
                              </div>
                            <?php endif; ?>

                            <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                          </div>
                        </div>

                        <div class="jl_fe_text">
                          <div class="jl_fe_inner">
                            <span class="jl_f_cat jl_lb3">
                              <a class="jl_cat_lbl jl_cat67"
                                 style="background:<?= htmlspecialchars(category_color($catCulture)) ?>!important"
                                 href="<?= htmlspecialchars($uCatCulture) ?>">
                                <span><?= htmlspecialchars($catCulture) ?></span>
                              </a>
                            </span>

                            <h2 class="h2 jl_fe_title jl_txt_2row">
                              <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                            </h2>

                            <p class="jl_fe_des"><?= htmlspecialchars(excerpt($cultureBig['contenu'] ?? '', 220)) ?></p>

                            <span class="jl_post_meta">
                              <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                              <span class="post-date"><?= htmlspecialchars($date) ?></span>
                            </span>

                            <a href="<?= htmlspecialchars($uArticle) ?>" aria-label="<?= htmlspecialchars($t) ?>" class="jl_cap_ov"></a>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                 <!-- /large article Events : 1 seul article -->  

               <!-- minimalistes articles Events : 4 articles -->
                     <?php foreach ($cultureSmalls as $row): ?>
                      <?php
                        $t = clean_title($row['titre'] ?? '');
                        $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                        $date = fmt_date($row['date_add'] ?: $row['created_at']);
                        $author = $row['auteur_nom'] ?? 'Rédaction';

                        $imgs = parse_cover_images($row['cover'] ?? '');
                        $img = $imgs[0] ?? null;

                        $isVideo = !empty(trim((string)($row['videos'] ?? '')));
                      ?>

                      <div class="jl_p_fr6 jl_cgrid_layout jl_frsha jl_sm_mt">
                        <div class="jl_imgw jl_radus_e">
                          <div class="jl_imgin">
                            <?php if ($img): ?>
                              <img loading="lazy" decoding="async" width="680" height="451"
                                   src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                   class="attachment-bopea_layouts size-bopea_layouts jl-lazyload lazyload wp-post-image"
                                   alt="<?= htmlspecialchars($t) ?>"
                                   data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                            <?php else: ?>
                              <div style="width:100%;height:160px;background:#111;border-radius:12px;"></div>
                            <?php endif; ?>
                          </div>

                          <?php if ($isVideo): ?>
                            <div class="jl_video_badge" aria-hidden="true">
                              <div class="jl_play"><i class="bi bi-youtube"></i></div>
                            </div>
                          <?php endif; ?>

                          <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>

                          <span class="jl_f_cat jl_lb7">
                            <a class="jl_cat_lbl jl_cat67"
                               style="background:<?= htmlspecialchars(category_color($catCulture)) ?>!important"
                               href="<?= htmlspecialchars($uCatCulture) ?>">
                              <span><?= htmlspecialchars($catCulture) ?></span>
                            </a>
                          </span>
                        </div>

                        <div class="jl_fe_text">
                          <h3 class="h3 jl_fe_title jl_txt_2row">
                            <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                          </h3>

                          <span class="jl_post_meta">
                            <span class="jl_author_img_w">Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a></span>
                            <span class="post-date"><?= htmlspecialchars($date) ?></span>
                          </span>
                        </div>
                      </div>

                    <?php endforeach; ?>
                  <!-- /minimalistes articles Events : 4 articles -->





                  </div>
               </div>           
               </div>
        </div>          
    </div>              
</div>
</div>

<!-- end bloc Events -->

</div>




<div class="elementor-element elementor-element-a87d222 e-con-full e-flex e-con e-child" data-id="a87d222" data-element_type="container">
    

<!-- publicités -->
   <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
        <div class="elementor-widget-container">
            <div class="jl_ads_img_w">
                <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="rectangle" data-emplacement="pub-body-sidebar-2" data-rotate="10000"></div>                       
            </div>
      </div>
    </div>
<!-- publicités -->


<!-- start block articles les plus vus -->

    <div class="elementor-element elementor-element-159d15e elementor-widget elementor-widget-bopea-section-title" data-id="159d15e" data-element_type="widget" data-widget_type="bopea-section-title.default">
        <div class="elementor-widget-container">
            <div class="jlcus_sec_title jl_sec_style13 jl_secf_title  ">
            <div class="jlcus_sect_inner">
               <h2 class="jl-heading-text">
                  <span class="jl_ttw">Les plus vus</span>                    
               </h2>
            </div>                    
         </div>                    
        </div>
    </div>
    
   <div class="elementor-element elementor-element-b94b1fc elementor-widget elementor-widget-bopea-main-ov-sm-li" data-id="b94b1fc" data-element_type="widget" data-widget_type="bopea-main-ov-sm-li.default">
        <div class="elementor-widget-container">
            <div id="blockid_b94b1fc" class="block-section jl-main-block" data-blockid="blockid_b94b1fc" data-section_style="jl_main_ov_sm_li" data-post_type="post" data-post_type_tax="none" data-page_max="17" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="3" data-offset="47" data-tabs_link="none" >
                <div class="jl_mb_wrap_f jl_wrap_eb jl_clear_at">
                  <div class="jl-roww jl_contain jl_contain_mix jl-col-row">                 <div class="jl_fr16_wrap"> 
                        <div class="jl_fr16_inner">     
                           
                        <!-- large article les plus vus : 1 seul article -->
                          <?php if (!empty($mvBig)): ?>
                          <?php
                            $t = clean_title($mvBig['titre'] ?? '');
                            $uArticle = "/article/" . (int)$mvBig['id'] . "/" . slugify($t);
                            $uCat = category_url($mvBig['categorie'] ?? '');
                            $date = fmt_date($mvBig['date_add'] ?: $mvBig['created_at']);
                            $author = $mvBig['auteur_nom'] ?? 'Rédaction';

                            $imgs = parse_cover_images($mvBig['cover'] ?? '');
                            $img = $imgs[0] ?? null;

                            $isVideo = !empty(trim((string)($mvBig['videos'] ?? '')));
                          ?>

                          <div class="jl_mini_ov_sb">
                            <div class="jl_ov_layout jl_ov_mix_opt jl_ov_el jl_ov_mh jl_sm_mt">
                              <div class="jl_img_holder">
                                <div class="jl_imgw jl_radus_e">
                                  <div class="jl_imgin">
                                    <?php if ($img): ?>
                                      <img loading="lazy" decoding="async" width="680" height="486"
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

                                  <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                                </div>
                              </div>

                              <div class="jl_fe_text">
                                <div class="jl_fe_inner">
                                  <?php if (!empty($mvBig['categorie'])): ?>
                                    <span class="jl_f_cat jl_lb3">
                                      <a class="jl_cat_lbl jl_cat67"
                                         style="background:<?= htmlspecialchars(category_color($mvBig['categorie'])) ?>!important"
                                         href="<?= htmlspecialchars($uCat) ?>">
                                        <span><?= htmlspecialchars($mvBig['categorie']) ?></span>
                                      </a>
                                    </span>
                                  <?php endif; ?>

                                  <h3 class="h3 jl_fe_title jl_txt_2row">
                                    <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                                  </h3>

                                  <span class="jl_post_meta">
                                    <span class="jl_author_img_w">
                                      Par <a href="#!" rel="author"><?= htmlspecialchars($author) ?></a>
                                    </span>
                                    <span class="post-date"><?= htmlspecialchars($date) ?></span>
                                  </span>

                                  <a href="<?= htmlspecialchars($uArticle) ?>" aria-label="<?= htmlspecialchars($t) ?>" class="jl_cap_ov"></a>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>
                        <!-- /large article les plus vus : 1 seul article -->

                        <!-- minimalistes articles les plus vus : 2 articles -->
                        <?php foreach ($mvSmalls as $row): ?>

                            <?php
                                  $t = clean_title($row['titre'] ?? '');
                                  $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                                  $uCat = category_url($row['categorie'] ?? '');
                                  $date = fmt_date($row['date_add'] ?: $row['created_at']);

                                  $imgs = parse_cover_images($row['cover'] ?? '');
                                  $img = $imgs[0] ?? null;

                                  $isVideo = !empty(trim((string)($row['videos'] ?? '')));
                                ?>
                            <div class="jl_mmlistw jl_lisep">

                                <div class="jl_mmlistc">
                                  <div class="jl_mmlist_layout jl_lisep">
                                    <div class="jl_li_in">
                                      <div class="jl_img_holder">
                                        <div class="jl_imgw jl_radus_e">
                                          <div class="jl_imgin">
                                            <?php if ($img): ?>
                                              <img loading="lazy" decoding="async" width="200" height="143"
                                                   src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                                   class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                                                   alt="<?= htmlspecialchars($t) ?>"
                                                   data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                                            <?php else: ?>
                                              <div style="width:200px;height:143px;background:#111;border-radius:12px;"></div>
                                            <?php endif; ?>
                                          </div>

                                          <?php if ($isVideo): ?>
                                            <div class="jl_video_badge" aria-hidden="true">
                                              <div class="jl_play"><i class="bi bi-youtube"></i></div>
                                            </div>
                                          <?php endif; ?>

                                          <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                                        </div>
                                      </div>

                                      <div class="jl_fe_text">
                                        <?php if (!empty($row['categorie'])): ?>
                                          <span class="jl_f_cat jl_lb">
                                            <a class="jl_cat_txt jl_cat67"
                                               style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'])) ?>!important"
                                               href="<?= htmlspecialchars($uCat) ?>">
                                              <span><?= htmlspecialchars($row['categorie']) ?></span>
                                            </a>
                                          </span>
                                        <?php endif; ?>

                                        <h3 class="h3 jl_fe_title jl_txt_2row">
                                          <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                                        </h3>

                                        <span class="jl_post_meta">
                                          <span class="post-date"><?= htmlspecialchars($date) ?></span>
                                        </span>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                            </div>
                         <?php endforeach; ?>
                        <!-- /minimalistes articles les plus vus : 2 articles -->


                        </div>
                     </div>                             
                     </div>
                    </div>
            </div>              
         </div>
        </div>
<!-- end block articles les plus vus -->


    </div>
</div>
</div>
</div>
</div>
