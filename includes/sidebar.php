<?php

require_once __DIR__.'/categories.php';

// --- Top 10 hebdo -> afficher 3 à chaque reload (rotation) ---
// IMPORTANT: session_start() doit être fait dans index.php AVANT tout output

if (! defined('WEEK_TOP10_LIMIT')) {
    define('WEEK_TOP10_LIMIT', 10);
}
if (! defined('WEEK_SHOW_PER_LOAD')) {
    define('WEEK_SHOW_PER_LOAD', 3);
}
if (! defined('WEEK_RANGE_DAYS')) {
    define('WEEK_RANGE_DAYS', 7);
}

// clé semaine ISO (change chaque semaine)
$weekKey = date('o-\WW'); // ex: 2026-W01

// reset si nouvelle semaine
if (($_SESSION['week_top10_key'] ?? null) !== $weekKey) {
    $_SESSION['week_top10_key'] = $weekKey;
    $_SESSION['week_top10_ids'] = [];
    $_SESSION['week_top10_pos'] = 0;
}

// 1) construire la liste top10 si vide
if (empty($_SESSION['week_top10_ids'])) {

    $sqlTop10 = "
    SELECT a.id
    FROM actualites a
    WHERE a.status = 1
      AND a.statut_validation = 'valide'
      AND a.statut_paiement IN ('paye','gratuit')
      AND COALESCE(a.date_add, a.created_at) >= (NOW() - INTERVAL " . WEEK_RANGE_DAYS . " DAY)
    ORDER BY CAST(a.vues AS UNSIGNED) DESC, COALESCE(a.date_add, a.created_at) DESC
    LIMIT " . WEEK_TOP10_LIMIT . "
    ";
    $ids = $db->query($sqlTop10)->fetchAll(PDO::FETCH_COLUMN);

    // fallback si pas assez d'articles cette semaine
    if (count($ids) < WEEK_TOP10_LIMIT) {
        $need = WEEK_TOP10_LIMIT - count($ids);

        $whereNotIn = '';
        if (!empty($ids)) {
            $safe = array_map('intval', $ids);
            $whereNotIn = " AND a.id NOT IN (" . implode(',', $safe) . ") ";
        }

        $sqlFill = "
        SELECT a.id
        FROM actualites a
        WHERE a.status = 1
          AND a.statut_validation = 'valide'
          AND a.statut_paiement IN ('paye','gratuit')
          $whereNotIn
        ORDER BY COALESCE(a.date_add, a.created_at) DESC
        LIMIT $need
        ";
        $more = $db->query($sqlFill)->fetchAll(PDO::FETCH_COLUMN);
        $ids = array_merge($ids, $more);
    }

    $_SESSION['week_top10_ids'] = array_values(array_unique(array_map('intval', $ids)));
    $_SESSION['week_top10_pos'] = 0;
}

// 2) slice 3 ids selon pos
$topIds = $_SESSION['week_top10_ids'];
$count  = count($topIds);

$weekTopRows = [];
if ($count > 0) {

    $pos = (int)($_SESSION['week_top10_pos'] ?? 0);

    $slice = array_slice($topIds, $pos, WEEK_SHOW_PER_LOAD);
    if (count($slice) < WEEK_SHOW_PER_LOAD) {
        $slice = array_merge($slice, array_slice($topIds, 0, WEEK_SHOW_PER_LOAD - count($slice)));
    }

    // 3) avancer le pointeur pour le prochain reload
    $_SESSION['week_top10_pos'] = ($pos + WEEK_SHOW_PER_LOAD) % $count;

    // 4) fetch infos
    $in = implode(',', array_fill(0, count($slice), '?'));
    $sqlFetch = "
    SELECT a.*, u.nom AS auteur_nom, CAST(a.vues AS UNSIGNED) AS vues_int
    FROM actualites a
    LEFT JOIN users u ON u.id = a.id_redaction
    WHERE a.id IN ($in)
    ";
    $st = $db->prepare($sqlFetch);
    $st->execute($slice);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // reorder selon $slice
    $map = [];
    foreach ($rows as $r) $map[(int)$r['id']] = $r;

    foreach ($slice as $id) {
        if (isset($map[(int)$id])) $weekTopRows[] = $map[(int)$id];
    }
}
// --- /Top 10 hebdo -> afficher 3 à chaque reload ---
?>


