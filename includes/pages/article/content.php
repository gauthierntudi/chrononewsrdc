<div class="jl_block_content jl_sp_con" id="20148">
    <div class="jl_rd_wrap">
      <div class="jl_rd_read" data-key="20148"></div>
    </div>
   <div class="jlc-container">
      <div class="jlc-row main_content jl_single_tpl8">

<!-- publicités -->
   <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
        <div class="elementor-widget-container">
            <div class="jl_ads_img_w">
                <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-body-1" data-format="paysage_small" data-rotate="10000"></div>                       
            </div>
      </div>
    </div>
<!-- publicités -->

<!-- header image cover article And titre -->
            <div class="jlc-col-md-12" style="padding-bottom: 40px;">
                <div class="jl_shead_tpl4" >
                    <div class="jl_ov_layout jl_ov_el" style="<?php echo (has_video($currentArticle) && !empty($currentArticle['videos'])) ? 'pointer-events: none;' : ''; ?>">
                        <div class="jl_img_holder">
                            <div class="jl_imgw">
                                <div class="jl_imgin">
                                    <?php
                                    if (has_video($currentArticle) && !empty($currentArticle['videos'])):
                                        $videos = array_filter(array_map('trim', explode(',', $currentArticle['videos'])));
                                        $first_video = reset($videos);
                                        $video_id = youtube_id_from_url($first_video);
                                        if ($video_id):
                                    ?>
                                        <div class="video-wrapper" id="main-video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0; width: 100%; pointer-events: auto;">
                                            <iframe
                                                id="main-youtube-video"
                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: auto;"
                                                src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>?enablejsapi=1&rel=0&modestbranding=1&showinfo=0&iv_load_policy=3"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen>
                                            </iframe>
                                        </div>
                                    <?php
                                        endif;
                                    else:
                                        $article_covers = parse_cover_images($currentArticle['cover']);
                                        if (empty($article_covers)) {
                                            $article_covers = ['/img/placeholder-8.webp'];
                                        }
                                        $has_multiple_covers = count($article_covers) > 1;
                                    ?>
                                        <?php if ($has_multiple_covers): ?>
                                        <div class="article-cover-slider" style="position: relative; width: 100%; height: 100%;">
                                            <?php foreach ($article_covers as $idx => $cover_img): ?>
                                            <img width="1280" height="853"
                                                 src="<?php echo htmlspecialchars(cn_media_url($cover_img) ?? ''); ?>"
                                                 class="attachment-bopea_large size-bopea_large wp-post-image slide-fade"
                                                 alt="<?php echo htmlspecialchars(clean_title($currentArticle['titre'])); ?> - Image <?php echo $idx + 1; ?>"
                                                 decoding="async"
                                                 style="position: <?php echo $idx === 0 ? 'relative' : 'absolute'; ?>; top: 0; left: 0; width: 100%; opacity: <?php echo $idx === 0 ? '1' : '0'; ?>; transition: opacity 1s ease-in-out;" />
                                            <?php endforeach; ?>
                                        </div>
                                        <script>
                                        (function() {
                                            const slider = document.querySelector('.article-cover-slider');
                                            if (!slider) return;

                                            const slides = slider.querySelectorAll('.slide-fade');
                                            let currentIndex = 0;
                                            const totalSlides = slides.length;

                                            function showNextSlide() {
                                                slides[currentIndex].style.opacity = '0';
                                                currentIndex = (currentIndex + 1) % totalSlides;
                                                slides[currentIndex].style.opacity = '1';
                                            }

                                            setInterval(showNextSlide, 8000);
                                        })();
                                        </script>
                                        <?php else: ?>
                                        <img width="1280" height="853"
                                             src="<?php echo htmlspecialchars(cn_media_url($article_covers[0]) ?? ''); ?>"
                                             class="attachment-bopea_large size-bopea_large wp-post-image"
                                             alt="<?php echo htmlspecialchars(clean_title($currentArticle['titre'])); ?>"
                                             decoding="async" />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <!-- info sur la cover -->
                        <?php if (!has_video($currentArticle) || empty($currentArticle['videos'])): ?>
                        <div class="jl_fe_text">
                            <div class="jl_fe_inner">
                                <div class="jl_breadcrumbs">
                                    <span class="jl_item_bread">
                                        <a href="/">
                                            Accueil
                                        </a>
                                    </span>
                                    <i class="jli-right-chevron"></i>
                                    <span class="jl_item_bread">
                                        <a href="/categorie/<?php echo urlencode($currentArticle['categorie']); ?>">
                                            <?php echo htmlspecialchars($currentArticle['categorie']); ?>
                                        </a>
                                    </span>

                                    <i class="jli-right-chevron"></i>
                                    <span class="jl_item_bread">
                                        <?php echo htmlspecialchars(clean_title($currentArticle['titre'])); ?>
                                    </span>
                                </div>
                                
                                <span class="jl_f_cat jl_lb3">
                                    <a class="jl_cat_lbl" href="/categorie/<?php echo urlencode($currentArticle['categorie']); ?>" style="background-color: <?php echo category_color($currentArticle['categorie']); ?>">
                                        <span><?php echo htmlspecialchars($currentArticle['categorie']); ?></span>
                                    </a>
                                </span>
                                <h1 class="jl_head_title jl_fe_title">
                                    <?php echo clean_title($currentArticle['titre']); ?>
                                </h1>
                                <p class="post_subtitle_text">
                                    <?php echo clean_title($currentArticle['legende']); ?>
                                </p>
                                
                                <div class="jl_mt_wrap">
                                    <span class="jl_post_meta jl_slimeta jl_au_lw">
                                        <span class="jl_author_img_w jl_au_l">
                                            <span class="jl_aimg_in">
                                                <a href="#!">
                                                    <?php
                                                    $auteur_image = !empty($currentArticle['auteur_cover']) ? cn_media_url($currentArticle['auteur_cover']) : '/img/user.jpg';
                                                    $auteur_nom = htmlspecialchars($currentArticle['auteur_nom'] ?? 'Anonyme');
                                                    ?>
                                                    <img src="<?php echo $auteur_image; ?>" width="120" height="120" alt="<?php echo $auteur_nom; ?>" class="avatar avatar-120 wp-user-avatar wp-user-avatar-120 alignnone photo lazyload" />
                                                </a>
                                            </span>
                                        </span>
                                        <span class="jl_mt_rw">
                                            <span class="jl_mt_t">
                                                <span class="jl_author_img_w">Par<a href="#!" title="Posts by <?php echo htmlspecialchars($currentArticle['auteur_nom'] ?? 'Anonyme'); ?>" rel="author"><?php echo htmlspecialchars($currentArticle['auteur_nom'] ?? 'Anonyme'); ?></a>
                                                </span>
                                                <span class="post-date">
                                                    <?php echo fmt_date($currentArticle['date_add'] ?? $currentArticle['created_at']); ?>
                                                </span>
                                            </span>
                                            <span class="jl_mt_b">
                                                <span class="post-read-time">
                                                    <i class="jli-timer"></i>
                                                    <?php
                                                    $word_count = str_word_count(strip_tags($currentArticle['contenu'] ?? ''));
                                                    $read_time = max(1, ceil($word_count / 200));
                                                    echo $read_time;
                                                    ?> Mins read
                                                </span>
                                                <span class="jl_view_options">
                                                    <?php echo number_format($currentArticle['vues_int']); ?> Views
                                                </span>
                                            </span>
                                        </span>
                                    </span>                            
                                    
                                <!-- boutons de partage -->
                                    <div class="jlp_hs">
                                        <span class="jl_sh_t">
                                            <i class="jli-share"></i>
                                            <span>Partager</span>
                                        </span>            
                                        
                                        <span class="jl_sli_w">
                                            <span class="jl_sli_in">
                                                <span class="jl_sli_fb jl_shli">
                                                    <a class="jl_sshl" href="<?php echo $links['facebook']; ?>" rel="nofollow" alt="facebook">
                                                        <i class="jli-facebook"></i>
                                                    </a>
                                                </span>
                                                <span class="jl_sli_tw jl_shli">
                                                    <a class="jl_sshl" href="<?php echo $links['twitter']; ?>" rel="nofollow" alt="twitter">
                                                        <i class="jli-x"></i>
                                                    </a>
                                                </span>
                                                <span class="jl_sli_pi jl_shli">
                                                    <a class="jl_sshl" href="<?php echo $links['pinterest']; ?>" rel="nofollow" alt="pinterest">
                                                        <i class="jli-pinterest"></i>
                                                    </a>
                                                </span>

                                                <span class="jl_sli_din jl_shli">
                                                    <a class="jl_sshl" href="<?php echo $links['linkedin']; ?>" rel="nofollow" alt="linkedin">
                                                        <i class="jli-linkedin"></i>
                                                    </a>
                                                </span>
                                                
                                                <span class="jl_sli_wapp jl_shli">
                                                    <a class="jl_sshl" href="<?php echo $links['whatsapp']; ?>" data-action="share/whatsapp/share" rel="nofollow" alt="whatsapp">
                                                        <i class="jli-whatsapp"></i>
                                                    </a>
                                                </span>
                                                
                                                <span class="jl_sli_flip jl_shli"><a class="jl_sshl" href="<?php echo $links['flipboard']; ?>" rel="nofollow" alt="flipboard">
                                                    <svg fill="currentColor" height="1em" role="img" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="1 1 12 12">
                                                        <path d="m 6.9999999,0.99982 5.9993001,0 0,1.99835 0,1.99357 -1.993367,0 -1.9980673,0 -0.014998,1.99357 -0.01,1.99835 -1.9980669,0.01 -1.9933674,0.0146 -0.014998,1.99835 -0.01,1.99357 -1.9834686,0 -1.9836686,0 0,-6.00006 0,-5.99994 5.9992001,0 z"></path>
                                                    </svg>
                                                </a>
                                            </span>
                                            <span class="jl_sli_tele jl_shli">
                                                <a class="jl_sshl" href="<?php echo $links['telegram']; ?>" rel="nofollow" alt="telegram">
                                                    <i class="jli-telegram"></i>
                                                </a>
                                            </span>        
                                            <span class="jl_sli_tumblr jl_shli">
                                                <a class="jl_sshl" href="<?php echo $links['tumblr']; ?>" rel="nofollow" alt="tumblr">
                                                    <i class="jli-tumblr"></i>
                                                </a>
                                            </span>
                                            <span class="jl_sli_line jl_shli">
                                                <a class="jl_sshl" href="<?php echo $links['line']; ?>" rel="nofollow" alt="line">
                                                    <span class="jli-line">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    </span>
                                                </a>
                                            </span>
                                            <span class="jl_sli_mil jl_shli">
                                                <a class="jl_sshm" href="<?php echo $links['email']; ?>" target="_blank" alt="mail" rel="nofollow">
                                                    <i class="jli-mail"></i>
                                                </a>
                                            </span>            
                                        </span>
                                    </span>
                                </div>

                                <!-- /boutons de partage -->

                            </div>
                        </div>
                    </div>
                        <?php endif; ?>
                <!-- /info sur la cover -->

                </div>
            </div>         
        </div>
