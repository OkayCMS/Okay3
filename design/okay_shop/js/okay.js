/* Начальное кол-во для смены в карточке и корзине */
okay.amount = 1;

/* Аяксовая корзина */
$(document).on('submit', '.fn_variants', function(e) {
    e.preventDefault();
    var variant,
        amount;
    /* Вариант */
    if($(this).find('input[name=variant]:checked').length > 0 ) {
        variant = $(this).find('input[name=variant]:checked').val();
    } else if($(this ).find('input[name=variant]').length > 0 ) {
        variant = $(this).find('input[name=variant]').val();
    } else if($(this).find('select[name=variant]').length > 0 ) {
        variant = $(this).find('select[name=variant]').val();
    }
    /* Кол-во */
    if($(this).find('input[name=amount]').length>0) {
        amount = $(this).find('input[name=amount]').val();
    } else {
        amount = 1;
    }
    /* ajax запрос */
    $.ajax( {
        url: okay.router['cart_ajax'],
        data: {
            action: 'add_citem',
            variant_id: variant,
            amount: amount
        },
        dataType: 'json',
        success: function(data) {
            console.log(data.cart_informer);
            $( '#cart_informer' ).html( data.cart_informer );
            
        }
    } );
    /* Улеталка */
    transfer( $('#cart_informer'), $(this) );
});

/* Смена варианта в превью товара и в карточке */
$(document).on('change', '.fn_variant', function() {
    var selected = $( this ).children( ':selected' ),
        parent = selected.closest( '.fn_product' ),
        price = parent.find( '.fn_price' ),
        cprice = parent.find( '.fn_old_price' ),
        sku = parent.find( '.fn_sku' ),
        stock = parseInt( selected.data( 'stock' ) ),
        amount = parent.find( 'input[name="amount"]' ),
        camoun = parseInt( amount.val()),
        units = selected.data('units');
    price.html( selected.data( 'price' ) );
    amount.data('max', stock);
    /* Количество товаров */
    if ( stock < camoun ) {
        amount.val( stock );
    } else if ( okay.amount > camoun ) {
        amount.val( okay.amount );
    }
    else if(isNaN(camoun)){
        amount.val( okay.amount );
    }
    /* Цены */
    if( selected.data( 'cprice' ) ) {
        cprice.html( selected.data( 'cprice' ) );
        cprice.parent().removeClass( 'hidden-xs-up' );
    } else {
        cprice.parent().addClass( 'hidden-xs-up' );
    }
    if( selected.data( 'discount' ) ) {
        parent.find('.fn_discount_label').html(selected.data( 'discount' )).removeClass( 'hidden-xs-up' );
    } else {
        parent.find('.fn_discount_label').addClass( 'hidden-xs-up' );
    }
    /* Артикул */
    if( typeof(selected.data( 'sku' )) != 'undefined' ) {
        sku.text( selected.data( 'sku' ) );
        sku.parent().removeClass( 'hidden-xs-up' );
    } else {
        sku.text( '' );
        sku.parent().addClass( 'hidden-xs-up' );
    }
    /* Наличие на складе */
    if (stock == 0) {
        parent.find('.fn_not_stock').removeClass('hidden-xs-up');
        parent.find('.fn_in_stock').addClass('hidden-xs-up');
    } else {
        parent.find('.fn_in_stock').removeClass('hidden-xs-up');
        parent.find('.fn_not_stock').addClass('hidden-xs-up');
    }
    /* Предзаказ */
    if (stock == 0 && okay.is_preorder) {
        parent.find('.fn_is_preorder').removeClass('hidden-xs-up');
        parent.find('.fn_is_stock, .fn_not_preorder').addClass('hidden-xs-up');
    } else if (stock == 0 && !okay.is_preorder) {
        parent.find('.fn_not_preorder').removeClass('hidden-xs-up');
        parent.find('.fn_is_stock, .fn_is_preorder').addClass('hidden-xs-up');
    } else {
        parent.find('.fn_is_stock').removeClass('hidden-xs-up');
        parent.find('.fn_is_preorder, .fn_not_preorder').addClass('hidden-xs-up');
    }

    if( typeof(units) != 'undefined' ) {
        parent.find('.fn_units').text(', ' + units);
    } else {
        parent.find('.fn_units').text('');
    }
});

