<?php

require_once __DIR__.'/brand.php';
require_once __DIR__.'/categories.php';

if (! isset($db) || ! $db instanceof PDO) {
    require_once __DIR__.'/legacy-database.php';
    $db = Database::getInstance()->getConnection();
}

$cnSiteName = cn_site_name();
$cnLogoLight = cn_logo_light();
$cnLogoDark = cn_logo_dark();

// --- Header Mega Menu : 4 catégories x 4 articles (MySQL 5.7 compatible) ---
$headerCats = ['Actualités', 'Institutions', 'Politique', 'Économie'];
$headerSimpleCats = ['Justice & Sécurité'];
$headerMoreCats = array_values(array_diff(chrononews_categories(), array_merge($headerCats, $headerSimpleCats)));
$perCat = 4;

$avoidDuplicatesHeader = false; // optionnel, indépendant (pas de conflit)
$whereNotIn = '';
$notInParams = [];

if ($avoidDuplicatesHeader && !empty($excludeHomeIds)) {
    $ph = [];
    foreach (array_values($excludeHomeIds) as $i => $id) {
        $k = ":exhdr{$i}";
        $ph[] = $k;
        $notInParams[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

// 1 requête préparée réutilisée pour les 4 catégories
$sqlHeaderOneCat = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT $perCat
";

$stmt = $db->prepare($sqlHeaderOneCat);

// init
$headerPosts = [];
foreach ($headerCats as $c) $headerPosts[$c] = [];

foreach ($headerCats as $cat) {
    $params = array_merge([':cat' => $cat], $notInParams);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fallback: si pas assez d'articles, on répète sans laisser de vide
    $headerPosts[$cat] = fill_repeat($rows, $perCat);
}

// --- LOGIC FOR DYNAMIC MENU ---
$current_uri_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$active_menu_slug = '';

// 1. Home
if ($current_uri_path === '/' || $current_uri_path === '/accueil' || $current_uri_path === '/index.php') {
    // Verifier qu'on n'a pas de parametres categorie/article (pour index.php?category=...)
    if (empty($_GET['category']) && empty($_GET['article'])) {
        $active_menu_slug = 'home';
    }
}

// 2. Category via URL (prioritaire sur $_GET si URL rewriting actif)
if (!$active_menu_slug && preg_match('#/categorie/([^/]+)#', $current_uri_path, $m)) {
    $resolved = function_exists('category_from_slug') ? category_from_slug($m[1]) : null;
    $active_menu_slug = $resolved ?? urldecode($m[1]);
}

// 3. Article page (use $currentArticle if available)
if (!$active_menu_slug && isset($currentArticle['categorie'])) {
    $active_menu_slug = $currentArticle['categorie'];
}

// 4. Fallback $_GET['category']
if (!$active_menu_slug && isset($_GET['category'])) {
    $active_menu_slug = urldecode($_GET['category']);
}

if (!function_exists('active_class')) {
    function active_class($item_slug, $active_slug) {
        // Normaliser les apostrophes
        $item_slug = str_replace(['’', "'"], "'", (string)$item_slug);
        $active_slug = str_replace(['’', "'"], "'", (string)$active_slug);
        
        if ($item_slug === 'home' && $active_slug === 'home') return 'current-menu-item';
        if ($item_slug !== 'home' && mb_strtolower($item_slug, 'UTF-8') === mb_strtolower($active_slug, 'UTF-8')) return 'current-menu-item';
        return '';
    }
}
// -----------------------------

// optionnel: ajouter au exclude global (si tu veux vraiment éviter doublons avec autres blocs)
if ($avoidDuplicatesHeader && function_exists('add_exclude_ids')) {
    $flat = [];
    foreach ($headerPosts as $cat => $list) foreach ($list as $it) $flat[] = $it;
    add_exclude_ids($excludeHomeIds, $flat);
}
// --- /Header Mega Menu ---

?>

<?php include __DIR__.'/logo-styles.php'; ?>
<?php include __DIR__.'/footer-styles.php'; ?>
<?php include __DIR__.'/brand-colors.php'; ?>
<?php include __DIR__.'/header-layout.php'; ?>

<style>
.mobile-only {
  display: none;
}

@media (max-width: 768px) {
  .mobile-only {
    display: block;
  }
}

</style>

<header class="jlc-hmain-w jlh-e jl_base_menu jl_md_main">
<div data-elementor-type="wp-post" data-elementor-id="22453" class="elementor elementor-22453">
<div class="elementor-element elementor-element-39a0bc0 e-flex e-con-boxed e-con e-parent" data-id="39a0bc0" data-element_type="container" data-settings='{"background_background":"classic"}'>
<div class="e-con-inner">
<div class="elementor-element elementor-element-0656b25 e-con-full elementor-hidden-mobile e-flex e-con e-child" data-id="0656b25" data-element_type="container" >
<div class="elementor-element elementor-element-fe21490 elementor-widget elementor-widget-bopea-simple-menu" data-id="fe21490" data-element_type="widget" data-widget_type="bopea-simple-menu.default">
<div class="elementor-widget-container">
<div class="jl_jinm jl_lbl_in">
<div class="jlcm--simple-menu widget_nav_menu jlcm-main-jl_menu_inline">
<ul id="menu-fe21490" class="menu">
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23432">
<a href="/qui-sommes-nous">Qui sommes-nous?</a>
</li>
<!-- <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23433">
<a href="#">Sponsorship</a>
</li> -->
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-23431">
<a href="/nous-contacter">Contact</a>
</li>

</ul>
</div>
</div>
</div>
</div>
</div>
<div class="elementor-element elementor-element-3f80140 e-con-full e-flex e-con e-child" data-id="3f80140" data-element_type="container">
<!-- <div class="elementor-element elementor-element-6a33968 elementor-widget elementor-widget-bopea-current-date" data-id="6a33968" data-element_type="widget" data-widget_type="bopea-current-date.default">
	<div class="elementor-widget-container">
		<div class="jl_cur_date jl_lbl_in">
			<span class="jl_lbl_op">Aujourd'hui</span>
			<span class="jl_lbl_date">Dim , 7 Décembre 2025</span>
		</div>
	</div>
</div> -->
<div class="elementor-element elementor-element-6a33968 elementor-widget" data-id="6a33968" data-element_type="widget">
  <div class="elementor-widget-container">
    <div class="jl_lbl_in">
      <span class="">Rejoignez-nous</span>
    </div>
  </div>
</div>
<div class="elementor-element elementor-element-7ffe091 elementor-widget elementor-widget-bopea-social-list" data-id="7ffe091" data-element_type="widget"data-widget_type="bopea-social-list.default">
<div class="elementor-widget-container">
<?php $socialVariant = 'header'; include __DIR__.'/partials/social-links.php'; ?>
</div>
</div>
</div>
</div>
</div>
<div class="elementor-element elementor-element-670991ed e-flex e-con-boxed e-con e-parent" data-id="670991ed" data-element_type="container" data-settings='{"background_background":"classic"}'>
<div class="e-con-inner">
<div class="elementor-element elementor-element-738986c7 e-con-full e-flex e-con e-child" data-id="738986c7" data-element_type="container">
<div class="elementor-element elementor-element-47fe1331 elementor-widget elementor-widget-bopea-section-logo" data-id="47fe1331" data-element_type="widget" data-widget_type="bopea-section-logo.default" >
<div class="elementor-widget-container">
<div class="logo_small_wrapper_table">
<div class="logo_small_wrapper">
<a class="logo_link" href="/accueil" aria-label="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>">
<span>
<img
class="jl_logo_n"
src="<?= htmlspecialchars($cnLogoLight, ENT_QUOTES, 'UTF-8') ?>"
width="72"
height="72"
alt="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>"
/>
<img
class="jl_logo_w"
src="<?= htmlspecialchars($cnLogoDark, ENT_QUOTES, 'UTF-8') ?>"
width="72"
height="72"
alt="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>"
/>
</span>
</a>
</div>
</div>
</div>
</div>
<div
class="elementor-element elementor-element-74e59b79 elementor-widget elementor-widget-bopea-main-menu"
data-id="74e59b79"
data-element_type="widget"
data-widget_type="bopea-main-menu.default"
>
<div class="elementor-widget-container">
<div class="navigation_wrapper jl_mb_wp jl_mm_lbt">
<div class="menu-main-menu-container">
<ul id="menu-74e59b79" class="jl_main_menu jl_mm_box">
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home page_item page-item-13574 current_page_item <?= active_class('home', $active_menu_slug) ?>">
<a href="/">
	<span class="jl_mblt">Accueil</span>
</a>
</li>

<!-- mega menu  Actualités -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Actualités', $active_menu_slug) ?>">
<a href="<?php echo htmlspecialchars(category_url('Actualités')); ?>">
	<span class="jl_mblt">Actualités</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23462"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="65"
         data-posts_per_page="4">
         <div class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list">
            <div class="jl-roww jl_contain jl-col-row">
               
               
           	<?php $cat = 'Actualités'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>

               
            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu  Actualités -->

<!-- mega menu  Interview-->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Institutions', $active_menu_slug) ?>">
<a href="<?= category_url('Institutions') ?>">
	<span class="jl_mblt">Institutions</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23464"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="69"
         data-posts_per_page="4"
         >
         <div
            class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list"
            >
            <div class="jl-roww jl_contain jl-col-row">
               

            <?php $cat = 'Institutions'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>



            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu  Interview-->


<!-- mega menu  economie-->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Économie', $active_menu_slug) ?>">
<a href="<?= category_url('Économie') ?>">
	<span class="jl_mblt">Économie</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23464"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="69"
         data-posts_per_page="4"
         >
         <div
            class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list"
            >
            <div class="jl-roww jl_contain jl-col-row">
               

            <?php $cat = 'Économie'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>



            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu  economie-->



<!-- mega menu  Politique -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Politique', $active_menu_slug) ?>">
<a href="<?php echo htmlspecialchars(category_url('Politique')); ?>">
	<span class="jl_mblt">Politique</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23464"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="69"
         data-posts_per_page="4"
         >
         <div
            class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list"
            >
            <div class="jl-roww jl_contain jl-col-row">
               

            <?php $cat = 'Politique'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>">
                  <div class="jl_mega_p_inner jl_mega_sml">
                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom:2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->
            <?php endforeach; ?>



            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu  Events -->

<?php foreach ($headerSimpleCats as $simpleCat): ?>
<li class="menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class($simpleCat, $active_menu_slug) ?>">
   <a href="<?= category_url($simpleCat) ?>">
      <span class="jl_mblt"><?= htmlspecialchars($simpleCat, ENT_QUOTES, 'UTF-8') ?></span>
   </a>
</li>
<?php endforeach; ?>

<li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-has-children">
<a href="#!"><span class="jl_mblt">Plus d'infos</span></a
>
<ul class="sub-menu">
<?php foreach ($headerMoreCats as $moreCat): ?>
<li class="menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class($moreCat, $active_menu_slug) ?>">
   <a href="<?= category_url($moreCat) ?>">
         <span class="jl_mblt"><?= htmlspecialchars($moreCat, ENT_QUOTES, 'UTF-8') ?></span>
   </a>
</li>
<?php endforeach; ?>
</ul>
</li>

</ul>
</div>
</div>
</div>
</div>
</div>


<div class="elementor-element elementor-element-55773142 e-con-full e-flex e-con e-child" data-id="5577314" data-element_type="container">

<div class="elementor-element elementor-element-21402e45 elementor-widget elementor-widget-bopea-dark-mode-switch" data-id="21402e45" data-element_type="widget" data-widget_type="bopea-dark-mode-switch.default">
	<div class="elementor-widget-container">
		<div class="bopea_day_night jl_day_en">
			<span class="jl-night-toggle-icon">
				<span class="jl_moon">
					<i class="jli-moon"></i>
				</span>
				<span class="jl_sun">
					<i class="jli-sun"></i>
				</span>
			</span>
		</div>
	</div>
</div>

<div class="elementor-element elementor-element-153a253e elementor-widget elementor-widget-bopea-search-button" data-id="153a253e" data-element_type="widget" data-widget_type="bopea-search-button.default">
<div class="elementor-widget-container">
<div class="jl_shwp">
<div class="search_header_wrapper jlce-seach search_form_menu_personal_click">
	<i class="jli-search"></i>
</div>


<!-- live search -->
<div class="jl_ajse search_form_menu_personal">
  <div class="jl_search_head jl_search_list">
    
  <!-- Live search component -->
    <div class="jl-live-search">

      <form method="get" class="searchform_theme" action="/recherche">
        <input
          type="text"
          name="q"
          value="<?php echo htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
          class="search_btn js-live-search-input"
          placeholder="Tapez quelque chose..."
          autocomplete="off"
        />
        <button type="submit" class="button">
          <span class="jl_sebtn">Rechercher</span>
        </button>
      </form>

      <div class="myjl_search_box_li js-live-search-box" style="display:none;">
        <div class="jl_search_wrap_li">
          <div class="jl_grid_wrap_f jl_wrap_eb jl_sf_grid jl_clear_at js-live-search-results">
          </div>
        </div>
      </div>

    </div>
  <!-- /Live search component -->

  </div>
</div>
<!-- /live search -->

</div>
</div>
</div>

<div class="elementor-element hamburger-menu elementor-element-790087f9 elementor-widget elementor-widget-bopea-mobile-menu" data-id="790087f9" data-element_type="widget" data-widget_type="bopea-mobile-menu.default">
	<div class="elementor-widget-container">
		<div class="menu_mobile_icons_wrap">
			<div class="menu_mobile_icons">
				<div class="jlm_w">
					<span class="jlma"></span>
					<span class="jlmb"></span>
					<span class="jlmc"></span>
				</div>
			</div>
		</div>
	</div>
</div>

</div>
</div>
</div>
</div>
</header>



<div class="jlc-stick-main-w jl_cus_sihead jl_r_menu">
<div data-elementor-type="wp-post" data-elementor-id="22454" class="elementor elementor-22454">
<div class="elementor-element elementor-element-376bc8c3 e-flex e-con-boxed e-con e-parent" data-id="376bc8c3" data-element_type="container" data-settings='{"background_background":"classic"}' >
<div class="e-con-inner">
<div class="elementor-element elementor-element-32b3ef37 e-con-full e-flex e-con e-child" data-id="32b3ef37" data-element_type="container">

<div class="elementor-element elementor-element-2cf0189e elementor-widget elementor-widget-bopea-section-logo" data-id="2cf0189e" data-element_type="widget" data-widget_type="bopea-section-logo.default">
<div class="elementor-widget-container">
<div class="logo_small_wrapper_table">
<div class="logo_small_wrapper">
<a class="logo_link" href="/accueil" aria-label="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>">
<span>
<img
class="jl_logo_n"
src="<?= htmlspecialchars($cnLogoLight, ENT_QUOTES, 'UTF-8') ?>"
width="72"
height="72"
alt="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>"
/>
<img
class="jl_logo_w"
src="<?= htmlspecialchars($cnLogoDark, ENT_QUOTES, 'UTF-8') ?>"
width="72"
height="72"
alt="<?= htmlspecialchars($cnSiteName, ENT_QUOTES, 'UTF-8') ?>"
/>
</span>
</a>
</div>
</div>
</div>
</div>
<div
class="elementor-element elementor-element-3420e1f4 elementor-widget elementor-widget-bopea-main-menu" data-id="3420e1f4" data-element_type="widget" data-widget_type="bopea-main-menu.default">
<div class="elementor-widget-container">
<div class="navigation_wrapper jl_mb_wp jl_mm_lbt">
<div class="menu-main-menu-container">
<ul id="menu-3420e1f4" class="jl_main_menu jl_mm_box">

<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home page_item page-item-13574 current_page_item <?= active_class('home', $active_menu_slug) ?>">
	<a href="/">
		<span class="jl_mblt">Accueil</span>
	</a>
</li>



<!-- mega menu 2 Actualités -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Actualités', $active_menu_slug) ?>">
<a href="<?php echo htmlspecialchars(category_url('Actualités')); ?>">
	<span class="jl_mblt">Actualités</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23462"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="65"
         data-posts_per_page="4"
         >
         <div class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list">
            <div class="jl-roww jl_contain jl-col-row">
               
               
           	<?php $cat = 'Actualités'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>2">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>

            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu 2 Actualités -->


<!-- mega menu 2 Interview -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Institutions', $active_menu_slug) ?>">
<a href="<?= category_url('Institutions') ?>">
	<span class="jl_mblt">Institutions</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23462"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="65"
         data-posts_per_page="4"
         >
         <div class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list">
            <div class="jl-roww jl_contain jl-col-row">
               
               
           	<?php $cat = 'Institutions'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>2">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>

            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu 2 Interview -->


<!-- mega menu 2 Economie -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Économie', $active_menu_slug) ?>">
<a href="<?= category_url('Économie') ?>">
	<span class="jl_mblt">Économie</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23462"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="65"
         data-posts_per_page="4"
         >
         <div class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list">
            <div class="jl-roww jl_contain jl-col-row">
               
               
           	<?php $cat = 'Économie'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>2">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>

            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu 2 Economie -->



<!-- mega menu 2 Politique -->
<li class="menupost mega-category-menu menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class('Politique', $active_menu_slug) ?>">
<a href="<?php echo htmlspecialchars(category_url('Politique')); ?>">
	<span class="jl_mblt">Politique</span>
</a>
<div class="sub-menu menu_post_feature jl-cus-mega-menu">
<div class="jl_mega_inner">
   <div class="jl_mega_contents">
      <div
         class="jl_clear_at jl-wp-mu jl_mega_post_4 block-section jl-main-block"
         data-blockid="block-mega-23462"
         data-section_style="mega_small_list"
         data-post_type="post"
         data-page_max="2"
         data-page_current="1"
         data-category="65"
         data-posts_per_page="4"
         >
         <div class="jl_mega_c_wrap jl_wrap_eb jl_clear_at mega_small_list">
            <div class="jl-roww jl_contain jl-col-row">
               
               
           	<?php $cat = 'Politique'; ?>
            <?php foreach ($headerPosts[$cat] as $row): ?>
              <?php
                $t = clean_title($row['titre'] ?? '');
                $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                $uCat = category_url($row['categorie'] ?? $cat);
                $date = fmt_date($row['date_add'] ?: $row['created_at']);

                $imgs = parse_cover_images($row['cover'] ?? '');
                $img = $imgs[0] ?? null;
              ?>

              <!-- single article -->
              <div class="jl_mega_cols">
                <div class="p-wraper post-<?= (int)$row['id'] ?>2">
                  <div class="jl_mega_p_inner jl_mega_sml">

                    <div class="jl_imgw jl_radus_e">
                      <div class="jl_imgin">
                        <?php if ($img): ?>
                          <img width="200" height="128"
                               src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"
                               class="attachment-bopea_small size-bopea_small jl-lazyload lazyload wp-post-image"
                               alt="<?= htmlspecialchars($t) ?>"
                               decoding="async"
                               data-src="<?= htmlspecialchars(cn_media_url($img) ?? '') ?>"/>
                        <?php else: ?>
                          <div style="width:200px;height:128px;background:#111;border-radius:12px;"></div>
                        <?php endif; ?>
                      </div>
                      <a class="jl_imgl" aria-label="<?= htmlspecialchars($t) ?>" href="<?= htmlspecialchars($uArticle) ?>"></a>
                    </div>

                    <div class="jl_mega_text">
                      <span class="jl_f_cat jl_lb">
                        <a class="jl_cat_txt jl_cat60"
                           style="border-bottom: 2px solid <?= htmlspecialchars(category_color($row['categorie'] ?? $cat)) ?>!important"
                           href="<?= htmlspecialchars($uCat) ?>">
                          <span><?= htmlspecialchars($row['categorie'] ?? $cat) ?></span>
                        </a>
                      </span>

                      <h3 class="jl_fr_ptxt jl_fe_title jl_txt_2row">
                        <a href="<?= htmlspecialchars($uArticle) ?>"><?= htmlspecialchars($t) ?></a>
                      </h3>

                      <span class="jl_post_meta">
                        <span class="post-date"><?= htmlspecialchars($date) ?></span>
                      </span>
                    </div>

                  </div>
                </div>
              </div>
              <!-- /single article -->

            <?php endforeach; ?>

            </div>
            
         </div>
      </div>
   </div>
</div>
</div>
</li>
<!-- /mega menu 2 Events -->

<?php foreach ($headerSimpleCats as $simpleCat): ?>
<li class="menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class($simpleCat, $active_menu_slug) ?>">
   <a href="<?= category_url($simpleCat) ?>">
      <span class="jl_mblt"><?= htmlspecialchars($simpleCat, ENT_QUOTES, 'UTF-8') ?></span>
   </a>
</li>
<?php endforeach; ?>

<!-- plus d'infos 2 -->
<li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-has-children">
<a href="#!">
	<span class="jl_mblt">Plus d'infos</span>
</a>
<ul class="sub-menu">
<?php foreach ($headerMoreCats as $moreCat): ?>
<li class="menu-item menu-item-type-taxonomy menu-item-object-category <?= active_class($moreCat, $active_menu_slug) ?>">
   <a href="<?= category_url($moreCat) ?>">
         <span class="jl_mblt"><?= htmlspecialchars($moreCat, ENT_QUOTES, 'UTF-8') ?></span>
   </a>
</li>
<?php endforeach; ?>
</ul>
</li>
<!-- /plus d'infos 2 -->

</ul>
</div>
</div>
</div>
</div>
</div>
<div
class="elementor-element elementor-element-60fbaf0b e-con-full e-flex e-con e-child" data-id="60fbaf0b" data-element_type="container">

<div class="elementor-element elementor-element-16b25ef5 elementor-widget elementor-widget-bopea-dark-mode-switch" data-id="16b25ef5" data-element_type="widget" data-widget_type="bopea-dark-mode-switch.default">
	<div class="elementor-widget-container">
		<div class="bopea_day_night jl_day_en">
			<span class="jl-night-toggle-icon">
			<span class="jl_moon">
				<i class="jli-moon"></i>
			</span>
			<span class="jl_sun">
				<i class="jli-sun"></i>
			</span>
			</span>
		</div>
	</div>
</div>


<div class="elementor-element elementor-element-531b1e70 elementor-widget elementor-widget-bopea-search-button" data-id="531b1e70" data-element_type="widget" data-widget_type="bopea-search-button.default">
<div class="elementor-widget-container">
<div class="jl_shwp">
<div class="search_header_wrapper jlce-seach search_form_menu_personal_click">
<i class="jli-search"></i>
</div>

<!-- live search -->
<div class="jl_ajse search_form_menu_personal">
  <div class="jl_search_head jl_search_list">
    
    <!-- Live search component -->
    <div class="jl-live-search">

      <form method="get" class="searchform_theme" action="/recherche">
        <input
          type="text"
          name="q"
          value="<?php echo htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
          class="search_btn js-live-search-input"
          placeholder="Tapez quelque chose..."
          autocomplete="off"
        />
        <button type="submit" class="button">
          <span class="jl_sebtn">Rechercher</span>
        </button>
      </form>

      <div class="myjl_search_box_li js-live-search-box" style="display:none;">
        <div class="jl_search_wrap_li">
          <div class="jl_grid_wrap_f jl_wrap_eb jl_sf_grid jl_clear_at js-live-search-results">
          </div>
        </div>
      </div>

    </div>
    <!-- /Live search component -->



  </div>
</div>
<!-- /live search -->

</div>
</div>
</div>

<div class="elementor-element hamburger-menu elementor-element-49535a7f elementor-widget elementor-widget-bopea-mobile-menu" data-id="49535a7f" data-element_type="widget" data-widget_type="bopea-mobile-menu.default">
	<div class="elementor-widget-container">
		<div class="menu_mobile_icons_wrap">
			<div class="menu_mobile_icons">
				<div class="jlm_w">
					<span class="jlma"></span>
					<span class="jlmb"></span>
					<span class="jlmc"></span>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Sélecteur de l'élément où afficher la date
    const dateElement = document.querySelector(".jl_lbl_date");
    if (!dateElement) return;

    // Obtenir la date du jour
    const now = new Date();

    // Formater la date en FR : ex. "Dim, 7 Décembre 2025"
    const options = { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' };
    let formattedDate = now.toLocaleDateString('fr-FR', options);

    // Mettre la première lettre du jour en majuscule
    formattedDate = formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);

    // Remplacer dans le HTML
    dateElement.textContent = formattedDate;
});
</script>