<div id="jl_sb_nav" class="jl_mobile_nav_wrapper">
   <div id="nav" class="jl_mobile_nav_inner">
      <div class="logo_small_wrapper_table">
         <div class="logo_small_wrapper">
            <a class="logo_link" href="index.php"></a>
         </div>
         <div class="menu_mobile_icons mobile_close_icons closed_menu">
            <span class="jl_close_wapper">
               <span class="jl_close_1"></span>
               <span class="jl_close_2"></span>
            </span>
         </div>              
      </div>               
      
      <ul id="mobile_menu_slide" class="menu_moble_slide">
         
         <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-13574 current_page_item menu-item-23497">
            <a href="accueil" aria-current="page">
               Accueil
               <span class="border-menu"></span>
            </a>
         </li>
         
         <?php foreach (chrononews_categories() as $sidebarCat): ?>
         <li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-23464">
            <a href="<?= category_url($sidebarCat) ?>">
               <?= htmlspecialchars($sidebarCat, ENT_QUOTES, 'UTF-8') ?>
               <span class="border-menu"></span>
            </a>
         </li>
         <?php endforeach; ?>

      </ul>               
      
<div id="bopea_widget_add_layouts-2" class="widget jl_cus_layouts_widget">
   <div class="widget_jl_wrapper jl-cuslayouts-wrapper">
   <div class="jl_cuslayouts_inner">
		<div data-elementor-type="wp-post" data-elementor-id="12224" class="elementor elementor-12224">
		<div class="elementor-element elementor-element-a0cd274 e-flex e-con-boxed e-con e-parent" data-id="a0cd274" data-element_type="container">
		<div class="e-con-inner">
		
      <div class="elementor-element elementor-element-c905183 elementor-widget elementor-widget-bopea-section-title" data-id="c905183" data-element_type="widget" data-widget_type="bopea-section-title.default">
         <div class="elementor-widget-container">
            <div class="jlcus_sec_title jl_sec_style8 jl_secf_title  ">
               <div class="jlcus_sect_inner">
                  <h2 class="jl-heading-text">
                     <span class="jl_ttw">Mise à jour Hebdo</span>
                  </h2>
               </div>                    
            </div>                    
 			</div>
		</div>

		<div class="elementor-element elementor-element-bf3cec5 elementor-widget elementor-widget-bopea-xsmall-list" data-id="bf3cec5" data-element_type="widget" data-widget_type="bopea-xsmall-list.default">
		<div class="elementor-widget-container">
			<div class="jl_clear_at block-section jl-main-block jl_hide_meta jl_sh_num jl_num_mid jl_hide_line jl_hide_col_line" data-blockid="blockid_bf3cec5" data-section_style="jl_xsli" data-post_type="post" data-post_type_tax="none" data-page_max="17" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="3" data-offset="31" data-tabs_link="none" >
			   <div class="jl_grid_wrap_f jl_wrap_eb jl_xsgrid jl_clear_at">
		      <div class="jl-roww jl_contain jl_fli_wrap jl-col-row">					
				

         <?php foreach ($weekTopRows as $row): ?>
           <?php
             $t = clean_title($row['titre'] ?? '');
             $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
             $uCat = category_url($row['categorie'] ?? '');
             $date = fmt_date($row['date_add'] ?: $row['created_at']);

             $imgs = parse_cover_images($row['cover'] ?? '');
             $img = $imgs[0] ?? null;

             $isVideo = !empty(trim((string)($row['videos'] ?? '')));
           ?>

           <!-- single article mobile menu or sidebar right -->
           <div class="jl_mmlist_layout jl_lisep jl_risep jl_in_num jl_numl">
             <div class="jl_li_in jl_nun_i">
               <span class="jl_nun_d"></span>

               <div class="jl_img_holder">
                 <div class="jl_imgw jl_radus_e">
                   <div class="jl_imgin">
                     <?php if ($img): ?>
                       <img width="200" height="125"
                            src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                            class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                            alt="<?= htmlspecialchars($t) ?>"
                            decoding="async"
                            data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                     <?php else: ?>
                       <div style="width:200px;height:125px;background:#111;border-radius:12px;"></div>
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
                   <a class="jl_cat_txt jl_cat65"
                      style="border-bottom:2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? '')) ?>!important"
                      href="<?= htmlspecialchars($uCat) ?>">
                     <span><?= htmlspecialchars($row['categorie'] ?? '') ?></span>
                   </a>
                 </span>

                 <h2 class="h3 jl_fe_title jl_txt_2row">
                   <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                 </h2>

                 <span class="jl_post_meta">
                   <span class="post-date"><?= htmlspecialchars($date) ?></span>
                 </span>
               </div>

             </div>
           </div>
           <!-- /single article mobile menu or sidebar right -->
         <?php endforeach; ?>


			</div>				
										
			</div>		
		</div>				
   </div>
	</div>