/* Количество товара в карточке и корзине */
$( document ).on( 'click', '.fn_product_amount span', function() {
    var input = $( this ).parent().find( 'input' ),
        action;
    if ( $( this ).hasClass( 'fn_plus' ) ) {
        action = 'plus';
    } else if ( $( this ).hasClass( 'fn_minus' ) ) {
        action = 'minus';
    }
    amount_change( input, action );
} );

/* Функция добавления / удаления в папку сравнения */
$(document).on('click', '.fn_comparison', function(e){
    e.preventDefault();
    var button = $( this ),
        action = $( this ).hasClass( 'selected' ) ? 'delete' : 'add',
        product = parseInt( $( this ).data( 'id' ) );
    /* ajax запрос */
    $.ajax( {
        url: okay.router['comparison_ajax'],
        data: { product: product, action: action },
        dataType: 'json',
        success: function(data) {
            $( '#comparison' ).html( data );
            /* Смена класса кнопки */
            if( action == 'add' ) {
                button.addClass( 'selected' );
            } else if( action == 'delete' ) {
                button.removeClass( 'selected' );
            }
            /* Смена тайтла */
            if( button.attr( 'title' ) ) {
                var text = button.data( 'result-text' ),
                    title = button.attr( 'title' );
                button.data( 'result-text', title );
                button.attr( 'title', text );
            }
            /* Если находимся на странице сравнения - перезагрузить */
            if( $( '.fn_comparison_products' ).length ) {
                window.location = window.location;
            }
        }
    } );
    /* Улеталка */
    if( !button.hasClass( 'selected' ) ) {
        transfer( $( '#comparison' ), $( this ) );
    }
});

/* Функция добавления / удаления в папку избранного */
$(document).on('click', '.fn_wishlist', function(e){
    e.preventDefault();
    var button = $( this ),
        action = $( this ).hasClass( 'selected' ) ? 'delete' : '';
    /* ajax запрос */
    $.ajax( {
        url: okay.router['wishlist_ajax'],
        data: { id: $( this ).data( 'id' ), action: action },
        dataType: 'json',
        success: function(data) {
            $( '#wishlist' ).html( data );
            /* Смена класса кнопки */
            if (action == '') {
                button.addClass( 'selected' );
            } else {
                button.removeClass( 'selected' );
            }
            /* Смена тайтла */
            if( button.attr( 'title' ) ) {
                var text = button.data( 'result-text' ),
                    title = button.attr( 'title' );
                button.data( 'result-text', title );
                button.attr( 'title', text );
            }
            /* Если находимся на странице сравнения - перезагрузить */
            if( $( '.fn_wishlist_page' ).length ) {
                window.location = window.location;
            }
        }
    } );
    /* Улеталка */
    if( !button.hasClass( 'selected' ) ) {
        transfer( $( '#wishlist' ), $( this ) );
    }
});

/* Отправка купона по нажатию на enter */
$( document ).on( 'keypress', '.fn_coupon', function(e) {
    if( e.keyCode == 13 ) {
        e.preventDefault();
        ajax_coupon();
    }
} );

/* Отправка купона по нажатию на кнопку */
$( document ).on( 'click', '.fn_sub_coupon', function(e) {
    ajax_coupon();
} );

