<?php
require_once __DIR__.'/brand.php';
require_once __DIR__.'/categories.php';

$cnSiteName = cn_site_name();
$cnTagline = cn_tagline();
// --- Bloc Footer : 3 articles ---
$avoidDuplicatesFooter = false; // ✅ indépendant, évite conflit avec $avoidDuplicates

$whereNotIn = '';
$params = [];

if ($avoidDuplicatesFooter && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exfoot{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlFooter = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  $whereNotIn
ORDER BY CAST(a.vues AS UNSIGNED) DESC, COALESCE(a.date_add, a.created_at) DESC
LIMIT 3
";

$stmt = $db->prepare($sqlFooter);
$stmt->execute($params);
$footerRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback si < 3
$footerRows = fill_repeat($footerRows, 3);

// optionnel : ajouter au pool d’exclusion global
if ($avoidDuplicatesFooter && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $footerRows);
}
// --- /Bloc Footer ---
?>

<footer id="jl-footer-custpl" class="jl_ftpls jl_fwr">

<!-- publicités -->
<div class="jl_ads_img_w" style="width: 100%; display: flex; justify-content: center;">
    <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="paysage_large" data-emplacement="pub-footer" data-rotate="15000"></div>                       
</div>
<!-- publicités -->

   
	<div data-elementor-type="wp-post" data-elementor-id="22143" class="elementor elementor-22143">
		<div class="elementor-element elementor-element-39faf867 e-flex e-con-boxed e-con e-parent" data-id="39faf867" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
			<div class="e-con-inner">
		      <div class="elementor-element elementor-element-3a7d4c67 e-con-full e-flex e-con e-child" data-id="3a7d4c67" data-element_type="container">
				   <div class="elementor-element elementor-element-714d945c elementor-widget elementor-widget-bopea-section-logo" data-id="714d945c" data-element_type="widget" data-widget_type="bopea-section-logo.default">
				      <div class="elementor-widget-container">
					      <div class="logo_small_wrapper_table">
                        <div class="logo_small_wrapper">
                           <a class="logo_link" href="/accueil" aria-label="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>">
                              <span>
                              <img class="cn-footer-logo" src="<?= htmlspecialchars(cn_logo_footer(), ENT_QUOTES, 'UTF-8') ?>" width="72" height="72" alt="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>" />
                              </span>
                           </a>
                        </div>
                     </div>
    				   </div>
				   </div>
				   
               <div class="elementor-element elementor-element-54536c6e elementor-widget elementor-widget-bopea-text" data-id="54536c6e" data-element_type="widget" data-widget_type="bopea-text.default">
				      <div class="elementor-widget-container">
					      <div class="jlc-ctw">
                        <p class="jl-cust">
                           <?= htmlspecialchars($cnTagline, ENT_QUOTES, 'UTF-8') ?>             
                        </p>
                     </div>
        				</div>
				   </div>

				<div class="elementor-element elementor-element-c29ecfa elementor-widget elementor-widget-bopea-text" data-id="c29ecfa" data-element_type="widget" data-widget_type="bopea-text.default">
				   <div class="elementor-widget-container">
					   <div class="jlc-ctw">
                     <p class="jl-cust">
                        tel : +243 995 801 328                
                     </p>
                     <p class="jl-cust">
                        WhatsApp : +243 995 801 328              
                     </p>
                     <p class="jl-cust">
                        Email : contact@fintechmedias.cd              
                     </p>
                  </div>
        			</div>
				</div>
				<div class="elementor-element elementor-element-7cf2e1d8 elementor-widget elementor-widget-bopea-social-list" data-id="7cf2e1d8" data-element_type="widget" data-widget_type="bopea-social-list.default">
				<div class="elementor-widget-container">
               <?php $socialVariant = 'footer'; include __DIR__.'/partials/social-links.php'; ?>
            </div>
         </div> 
      </div>

		<div class="elementor-element elementor-element-5bbb1add e-con-full e-flex e-con e-child" data-id="5bbb1add" data-element_type="container">
			<div class="elementor-element elementor-element-20eaa4c8 elementor-widget elementor-widget-bopea-section-title" data-id="20eaa4c8" data-element_type="widget" data-widget_type="bopea-section-title.default">
				<div class="elementor-widget-container">
					<div class="jlcus_sec_title jl_sec_style8 jl_secf_title  ">
                  <div class="jlcus_sect_inner">
                     <h2 class="jl-heading-text">
                        <span class="jl_ttw">Curated Collections</span>
                     </h2>
                  </div>                    
               </div>                    
    			</div>
			</div>
			
      <div class="elementor-element elementor-element-73d9500 elementor-widget elementor-widget-bopea-xsmall-list" data-id="73d9500" data-element_type="widget" data-widget_type="bopea-xsmall-list.default">
			<div class="elementor-widget-container">
				<div class="jl_clear_at block-section jl-main-block jl_hide_meta jl_num_top jl_hide_line jl_hide_col_line" data-blockid="blockid_73d9500" data-section_style="jl_xsli" data-post_type="post" data-post_type_tax="none" data-page_max="17" data-page_current="1" data-author="none" data-order="date_post" data-posts_per_page="3" data-offset="12" data-tabs_link="none" >
					<div class="jl_grid_wrap_f jl_wrap_eb jl_xsgrid jl_clear_at">
			         <div class="jl-roww jl_contain jl_fli_wrap jl-col-row">					
							

                     <!-- single article footer -->

                     <?php foreach ($footerRows as $row): ?>
                       <?php
                         $t = clean_title($row['titre'] ?? '');
                         $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                         $uCat = category_url($row['categorie'] ?? '');
                         $date = fmt_date($row['date_add'] ?: $row['created_at']);

                         $imgs = parse_cover_images($row['cover'] ?? '');
                         $img = $imgs[0] ?? null;
                       ?>

                       <div class="jl_mmlist_layout jl_lisep jl_risep jl_in_num jl_numl">
                         <div class="jl_li_in jl_nun_i">
                           <span class="jl_nun_d"></span>

                           <div class="jl_img_holder">
                             <div class="jl_imgw jl_radus_e">
                               <div class="jl_imgin">
                                 <?php if ($img): ?>
                                   <img width="200" height="128"
                                        src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                                        class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                                        alt="<?= htmlspecialchars($t) ?>"
                                        decoding="async"
                                        data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>" />
                                 <?php else: ?>
                                   <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                                 <?php endif; ?>
                               </div>

                               <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                             </div>
                           </div>

                           <div class="jl_fe_text">
                             <?php if (!empty($row['categorie'])): ?>
                               <span class="jl_f_cat jl_lb">
                                 <a class="jl_cat_txt jl_cat69"
                                    style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'])) ?>!important"
                                    href="<?= htmlspecialchars($uCat) ?>">
                                   <span><?= htmlspecialchars($row['categorie']) ?></span>
                                 </a>
                               </span>
                             <?php endif; ?>

                             <h2 class="h3 jl_fe_title jl_txt_2row">
                               <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                             </h2>

                             <span class="jl_post_meta">
                               <span class="post-date"><?= htmlspecialchars($date) ?></span>
                             </span>
                           </div>
                         </div>
                       </div>

                     <?php endforeach; ?>
            <!-- /single article footer -->

					

				
				</div>				
										
			</div>		
		</div>				
   </div>
</div>
</div>


<div class="elementor-element elementor-element-3f970850 e-con-full e-flex e-con e-child" data-id="3f970850" data-element_type="container">
	<div class="elementor-element elementor-element-a6af4a0 elementor-widget elementor-widget-bopea-section-title" data-id="a6af4a0" data-element_type="widget" data-widget_type="bopea-section-title.default">
		<div class="elementor-widget-container">
			<div class="jlcus_sec_title jl_sec_style8 jl_secf_title  ">
            <div class="jlcus_sect_inner">
               <h2 class="jl-heading-text">
                  <span class="jl_ttw">Accès rapide</span>
               </h2>
            </div> 
         </div>
      </div>
   </div>


<div class="elementor-element elementor-element-6933093 elementor-widget elementor-widget-bopea-simple-menu" data-id="6933093" data-element_type="widget" data-widget_type="bopea-simple-menu.default">
<div class="elementor-widget-container">
	<div class="jl_jinm jl_lbl_in">
                        
      <div class="jlcm--simple-menu widget_nav_menu jlcm-main-jl_menu_list" >
         <ul id="menu-6933093" class="menu">
            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23440">
               <a href="accueil">Accueil</a>
            </li>
            <?php foreach (chrononews_categories() as $footerCat): ?>
            <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23441">
               <a href="<?= category_url($footerCat) ?>"><?= htmlspecialchars($footerCat, ENT_QUOTES, 'UTF-8') ?></a>
            </li>
            <?php endforeach; ?>
            
         </ul>                        
      </div>
   </div>
</div>
</div>
</div>
</div>
</div>


<div class="elementor-element elementor-element-6cf4787 e-flex e-con-boxed e-con e-parent" data-id="6cf4787" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
	<div class="e-con-inner">
		<div class="elementor-element elementor-element-6b7aeb3 elementor-widget-divider--view-line elementor-widget elementor-widget-divider" data-id="6b7aeb3" data-element_type="widget" data-widget_type="divider.default">
			<div class="elementor-widget-container">
				<div class="elementor-divider">
			      <span class="elementor-divider-separator"></span>
		       </div>
			</div>
		</div>
	</div>
</div>

<div class="elementor-element elementor-element-440c9edb e-flex e-con-boxed e-con e-parent" data-id="440c9edb" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
	<div class="e-con-inner">
		<div class="elementor-element elementor-element-db0edfb e-con-full e-flex e-con e-child" data-id="db0edfb" data-element_type="container">
			<div class="elementor-element elementor-element-434c995f elementor-widget elementor-widget-bopea-text" data-id="434c995f" data-element_type="widget" data-widget_type="bopea-text.default">
				<div class="elementor-widget-container">
					<div class="jlc-ctw">
                  <p class="jl-cust">
                     Copyright <?php echo date('Y');?> <b style="color:<?= CN_PRIMARY_COLOR ?>"><?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?></b>. Tous droits réservés.                
                  </p>
               </div>
        	   </div>
			</div>
		</div>
		
      <div class="elementor-element elementor-element-30c6db78 e-con-full e-flex e-con e-child" data-id="30c6db78" data-element_type="container">
			<div class="elementor-element elementor-element-78521e0a elementor-widget elementor-widget-bopea-simple-menu" data-id="78521e0a" data-element_type="widget" data-widget_type="bopea-simple-menu.default">
				<div class="elementor-widget-container">
					<div class="jl_jinm jl_lbl_in">
                        
               <div class="jlcm--simple-menu widget_nav_menu jlcm-main-jl_menu_inline" >
                  <ul id="menu-78521e0a" class="menu">
                     
                     <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23430">
                        <a href="/qui-sommes-nous" title="">qui sommes-nous?</a>
                     </li>
                     
                     <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23434">
                        <a href="/politique-de-confidentialite" title="">Politique de confidentialité</a>
                     </li>
                     <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23435">
                        <a href="/nous-contacter" title="">Contact</a>
                     </li>
                     <!-- <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23435">
                        <a href="https://linktr.ee/gauthiernt" target="_blank" title=""><strong style="color: #5570ad;text-decoration: underline;"> Par Gauthier N</strong></a>
                     </li> -->
                  </ul>
               </div>
               </div>
        		</div>
			</div>
		</div>
	</div>
</div>
</div>
</footer>