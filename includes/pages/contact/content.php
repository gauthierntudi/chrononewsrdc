<div class="jl_block_content jl_sp_con" id="page-contact">
   <div class="jlc-container">
      <div class="jlc-row main_content jl_single_tpl8">




         <div class="jlc-col-md-8 jl_smmain_con">
            <div class="jl_smmain_w">
                <div class="jl_smmain_in">
                    
                    
                <div class="jl_sg_rgap">
                    <div class="post_content_w">
                            
                            
                        
                        <div class="jls_con_w">
                            <div class="post_content jl_content">
                                    
                                <h1 class="jl_head_title">Contactez-nous</h1>
                                <br>

                                <p class="has-drop-cap">
                                    Vous avez une question, une information à partager, une suggestion ou une opportunité de partenariat avec <strong>Chrono News</strong> ? Écrivez-nous via le formulaire ci-dessous ou retrouvez-nous sur nos réseaux sociaux.
                                </p>
                                
                                <div class="kmb-contact-form-wrapper">
                                    <form action="#" method="post" class="wpcf7-form init" id="kmb-contact-form">
                                        
                                        <div class="kmb-form-row">
                                            <div class="kmb-form-col">
                                                <label for="your-name" class="kmb-form-label">Votre nom</label>
                                                <span class="wpcf7-form-control-wrap" data-name="your-name">
                                                    <input size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required kmb-form-input" id="your-name" autocomplete="name" aria-required="true" aria-invalid="false" value="" type="text" name="your-name" placeholder="Entrez votre nom complet" />
                                                </span>
                                            </div>

                                            <div class="kmb-form-col">
                                                <label for="your-email" class="kmb-form-label">Votre e-mail</label>
                                                <span class="wpcf7-form-control-wrap" data-name="your-email">
                                                    <input size="40" class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-email kmb-form-input" id="your-email" autocomplete="email" aria-required="true" aria-invalid="false" value="" type="email" name="your-email" placeholder="exemple@domaine.com" />
                                                </span>
                                            </div>
                                        </div>

                                        <div class="kmb-form-group">
                                            <label for="subject-select" class="kmb-form-label">Sujet</label>
                                            <select id="subject-select" class="kmb-form-select" onchange="toggleOtherSubject()">
                                                <option value="" disabled selected>Choisissez un sujet...</option>
                                                <option value="Partenariat">Partenariat & Sponsoring</option>
                                                <option value="Rédaction">Contacter la rédaction</option>
                                                <option value="Signalement">Signaler une information</option>
                                                <option value="Publicité">Régie publicitaire</option>
                                                <option value="Support">Support technique</option>
                                                <option value="Autre">Autre (préciser)</option>
                                            </select>
                                            
                                            <!-- Champ caché qui stockera la valeur finale du sujet -->
                                            <input type="hidden" name="your-subject" id="final-subject" value="">
                                            
                                            <div id="other-subject-group">
                                                <label for="other-subject-input" class="kmb-form-label" style="margin-top: 10px; font-size: 0.9em;">Précisez le sujet :</label>
                                                <input type="text" id="other-subject-input" class="kmb-form-input" placeholder="Indiquez l'objet de votre message" oninput="updateFinalSubject()">
                                            </div>
                                        </div>

                                        <div class="kmb-form-group">
                                            <label for="your-message" class="kmb-form-label">Votre message</label>
                                            <span class="wpcf7-form-control-wrap" data-name="your-message">
                                                <textarea cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea kmb-form-textarea" id="your-message" aria-invalid="false" name="your-message" placeholder="Comment pouvons-nous vous aider ?"></textarea>
                                            </span>
                                        </div>

                                        <div class="kmb-form-group">
                                            <button type="submit" class="wpcf7-form-control wpcf7-submit has-spinner kmb-submit-btn">
                                                Envoyer le message <i class="jli-right-chevron" style="margin-left: 10px;"></i>
                                            </button>
                                        </div>
                                        
                                    </form>
                                </div>
                                
                                <script>
                                function toggleOtherSubject() {
                                    var select = document.getElementById('subject-select');
                                    var otherGroup = document.getElementById('other-subject-group');
                                    var otherInput = document.getElementById('other-subject-input');
                                    var finalInput = document.getElementById('final-subject');
                                    
                                    if (select.value === 'Autre') {
                                        otherGroup.style.display = 'block';
                                        otherInput.required = true;
                                        otherInput.focus();
                                        finalInput.value = otherInput.value; // Initialize with current input value
                                    } else {
                                        otherGroup.style.display = 'none';
                                        otherInput.required = false;
                                        finalInput.value = select.value;
                                    }
                                }

                                function updateFinalSubject() {
                                    var select = document.getElementById('subject-select');
                                    var otherInput = document.getElementById('other-subject-input');
                                    var finalInput = document.getElementById('final-subject');
                                    
                                    if (select.value === 'Autre') {
                                        finalInput.value = otherInput.value;
                                    }
                                }
                                </script>
                                
                                <h3>Autres moyens de contact</h3>
                                <div style="margin-top: 20px;">
                                    <p style="margin-bottom: 15px;">
                                        <strong><i class="jli-envelope" style="margin-right: 8px;"></i> E-mail :</strong> <br>
                                        <?= htmlspecialchars(cn_contact_email(), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p style="margin-bottom: 15px;">
                                        <strong><i class="jli-map-marker" style="margin-right: 8px;"></i> Adresse :</strong> <br>
                                        <?= htmlspecialchars(cn_contact_address(), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p style="margin-bottom: 15px;">
                                        <strong><i class="jli-phone" style="margin-right: 8px;"></i> Téléphone :</strong> <br>
                                        <?= htmlspecialchars(cn_contact_phone(), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p style="margin-bottom: 15px;">
                                        <strong><i class="jli-whatsapp" style="margin-right: 8px;"></i> WhatsApp :</strong> <br>
                                        <?= htmlspecialchars(cn_contact_whatsapp(), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>

                            </div>
                    </div>
                </div>
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
         <?php $socialVariant = 'widget'; include dirname(__DIR__, 2).'/partials/social-links-widget.php'; ?>

        <!-- publicités -->
           <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                <div class="elementor-widget-container">
                    <div class="jl_ads_img_w">
                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="rectangle" data-rotate="10000"></div>                       
                    </div>
              </div>
            </div>
        <!-- publicités -->

        <!-- publicités -->
           <div class="elementor-element elementor-element-1fb4b2b elementor-widget elementor-widget-bopea-section-ads-img" data-id="1fb4b2b" data-element_type="widget" data-widget_type="bopea-section-ads-img.default">
                <div class="elementor-widget-container">
                    <div class="jl_ads_img_w">
                        <div class="jl_ads_inner jl_ads_img ad-slot lazyload" data-format="portrait" data-rotate="13000"></div>                       
                    </div>
              </div>
            </div>
        <!-- publicités -->
        
        
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