function price_slider_init() {
console.log(window.location.href.replace( /\/page-(\d{1,5})/, '' ))
    var slider_all = $( '#fn_slider_min, #fn_slider_max' ),
        slider_min = $( '#fn_slider_min' ),
        slider_max = $( '#fn_slider_max' ),
        current_min = slider_min.val(),
        current_max = slider_max.val(),
        range_min = slider_min.data( 'price' ),
        range_max = slider_max.data( 'price' ),
        link = window.location.href.replace( /\/page-(\d{1,5})/, '' ),
        ajax_slider = function() {
            $.ajax( {
                url: link,
                data: {
                    ajax: 1,
                    'p[min]': slider_min.val(),
                    'p[max]': slider_max.val()
                },
                dataType: 'json',
                success: function(data) {
                    $('#fn_products_content').html( data.products_content );
                    $('.fn_pagination').html( data.products_pagination );
                    $('.fn_products_sort').html(data.products_sort);
                    $('.fn_features').html(data.features);
                    $('.fn_selected_features').html(data.selected_features);
                    // Выпадающие блоки
                    $('.fn_switch').click(function(e){
                        e.preventDefault();

                        $(this).next().slideToggle(300);

                        if ($(this).hasClass('active')) {
                            $(this).removeClass('active');
                        }
                        else {
                            $(this).addClass('active');
                        }
                    });
                    $(".lazy").each(function(){
                        var myLazyLoad = new LazyLoad({
                            elements_selector: ".lazy"
                        });
                    });
                    $(".fn_select2").each(function(){
                        $(this).select2({
                            minimumResultsForSearch: 20,
                            dropdownParent: $(this).next('.dropDownSelect2')
                        });
                    });

                    price_slider_init();

                    $('.fn_ajax_wait').remove();
                }
            } );
        };
    link = link.replace(/\/sort-([a-zA-Z_]+)/, '');

    $( '#fn_slider_price' ).slider( {
        range: true,
        min: range_min,
        max: range_max,
        values: [current_min, current_max],
        slide: function(event, ui) {
            slider_min.val( ui.values[0] );
            slider_max.val( ui.values[1] );
        },
        stop: function(event, ui) {
            slider_min.val( ui.values[0] );
            slider_max.val( ui.values[1] );
            $('.fn_categories').append('<div class="fn_ajax_wait"></div>');
            ajax_slider();
        }
    } );

    slider_all.on( 'change', function() {
        $( "#fn_slider_price" ).slider( 'option', 'values', [slider_min.val(), slider_max.val()] );
        ajax_slider();
    } );
}