</div>
</div>


<div class="elementor-element elementor-element-21e1ec9 e-flex e-con-boxed e-con e-parent" data-id="21e1ec9" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
	<div class="e-con-inner">
		<div class="elementor-element elementor-element-b5e6b29 elementor-view-default elementor-widget elementor-widget-icon" data-id="b5e6b29" data-element_type="widget" data-widget_type="icon.default">
			<div class="elementor-widget-container">
   			<div class="elementor-icon-wrapper">
      			<div class="elementor-icon">
         			<svg xmlns="http://www.w3.org/2000/svg" viewBox="2 4 20 16">
                     <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"></path>
                        <path d="m3 7l9 6l9-6"></path>
                     </g>
                  </svg>			
               </div>
   		   </div>
			</div>
		</div>
		
      <div class="elementor-element elementor-element-c0b8ea8 elementor-widget elementor-widget-bopea-text" data-id="c0b8ea8" data-element_type="widget" data-widget_type="bopea-text.default">
		   <div class="elementor-widget-container">
				<div class="jlc-ctw">
               <h3 class="jl-cust">
                Newsletter                
               </h3>
            </div>
        	</div>
		</div>
		
      <div class="elementor-element elementor-element-5ff43db elementor-widget elementor-widget-bopea-text" data-id="5ff43db" data-element_type="widget" data-widget_type="bopea-text.default">
			<div class="elementor-widget-container">
				<div class="jlc-ctw">
               <p class="jl-cust">
                  Recevez les derniers articles de ChronoNews actualités                
               </p>
            </div>
        	</div>
		</div>
		
      <div class="elementor-element elementor-element-cfc296e elementor-widget elementor-widget-bopea-contact-form" data-id="cfc296e" data-element_type="widget" data-widget_type="bopea-contact-form.default">
			<div class="elementor-widget-container">
				<div class="jl-cf7 jl_c7g">
        
               <div class="wpcf7 no-js" id="wpcf7-f6758-p13574-o2" lang="en-US" dir="ltr" data-wpcf7-id="6758">
               <div class="screen-reader-response">
                  <p role="status" aria-live="polite" aria-atomic="true"></p> 
                  <ul></ul>
               </div>
               
               <form action="#wpcf7-f20925-p13574-o2"
                  method="post"
                  class="wpcf7-form init js-nl-form"
                  data-action="/publication/ajax/newsletter_subscribe.php"
                  data-source="sidebar_newsletter">
                  
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_newsletter'] ?? '') ?>">
                  <fieldset class="hidden-fields-container">
                     <input type="hidden" name="localisation" value="en_US" />
                     <input type="hidden" name="data_hash" value="" />
                  </fieldset>
                  
                  <div class="jl-c7sub">
                     <span class="wpcf7-form-control-wrap" data-name="your-email">
                        <input size="40" maxlength="400" class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-email" aria-required="true" aria-invalid="false" placeholder="Entrez votre e-mail" value="" type="email" name="your-email" />
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
</div>
</div>            
</div>

<div class="nav_mb_f">
   <?php $socialVariant = 'mobile'; include __DIR__.'/partials/social-links.php'; ?>
</div>            
</div>

<div class="mobile_menu_overlay"></div>