<!-- /header image cover article And titre-->


        <div class="jlc-col-md-8 jl_smmain_con">
            <div class="jl_smmain_w">
                <div class="jl_smmain_in">
                    
                    






                <div class="jl_sg_rgap">
                    <div class="post_content_w">
                        <span class="post_sw">
                            <span class="post_s">
                                <span class="jl_sh_t">
                                    <i class="jli-share"></i>
                                    <span>Share</span>
                                </span>
                                <span class="jl_sli_w">
                                    <span class="jl_sli_in">
                                        
                                        <span class="jl_sli_fb jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['facebook']; ?>" rel="nofollow" alt="facebook">
                                                <i class="jli-facebook"></i>
                                            </a>
                                        </span>
                                        
                                        <span class="jl_sli_tw jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['twitter']; ?>" rel="nofollow" alt="twitter">
                                                <i class="jli-x"></i>
                                            </a>
                                        </span>
                                        
                                        <span class="jl_sli_pi jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['pinterest']; ?>" rel="nofollow" alt="pinterest">
                                                <i class="jli-pinterest"></i>
                                            </a>
                                        </span>
                                    
                                        <span class="jl_sli_din jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['linkedin']; ?>" rel="nofollow" alt="linkedin">
                                                <i class="jli-linkedin"></i>
                                            </a>
                                        </span>

                                        <span class="jl_sli_wapp jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['whatsapp']; ?>" data-action="share/whatsapp/share" rel="nofollow" alt="whatsapp">
                                                <i class="jli-whatsapp"></i>
                                            </a>
                                        </span>

                                        <span class="jl_sli_flip jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['flipboard']; ?>" rel="nofollow" alt="flipboard">
                                                <svg fill="currentColor" height="1em" role="img" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="1 1 12 12">
                                                    <path d="m 6.9999999,0.99982 5.9993001,0 0,1.99835 0,1.99357 -1.993367,0 -1.9980673,0 -0.014998,1.99357 -0.01,1.99835 -1.9980669,0.01 -1.9933674,0.0146 -0.014998,1.99835 -0.01,1.99357 -1.9834686,0 -1.9836686,0 0,-6.00006 0,-5.99994 5.9992001,0 z"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    
                                        <span class="jl_sli_tele jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['telegram']; ?>" rel="nofollow" alt="telegram">
                                                <i class="jli-telegram"></i>
                                            </a>
                                        </span>
                                    
                                        <span class="jl_sli_tumblr jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['tumblr']; ?>" rel="nofollow" alt="tumblr">
                                                <i class="jli-tumblr"></i>
                                            </a>
                                        </span>
                                    
                                        <span class="jl_sli_line jl_shli">
                                            <a class="jl_sshl" href="<?php echo $links['line']; ?>" rel="nofollow" alt="line">
                                                <span class="jli-line">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </span>
                                            </a>
                                        </span>
                                    
                                        <span class="jl_sli_mil jl_shli">
                                            <a class="jl_sshm" href="<?php echo $links['email']; ?>" target="_blank" alt="mail" rel="nofollow">
                                                <i class="jli-mail"></i>
                                            </a>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </span>
                        
                        <div class="jls_con_w">
                            <div class="post_content jl_content">
                                <?php if ($has_access): ?>
                                    <?php
                                    // --- AFFICHAGE COMPLET (ACCÈS AUTORISÉ) ---
                                    $content = $currentArticle['contenu'] ?? '';
                                    $paragraphs = array_filter(array_map('trim', explode("\n", $content)));

                                    if (!empty($paragraphs)) {
                                        foreach ($paragraphs as $index => $paragraph) {
                                            if ($index === 0) {
                                                echo '<p class="has-drop-cap">' . nl2br(htmlspecialchars($paragraph)) . '</p>';
                                            } else {
                                                echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
                                            }
                                        }
                                    } else {
                                        echo '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
                                    }
                                    ?>

                                    <?php
                                    // Récupération des blocs de contenu de l'article
                                    try {
                                        $stmt_blocks = $db->prepare("
                                            SELECT titre, contenu, cover, legende, type_post, videos, num_block
                                            FROM block_news
                                            WHERE id_news = :id_news AND status = 1
                                            ORDER BY id ASC
                                        ");
                                        $stmt_blocks->bindValue(':id_news', $article_id, PDO::PARAM_INT);
                                        $stmt_blocks->execute();
                                        $blocks = $stmt_blocks->fetchAll(PDO::FETCH_ASSOC);

                                        // Affichage des blocs
                                        if (!empty($blocks)) {
                                            foreach ($blocks as $block) {
                                                echo '<div class="article-block" style="margin: 30px 0;">';

                                                // 1. Images ou vidéos en premier
                                                if (!empty($block['videos'])) {
                                                    // Vidéos YouTube (sans légende)
                                                    $block_videos = array_filter(array_map('trim', explode(',', $block['videos'])));
                                                    foreach ($block_videos as $video_url) {
                                                        $video_id = youtube_id_from_url($video_url);
                                                        if ($video_id) {
                                                            echo '<div class="video-container" style="margin: 20px 0;">';
                                                            echo '<iframe width="100%" height="500" src="https://www.youtube.com/embed/' . htmlspecialchars($video_id) . '?rel=0&modestbranding=1&showinfo=0&iv_load_policy=3" frameborder="0" allowfullscreen></iframe>';
                                                            echo '</div>';
                                                        }
                                                    }
                                                } elseif (!empty($block['cover'])) {
                                                    // Images avec légende en overlay
                                                    $block_images = parse_cover_images($block['cover']);

                                                    if (count($block_images) > 1) {
                                                        // Slider pour plusieurs images
                                                        echo '<div class="block-slider" style="position: relative; margin: 20px 0;">';
                                                        foreach ($block_images as $idx => $block_image) {
                                                            echo '<div class="slider-item" style="position: relative; display: ' . ($idx === 0 ? 'block' : 'none') . ';">';
                                                            echo '<img decoding="async" style="width: 100%; height: auto; display: block;" src="'.htmlspecialchars(cn_media_url($block_image)).'" alt="'.htmlspecialchars($block['titre'] ?? '').'" />';

                                                            // Légende en overlay
                                                            if (!empty($block['legende'])) {
                                                                echo '<div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding: 20px 15px 10px; color: white;">';
                                                                echo '<p style="margin: 0; font-size: 14px;">' . htmlspecialchars($block['legende']) . '</p>';
                                                                echo '</div>';
                                                            }
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
                                                    } else {
                                                        // Image unique avec légende en overlay
                                                        foreach ($block_images as $block_image) {
                                                            echo '<figure class="wp-block-image size-large" style="position: relative; margin: 20px 0;">';
                                                            echo '<img decoding="async" style="width: 100%; height: auto; display: block;" src="'.htmlspecialchars(cn_media_url($block_image)).'" alt="'.htmlspecialchars($block['titre'] ?? '').'" />';

                                                            // Légende en overlay
                                                            if (!empty($block['legende'])) {
                                                                echo '<figcaption style="position: absolute;bottom: 0;left: 0;right: 0;background: linear-gradient(to top, rgba(0,0,0,1), transparent);padding: 20px 15px 10px;color: white;margin: 0;z-index: 10;text-align:left">';
                                                                echo htmlspecialchars($block['legende']);
                                                                echo '</figcaption>';
                                                            }
                                                            echo '</figure>';
                                                        }
                                                    }
                                                }

                                                // 2. Titre du bloc
                                                if (!empty($block['titre'])) {
                                                    echo '<h2 style="margin: 20px 0 15px;">' . htmlspecialchars($block['titre']) . '</h2>';
                                                }

                                                // 3. Contenu du bloc
                                                if (!empty($block['contenu'])) {
                                                    echo '<div class="block-content">' . $block['contenu'] . '</div>';
                                                }

                                                echo '</div>';
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        error_log("Erreur lors de la récupération des blocs: " . $e->getMessage());
                                    }
                                    ?>

                                <?php else: ?>
                                    <?php
                                    // --- AFFICHAGE RESTREINT (PREMIUM) ---
                                    $content = $currentArticle['contenu'] ?? '';
                                    // Prendre les 300 premiers caractères proprement
                                    $teaser = substr(strip_tags($content), 0, 350) . '...';
                                    echo '<p class="has-drop-cap">' . $teaser . '</p>';
                                    ?>
                                    
                                    <div class="premium-blur">
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
                                        <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.</p>
                                    </div>

                                    <div class="premium-lock-overlay">
                                        <div class="premium-lock-card">
                                            <div class="premium-lock-icon">
                                                <i class="fa-solid fa-lock"></i>
                                            </div>
                                            <h3 class="premium-lock-title">Cet article est réservé aux abonnés</h3>
                                            <p class="premium-lock-desc">Accédez à l'intégralité de cet article en l'achetant à l'unité ou en souscrivant à un abonnement.</p>
                                            
                                            <div class="premium-options">
                                                <!-- Achat unique -->
                                                <div onclick="initiatePurchase(<?php echo $article_id; ?>, <?php echo $article_price; ?>)" style="cursor: pointer;">
                                                    <button type="button" class="premium-btn btn-buy">
                                                        Acheter l'article<br>
                                                        <span class="premium-btn-price">$<?php echo $article_price; ?></span>
                                                    </button>
                                                </div>
                                                
                                                <!-- Abonnement -->
                                                <div onclick="redirectToSubscription()" style="cursor: pointer;">
                                                    <button type="button" class="premium-btn btn-sub">
                                                        S'abonner<br>
                                                        <span class="premium-btn-sub">Accès illimité</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <?php if (!$is_logged_in): ?>
                                                <p class="premium-lock-login">
                                                    Déjà client ? <a href="#" onclick="showLoginModal(); return false;">Se connecter</a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>


                    <!-- publicités -->
                       <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                            <div class="elementor-widget-container">
                                <div class="jl_ads_img_w">
                                    <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-body-2" data-format="paysage_medium" data-rotate="10000"></div>                       
                                </div>
                          </div>
                        </div>
                    <!-- publicités -->

                    <!-- tags -->
                        <?php
                        $tags = get_or_create_article_tags(
                            $currentArticle['id'],
                            $currentArticle['titre'],
                            $currentArticle['contenu']
                        );

                        if (count($tags) > 0):
                        ?>
                        <div class="single_tag_share">
                            <div class="tag-cat">
                                <ul class="single_post_tag_layout">
                                    <?php foreach ($tags as $tag): ?>
                                    <li>
                                        <a href="/search.php?q=<?php echo urlencode($tag); ?>" rel="tag">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    <!-- /tags -->


                    </div>
                </div>
                
                <div class="jl_sfoot">
                    <span class="jl_sh_t">
                        <i class="jli-share"></i>
                        <span>Share</span>
                    </span>
                        
                    <span class="jl_sli_w">
                        <span class="jl_sli_in">
                            <span class="jl_sli_fb jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['facebook']; ?>" rel="nofollow" alt="facebook">
                                    <i class="jli-facebook"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_tw jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['twitter']; ?>" rel="nofollow" alt="twitter">
                                    <i class="jli-x"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_pi jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['pinterest']; ?>" rel="nofollow" alt="pinterest">
                                    <i class="jli-pinterest"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_din jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['linkedin']; ?>" rel="nofollow" alt="linkedin">
                                    <i class="jli-linkedin"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_wapp jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['whatsapp']; ?>" rel="nofollow" alt="whatsapp">
                                    <i class="jli-whatsapp"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_flip jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['flipboard']; ?>" rel="nofollow" alt="flipboard">
                                    <svg fill="currentColor" height="1em" role="img" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="1 1 12 12">
                                       <path d="m 6.9999999,0.99982 5.9993001,0 0,1.99835 0,1.99357 -1.993367,0 -1.9980673,0 -0.014998,1.99357 -0.01,1.99835 -1.9980669,0.01 -1.9933674,0.0146 -0.014998,1.99835 -0.01,1.99357 -1.9834686,0 -1.9836686,0 0,-6.00006 0,-5.99994 5.9992001,0 z"></path>
                                    </svg>
                                </a>
                            </span>
                            
                            <span class="jl_sli_tele jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['telegram']; ?>" rel="nofollow" alt="telegram">
                                    <i class="jli-telegram"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_tumblr jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['tumblr']; ?>" rel="nofollow" alt="tumblr">
                                    <i class="jli-tumblr"></i>
                                </a>
                            </span>
                            
                            <span class="jl_sli_line jl_shli">
                                <a class="jl_sshl" href="<?php echo $links['line']; ?>" rel="nofollow" alt="line">
                                    <span class="jli-line">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </span>
                                </a>
                            </span>
                            
                            <span class="jl_sli_mil jl_shli">
                                <a class="jl_sshm" href="<?php echo $links['email']; ?>" target="_blank" alt="mail" rel="nofollow">
                                    <i class="jli-mail"></i>
                                </a>
                            </span>
                        </span>
                    </span>
                </div>
                
                <!-- footer infos auteur -->
                <div class="jl_auth_single">
                    <div class="author-info jl_info_auth">
                        <div class="author-avatar">
                            <a href="#!">
                                <?php
                                $auteur_image_footer = !empty($currentArticle['auteur_cover']) ? cn_media_url($currentArticle['auteur_cover']) : '/img/user.jpg';
                                $auteur_nom_footer = htmlspecialchars($currentArticle['auteur_nom'] ?? 'Anonyme');
                                $auteur_titre = htmlspecialchars($currentArticle['auteur_titre'] ?? '');
                                ?>
                                <img src="<?php echo $auteur_image_footer; ?>" width="165" height="165" alt="<?php echo $auteur_nom_footer; ?>" class="avatar avatar-165 wp-user-avatar wp-user-avatar-165 alignnone photo"/>
                            </a>
                        </div>

                        <div class="author-description">
                            <div class="jl_auth_lbl">
                                Écrit par
                            </div>

                            <span class="jl_auth_name h3 jl_fe_title">
                                <a href="#!"><?php echo $auteur_nom_footer; ?></a>
                                <?php if (!empty($auteur_titre)): ?>
                                <span>- <?php echo $auteur_titre; ?></span>
                                <?php endif; ?>
                            </span>
                            
                            <p class="jl_auth_desc">
                                <?php if (!empty($currentArticle['auteur_bio'])): ?>
                                <?php echo htmlspecialchars($currentArticle['auteur_bio']); ?>
                                <?php endif; ?>
                            </p>
                            
                            <ul class="jl_auth_link clearfix">
                                <?php if (!empty($currentArticle['auteur_facebook'])): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($currentArticle['auteur_facebook']); ?>" target="_blank" rel="nofollow">
                                        <i class="jli-facebook"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if (!empty($currentArticle['auteur_twitter'])): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($currentArticle['auteur_twitter']); ?>" target="_blank" rel="nofollow">
                                       <svg fill="currentColor" height="0.8em" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 462.8">
                                            <path fill-rule="nonzero" d="M403.229 0h78.506L310.219 196.04 512 462.799H354.002L230.261 301.007 88.669 462.799h-78.56l183.455-209.683L0 0h161.999l111.856 147.88L403.229 0zm-27.556 415.805h43.505L138.363 44.527h-46.68l283.99 371.278z"
                                             ></path>
                                        </svg>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if (!empty($currentArticle['auteur_instagram'])): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($currentArticle['auteur_instagram']); ?>" target="_blank" rel="nofollow">
                                        <i class="jli-instagram"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if (!empty($currentArticle['auteur_youtube'])): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($currentArticle['auteur_youtube']); ?>" target="_blank" rel="nofollow">
                                        <i class="jli-youtube"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                 <!-- /footer infos auteur -->

                <!-- nav prev and next article -->
                <?php if ($prevArticle || $nextArticle): ?>
                <div class="postnav_w">
                    <?php if ($prevArticle): ?>
                    <?php
                        $t = clean_title($prevArticle['titre'] ?? '');
                        $prevArticleTitle = "/article/" . (int)$prevArticle['id'] . "/" . slugify($t);
                    ?>
                    <div class="jl_navpost postnav_left">
                        <a class="jl_nav_link" href="<?php echo htmlspecialchars($prevArticleTitle); ?>" id="prepost">
                            <span class="jl_nav_img">
                                <?php
                                $prevCovers = parse_cover_images($prevArticle['cover'] ?? '');
                                $prevCoverImg = !empty($prevCovers) ? $prevCovers[0] : '';
                                ?>
                                <?php if (!empty($prevCoverImg)): ?>
                                <img width="150" height="150"
                                     src="<?php echo htmlspecialchars(cn_media_url($prevCoverImg) ?? ''); ?>"
                                     class="attachment-thumbnail size-thumbnail wp-post-image"
                                     alt="<?php echo htmlspecialchars($t); ?>"
                                     loading="lazy"/>
                                <?php else: ?>
                                <img width="150" height="150"
                                     src="img/placeholder-8.webp"
                                     class="attachment-thumbnail size-thumbnail wp-post-image"
                                     alt="<?php echo htmlspecialchars($t); ?>"
                                     loading="lazy"/>
                                <?php endif; ?>
                            </span>
                            <span class="jl_nav_wrap">
                                <span class="jl_nav_label">
                                    Article précédent
                                </span>
                                <span class="jl_cpost_title">
                                    <?php
                                    $prevTitre = $prevArticle['titre'];
                                    echo htmlspecialchars(mb_strlen($prevTitre) > 60 ? mb_substr($prevTitre, 0, 60) . '...' : $prevTitre);
                                    ?>
                                </span>
                            </span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($nextArticle): ?>

                    <?php
                        $t2 = clean_title($nextArticle['titre'] ?? '');
                        $nextArticleTitle = "/article/" . (int)$nextArticle['id'] . "/" . slugify($t2);
                    ?>
                    <div class="jl_navpost postnav_right">
                        <a class="jl_nav_link" href="<?php echo htmlspecialchars($nextArticleTitle); ?>" id="nextpost">
                            <span class="jl_nav_img">
                                <?php
                                $nextCovers = parse_cover_images($nextArticle['cover'] ?? '');
                                $nextCoverImg = !empty($nextCovers) ? $nextCovers[0] : '';
                                ?>
                                <?php if (!empty($nextCoverImg)): ?>
                                <img width="150" height="150"
                                     src="<?php echo htmlspecialchars(cn_media_url($nextCoverImg) ?? ''); ?>"
                                     class="attachment-thumbnail size-thumbnail wp-post-image"
                                     alt="<?php echo htmlspecialchars($t2); ?>"
                                     loading="lazy"/>
                                <?php else: ?>
                                <img width="150" height="150"
                                     src="/img/placeholder-8.webp"
                                     class="attachment-thumbnail size-thumbnail wp-post-image"
                                     alt="<?php echo htmlspecialchars($t2); ?>"
                                     loading="lazy"/>
                                <?php endif; ?>
                            </span>
                            <span class="jl_nav_wrap">
                                <span class="jl_nav_label">
                                    Article suivant
                                </span>
                                <span class="jl_cpost_title">
                                    <?php
                                    $nextTitre = $nextArticle['titre'];
                                    echo htmlspecialchars(mb_strlen($nextTitre) > 60 ? mb_substr($nextTitre, 0, 60) . '...' : $nextTitre);
                                    ?>
                                </span>
                            </span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <!-- /nav prev and next article -->

            </div>
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
         
         <!-- <div class="elementor-element elementor-element-32b62646 elementor-widget elementor-widget-bopea-social-list" data-id="32b62646" data-element_type="widget" data-widget_type="bopea-social-list.default">
            <div class="elementor-widget-container">
                <ul class="jl_wr_soci jl_sh_cter jl_sh1">
                    <li class="jl_facebook_url" style="--jl-social-color:#4080FF;">
                       <a aria-label="facebook" href="https://web.facebook.com/Kongomikilibusinessforum" target="_blank" rel="nofollow">
                          <span class="jl_sh_i"><i class="jli-facebook"></i></span>
                          <span class="jl_sh_t">Facebook</span>
                          <span class="jl_sh_w">
                          <span class="jl_sh_c">23k</span>
                          <span class="jl_sh_l">Likes</span>
                          </span>
                       </a>
                    </li>
                    
                    <li class="jl_instagram_url" style="--jl-social-color:#e83685;">
                       <a aria-label="instagram" href="#!" target="_blank" rel="nofollow">
                          <span class="jl_sh_i">
                             <i class="jli-instagram"></i>
                          </span>
                          <span class="jl_sh_t">Instagram</span>
                          <span class="jl_sh_w">
                          <span class="jl_sh_c">32k</span>
                          <span class="jl_sh_l">Abonnés</span>
                          </span>
                       </a>
                    </li>
                    
                    <li class="jl_linkedin_url" style="--jl-social-color:#2c408b;">
                       <a aria-label="linkedin" href="https://www.linkedin.com/showcase/kongo-mikili-business-forum/about/?viewAsMember=true" target="_blank" rel="nofollow">
                          <span class="jl_sh_i">
                             <i class="jli-linkedin"></i>
                          </span>
                          <span class="jl_sh_t">Linkedin</span>
                          <span class="jl_sh_w">
                          <span class="jl_sh_c">42k</span>
                          <span class="jl_sh_l">Abonnés</span>
                          </span>
                       </a>
                    </li>
                    
                    <li class="jl_youtube_url" style="--jl-social-color:#ff0000;">
                       <a aria-label="YouTube" href="https://www.youtube.com/@mitekaadvertising7158" target="_blank" rel="nofollow">
                          <span class="jl_sh_i">
                             <i class="jli-youtube"></i>
                          </span>
                          <span class="jl_sh_t">YouTube</span>
                          <span class="jl_sh_w">
                          <span class="jl_sh_c">100k</span>
                          <span class="jl_sh_l">Subscribers</span>
                          </span>
                       </a>
                    </li>
                    
                    <li class="jl_twitter_url" style="--jl-social-color:#292b30;">
                        <a aria-label="twitter" href="https://x.com/KongoMikili">
                            <span class="jl_sh_i"><i class="jli-x"></i></span>
                            <span class="jl_sh_t">Twitter</span>
                            <span class="jl_sh_w">
                            <span class="jl_sh_c">65k</span>
                            <span class="jl_sh_l">Followers</span>
                            </span>
                        </a>
                    </li>
                                                                                                                                                                                                        
                    
                    <li class="jl_tiktok_url" style="--jl-social-color:#980ac1;">
                        <a aria-label="tiktok" href="#!">
                            <span class="jl_sh_i"><i class="jli-tiktok"></i></span>
                            <span class="jl_sh_t">Tiktok</span>
                            <span class="jl_sh_w">
                            <span class="jl_sh_c">65k</span>
                            <span class="jl_sh_l">Followers</span>
                            </span>
                        </a>
                    </li>
               </ul>
            </div>
         </div> -->
         
        <!-- publicités -->
           <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                <div class="elementor-widget-container">
                    <div class="jl_ads_img_w">
                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-emplacement="pub-sidebar-1" data-format="rectangle" data-rotate="10000"></div>                       
                    </div>
              </div>
            </div>
        <!-- publicités -->

        <div class="elementor-element elementor-element-2ff2179f elementor-widget elementor-widget-bopea-section-title" data-id="2ff2179f" data-element_type="widget" data-widget_type="bopea-section-title.default">
            <div class="elementor-widget-container">
                <div class="jlcus_sec_title jl_sec_style13 jl_secf_title">
                    <div class="jlcus_sect_inner">
                        <h2 class="jl-heading-text">
                            <span class="jl_ttw">À ne pas manquer</span>
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="elementor-element elementor-element-3fa9d3f1 elementor-widget elementor-widget-bopea-grid-post" data-id="3fa9d3f1" data-element_type="widget" data-widget_type="bopea-grid-post.default">
            <div class="elementor-widget-container">
                <div class="jl_clear_at block-section jl-main-block jl_hide_meta jl_hide_desc jl_hide_line jl_hide_col_line jl_sh_num jl_num_top"
                  data-blockid="blockid_3fa9d3f1"
                  data-section_style="jl_mgrid"
                  data-post_type="post"
                  data-post_type_tax="none"
                  data-page_max="25"
                  data-page_current="1"
                  data-author="none"
                  data-order="date_post"
                  data-posts_per_page="2"
                  data-offset="40"
                  data-tabs_link="none">
                    

                    <div class="jl_grid_wrap_f jl_wrap_eb jl_clear_at">
                        <div class="jl-roww jl_contain jl_cgrid_wrap jl-col-row">

                            
                            <?php foreach ($mustRows as $row): ?>
                  <?php
                    $t = clean_title($row['titre'] ?? '');
                    $uArticle = "/article/" . (int)$row['id'] . "/" . slugify($t);
                    $uCat = "/categorie/" . rawurlencode($row['categorie'] ?? '');
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




<!-- Articles similaires -->
<?php if (!empty($similarArticles)): ?>
<div class="jl_relsec_wrap">
    <div class="jl_relsec">
        <div class="jl_relsec_in jl_sep_dot">
            <span class="rel_head h2">Articles similaires</span>
            <div class="jl_rel_posts">
                <?php foreach ($similarArticles as $similar):
                    $t = clean_title($similar['titre']);
                    $similar_url = "/article/" . (int)$similar['id'] . "/" . slugify($t);
                    $similar_category_url = "/categorie/" . rawurlencode($similar['category_name'] ?? '');
                    $similar_date = fmt_date($similar['date_add']);
                    
                    $titre_excerpt = strip_tags($similar['titre']);
                    if (strlen($titre_excerpt) > 80) {
                        $titre_excerpt = substr($titre_excerpt, 0, 80) . '...';
                    }

                    $similar_excerpt = strip_tags($similar['contenu']);
                    if (strlen($similar_excerpt) > 100) {
                        $similar_excerpt = substr($similar_excerpt, 0, 100) . '...';
                    }
                ?>
                <div class="jl_cgrid_layout">
                    <div class="jl_img_holder">
                        <div class="jl_imgw jl_radus_e">
                            <div class="jl_imgin">
                                <?php
                                $similarCovers = parse_cover_images($similar['cover'] ?? '');
                                $similarCoverImg = !empty($similarCovers) ? $similarCovers[0] : '/img/placeholder-8.webp';
                                ?>
                                <img width="680" height="510"
                                     src="<?php echo htmlspecialchars(cn_media_url($similarCoverImg) ?? ''); ?>"
                                     class="attachment-bopea_layouts size-bopea_layouts wp-post-image"
                                     alt="<?php echo htmlspecialchars($similar['titre']); ?>"
                                     decoding="async"/>
                            </div>
                            <a class="jl_imgl" href="<?php echo htmlspecialchars($similar_url); ?>"></a>
                            <?php if (!empty($similar['category_name'])): ?>
                            <span class="jl_f_cat jl_lb7">
                                <a class="jl_cat_lbl jl_cat70" style="background:<?= htmlspecialchars(category_color($similar['category_name'])) ?>!important" href="<?php echo htmlspecialchars($similar_category_url); ?>">
                                    <span><?php echo htmlspecialchars($similar['category_name']); ?></span>
                                </a>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jl_fe_text">
                        <h3 class="h3 jl_fe_title">
                            <a href="<?php echo htmlspecialchars($similar_url); ?>">
                                <?php echo htmlspecialchars($titre_excerpt); ?>
                            </a>
                        </h3>
                        <p class="jl_fe_des">
                            <?php echo htmlspecialchars($similar_excerpt); ?>
                        </p>

                        <span class="jl_post_meta">
                            <?php if (!empty($similar['auteur_nom'])): ?>
                            <span class="jl_author_img_w">
                                Par <a href="#!" rel="author"><?php echo htmlspecialchars($similar['auteur_nom']); ?></a>
                            </span>
                            <?php endif; ?>
                            <span class="post-date"><?php echo $similar_date; ?></span>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- /Articles similaires -->


</div>