/* Document ready */
$(function(){

    update_delivery_module_data();
    
    /* Мега меню */
        $('.fn_category_scroll').each(function() {
            if ($(this).children('li').length > 11) {
                $(this).addClass('scroll');
            }
        });
        $('.fn_category_scroll.scroll').append('<li class="hover_scroll hover_scroll_up"></li>');
        $('.fn_category_scroll.scroll').append('<li class="hover_scroll hover_scroll_down"></li>');
        $('.hover_scroll_up').hide();
        var scrolling = false;
        $(".hover_scroll_up").bind("mouseover", function(event) {
            scrolling = true;
            scrollContent("up", this);
        }).bind("mouseout", function(event) {
            scrolling = false;
        });
        $(".hover_scroll_down").bind("mouseover", function(event) {
            scrolling = true;
            scrollContent("down", this);
        }).bind("mouseout", function(event) {
            scrolling = false;
        });

        $('.scroll').on("scroll", function() {
            var maxScrollHeight = this.scrollHeight - this.clientHeight;

            if ($(this).scrollTop() == 0) {
                $(this).find('.hover_scroll_up').hide();
            } else if (Math.round($(this).scrollTop()) == Math.round(maxScrollHeight)) {
                $(this).find('.hover_scroll_down').hide();
            } else  {
                $(this).find('.hover_scroll').show();
            }
        });
        function scrollContent(direction, test) {
            var test2 = $(test).parent();
            var amount = (direction === "up" ? "-=5px" : "+=5px");
            $(test2).animate({
                scrollTop: amount
            }, 1, function() {
                if (scrolling) {
                    scrollContent(direction, test);
                }
            });
        }

    $('.scrollbar-inner').scrollbar();

    /* Carousel products */
    if( $('.fn_products_slide').length ) {
        $('.fn_products_slide').owlCarousel({
            loop:false,
            margin:0,
            lazyLoad:true,
            mouseDrag:false,
            nav:false,
            dotsEach:true,
            responsive:{
                320:{items:1},
                360:{items:2},
                576:{items:3},
                768:{
                    mouseDrag:true,
                    items:3
                },
                992:{items:4},
                1200:{items:5}
            }
        });
    }

    /* Aside products */
    if( $('.fn_aside_products_slide').length ) {
        $('.fn_aside_products_slide').owlCarousel({
            loop:false,
            margin:0,
            items:1,
            lazyLoad:true,
            mouseDrag:false,
            nav:true,
            dotsEach:false,
        });
    }


    /* Mobile search */
    $(document).on("click", ".fn_search_toggle", function() {
        $(".fn_search_mob").slideToggle(300);
        return false;
    });

    /* Lazy load */
    if( $('.lazy').length ) {
        var myLazyLoad = new LazyLoad({
            elements_selector: ".lazy",
            load_delay: 100
        });
    }

	/* Cart sticky */
    if( $('.fn_cart_sticky').length ) {
        var sticky = new Sticky('.fn_cart_sticky');
    }

    /* Header sticky */
    if( $('.fn_header__sticky').length ) {
        var sticky = new Sticky('.fn_header__sticky');
    }

    /* Select2 */
    if( $('.fn_select2').length ) {
        $(".fn_select2").each(function(){
            $(this).select2({
                minimumResultsForSearch: 20,
                dropdownParent: $(this).next('.dropDownSelect2')
            });
        });
    }

    /* Hiding blocks with great text */
    if( $('.fn_readmore').length ) {
        $('.fn_readmore').readmore({
            collapsedHeight: 215,
            lessLink: '<a href="#"><span>-</span></a>',
            moreLink: '<a href="#"><span>+</span></a>',
            afterToggle: function(trigger, element, expanded) {
                if(! expanded) { // The "Close" link was clicked
                  $('html, body').animate( { scrollTop: element.offset().top - 100 }, {duration: 300 } );
                }
              }
        });
    }

    /* Anchor to reviews */
    if( $('.fn_anchor_comments').length ) {
        $('.fn_anchor_comments').click(function(){
            $("#fn_tab_comments").trigger("click");
            var target = "[id='"+$(this).attr("href").substr(1)+"']",
                destination = $(target).offset().top -60;
            $('body, html').animate( { scrollTop: destination }, 1000 );
            return false;
        });
    }

    /* Callback */
    $('.fn_callback').fancybox();

    // Drop down blocks
    $('.fn_switch').click(function(e){
        e.preventDefault();

        $(this).next().slideToggle(300);

        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        }
        else {
            $(this).addClass('active');
        }
    });

    /* Drop down categories */
    $('.fn_catalog_switch').click(function(e){
        e.preventDefault();

        $('.fn_catalog_menu').slideToggle(300);

        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        }
        else {
            $(this).addClass('active');
        }
    });

    /* Mobile filters */
    $('.fn_switch_mobile_filter').click(function(){

        if ($('.fn_mobile_toogle').hasClass('opened')) {
            $('.fn_mobile_toogle').removeClass('opened');
        }
        else {
            $('.fn_mobile_toogle').addClass('opened');
        }
    });

    //Фильтры мобильные, каталог мобильные
    $('.fn_switch_parent').click(function(){
        $(this).parent().next().slideToggle(500);

        if ($(this).hasClass('down')) {
            $(this).removeClass('down');
        }
        else {
            $(this).addClass('down');
        }
    });
    $('.blog_catalog .selected').parents('.parent').addClass('opened').find('> .switch').addClass('active');


    /* Tabs */
    if( $('.tabs').length ) {
        var nav = $('.tabs').find('.tabs__navigation');
        var tabs = $('.tabs').find('.tabs__content');

        if(nav.children('.selected').length > 0) {
            $(nav.children('.selected').attr("href")).show();
        } else {
            nav.children().first().addClass('selected');
            tabs.children().first().show();
        }

        $('.tabs__navigation a').click(function(e){
            e.preventDefault();
            if($(this).hasClass('selected')){
                return true;
            }
            tabs.children().hide();
            nav.children().removeClass('selected');
            $(this).addClass('selected');
            $($(this).attr("href")).fadeIn(200);
        });
    }

    /* Accordion */
    if( $('.fn_accordion').length ) {
        $(".fn_accordion").on('click', '.accordion__title', function() {
            var outerBox = $(this).parents('.fn_accordion');
            var target = $(this).parents('.accordion__item');

            if($(this).hasClass('active')!==true){
                $(outerBox).find('.accordion__item .accordion__title').removeClass('active');
            }

            if ($(this).next('.accordion__content').is(':visible')){
                return false;
            }else{
                $(this).addClass('active');
                $(outerBox).children('.accordion__item').removeClass('visible');
                $(outerBox).find('.accordion__item').children('.accordion__content').slideUp(300);
                target.addClass('visible');
                $(this).next('.accordion__content').slideDown(300);
            }
        });
    }

    /* To top button */
    $(window).scroll(function () {
        var scroll_height = $(window).height();

        if ($(this).scrollTop() >= scroll_height) {
            $('.fn_to_top').fadeIn();
        } else {
            $('.fn_to_top').fadeOut();
        }
    });

    $('.fn_to_top').click(function(){
        $("html, body").animate({scrollTop: 0}, 500);
    });

    /* Checking fields for placeholders */
    $('.form__placeholder--focus').on('blur', function() {
        if( $(this).val().trim().length > 0 ) {
            $(this).parent().addClass('filled');
        } else {
            $(this).parent().removeClass('filled');
        }
    });

    $('.form__placeholder--focus').each(function() {
        if( $(this).val().trim().length > 0 ) {
            $(this).parent().addClass('filled');
        }
    });

    /* Gallery images for product */
    if( $('.xzoom4').length ) {
        var xzoom = $('.xzoom4').xzoom({
            zoomWidth: 600,
            adaptive: 'true',
            position: "inside",
            title: true,
            tint: '#fff',
            Xoffset: 15
        });

        $('.xzoom-gallery4').each(function(){
            xzoom.xappend($(this));
        });

        $(".fn_xzoom-fancy:first").bind('click', function(event) {
            var xzoom = $(this).data('xzoom');
            xzoom.closezoom();
            var i, images = new Array();
            var gallery = xzoom.gallery().ogallery;
            var index = xzoom.gallery().index;
            for (i in gallery) {
                images[i] = {src: gallery[i]};
            }
            $.fancybox.open(images, {loop: false}, index);
            event.preventDefault();
        });

        //Integration with hammer.js
        var isTouchSupported = 'ontouchstart' in window;
        if (isTouchSupported) {
            xzoom.eventunbind();
            var mc1 = new Hammer($('.xzoom4')[0]);
            mc1.on("tap", function(event) {
                event.pageX = event.srcEvent.pageX;
                event.pageY = event.srcEvent.pageY;
                xzoom.eventclick = function(element) {
                    element.css('touch-action', 'pan-x');
                }
                xzoom.eventmove = function(element) {
                    var mc2 = new Hammer(element[0]);
                    mc2.get('pan').set({
                        direction: Hammer.DIRECTION_ALL,
                    });
                    mc2.on('pan', function(event) {
                        event.pageX = event.srcEvent.pageX;
                        event.pageY = event.srcEvent.pageY;
                        xzoom.movezoom(event);
                        // event.srcEvent.preventDefault();
                    });
                }
                xzoom.eventleave = function(element) {
                    var mc3 = new Hammer(element[0]);
                    mc3.on('tap', function(event) {
                        xzoom.closezoom();
                    });
                }
                xzoom.openzoom(event);
            });
        }
    }

    $.fancybox.defaults.hash = false;

    /* Аяксовый фильтр по цене */
    if( $( '#fn_slider_price' ).length ) {

        price_slider_init();
        
        // Если после фильтрации у нас осталось товаров на несколько страниц, то постраничную навигацию мы тоже проведем с помощью ajax чтоб не сбить фильтр по цене
        $( document ).on( 'click', 'a.fn_sort_pagination_link', function(e) {
            e.preventDefault();
            var link = $(this).data('href') ? $(this).data('href') : $(this).attr('href');
            
            if ($(this).closest('.fn_ajax_buttons').hasClass('fn_is_ajax')) {

                $('.fn_categories').append('<div class="fn_ajax_wait"></div>');
                var send_min = $("#fn_slider_min").val();
                send_max = $("#fn_slider_max").val();
                $.ajax({
                    url: link,
                    data: {ajax: 1, 'p[min]': send_min, 'p[max]': send_max},
                    dataType: 'json',
                    success: function (data) {
                        $('#fn_products_content').html(data.products_content);
                        $('.fn_pagination').html(data.products_pagination);
                        $('.fn_products_sort').html(data.products_sort);
                        $('.fn_features').html(data.features);
                        $('.fn_selected_features').html(data.selected_features);
                        $(".fn_select2").each(function(){
                            $(this).select2({
                                minimumResultsForSearch: 20,
                                dropdownParent: $(this).next('.dropDownSelect2')
                            });
                        });
                        $(".lazy").each(function(){
                            var myLazyLoad = new LazyLoad({
                                elements_selector: ".lazy"
                            });
                        });
                        price_slider_init();

                        $('.fn_ajax_wait').remove();
                    }
                });
            } else {
                document.location.href = link;
            }
        } );
    }


    /* Автозаполнитель поиска */
    $( ".fn_search" ).devbridgeAutocomplete( {
        serviceUrl: okay.router['ajax_search'],
        minChars: 1,
        appendTo: "#fn_search",
        maxHeight: 320,
        noCache: true,
		onSearchStart: function(params) {
            ut_tracker.start('search_products');
        },
        onSearchComplete: function(params) {
            ut_tracker.end('search_products');
        },
        onSelect: function(suggestion) {
            $( "#fn_search" ).submit();
        },
        transformResult: function(result, query) {
            var data = JSON.parse(result);
            $(".fn_search").devbridgeAutocomplete('setOptions', {triggerSelectOnValidInput: data.suggestions.length == 1});
            return data;
        },
        formatResult: function(suggestion, currentValue) {
            var reEscape = new RegExp( '(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join( '|\\' ) + ')', 'g' );
            var pattern = '(' + currentValue.replace( reEscape, '\\$1' ) + ')';
            return "<div>" + (suggestion.data.image ? "<img align='middle' src='" + suggestion.data.image + "'> " : '') + "</div>" + "<a href=" + suggestion.data.url + '>' + suggestion.value.replace( new RegExp( pattern, 'gi' ), '<strong>$1<\/strong>' ) + '<\/a>' + "<span>" + suggestion.price + " " + suggestion.currency + "</span>";
        }
    } );

    /* Слайдер в сравнении */
    if( $( '.fn_comparison_products' ).length ) {
        /* Carousel products */
        $('.fn_comparison_products').owlCarousel({
            loop:false,
            margin:0,
            lazyLoad:true,
            nav:true,
            dots:false,
            responsive:{
                320:{items:1},
                360:{items:1},
                576:{items:2},
                768:{items:2},
                992:{items:2},
                1200:{items:3}
            }
        });

        resize_comparison();

        /* Показать / скрыть одинаковые характеристики в сравнении */
        $( document ).on( 'click', '.fn_show a', function(e) {
            e.preventDefault();
            $( '.fn_show a.active' ).removeClass( 'active' );
            $( this ).addClass( 'active' );
            if( $( this ).hasClass( 'unique' ) ) {
                $( '.cell.not_unique' ).hide();
            } else {
                $( '.cell.not_unique' ).show();
            }
        } );
    };


    /* Переключатель способа оплаты */
    $( document ).on( 'click', '[name="payment_method_id"]', function() {
        $( '[name="payment_method_id"]' ).parent().removeClass( 'active' );
        $( this ).parent().addClass( 'active' );
    } );
});

