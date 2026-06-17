(function ($) {
    "use strict";
    var $window, $document, $body;
    var jlCookies = {
        setCookie: function setCookie(key, value, time, path) {
            var expires = new Date();
            expires.setTime(expires.getTime() + time);
            var pathValue = '';
            if (typeof path !== 'undefined') {
                pathValue = 'path=' + path + ';';
            }
            document.cookie = key + '=' + value + ';' + pathValue + 'expires=' + expires.toUTCString();
        },
        getCookie: function getCookie(key) {
            var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : null;
        }
    };
    $window = $(window);
    $document = $(document);
    $body = $("body");
    var Optloadfnc = {
        initialised: false,
        init: function () {
            if (!this.initialised) {
                this.initialised = true;
            } else {
                return;
            }
            this.jlmenucalc();
            this.jl_img_pop();
            this.jl_vid_pop();
            this.jl_img_slider();
            this.jl_nav_detect();
            this.jl_nav_custom();
            this.jl_gdpr();
            this.jl_toggle();
            this.jl_to_top();
            this.jl_qnt_num();
            this.jl_dn_tog();
            this.jl_lazy_opt();
            this.jl_n_mode_opt();
            this.jl_n_mode_store();
            this.livmode();
            this.jl_sh_link();
            this.jl_rd_scroll();
        },
        jl_img_pop: function () {
            var img_container = $('.wp-block-image');
            var gal_container = $('.wp-block-gallery');
            var filterImages = function () {
                if (!$(this).attr('href')) {
                    return false;
                }
                return $(this).attr('href').match(/\.(jpe?g|webp|png|bmp|gif|mp4)($|\?.+?)|youtube|vimeo|google.com\/maps/);
            };
            if (img_container.length) {
                var galItems = img_container.find(' > a');
                galItems
                    .filter(filterImages)
                    .addClass('jl_gals_link');
                img_container.addClass('jl_gals_w');
            }
            if (img_container.length) {
                var galItems = img_container.find(' figure > a');
                galItems
                    .filter(filterImages)
                    .addClass('jl_gals_link');
                img_container.addClass('jl_gals_w');
            }
            if (gal_container.length) {
                var galItems = gal_container.find('figure');
                gal_container.addClass('jl_gals_w');
                galItems.removeClass('jl_gals_w');
            }
            var pop_img_wrap = $('.jl_gals_w');
            if (pop_img_wrap.length > 0) {
                pop_img_wrap.each(function () {
                    var galPops = GLightbox({
                        selector: '.jl_gals_link'
                    });
                })
            }
        },
        jl_vid_pop: function () {
            var initVid = GLightbox({
                selector: '.jl_pop_vid',
                videosWidth: '100%',
            });
        },
        jl_img_slider: function () {
            let sg_slider_wrap = $('.jl-pgal-slider');
            if (sg_slider_wrap.length > 0) {
                const swiperElm = document.querySelectorAll(".jl-pgal-slider");
                swiperElm.forEach(function (swiperelm) {
                    const swiperOptions = JSON.parse(swiperelm.dataset.swiperOptions);
                    let thmSwiperSlider = new Swiper(swiperelm, swiperOptions);
                });
            }
        },
        jl_nav_detect: function () {
            var $menuItems = $('.navigation_wrapper .jl_main_menu > li.menu-item-has-children');
            if ($menuItems.length) {
                $menuItems.each(function (i) {
                    var thisItem = $(this),
                        menuItemPosition = thisItem.offset().left,
                        dropdownMenuItem = thisItem.find(' > ul'),
                        dropdownMenuWidth = dropdownMenuItem.outerWidth(),
                        menuItemFromLeft = $(window).width() - menuItemPosition;
                    var dropDownMenuFromLeft;
                    if (thisItem.find('li.menu-item-has-children').length > 0) {
                        dropDownMenuFromLeft = menuItemFromLeft - dropdownMenuWidth;
                    }
                    dropdownMenuItem.removeClass('jlc-right-menu');
                    if (menuItemFromLeft < dropdownMenuWidth || dropDownMenuFromLeft < dropdownMenuWidth) {
                        dropdownMenuItem.addClass('jlc-right-menu');
                    }
                });
            }
        },
        jl_nav_custom: function () {
            $('.menu_mobile_icons, .mobile_menu_overlay').on("click", function () {
                $('#jl_sb_nav').toggleClass('jl_mobile_nav_open');
                $('.mobile_menu_overlay').toggleClass('mobile_menu_active');
                $('.mobile_nav_class').toggleClass('active_mobile_nav_class');
            });

            $('.widget_nav_menu ul > li.menu-item-has-children').on('click', function () {
                var parentMenu = jQuery(this);
                parentMenu.toggleClass('active');
                return false;
            });

            $("#mobile_menu_slide .menu-item-has-children > a").append($("<span/>", {
                class: 'arrow_down'
            }).html('<i class="jli-down-chevron" aria-hidden="true"></i>'));
            $('#mobile_menu_slide .arrow_down i').on("click", function () {
                var submenu = $(this).closest('.menu-item-has-children').find(' > .sub-menu');
                $(this).toggleClass("jli-down-chevron").toggleClass("jli-up-chevron");
                $(submenu).slideToggle(150, "linear");
                return false;
            });

            let se_wrapper = $('.jl_shsmb');
            if (se_wrapper.length > 0) {
                se_wrapper.each(function () {
                    let mainElement = $(this);
                    let search_click = mainElement.find('.search_form_menu_personal_click');
                    let search_form = mainElement.find('.search_form_menu_personal');
                    search_click.on("click", function () {
                        search_form.toggleClass('search_form_menu_personal_active');
                        setTimeout(function () {
                            if (!$("body").hasClass("jlac_smseah")) {
                                search_form.find('.search_btn').focus()
                            }
                        }, 100)
                    });
                });
            } else {
                $('.search_form_menu_personal_click').on("click", function () {
                    $('.search_form_menu_personal').toggleClass('search_form_menu_personal_active');
                    $('.mobile_nav_class').toggleClass('active_search_box');
                    setTimeout(function () {
                        if (!$("body").hasClass("jlac_smseah")) {
                            $('.search_form_menu_personal').find('.search_btn').focus()
                        }
                    }, 100)

                });
            }

            $(document).keyup(function (e) {
                if (e.keyCode == 27) {
                    $('.search_form_menu_personal.search_form_menu_personal_active').toggleClass('search_form_menu_personal_active');
                    $('.mobile_nav_class.active_search_box').toggleClass('active_search_box');
                }
            });

            $('.search_form_menu_click').on('click', function (e) {
                e.preventDefault();
                $('.search_form_menu').toggle();
                $(this).toggleClass('active');
            });
            if ($('.sb-toggle-left').length) {
                $('.sb-toggle-left').on("click", function () {
                    $('#nav-wrapper').toggle(100);
                });
                $("#menu-main-menu .menu-item-has-children > a").append($("<span/>", {
                    class: 'arrow_down'
                }).html('<i class="jli-down-chevron"></i>'));
            }

            $('#nav-wrapper .menu .arrow_down').on("click", function () {
                var $submenu = $(this).closest('.menu-item-has-children').find(' > .sub-menu');

                if ($submenu.hasClass('menu-active-class')) {
                    $submenu.removeClass('menu-active-class');
                } else {
                    $submenu.addClass('menu-active-class');
                }
                return false;
            });

            $('.jl_filter_btn').on("click", function () {
                $('.jl_shop_filter_content').toggleClass('jl_woo_fs');
            });

            if ($('body').hasClass('jl_nav_stick')) {
                var theElement = $('body').hasClass('jl_nav_active') ? $('.jl_cus_sihead') : $('.tp_head_on');
                var jl_navbase, $orgElement, $jl_r_menudElement, orgElementTop, currentScroll, previousScroll = 0, scrollDifference, detachPoint = 320, hideShowOffset = 2,
                    $html = $('body');
                $orgElement = $('.jl_base_menu');
                if ($orgElement.length) {
                    $jl_r_menudElement = $('.jl_r_menu');
                    jl_navbase = $('body').hasClass('jl_nav_slide');
                    $jl_r_menudElement.width($orgElement.width());
                    $(window).on("resize", function () {
                        $jl_r_menudElement.width($orgElement.width());
                    });
                    $(window).on("scroll", function () {
                        if (jQuery(this).scrollTop() > 500) {
                            jQuery("#go-top").fadeIn();
                        } else {
                            jQuery("#go-top").fadeOut();
                        }
                        currentScroll = $(this).scrollTop(),
                            scrollDifference = Math.abs(currentScroll - previousScroll);

                        if ($(this).scrollTop() <= 330) {
                            if (!$html.hasClass('menu-hide-fixed')) {
                                $html.addClass('menu-hide-fixed');
                            }
                        } else {
                            $html.removeClass('menu-hide-fixed');
                        }

                        $jl_r_menudElement.width($orgElement.width());
                        orgElementTop = $orgElement.offset().top;
                        if (currentScroll >= (orgElementTop) && currentScroll != 0) {
                            if (jl_navbase) {
                                if (currentScroll > detachPoint) {
                                    if (!$html.hasClass('menu-detached')) {
                                        $html.addClass('menu-detached');
                                    }

                                }
                                if (scrollDifference >= hideShowOffset) {
                                    if (currentScroll > previousScroll) {
                                        if (!$html.hasClass('menu-invisible')) {
                                            $html.addClass('menu-invisible');
                                        }
                                    } else {
                                        if ($html.hasClass('menu-invisible')) {
                                            $html.removeClass('menu-invisible');
                                        }
                                    }
                                }
                            } else {
                                $jl_r_menudElement.addClass('m-visible');
                                $orgElement.addClass('m-hidden');
                            }
                        } else {
                            $jl_r_menudElement.removeClass('m-visible');
                            $orgElement.removeClass('m-hidden');

                            if (jl_navbase) {
                                $html.removeClass('menu-detached').removeClass('menu-invisible');
                            }
                        }
                        previousScroll = currentScroll;
                    });
                }
            }
        },
        jlmenucalc: function () {
            var jlCusMenu = $('.jl_hwrap');
            if (jlCusMenu.length > 0) {
                jlCusMenu.each(function () {
                    var thisItem = $(this);
                    thisItem.find('.jl-cus-mega-menu').css({
                        'width': $(window).width(),
                        'left': -thisItem.offset().left,
                    });
                    thisItem.addClass('mega-menu-loaded')
                })
            }


            var wooselect = $('.woocommerce-ordering select');
            if (wooselect.length > 0) {
                wooselect.each(function () {
                    var $originalSelect = $(this);
                    $originalSelect.next(".jl-select-container").remove();
                    var selectedText = $originalSelect.find("option:selected").text();
                    var $customSelectContainer = $("<div class='jl-select-container'></div>");
                    var $customDisplay = $("<div class='jl-select-display'></div>").text(selectedText);
                    var $customOptionsContainer = $("<div class='jl-select-opt'></div>");
                    $originalSelect.find("option").each(function () {
                        var optionText = $(this).text();
                        var optionValue = $(this).val();
                        var $customOption = $("<div class='jl-custom-opt'></div>")
                            .text(optionText)
                            .attr("data-value", optionValue);

                        $customOptionsContainer.append($customOption);
                    });
                    $originalSelect.after($customSelectContainer);
                    $customSelectContainer.append($customDisplay);
                    $customSelectContainer.append($customOptionsContainer);
                    $customDisplay.on("click", function () {
                        $(".jl-select-opt").not($customOptionsContainer).hide();
                        $customOptionsContainer.toggle();
                    });
                    $customOptionsContainer.on("click", ".jl-custom-opt", function () {
                        var selectedText = $(this).text();
                        var selectedValue = $(this).attr("data-value");
                        $customDisplay.text(selectedText);
                        $originalSelect.val(selectedValue).trigger("change");
                        $customOptionsContainer.hide();
                    });
                    $(document).on("click", function (e) {
                        if (!$(e.target).closest(".jl-select-container").length) {
                            $(".jl-select-opt").hide();
                        }
                    });
                });
            }
        },
        jl_gdpr: function () {
            if ($('#jl-gdpr').length > 0) {
                if ($.cookie('jl_cookie_accept') !== '1') {
                    $('#jl-gdpr').css('display', 'block');
                    setTimeout(function () {
                        $('#jl-gdpr').addClass('jl-display');
                    }, 10)
                }
                $('#jl-gdpr-accept').off('click').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $.cookie('jl_cookie_accept', '1', { expires: 30, path: '/' });
                    $('#jl-gdpr').removeClass('jl-display');
                    setTimeout(function () {
                        $('#jl-gdpr').css('display', 'none');
                    }, 500)
                })
            }
        },
        jl_toggle: function () {
            if ($('#bbp-new-topic-toggle-btn').length > 0) {
                $('#bbp-new-topic-toggle-btn').off('click').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#bbp-new-topic-toggle').slideToggle('400');
                })
            }
        },
        jl_to_top: function () {
            $("#go-top").on("click", function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                return false;
            });
        },
        jl_qnt_num: function () {
            $('.quantity .jlb-btn').on("click", function (e) {
                e.preventDefault();
                var woo_btn = $(this);
                var step = 1;
                var input = woo_btn.parent().find('input');
                var min = 1;
                var max = 99999;
                var input_old_val = parseInt(input.val());
                var input_new_val = parseInt(input.val());
                if (input.attr('step')) {
                    step = parseInt(input.attr('step'));
                }
                if (input.attr('min')) {
                    min = parseInt(input.attr('min'));
                }
                if (input.attr('max')) {
                    max = parseInt(input.attr('max'));
                }
                if (woo_btn.hasClass('up')) {
                    if (input_old_val < max) {
                        input_new_val = input_old_val + step;
                    } else {
                        input_new_val = 1;
                    }
                } else if (woo_btn.hasClass('down')) {
                    if (input_old_val > min) {
                        input_new_val = input_old_val - step;
                    } else {
                        input_new_val = 0;
                    }
                }
                if (!input.attr('disabled')) {
                    input.val(input_new_val).change();
                }
            });
        },
        jl_ma_effect_load: function (jl_post_effect) {
            var $commonElements = $('.jl_ani_col');
            if ($commonElements.length) {
                $commonElements.each(function () {
                    var $thisItem = $(this);
                    if (jl_post_effect) {
                        $thisItem.addClass('jl_ma_nitem');
                        setTimeout(function () {
                            $thisItem.addClass('jl_ma_appear');
                        }, 1100);
                    } else {
                        $thisItem.waypoint(function () {
                            $thisItem.addClass('jl_ma_appear');
                        }, {
                            offset: '105%'
                        });
                    }
                });
            }
        },
        jl_rd_scroll: function () {
            $(window).on("scroll", function () {
                var target = $('.jl_sp_con');
                if (target.length > 0) {
                    target.each(function (id, el) {
                        var _el = $(el),
                            pageTop = $(window).scrollTop(),
                            pageBottom = pageTop + $(window).height(),
                            elementTop = _el.offset().top,
                            elementBottom = elementTop + _el.height() + 80,
                            elementBottomNew = elementTop + 0,
                            indicatorHeight = _el.outerHeight(true) - $(window).height(),
                            key = _el.attr('id');

                        if (pageTop >= elementTop && pageTop <= elementBottom) {
                            var elementHeight = _el.height();
                            var totalScroll = ((pageTop - elementTop) / indicatorHeight) * 100;
                            $('.jl_rd_read[data-key = ' + key + ']').css({ 'width': totalScroll + '%', 'opacity': '1' });
                        }
                        if (pageTop < elementTop) {
                            $('.jl_rd_read[data-key = ' + key + ']').css({ 'width': '0', 'opacity': '0' });
                        }
                        if (pageTop > elementBottom) {
                            $('.jl_rd_read[data-key = ' + key + ']').css({ 'width': '100%', 'opacity': '0' });
                        }
                    });
                }
            });
        },
        jl_dn_tog: function () {
            $('.bopea_day_night .jl_moon').on("click", function () {
                $('.bopea_day_night').addClass('jl_night_en');
                $('.bopea_day_night').removeClass('jl_day_en');
                $('.jl_en_day_night').addClass('options_dark_skin');
                $('.mobile_nav_class').addClass('wp-night-mode-on');
                $.cookie('jlendnight', 'no', {
                    expires: 7,
                    path: '/'
                });
                $.cookie('jlenday', 'no', {
                    expires: 7,
                    path: '/'
                });
            });
            $('.bopea_day_night .jl_sun').on("click", function () {
                $('.bopea_day_night').addClass('jl_day_en');
                $('.bopea_day_night').removeClass('jl_night_en');
                $('.jl_en_day_night').removeClass('options_dark_skin');
                $('.mobile_nav_class').removeClass('wp-night-mode-on');

                $.cookie('jlenday', 'no', {
                    expires: 7,
                    path: '/'
                });
                $.cookie('jlendnight', 'no', {
                    expires: 7,
                    path: '/'
                });

            });
        },
        jl_lazy_opt: function () {
            window.lazySizesConfig = window.lazySizesConfig || {};
            window.lazySizesConfig.expand = 1000;
            window.lazySizesConfig.loadMode = 1;
            window.lazySizesConfig.loadHidden = false;
        },
        jl_n_mode_opt: function () {
            var nightModeButton = document.querySelectorAll('.jl-night-toggle-icon');
            for (var i = 0; i < nightModeButton.length; i++) {
                nightModeButton.item(i).onclick = function (event) {
                    event.preventDefault();
                    for (var i = 0; i < nightModeButton.length; i++) {
                        nightModeButton[i].classList.toggle('active');
                    }
                    if (this.classList.contains('active')) {
                        jlCookies.setCookie('jlmode_dn', 'true', 2628000000, '/');
                    } else {
                        jlCookies.setCookie('jlmode_dn', 'false', 2628000000, '/');
                    }
                };
            }
        },
        jl_n_mode_store: function () {
            var nightModeButton = document.querySelectorAll('.jl-night-toggle-icon');
            if ('true' === jlCookies.getCookie('jlmode_dn')) {
                document.body.classList.add('wp-night-mode-on');
                $('.jl_en_day_night').addClass('options_dark_skin');
                $('.bopea_day_night').addClass('jl_night_en');
                $('.bopea_day_night').removeClass('jl_day_en');
                for (var i = 0; i < nightModeButton.length; i++) {
                    nightModeButton[i].classList.add('active');
                }
            } else if ('false' === jlCookies.getCookie('jlmode_dn')) {
                document.body.classList.remove('wp-night-mode-on');
                $('.jl_en_day_night').removeClass('options_dark_skin');
                $('.bopea_day_night').removeClass('jl_night_en');
                for (var i = 0; i < nightModeButton.length; i++) {
                    nightModeButton[i].classList.remove('active');
                }
            } else {
                if (jlParamsOpt.opt_dark == 1) {
                    document.body.classList.add('wp-night-mode-on');
                    $('.jl_en_day_night').addClass('options_dark_skin');
                    $('.bopea_day_night').addClass('jl_night_en');
                    $('.bopea_day_night').removeClass('jl_day_en');
                    for (var i = 0; i < nightModeButton.length; i++) {
                        nightModeButton[i].classList.add('active');
                    }
                }
            }
        },
        mediaFrameLoad: function () {
            var jlFrame = document.getElementsByClassName('jl_fm_vid_load'),
                jlOptFrame = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            jlOptFrame.unobserve(entry.target);
                            Optloadfnc.mediaFrameCheck(entry.target);
                        }
                    });
                }, { threshold: [0], rootMargin: '150px 0px', });
            for (var i = jlFrame.length - 1; i >= 0; i--) {
                if (!jlFrame[i].classList.contains('jl_fm_loading')) {
                    jlOptFrame.observe(jlFrame[i]);
                }
            }
        },
        mediaFrameCheck: function (iframe) {
            var src = iframe.getAttribute('data-lazy-src');
            iframe.setAttribute('src', src);
            $(iframe).on('load', function () {
                var commonElements = $('.jl_img_holder');
                var jl_vidfr_in = commonElements.find('.video-wrap');
                iframe.classList.add('jl_fm_loading');
                iframe.classList.add('loaded');
                $(this).parent().addClass('jl_load_vids');
            });
        },
        jl_wp_mu: function (i) {
            var commonElements = $('.jl-wp-mu');
            if (commonElements.length) {
                for (i = 0; i < commonElements.length; i++) {
                    var $commonElements = $(commonElements[i]);
                    let $optElements = $commonElements.find('.jl_mega_cols');
                    $optElements.each(
                        function (index) {
                            let $character = $(this), transitionDelay = (index * 100) + 'ms';
                            $character.css({ 'animation-delay': transitionDelay });
                        }
                    );
                }
            }
        },
        jl_sh_link: function () {
            var jlMainShare = $('.jl_shli .jl_sshl');
            if (jlMainShare.length > 0) {
                jlMainShare.on('click', function () {
                    var left = (screen.width / 2) - (640 / 2),
                        top = (screen.height / 2) - (440 / 2) - 100;
                    window.open($(this).attr('href'), 'mywin', 'left=' + left + ',top=' + top + ',width=640,height=440,toolbar=0');
                    return false;
                });
            }
        },
        livmode: function () {
            var $body = $('body');
            $body.on('click', '.jl_vid_sh', Optloadfnc.mdsOpt);
        },
        mdsOpt: function () {
            var video = document.createElement('video');
            var thisItem = $(this),
                optVid = thisItem.data(),
                vidDisplay;
            if (optVid.vidtype == 'embedvid') {
                if (optVid.embedvid === '') {
                    vidDisplay = '';
                } else {
                    vidDisplay = '<iframe title="Video" frameborder="0" seamless="seamless" allow="autoplay" src="' + optVid.embedvid + '" allowfullscreen></iframe>';
                }
            } else {
                if (video.canPlayType('video/mp4;')) { video.type = 'video/mp4'; if (optVid.localvid !== '') { video.src = optVid.localvid; } }
                video.setAttribute('controls', 'controls');
                video.setAttribute('controlsList', 'nodownload');
                video.play();
                vidDisplay = video;
            }
            var jl_vidli_w = thisItem.closest('.jl_vidsb_c');
            if (jl_vidli_w.hasClass('jl_ac_vid')) { return; }
            var jl_vidli_in = $('#jl-vid-alist-' + optVid.blockid);
            var jl_vidli_fr = jl_vidli_in.find('.jl_vid_msh');
            var jl_vidfr_in = jl_vidli_fr.find('.jl_vidfr');
            jl_vidli_fr.addClass('jl_vidprog').removeClass('jl_vidacsh');
            jl_vidfr_in.html('<div class="jl_vid_pac"><div id="jl-vid-ac-' + optVid.blockid + '" class="jl_vid_pin"></div></div>');
            $('#jl-vid-ac-' + optVid.blockid).append(vidDisplay);
            setTimeout(function () { jl_vidli_fr.addClass('jl_vidacsh jl_vidfr'); }, 190);
            if (thisItem.hasClass('jl_vid_tar')) { jl_vidli_w.addClass('jl_ac_vid').siblings().removeClass('jl_ac_vid'); }
            return false;
        }
    };
    $(document).ready(function () {
        Optloadfnc.init();
        cusMainScript.init();
    });
    $(window).on('load', function () {
        Optloadfnc.jl_ma_effect_load();
        Optloadfnc.jl_wp_mu();
        Optloadfnc.mediaFrameLoad();
    });
    window.addEventListener('resize', function () {
        Optloadfnc.jlmenucalc();
        Optloadfnc.jl_nav_detect();
    });
})(jQuery);
jlSpscript = (function ($) {
    var jlMainOpt = {
        jlSpLoad: function () { this.jlsgslider(); this.jlGalPop(); this.jlShLink(); this.JlLoadV(); },
        JlLoadV: function () {
            var jlFrame = document.getElementsByClassName('jl_fm_vid_load'),
                jlOptFrame = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            jlOptFrame.unobserve(entry.target);
                            var iframe = entry.target;
                            var src = iframe.getAttribute('data-lazy-src');
                            iframe.setAttribute('src', src);
                            $(iframe).on('load', function () {
                                var commonElements = $('.jl_img_holder');
                                var jl_vidfr_in = commonElements.find('.video-wrap');
                                iframe.classList.add('jl_fm_loading');
                                iframe.classList.add('loaded');
                                $(this).parent().addClass('jl_load_vids');
                            });
                        }
                    });
                }, { threshold: [0], rootMargin: '150px 0px', });
            for (var i = jlFrame.length - 1; i >= 0; i--) {
                if (!jlFrame[i].classList.contains('jl_fm_loading')) {
                    jlOptFrame.observe(jlFrame[i]);
                }
            }
        },
        jlsgslider: function () {
            let sg_slider_wrap = $('.jl-pgal-slider');
            if (sg_slider_wrap.length > 0) {
                const swiperElm = document.querySelectorAll(".jl-pgal-slider");
                swiperElm.forEach(function (swiperelm) {
                    const swiperOptions = JSON.parse(swiperelm.dataset.swiperOptions);
                    let thmSwiperSlider = new Swiper(swiperelm, swiperOptions);
                });
            }
        },
        jlShLink: function () {
            var jlMainShare = $('.jl_shli .jl_sshl');
            if (jlMainShare.length > 0) {
                jlMainShare.on('click', function () {
                    var left = (screen.width / 2) - (640 / 2),
                        top = (screen.height / 2) - (440 / 2) - 100;
                    window.open($(this).attr('href'), 'mywin', 'left=' + left + ',top=' + top + ',width=640,height=440,toolbar=0');
                    return false;
                });
            }
        },
        jlGalPop: function () {
            var img_container = $('.wp-block-image');
            var gal_container = $('.wp-block-gallery');
            var filterImages = function () {
                if (!$(this).attr('href')) {
                    return false;
                }
                return $(this).attr('href').match(/\.(jpe?g|webp|png|bmp|gif|mp4)($|\?.+?)|youtube|vimeo|google.com\/maps/);
            };
            if (img_container.length) {
                var galItems = img_container.find(' > a');
                galItems
                    .filter(filterImages)
                    .addClass('jl_gals_link');
                img_container.addClass('jl_gals_w');
            }
            if (img_container.length) {
                var galItems = img_container.find(' figure > a');
                galItems
                    .filter(filterImages)
                    .addClass('jl_gals_link');
                img_container.addClass('jl_gals_w');
            }
            if (gal_container.length) {
                var galItems = gal_container.find('figure');
                gal_container.addClass('jl_gals_w');
                galItems.removeClass('jl_gals_w');
            }
            var pop_img_wrap = $('.jl_gals_w');
            if (pop_img_wrap.length > 0) {
                pop_img_wrap.each(function () {
                    var galPops = GLightbox({
                        selector: '.jl_gals_link'
                    });
                })
            }
        },
    }
    return jlMainOpt;
})(jQuery);
(function () {
    const detectScrlDown = 0.76; let numPDisplay = []; let spSelect_load; let spLoadView; let spProgress = false; let spLoadItems = []; let spEachItem;
    function spLoadScript() { spMainLoad(spScript); }
    function spScript() {
        if (typeof JlOptPosts === 'undefined' || !window.fetch) { return; }
        numPDisplay = JlOptPosts;
        spEachItem = document.querySelector('.jl_sp_con');
        spSelect_load = spEachItem;
        spLoadItems.push(spSelect_load);
        if (!spSelect_load) { return; }
        Object.assign(spSelect_load.dataset, { title: document.title, url: window.location.href });
        document.addEventListener('scroll', () => {
            if (spProgress || spEachItem.dataset.loading) { return; }
            let triggerLoad = spEachItem.offsetTop + (spEachItem.offsetHeight * detectScrlDown);
            if (window.scrollY > triggerLoad) { spProgress = true; requestAnimationFrame(spDisplayItems); }
        });
        spLoadView = new IntersectionObserver(spActivePost, { root: null, rootMargin: '0px 0px -50%', threshold: 0 });
    }
    function spSpinView(target) {
        target = target || spEachItem;
        const spin = document.createElement('div');
        Object.assign(spin, { className: 'jl_sp_spin' });
        target.after(spin);
    }
    function spDisplayItems() {
        const spItems = numPDisplay.shift();
        if (!spItems) { return; }
        if (spSelect_load === spEachItem) { spSelect_load.classList.add('jl_spm_con'); }
        const addPostContainer = (html) => {
            if (!html) { return; }
            const spParam = new DOMParser();
            const spConType = spParam.parseFromString(html, 'text/html');
            const spOutput = spConType.querySelector('.jl_sp_con');
            if (!spOutput) { return; }
            const spWrapper = document.createElement('div');
            spEachItem.after(spWrapper);
            Object.assign(spWrapper.dataset, { url: spItems.url, title: spItems.title, });
            Object.assign(spWrapper, { className: 'jl_sp_lp', innerHTML: spOutput.outerHTML });
            spEachItem = spWrapper;
            spLoadItems.push(spWrapper);
            return spWrapper;
        };
        spSpinView();
        fetch(spItems.url).then(resp => resp.text()).then(html => {
            const spWrapper = addPostContainer(html);
            document.querySelectorAll('.jl_sp_spin').forEach(e => e.remove());
            if (!spWrapper) { return; }
            spsLoads(spWrapper);
            if (window.twttr && twttr.widgets && twttr.widgets.load) { twttr.widgets.load(); }
            requestAnimationFrame(() => {
                spProgress = false;
                spCheckLoad(spWrapper);
                spLoadView.observe(spWrapper);
                jlSpscript.jlSpLoad(spWrapper);
            });
        });
    }
    function spActivePost(entries) {
        let spMainPost;
        let spActiveItem;
        for (let element of entries) {
            if (element.intersectionRatio <= 0) { spActiveItem = element.target; continue; }
            spMainPost = element.target;
            break;
        }
        if (!spMainPost) {
            const spFirstPost = spLoadItems.findIndex(spItems => spItems === spActiveItem);
            const spMovePost = spLoadItems[spFirstPost - 1];
            if (spMovePost && spMovePost.getBoundingClientRect().bottom >= 0) { spMainPost = spMovePost; }
        }
        if (spMainPost && spMainPost.dataset.url) {
            window.history.pushState(null, spMainPost.dataset.title, spMainPost.dataset.url);
            document.title = spMainPost.dataset.title;
            spActiveView();
        }
    }
    function spActiveView() {
        if (!spEachItem || spEachItem.dataset.viewTracked) { return; }
        if (window.gtag) { window.gtag('event', 'page_view', { page_title: spEachItem.dataset.title, page_location: spEachItem.dataset.url }); }
        if (window.ga) { window.ga('send', 'pageview', spEachItem.dataset.url); }
        spEachItem.dataset.viewTracked = 1;
    }
    function spsLoads(element) {
        let debloatDelay;
        element.querySelectorAll('script').forEach(item => {
            const script = document.createElement('script');
            script.text = item.textContent;
            const attrs = item.attributes;
            for (const attr of attrs) {
                script.setAttribute(attr.name, attr.value || true);
            }
            if (script.type && ['rocketlazyloadscript', 'text/debloat-script', '/javascript'].includes(script.type)) {
                script.type = 'text/javascript';
            }
            if (script.dataset.debloatDelay) {
                debloatDelay = true;
            }
            if (!script.src && script.dataset.src) {
                script.src = script.dataset.src;
            }
            document.body.append(script);
        });

        if (debloatDelay) {
            document.dispatchEvent(new Event('debloat-load-js'));
        }
    }
    function spCheckLoad(spEachItem) {
        const spConType = document.documentElement;
        if (spConType.scrollHeight - spConType.scrollTop <= spConType.clientHeight + 75) { spEachItem.scrollIntoView(); }
    }
    function spMainLoad(optsc) { document.readyState !== 'loading' ? optsc() : document.addEventListener('DOMContentLoaded', optsc); }
    spLoadScript();
})();
var cusMainScript = (function (OptScript, $) {
    OptScript.$body = $('body'); OptScript.$document = $(document); OptScript.$html = $('html, body'); OptScript.$window = $(window); OptScript.$ajax = {}; OptScript.sjload = {};
    OptScript.init = function () { this.queryList(); this.cusLoadScript(); };
    OptScript.cusLoadScript = function () { this.jl_menu_cat(); this.navNextPre(); this.navloadMore(); this.jl_popup_search(); this.navautoload(); };
    OptScript.loadpFunctions = function () { this.$html.off(); this.$document.off(); this.$window.trigger('load'); this.cusLoadScript(); };
    OptScript.$blocksave = { data: {}, get: function (id) { return this.data[id]; }, set: function (id, data) { this.remove(id); this.data[id] = data; }, remove: function (id) { delete this.data[id]; }, exist: function (id) { return this.data.hasOwnProperty(id) && this.data[id] !== null; } };
    OptScript.blockDatalist = function (block) {
        return { blockid: block.data('blockid'), section_style: block.data('section_style'), post_type: block.data('post_type'), post_type_tax: block.data('post_type_tax'), term_slugs: block.data('term_slugs'), posts_per_page: block.data('posts_per_page'), page_max: block.data('page_max'), page_current: block.data('page_current'), category: block.data('category'), categories: block.data('categories'), orderby: block.data('orderby'), searchkey: block.data('searchkey'), author: block.data('author'), tags: block.data('tags'), tabs_link: block.data('tabs_link'), post_not_in: block.data('post_not_in'), format: block.data('format'), offset: block.data('offset') };
    };
    OptScript.trackPagenav = function (block) {
        var settings = this.blockDatalist(block); var max_offsets = settings.page_max - settings.offset;
        var settings_max = ((settings.page_max * settings.posts_per_page) - settings.offset) / settings.posts_per_page;
        if (settings.page_current >= max_offsets || settings.page_current >= settings.page_max || settings.page_max <= 1 || settings.page_current >= settings_max) {
            block.find('.jl-load-link').hide(); block.find('.jl-load-animation').hide(); block.find('.jl_el_nav_w').addClass('jl_hide_pagination'); block.find('.jl_lmore_c').addClass('jl_hide_pagination'); block.find('.jl_autoload').addClass('jl_hide_pagination');
        } else {
            block.find('.jl-load-link').show(); block.find('.jl-load-link').css('opacity', 1); block.find('.jl-load-animation').hide(); block.find('.jl_el_nav_w').removeClass('jl_hide_pagination'); block.find('.jl_lmore_c').removeClass('jl_hide_pagination'); block.find('.jl_autoload').removeClass('jl_hide_pagination');
        }
        if (settings.page_max < 2) { block.find('.jl-foot-nav').addClass('jl_disable'); }
        if (settings.page_current >= settings.page_max) { block.find('.jl-next-nav').addClass('jl_disable'); }
        if (settings.page_current <= 1) { block.find('.jl-prev-nav').addClass('jl_disable'); }
    };
    OptScript.queryList = function () {
        var object = this;
        $('.jl-tab-link').off('click').on('click', function (e) {
            e.preventDefault(); e.stopPropagation(); var link = $(this); var block = link.parents('.block-section'); var blockid = block.attr('id');
            if (true == object.$ajax[blockid + '_loading']) { return; }
            object.$ajax[blockid + '_loading'] = true;
            var filterVal = link.data('ajax_filter_val');
            block.find('.jl-tab-link').removeClass('jl-ac-m');
            block.find('.jl-tab-link').not(this).addClass('jl-rm-ac');
            link.addClass('jl-ac-m');
            object.startEffect(block, 'replace');
            var settings = object.blockDatalist(block);
            object.resetQuickFilter(block, settings, filterVal);
            setTimeout(function () { object.blockLink(block, settings); }, 400);
        });
        OptScript.navNextPre = function () {
            var object = this;
            $('.jl-foot-nav').off('click').on('click', function (e) {
                e.preventDefault(); e.stopPropagation(); var link = $(this); var block = link.parents('.block-section'); var blockid = block.attr('id');
                if (true == object.$ajax[blockid + '_loading']) { return; }
                object.$ajax[blockid + '_loading'] = true;
                var type = link.data('type'); var settings = object.blockDatalist(block);
                object.startEffect(block, 'replace');
                object.navNextPreProcess(block, settings, type);
            });
        };
        OptScript.navNextPreProcess = function (block, settings, type) {
            if ('prev' == type) { settings.page_next = parseInt(settings.page_current) - 1; } else { settings.page_next = parseInt(settings.page_current) + 1; }
            var cacheSettings = settings; delete cacheSettings.page_max; cacheSettings.page_current = settings.page_next; var cacheID = JSON.stringify(cacheSettings);
            if (object.$blocksave.exist(cacheID)) {
                var data = object.$blocksave.get(cacheID);
                if ('undefined' != typeof data.page_current) { block.data('page_current', data.page_current); }
                object.endEffect(block, data.content, 'replace');
                return false;
            } else {
                $.ajax({
                    type: 'POST', url: jlParamsOpt.ajaxurl, data: { action: 'bopea_loadnavs', data: settings },
                    success: function (data) {
                        data = $.parseJSON(JSON.stringify(data));
                        if ('undefined' != typeof data.page_current) { block.data('page_current', data.page_current); }
                        object.$blocksave.set(cacheID, data);
                        object.endEffect(block, data.content, 'replace');
                    },
                    complete: function () {
                        block.find('.load-animation').css({ 'display': 'none' });
                    }
                });
            }
        };
        OptScript.resetQuickFilter = function (block, settings, filterVal) {
            var object = this; var blockid = block.attr('id');
            settings.page_current = 1;
            block.data('page_current', 1);
            if ('category' == settings.tabs_link) {
                if ('undefined' == typeof (object.$ajax[blockid + '_category'])) { object.$ajax[blockid + '_category'] = 0; }
                if (0 == filterVal) {
                    settings.category = object.$ajax[blockid + '_category'];
                    settings.categories = object.$ajax[blockid + '_categories'];
                    block.data('category', object.$ajax[blockid + '_category']);
                    block.data('categories', object.$ajax[blockid + '_categories']);
                } else {
                    settings.category = filterVal;
                    settings.categories = 0;
                    block.data('category', filterVal);
                    block.data('categories', 0);
                }
            }
            if ('tag' == settings.tabs_link) { settings.tags = filterVal; block.data('tags', filterVal); }
        };
        OptScript.blockLink = function (block, settings) {
            var object = this; var cacheSettings = settings;
            delete cacheSettings.page_max;
            var cacheID = JSON.stringify(cacheSettings);
            if (object.$blocksave.exist(cacheID)) {
                var data = object.$blocksave.get(cacheID);
                if ('undefined' != typeof data.page_max) { block.data('page_max', data.page_max); }
                object.endEffect(block, data.content, 'replace');
                return false;
            } else {
                $.ajax({
                    type: 'POST', url: jlParamsOpt.ajaxurl, data: { action: 'bopea_block_link', data: settings },
                    success: function (data) {
                        data = $.parseJSON(JSON.stringify(data));
                        if ('undefined' != typeof data.page_max) { block.data('page_max', data.page_max); }
                        object.$blocksave.set(cacheID, data);
                        object.endEffect(block, data.content, 'replace');
                    }
                });
            }
        };
        OptScript.startEffect = function (block, action) {
            var wrapper = block.find('.jl_wrap_eb'); var jcontain = wrapper.find('.jl_contain');
            block.find('.jl-block-link').addClass('jl_disable');
            jcontain.stop();
            if (action == 'replace') {
                wrapper.css('height', wrapper.outerHeight());
                wrapper.prepend('<div class="jl-load-animation"></div>');
                jcontain.addClass('jl_overflow');
                jcontain.fadeTo('100', .3);
            } else {
                block.find('.jl-load-link').addClass('loading').animate({ opacity: 0 }, 100);
                block.find('.jl-load-animation').css({ 'display': 'block' }).delay(100).animate({ opacity: 1 }, 100);
            }
        };
        OptScript.navloadMore = function () {
            var object = this;
            $('.jl-load-link').off('click').on('click', function (e) {
                e.preventDefault(); e.stopPropagation();
                var link = $(this); var block = link.parents('.block-section'); var blockid = block.attr('id');
                if (true == object.$ajax[blockid + '_loading']) { return; }
                object.$ajax[blockid + '_loading'] = true;
                var settings = object.blockDatalist(block);
                if (settings.page_current >= settings.page_max) { return; }
                object.startEffect(block, 'append');
                object.navloadAction(block, settings);
            })
        };
        OptScript.navautoload = function () {
            var object = this; var infiniteElements = $('.jl_autoload');
            if (infiniteElements.length > 0) {
                infiniteElements.each(function () {
                    var link = $(this);
                    if (!link.hasClass('jl_hide_pagination')) {
                        var animation = link.find('.jl-load-animation'); var block = link.parents('.block-section'); var blockid = block.attr('id'); var sjloadID = 'infinite' + blockid; var settings = object.blockDatalist(block);
                        object.sjload[sjloadID] = new Waypoint({
                            element: link,
                            handler: function (direction) {
                                if ('down' == direction) {
                                    if (true == object.$ajax[blockid + '_loading']) { return; }
                                    object.$ajax[blockid + '_loading'] = true;
                                    object.startEffect(block, 'append');
                                    OptScript.navloadAction(block, settings);
                                    setTimeout(function () { object.sjload[sjloadID].destroy(); }, 10);
                                }
                            },
                            offset: '99%'
                        })
                    }
                });
            }
        };
        OptScript.navloadAction = function (block, settings) {
            settings.page_next = parseInt(settings.page_current) + 1;
            if (settings.page_next <= settings.page_max) {
                $.ajax({
                    type: 'POST', url: jlParamsOpt.ajaxurl, data: { action: 'bopea_loadnavs', data: settings },
                    success: function (data) {
                        data = $.parseJSON(JSON.stringify(data));
                        if ('undefined' != data.page_current) { block.data('page_current', data.page_current); }
                        if ('undefined' != data.notice) { data.content = data.content + data.notice; }
                        object.endEffect(block, data.content, 'append');
                    }
                });
            }
        };
        OptScript.jl_menu_cat = function () {
            var cat_haction; var cat_sub = $('.mega-category-menu .menu-item');
            cat_sub.hover(function (event) {
                event.stopPropagation();
                cat_sub = $(this);
                cat_sub.addClass('is-current-sub').siblings().removeClass('is-current-sub current-menu-item');
                var wrapper = cat_sub.parents('.mega-category-menu'); var block = wrapper.find('.block-section');
                cat_haction = setTimeout(function () { object.jl_menu_cat_load(cat_sub, block); }, 200);
            }, function () { clearTimeout(cat_haction); });
        };
        OptScript.jl_menu_cat_load = function (cat_sub, block) {
            var blockid = block.attr('id');
            if (true == object.$ajax[blockid + '_loading']) { return; }
            object.$ajax[blockid + '_loading'] = true;
            var settings = object.blockDatalist(block);
            settings.category = cat_sub.data('mega_sub_filter');
            settings.page_current = 1;
            settings.section_style = settings.section_style;
            settings.posts_per_page = settings.posts_per_page;
            block.data('category', settings.category);
            block.data('page_current', settings.page_current);
            object.startEffect(block, 'replace');
            setTimeout(function () { object.jl_menu_cat_fil(block, settings); }, 200);
        };
        OptScript.jl_menu_cat_fil = function (block, settings) {
            var jl_mcache = settings; delete jl_mcache.page_max; var cache_id = JSON.stringify(jl_mcache);
            if (object.$blocksave.exist(cache_id)) {
                var data = object.$blocksave.get(cache_id);
                if ('undefined' != data.page_max) { block.data('page_max', data.page_max); }
                object.endEffect(block, data.content, 'replace');
                return false;
            }
            $.ajax({
                type: 'POST', url: jlParamsOpt.ajaxurl, data: { action: 'bopea_menu_cat_opt', data: settings },
                success: function (data) {
                    data = $.parseJSON(data);
                    if ('undefined' != data.page_max) { block.data('page_max', data.page_max); }
                    object.$blocksave.set(cache_id, data);
                    object.endEffect(block, data.content, 'replace');
                }
            });
        };
        OptScript.jl_popup_search = function () {
            var jl_search_main = $('.jl_search_list');
            if (jl_search_main.length == 0) { return; }
            jl_search_main.each(function () {
                var jl_search_mainEl = $(this);
                // Live search ChronoNews (fetch JSON) — ne pas utiliser l’AJAX WordPress
                if (jl_search_mainEl.find('.jl-live-search').length) { return; }
                var input = jl_search_mainEl.find('.search_btn');
                var contentWrap = jl_search_mainEl.find('.jl_search_box_li');
                var jl_search_wrap = jl_search_mainEl.find('.searchform_theme');
                input.attr('autocomplete', 'off');
                var delay = (function () {
                    var timer = 0;
                    return function (callback, ms) { clearTimeout(timer); timer = setTimeout(callback, ms); };
                })();
                input.keyup(function () {
                    var param = $(this).val();
                    delay(function () {
                        if (param) {
                            jl_search_wrap.addClass('jl_search_act');
                            $.ajax({
                                type: 'POST',
                                url: jlParamsOpt.ajaxurl,
                                data: {
                                    action: 'bopea_search_view',
                                    s: param
                                },
                                success: function (data) {
                                    data = $.parseJSON(JSON.stringify(data));
                                    contentWrap.hide().empty().css('height', contentWrap.height());
                                    contentWrap.html(data);
                                    contentWrap.css('height', 'auto').fadeIn(250);
                                    $(window).trigger('load');
                                }
                            });
                        } else {
                            setTimeout(function () { jl_search_wrap.removeClass('jl_search_act'); }, 300);
                            contentWrap.fadeOut(300, function () { contentWrap.empty().css('height', 'auto'); });
                        }
                    }, 300);
                })
            });
        };
        OptScript.endEffect = function (block, content, action) {
            var object = this;
            block.delay(100).queue(function () {
                var blockid = block.attr('id'); var wrapper = block.find('.jl_wrap_eb'); var jcontain = block.find('.jl_contain');
                block.find('.filter-link').removeClass('jl_removes'); block.find('.jl-block-link').removeClass('jl_disable'); jcontain.stop();
                if ('replace' == action) {
                    wrapper.find('.jl-load-animation').remove();
                    jcontain.html(content);
                    if (jcontain.hasClass('large-jcontain')) {
                        jcontain.imagesLoaded(function () {
                            setTimeout(function () { jcontain.removeClass('jl_overflow'); wrapper.css('height', 'auto'); setTimeout(function () { jcontain.fadeTo(200, 1); }, 200); }, 100)
                        });
                    } else {
                        jcontain.removeClass('jl_overflow'); wrapper.css('height', 'auto');
                        setTimeout(function () { jcontain.fadeTo(200, 1); }, 200);
                    }
                } else {
                    content = $(content); content.addClass('jl_hide'); content.addClass('show_block'); jcontain.append(content);
                    block.find('.jl-load-animation').animate({ opacity: 0 }, 200, function () { $(this).css({ 'display': 'none' }); });
                    setTimeout(function () { content.removeClass('jl_hide'); }, 200);
                    block.find('.jl-load-link').removeClass('loading').delay(200).animate({ opacity: 1 }, 200);
                }
                if (jcontain.hasClass('jl_ma_layout')) { $(jcontain).isotope('reloadItems').isotope({ sortBy: 'original-order', transitionDuration: 0 }); }
                var initVid = GLightbox({ selector: '.jl_pop_vid' });
                object.trackPagenav(block); block.dequeue();
                var jlMainShare = $('.jl_shli .jl_sshl');
                if (jlMainShare.length > 0) {
                    jlMainShare.on('click', function () {
                        var left = (screen.width / 2) - (640 / 2),
                            top = (screen.height / 2) - (440 / 2) - 100;
                        window.open($(this).attr('href'), 'mywin', 'left=' + left + ',top=' + top + ',width=640,height=440,toolbar=0');
                        return false;
                    });
                }

                //makara                
                setTimeout(function () { object.$ajax[blockid + '_loading'] = false; object.loadpFunctions(); }, 50);
            });
        }
    };
    return OptScript;
}(cusMainScript || {}, jQuery));
(function ($) {
    var jl_wp_sl = function ($scope, $) {
        var swiperContainer = $scope.find('.jl-eb-sl').eq(0);
        if (swiperContainer.length > 0) {
            var dataOpt = swiperContainer.data('settings');
            var navid = '.jlc-navigation-' + dataOpt.uniqid,
                pagiid = '.jlc-pagination-' + dataOpt.uniqid;
            var pagination = {
                el: pagiid + ' .swiper-pagination',
                type: 'bullets',
                clickable: true,
            };
            var navigation = {
                nextEl: navid + ' .jl-swiper-button-next',
                prevEl: navid + ' .jl-swiper-button-prev',
            };
            var autoplay = {
                delay: dataOpt.autoplay_delay,
                disableOnInteraction: false,
            };
            if (dataOpt.autoplay == false) {
                autoplay = false;
            }
            if (dataOpt.pagination == false) {
                pagination = false;
            }
            if (dataOpt.navigation == false) {
                navigation = false;
            }
            var desktop = parseInt(dataOpt.slideitem['desktop']) || 5,
                tablet = parseInt(dataOpt.slideitem['tablet']) || 4,
                landscape_mobile = parseInt(dataOpt.slideitem['landscape_mobile']) || 3,
                large_mobile = parseInt(dataOpt.slideitem['large_mobile']) || 2,
                small_mobile = parseInt(dataOpt.slideitem['small_mobile']) || 1;

            const Swiper = elementorFrontend.utils.swiper;
            initSwiper();
            async function initSwiper() {
                var swiper = await new Swiper(swiperContainer, {
                    loop: dataOpt.loop,
                    autoplay: autoplay,
                    watchSlidesVisibility: true,
                    parallax: dataOpt.parallax,
                    spaceBetween: dataOpt.spacebetween,
                    centeredSlides: dataOpt.centered,
                    freeMode: dataOpt.freemode,
                    speed: dataOpt.speed,
                    pagination: pagination,
                    navigation: navigation,
                    slidesPerView: desktop,
                    effect: dataOpt.effect,
                    breakpoints: {
                        320: {
                            slidesPerView: small_mobile
                        },
                        480: {
                            slidesPerView: large_mobile
                        },
                        576: {
                            slidesPerView: landscape_mobile
                        },
                        768: {
                            slidesPerView: tablet
                        },
                        992: {
                            slidesPerView: desktop
                        },
                        1200: {
                            slidesPerView: desktop
                        }
                    },
                });
            };
        }
    };
    var jl_wp_sl_tab = function ($scope, $) {
        var swipertabs = $scope.find('.jl-eb-sl').eq(0);
        if (swipertabs.length > 0) {
            var dataOpt = swipertabs.data('settings');
            var navid = '.jlc-navigation-' + dataOpt.uniqid,
                pagiid = '.jlc-pagination-' + dataOpt.uniqid;
            var pagination = {
                el: pagiid + ' .swiper-pagination',
                type: 'bullets',
                clickable: true,
            };
            var navigation = {
                nextEl: navid + ' .jl-swiper-button-next',
                prevEl: navid + ' .jl-swiper-button-prev',
            };
            var autoplay = {
                delay: dataOpt.autoplay_delay,
                disableOnInteraction: false,
            };
            if (dataOpt.autoplay == false) {
                autoplay = false;
            }
            if (dataOpt.pagination == false) {
                pagination = false;
            }
            if (dataOpt.navigation == false) {
                navigation = false;
            }
            const Swiper = elementorFrontend.utils.swiper;
            initSwiper();
            async function initSwiper() {

                var swipertabes = $scope.find('.jl-eb-sltab').eq(0);
                if (swipertabes.length > 0) {
                    var swiper2 = await new Swiper(swipertabes, {
                        loop: false,
                        slidesPerView: 4,
                        direction: "vertical"
                    });
                }
                var swiper = await new Swiper(swipertabs, {
                    loop: dataOpt.loop,
                    autoplay: autoplay,
                    parallax: dataOpt.parallax,
                    spaceBetween: 0,
                    speed: dataOpt.speed,
                    pagination: pagination,
                    navigation: navigation,
                    slidesPerView: 1,
                    effect: dataOpt.effect,
                    thumbs: {
                        swiper: swiper2,
                    },
                });
            }
        }
    };
    var jl_wp_showcase = function ($scope, $) {
        var showcaseContainer = $scope.find('.jl-showcase-container').eq(0);
        if (showcaseContainer.length > 0) {
            showcaseContainer.each(function () {
                var $showContainer = $(this),
                    $bgsOpt = $showContainer.find('.jl-img-inner'),
                    $contentOpt = $showContainer.find('.jl-showcase-item-inner');
                var $showContent = $showContainer.find('.jl-showcase-content');
                var setActive = function () {
                    $bgsOpt.eq(0).addClass('jl-showcase-active');
                    $showContent.eq(0).addClass('jl-showcase-active');
                }
                $showContent.on('touchstart mouseenter', function (e) {
                    var $currentContent = $(this);
                    if ((!$currentContent.hasClass('jl-showcase-active'))) {
                        e.preventDefault();
                        $bgsOpt.removeClass('jl-showcase-active').eq($currentContent.index()).addClass('jl-showcase-active');
                        $showContent.removeClass('jl-showcase-active').eq($currentContent.index()).addClass('jl-showcase-active');
                    }
                }).on('touchend mouseleave', function (e) {
                    var $currentContent = $(this);
                    if ((!$currentContent.hasClass('jl-showcase-active'))) {
                        $showContent.removeClass('jl-showcase-active').eq($currentContent.index()).addClass('jl-showcase-active');
                        $bgsOpt.removeClass('jl-showcase-active').eq($currentContent.index()).addClass('jl-showcase-active');
                    }
                });
            });
        }
    };
    var jellyMenu = function ($scope, $) {
        var jlCusMenu = $scope.find('.navigation_wrapper');
        if (jlCusMenu.length > 0) {
            jlCusMenu.each(function () {
                var thisItem = $(this);
                thisItem.find('.jl-cus-mega-menu').css({
                    'width': $(window).width(),
                    'left': -thisItem.offset().left,
                });
                thisItem.addClass('mega-menu-loaded')
            })
        }

        window.addEventListener('resize', function () {
            if (jlCusMenu.length > 0) {
                jlCusMenu.each(function () {
                    var thisItem = $(this);
                    thisItem.find('.jl-cus-mega-menu').css({
                        'width': $(window).width(),
                        'left': -thisItem.offset().left,
                    });
                    thisItem.addClass('mega-menu-loaded')
                })
            }
        });
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-feature-carousel.default', jl_wp_sl);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-newsticker-text.default', jl_wp_sl);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-feature-center-slider.default', jl_wp_sl);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-feature-slider.default', jl_wp_sl);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-feature-sl-tab.default', jl_wp_sl_tab);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-feature-hover.default', jl_wp_showcase);
        elementorFrontend.hooks.addAction('frontend/element_ready/bopea-main-menu.default', jellyMenu);
    });
})(jQuery);