<script>
    
    {foreach $smarty.session.dynamic_js.js_vars as $var=>$value}
        okay.{$var} = {$value|escape};
    {/foreach}
    
    okay.max_order_amount = {$settings->max_order_amount};

    /*Сброс фильтра*/
    {if $controller == 'ProductsController' || $controller == 'BrandController' || $controller == 'CategoryController'}
        $(document).on('click', '.fn_filter_reset', function () {
            var date = new Date(0);
            document.cookie = "price_filter=; path=/; expires=" + date.toUTCString();
        });
    {/if}

    /* Mobile menu */
    $(function(){
        var $main_nav = $('.fn_mobile_menu');
        var $toggle = $('.fn_menu_switch');
        var defaultData = {
            maxWidth: false,
            navClass: 'mobile_nav',
            customToggle: $toggle,
            levelTitles: true,
            insertClose: -1,
            labelBack: '{$lang->mobile_menu_prev|escape}',
            labelClose: '{$lang->mobile_menu_close|escape}',
            closeLevels: false
        };
        $main_nav.hcOffcanvasNav(defaultData);
    });

    /* Показать все в фильтрах по свойствам и в футере категории */
    $( document ).on( 'click', '.fn_view_all', function(e) {
        $(this).closest('.fn_view_content').toggleClass('opened');
        $('.fn_view_all').not($(this)).html('{$lang->filter_view_show|escape}');
        $('.fn_view_all').not($(this)).closest('.fn_view_content').removeClass('opened');
        if ($(this).closest('.fn_view_content').hasClass('opened')) {
            $(this).html('{$lang->filter_view_hide|escape}');
        } else {
            $(this).html('{$lang->filter_view_show|escape}');
        }
        return false;
    });

    /* Предзаказ */
    okay.is_preorder = {$settings->is_preorder};

    /* Ошибка при отправке комментария в посте */
    {if $controller == 'BlogController' && $error}
        /* Переход по якорю к форме */
        $( window ).on( 'load', function() {
            location.href = location.href + '#fn_blog_comment';
            $( '#fn_blog_comment' ).trigger( 'submit' );
        } );
    {/if}

    {* Обратный звонок, отправка формы *}
    {if $call_sent}
        $( function() {
            $.fancybox.open( {
                src: '#fn_callback_sent',
                type : 'inline',
            } );
        } );
    {elseif $call_error}
        $(function() {
            $.fancybox.open({
                src: '#fn_callback',
                type : 'inline'
            });
        });
    {/if}

    {* Карточка товара, ошибка в форме *}
    {if $controller == 'ProductController' && $error}
        $( window ).on( 'load', function() {
            $( '.tabs__navigation a' ).removeClass( 'selected' );
            $( '.tab' ).hide();
            $( 'a[href="#comments"]' ).addClass( 'selected' );
             $( '#comments').show();
        } );
    {* Карточка товара, отправка комментария *}
    {elseif $controller == 'ProductController'}
        $( window ).on( 'load', function() {
            if( location.hash.search('comment') !=-1 ) {
                $( '.tabs__navigation a' ).removeClass( 'selected' );
                $( '.tab' ).hide();
                $( 'a[href="#comments"]' ).addClass( 'selected' );
                 $( '#comments').show();
            }
        } );
    {/if}

    {if $subscribe_success}
        $( function() {
            $.fancybox.open( {
                src: '#fn_subscribe_sent',
                type : 'inline',
            } );
        } );
    {elseif $subscribe_error}
        $( window ).on( 'load', function() {
            location.href = location.href + '#subscribe_error';
            $.fancybox.open( {
                src: '#subscribe_error',
                type : 'inline',
            } );
        } );
    {/if}

    var form_enter_name = "{$lang->form_enter_name|escape}";
    var form_enter_phone = "{$lang->form_enter_phone|escape}";
    var form_error_captcha = "{$lang->form_error_captcha|escape}";
    var form_enter_email = "{$lang->form_enter_email|escape}";
    var form_enter_password = "{$lang->form_enter_password|escape}";
    var form_enter_message = "{$lang->form_enter_message|escape}";

    if($(".fn_validate_product").length>0) {
        $(".fn_validate_product").validate({
            rules: {
                name: "required",
                text: "required",
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                text: form_enter_message,
                captcha_code: form_error_captcha
            }
        });
    }
    if($(".fn_validate_callback").length>0) {
        $(".fn_validate_callback").validate({
            rules: {
                name: "required",
                phone: "required",
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                phone: form_enter_phone,
                captcha_code: form_error_captcha
            }

        });
    }
    if($(".fn_validate_subscribe").length>0) {
        $(".fn_validate_subscribe").validate({
            rules: {
                subscribe_email: "required",
            },
            messages: {
                subscribe_email: form_enter_email
            }
        });
    }
    if($(".fn_validate_post").length>0) {
        $(".fn_validate_post").validate({
            rules: {
                name: "required",
                text: "required",
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                text: form_enter_message,
                captcha_code: form_error_captcha
            }
        });
    }

    if($(".fn_validate_feedback").length>0) {
        $(".fn_validate_feedback").validate({
            rules: {
                name: "required",
                email: {
                    required: true,
                    email: true
                },
                message: "required",
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                email: form_enter_email,
                message: form_enter_message,
                captcha_code: form_error_captcha
            }
        });
    }

    if($(".fn_validate_cart").length>0) {
        $(".fn_validate_cart").validate({
            rules: {
                name: "required",
                email: {
                    required: true,
                    email: true
                },
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                email: form_enter_email,
                captcha_code: form_error_captcha
            }
        });
		
		var submitted_cart = false;
        $('.fn_validate_cart').on('submit', function () {
            if ($('.fn_validate_cart').valid() === true) {
                if (submitted_cart === true) {
                    return false;
                } else {
                    submitted_cart = true;
                }
            }
        });
    }

    if($(".fn_validate_login").length>0) {
        $(".fn_validate_login").validate({
            rules: {
                email: "required",
                password: "required",
            },
            messages: {
                email: form_enter_email,
                password: form_enter_password
            }
        });
    }

    if($(".fn_validate_register").length>0) {
        $(".fn_validate_register").validate({
            rules: {
                name: "required",
                email: {
                    required: true,
                    email: true
                },
                password: "required",
                captcha_code: "required"
            },
            messages: {
                name: form_enter_name,
                email: form_enter_email,
                captcha_code: form_error_captcha,
                password: form_enter_password
            }
        });
    }

    {get_design_block block="front_scripts_after_validate"}
    
    {if $settings->sj_shares}
         if($(".fn_share").length>0) {
        {if $js_custom_socials}
        {*Расширяем функционал кастомными соц. сетями*}
        {foreach $js_custom_socials as $social=>$params}
        jsSocials.shares.{$social|escape} = {$params|json_encode};
        {/foreach}
            {/if}
                $(".fn_share").jsSocials({
                    showLabel: false,
                    showCount: false,
                    shares: {$settings->sj_shares|json_encode}
            });
        }
    {/if}

    /* Звёздный рейтинг товаров */
    if($(".product__rating").length>0) {
        $(function() {
            $('.product__rating').rater({ postHref: okay.router['ajax_product_rating'] });
        });
        $.fn.rater = function (options) {
            var opts = $.extend({}, $.fn.rater.defaults, options);
            return this.each(function () {
                var $this = $(this);
                var $on = $this.find('.rating_starOn');
                var $off = $this.find('.rating_starOff');
                opts.size = $on.height();
                if (opts.rating == undefined) opts.rating = $on.width() / opts.size;

                $off.mousemove(function (e) {
                    var left = e.clientX - $off.offset().left;
                    var width = $off.width() - ($off.width() - left);
                    width = Math.ceil(width / (opts.size / opts.step)) * opts.size / opts.step;
                    $on.width(width);
                }).hover(function (e) { $on.addClass('rating_starHover'); }, function (e) {
                    $on.removeClass('rating_starHover'); $on.width(opts.rating * opts.size);
                }).click(function (e) {
                    var r = Math.round($on.width() / $off.width() * (opts.units * opts.step)) / opts.step;
                    $off.unbind('click').unbind('mousemove').unbind('mouseenter').unbind('mouseleave');
                    $off.css('cursor', 'default'); $on.css('cursor', 'default');
                    opts.id = $this.attr('id');
                    $.fn.rater.rate($this, opts, r);
                }).css('cursor', 'pointer'); $on.css('cursor', 'pointer');
            });
        };

        $.fn.rater.defaults = {
            postHref: location.href,
            units: 5,
            step: 1
        };

        $.fn.rater.rate = function ($this, opts, rating) {
            var $on = $this.find('.rating_starOn');
            var $off = $this.find('.rating_starOff');
            $off.fadeTo(600, 0.4, function () {
                $.ajax({
                    url: opts.postHref,
                    type: "POST",
                    data: 'id=' + opts.id + '&rating=' + rating,
                    complete: function (req) {
                        if (req.status == 200) { /* success */
                            opts.rating = parseFloat(req.responseText);

                            if (opts.rating > 0) {
                                opts.rating = parseFloat(req.responseText);
                                $off.fadeTo(200, 0.1, function () {
                                    $on.removeClass('rating_starHover').width(opts.rating * opts.size);
                                    var $count = $this.find('.rating_count');
                                    $count.text(parseInt($count.text()) + 1);
                                    $this.find('.rating_value').text(opts.rating.toFixed(1));
                                    $off.fadeTo(200, 1);
                                });
                            }
                            else
                            if (opts.rating == -1) {
                                $off.fadeTo(200, 0.6, function () {
                                    $this.find('.rating_text').text('{$lang->rating_error|escape}');
                                });
                            }
                            else {
                                $off.fadeTo(200, 0.6, function () {
                                    $this.find('.rating_text').text('{$lang->rating_voted|escape}');
                                });
                            }
                        } else { /* failure */
                            alert(req.responseText);
                            $on.removeClass('rating_starHover').width(opts.rating * opts.size);
                            $this.rater(opts);
                            $off.fadeTo(2200, 1);
                        }
                    }
                });
            });
        };
    }

</script>