/* Обновление блоков: cart_informer, cart_purchases, cart_deliveries */
function ajax_set_result(data) {
    $( '#cart_informer' ).html( data.cart_informer );
    $( '#fn_purchases' ).html( data.cart_purchases );
    
    if (data.cart_coupon) {
        $( '#fn_cart_coupon' ).html(data.cart_coupon);
    }
    if (data.deliveries_data) {
        for (let i in data.deliveries_data) {
            let delivery_data = data.deliveries_data[i];
            let delivery_block = $('#deliveries_' + delivery_data.id).closest('.fn_delivery_item');
            delivery_block.find('input[name="delivery_id"]').data('total_price', delivery_data.total_price_with_delivery)
                .data('delivery_price', delivery_data.price)
                .data('is_free_delivery', delivery_data.is_free_delivery);
            delivery_block.find('.fn_delivery_price').html(delivery_data.delivery_price_text);
            var sticky = new Sticky('.fn_cart_sticky');
        }
    }
    $('input[name="delivery_id"]:checked').trigger('change');
}

/* Аяксовое изменение кол-ва товаров в корзине */
function ajax_change_amount(object, variant_id) {
    let amount = $( object ).val();
    /* ajax запрос */
    $.ajax( {
        url: okay.router['cart_ajax'],
        data: {
            action: 'update_citem',
            variant_id: variant_id,
            amount: amount
        },
        dataType: 'json',
        success: function(data) {
            if( data.result == 1 ) {
                ajax_set_result( data );
            } else {
                $( '#cart_informer' ).html( data.cart_informer );
                $(".fn_ajax_content").html( data.content );
            }
        }
    } );
}

/* Функция изменения количества товаров */
function amount_change(input, action) {
    var max_val,
        curr_val = parseFloat( input.val() ),
        step = 1,
        id = input.data('id');
    if(isNaN(curr_val)){
        curr_val = okay.amount;
    }

    /* Если включен предзаказ макс. кол-во товаров ставим максимально количество товаров в заказе */
    if ( input.parent().hasClass('fn_is_preorder')) {
        max_val = okay.max_order_amount;
    } else {
        max_val = parseFloat( input.data( 'max' ) );
    }
    /* Изменение кол-ва товара */
    if( action == 'plus' ) {
        input.val( Math.min( max_val, Math.max( 1, curr_val + step ) ) );
        input.trigger('change');
    } else if( action == 'minus' ) {
        input.val( Math.min( max_val, Math.max( 1, (curr_val - step) ) ) );
        input.trigger('change');
    } else if( action == 'keyup' ) {
        input.val( Math.min( max_val, Math.max( 1, curr_val ) ) );
        input.trigger('change');
    }
    okay.amount = parseInt( input.val() );
    /* в корзине */
    if( $('div').is('#fn_purchases') && ( (max_val != curr_val && action == 'plus' ) || ( curr_val != 1 && action == 'minus' ) ) ) {
        ajax_change_amount( input, id );
    }
}

/* Функция анимации добавления товара в корзину */
function transfer(informer, thisEl) {
    var o1 = thisEl.offset(),
        o2 = informer.offset(),
        dx = o1.left - o2.left,
        dy = o1.top - o2.top,
        distance = Math.sqrt(dx * dx + dy * dy);

    thisEl.closest( '.fn_transfer' ).find( '.fn_img' ).effect( "transfer", {
        to: informer,
        className: "transfer_class"
    }, 1500, distance );

    var container = $( '.transfer_class' );
    container.html( thisEl.closest( '.fn_transfer' ).find( '.fn_img' ).parent().html() );
    container.find( '*' ).css( 'display', 'none' );
    container.find( '.fn_img' ).css( {
        'display': 'block',
        'height': '100%',
        'z-index': '200',
        'position': 'relative'
    } );
}

/* Аяксовый купон */
function ajax_coupon() {
    let coupon_code = $('input[name="coupon_code"]').val();
    /* ajax запрос */
    $.ajax( {
        url: okay.router['cart_ajax'],
        data: {
            coupon_code: coupon_code,
            action: 'coupon_apply'
        },
        dataType: 'json',
        success: function(data) {
            if( data.result == 1 ) {
                ajax_set_result( data );
            } else {
                $( '#cart_informer' ).html( data.cart_informer );
                $(".fn_ajax_content").html( data.content );
            }
        }
    } );
}

function update_delivery_module_data() {

    let delivery_input = $('input[name="delivery_id"]:checked');
    let delivery_id    = delivery_input.val();

    const hide_price                     = delivery_input.data('hide_front_delivery_price');
    const $fn_total_delivery_price_block = $('#fn_total_delivery_price_block');

    if (hide_price) {
        $fn_total_delivery_price_block.hide();
    } else {
        $fn_total_delivery_price_block.show();
    }

    $( '.fn_delivery_module_html input, .fn_delivery_module_html select, .fn_delivery_module_html textarea' ).attr('disabled', true);

    let delivery_block = $( '#deliveries_' + delivery_id ).closest( '.delivery__item' );
    $( delivery_block ).find('.fn_delivery_module_html input').attr('disabled', false);
    $( delivery_block ).find('.fn_delivery_module_html select').attr('disabled', false);
    $( delivery_block ).find('.fn_delivery_module_html textarea').attr('disabled', false);
}

/* Аяксовое удаление товаров в корзине */
function ajax_remove(variant_id) {
    /* ajax запрос */
    $.ajax( {
        url: okay.router['cart_ajax'],
        data: {
            action: 'remove_citem',
            variant_id: variant_id
        },
        dataType: 'json',
        success: function(data) {
            if( data.result == 1 ) {
                ajax_set_result( data );
            } else {
                $( '#cart_informer' ).html( data.cart_informer );
                $(".fn_ajax_content").html( data.content );
            }
        }
    } );
}

/* Формирование ровных строчек для характеристик */
function resize_comparison() {
    var minHeightHead = 0;
    $('.fn_resize' ).each(function(){
        if( $(this ).height() > minHeightHead ) {
            minHeightHead = $(this ).height();
        }
    });
    $('.fn_resize' ).height(minHeightHead);
    if ($('[data-use]').length) {
        $('[data-use]').each(function () {
            var use = '.' + $(this).data('use');
            var minHeight = $(this).height();
            if ($(use).length) {
                $(use).each(function () {
                    if ($(this).height() >= minHeight) {
                        minHeight = $(this).height();
                    }
                });
                $(use).height(minHeight);
            }
        });
    }
}

/* В сравнении выравниваем строки */
if( $( '.fn_comparison_products' ).length ) {
    $(window).on('load', resize_comparison);
}

/* Адаптивное видео и таблицы tiny MCE */
if( $( '.block__description' ).length ) {
    $('.block__description').find('iframe').wrap('<p class="video"></p>');
    $('.block__description').find('table').wrap('<div class="table_responsive"></div>');

    $(document).ready(function() {
        $('img.fn_img_zoom').each(function(i){
            if(this.parentNode.tagName!='A'){
                $(this).wrap('<a class="fn_image_zoom" data-fancybox="fn_image_zoom'+i+'" rel="fancybox_'+i+'" href="'+this.src+'" target="_blank"/>');
            }
        });
        $('img.fn_img_gallery').each(function(i){
            if(this.parentNode.tagName!='A'){
                $(this).wrap('<a class="fn_image_gallery" data-fancybox="fn_image_gallery" rel="image_gallery" href="'+this.src+'" target="_blank"/>');
            }
        });
        $('img.fn_img_slider').each(function(i){
            if(this.parentNode.tagName!='A'){
                $(this).wrap('<a class="fn_image_slider" data-fancybox="fn_image_slider" rel="image_slider" href="'+this.src+'" target="_blank"/>');
            }
        });

        $("a.fn_image_gallery").wrapAll("<div class='fn_slider_gallery owl-carousel'></div>");

        $("a.fn_image_slider").wrapAll("<div class='fn_slider_mce owl-carousel'></div>");

        if( $('.fn_slider_gallery').length ) {
            $('.fn_slider_gallery').owlCarousel({
                loop:false,
                margin:0,
                lazyLoad:true,
                mouseDrag:false,
                nav:true,
                items:1,
                dotsEach:true,
                responsive:{
                    768:{
                        mouseDrag:true,
                        items:2
                    },
                    992:{items:3}
                }
            });
        }
        if( $('.fn_slider_mce').length ) {
            $('.fn_slider_mce').owlCarousel({
                loop:true,
                margin:0,
                lazyLoad:true,
                mouseDrag:true,
                nav:true,
                items:1,
                dotsEach:true
            });
        }
    });

}
