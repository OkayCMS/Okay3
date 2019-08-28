<!DOCTYPE html>
<html{if $language->href_lang} lang="{$language->href_lang|escape}"{/if} prefix="og: http://ogp.me/ns#">
<head>
    {* Full base address *}
    <base href="{$base}/">

    {* Meta data *}
    {include "head.tpl"}
    
	{* Favicon *}
    <link href="{$rootUrl}/{$config->design_images}{$settings->site_favicon}?v={$settings->site_favicon_version}" type="image/x-icon" rel="icon">
    <link href="{$rootUrl}/{$config->design_images}{$settings->site_favicon}?v={$settings->site_favicon_version}" type="image/x-icon" rel="shortcut icon">

    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap&subset=cyrillic" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/3.8.95/css/materialdesignicons.min.css" rel="stylesheet">
	
    {* JQuery *}
    <script>ut_tracker.start('parsing:page');</script>

    <script>ut_tracker.start('parsing:head:scripts');</script>
    {$ok_head}
    {if $controller == "Products"}
        {js file='jquery-migrate-3.0.1.min.js' defer=true}
    {/if}
    <script>ut_tracker.end('parsing:head:scripts');</script>
    
	{if !empty($counters['head'])}
        <script>ut_tracker.start('parsing:head:counters');</script>
        {foreach $counters['head'] as $counter}
            {$counter->code}
        {/foreach}
        <script>ut_tracker.end('parsing:head:counters');</script>
    {/if}
    
</head>

<body class="d-flex flex-column {if $controller == 'MainController'}main_page{else}other_page{/if}">

    {if !empty($counters['body_top'])}
        <script>ut_tracker.start('parsing:body_top:counters');</script>
        {foreach $counters['body_top'] as $counter}
        {$counter->code}
        {/foreach}
        <script>ut_tracker.end('parsing:body_top:counters');</script>
    {/if}

    <header class="header">
        <div class="header__top hidden-md-down">
            <div class="container">
                <div class="f_row flex-nowrap justify-content-between">
                    {* Account *}
                    <div id="account" class="d-flex align-items-center f_col">
                        {include file="user_informer.tpl"}
                    </div>
                    <div class="d-flex align-items-center f_col justify-content-end">
                        {* Callback *}
                        <a class="fn_callback callback" href="#fn_callback" data-language="index_back_call">
                            <i class="mdi mdi-phone-return"></i>
                            <span>{$lang->index_back_call}</span>
                        </a>
                        {* Language & Currency *}
                        <div class="switcher d-flex align-items-center">
                            {include file="switcher.tpl"}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header__center hidden-md-down">
            <div class="container">
                <div class="f_row no_gutters flex-nowrap align-items-center justify-content-between">
                    {* Menu button*}
                    <div class="fn_menu_switch menu_switcher"></div>
                    {* Logo *}
                    <div class="logo header__logo ">
                        {if !empty({$settings->site_logo})}
                        <a class="logo__link " href="{if $controller=='MainController'}javascript:;{else}{url_generator route="main"}{/if}">
                            <img class=""  src="{$rootUrl}/{$config->design_images}{$settings->site_logo}?v={$settings->site_logo_version}" alt="{$settings->site_name|escape}"/>
                        </a>
                        {/if}
                    </div>
                    {* Main menu *}
                    <div class="header__menu d-flex flex-wrap">
                        {$menu_header}
                    </div>
                    {* header contacts *}
                    <div class="header-contact">
                        <div class="header-contact__inner">
                            <div class="header-contact__item header-contact__item--visible">
                                <div class="header-contact__phone">
                                    <a class="header-contact__section mdi mdi-phone-in-talk" href="tel:{preg_replace('~[^0-9]~', '', $lang->company_phone_1)}" data-language="company_phone_1">
                                        <span>{$lang->company_phone_1}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="header-contact__item">
                                <div class="header-contact__phone">
                                    <a class="header-contact__section mdi mdi-phone-in-talk" href="tel:{preg_replace('~[^0-9]~', '', $lang->company_phone_2)}" data-language="company_phone_2">
                                        <span>{$lang->company_phone_2}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="header-contact__item">
                                <div class="header-contact__email">
                                    <a class="header-contact__section mdi mdi-email-outline" href="mailto:{$lang->company_email}" data-language="company_email">
                                        <span>{$lang->company_email}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="header-contact__item">
                                <div class="header-contact__time">
                                    <div class="header-contact__section mdi mdi-calendar-clock">
                                        <div class="header-contact__title" data-language="index_we_open">{$lang->index_we_open}</div>
                                        <div class="header-contact__title-s" data-language="company_open_hours">{$lang->company_open_hours}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="header-contact__item">
                                <div class="header-contact__location">
                                    <div class="header-contact__section mdi mdi-map-marker-outline">
                                        <div class="header-contact__title" data-language="index_location_title">{$lang->index_location_title}</div>
                                        <div class="header-contact__title-s" data-language="company_location">{$lang->company_location}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header__bottom">
            <div class="fn_header__sticky">
                <div class="container">
                    <div class="header__bottom_panel f_row no_gutters flex-nowrap align-content-stretch justify-content-between">
                        {* Mobile menu button*}
                        <div class="fn_menu_switch menu_switcher hidden-lg-up">
                            <div class="menu_switcher__heading d-flex align-items-center">
                                <i class="mdi mdi-menu catalog_icon"></i>
                                <span class="" data-language="index_categories">{$lang->index_mobile_menu}</span>
                            </div>
                        </div>
                        {* Catalog heading *}
                        {get_banner var=banner_group1 group='group1'}
                        <div class="{if $controller != 'MainController' || empty($banner_group1->items)}fn_catalog_switch button--blick{/if} catalog_button d-lg-flex hidden-md-down ">
                            <div class="catalog_button__heading d-flex align-items-center ">
                                <i class="mdi mdi-menu catalog_icon"></i>
                                <span class="" data-language="index_categories">{$lang->index_categories}</span>
                                {if $controller != 'MainController' || empty($banner_group1->items)}
                                    <span class="catalog_button__arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
                                {/if}
                            </div>
                         </div>
                        {* Search form *}
                        <form id="fn_search" class="fn_search_mob search d-md-flex" action="{url_generator route='search'}">
                            <input class="fn_search search__input" type="text" name="keyword" value="{$keyword|escape}" data-language="index_search" placeholder="{$lang->index_search}"/>
                            <button class="search__button mdi mdi-magnify d-flex align-items-center justify-content-center" type="submit"></button>
                        </form>
                        <div class="header_informers">
                            {* Mobile search toggle *}
                            <div class="fn_search_toggle header_informers__item mdi mdi-magnify hidden-md-up"></div>
                            {* Wishlist informer *}
                            <div id="wishlist" class="header_informers__item">
                                {include file="wishlist_informer.tpl"}
                            </div>
                            {* Comparison informer *}
                            <div id="comparison" class="header_informers__item">
                                {include "comparison_informer.tpl"}
                            </div>
                            {* Cart informer*}
                            <div id="cart_informer" class="header_informers__item">
                                {include file='cart_informer.tpl'}
                            </div>
                        </div>
                        {* Categories menu *}
                        {if $is_mobile == false && $is_tablet == false}
                            <nav class="fn_catalog_menu categories_nav hidden-md-down {if $controller == 'MainController' && !empty($banner_group1->items)}categories_nav--show{/if}">
                                {include file="desktop_categories.tpl"}
                            </nav>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {* Тело сайта *}
    <div id="fn_content" class="main">
        {* Banners *}
        {if !empty($banner_group1->items)}
        <div class="container">
            <div class="main_banner">
                <div class="fn_banner_group1 main_banner__inner owl-carousel">
                    {foreach $banner_group1->items as $bi}
                        {* Banners variant image *}
                        {* main_banner_var2 - изображение на всю ширину, main_banner_var1 - изображение справа на 50% *}
                        <div class="main_banner_var main_banner_var2 lazy" data-bg="url('{$bi->image|resize:1200:700:false:$config->resized_banners_images_dir}')">
                            <div class="main_banner_var__content">
                                <div class="main_banner_var__image">
                                    <img src="{$bi->image|resize:500:320:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}"/>
                                </div>
                                <div class="main_banner_var__column dark">
                                    <div class="main_banner_var__colboxed">
                                        {if $bi->title}
                                            <div class="main_banner_var__title">
                                                {$bi->title}
                                            </div>
                                        {/if}

                                        {if $bi->description}
                                            <div class="main_banner_var__text">
                                                {$bi->description}
                                            </div>
                                        {/if}

                                        {if $bi->url}
                                            <a href="{$bi->url}" target="_blank" class="main_banner_var__button button button--big button--basic button--blick">
                                                <span>Подробнее</span>
                                            </a>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        {/if}

        {* Контент сайта *}
        {if $controller == "MainController" || (!empty($page) && $page->url == '404')}
            <div class="fn_ajax_content">
                {$content}
            </div>
        {else}
            <div class="container">
                {include file='breadcrumb.tpl'}
                <div class="fn_ajax_content">
                    {$content}

                    {* Преимущества магазина *}
                    {include file='advantages.tpl'}
                </div>
            </div>
        {/if}
    </div>

    {* Кнопка на верх *}
    <div class="fn_to_top to_top"></div>

    {* Footer *}
    <footer class="footer">
        <div class="container">
            <div class="f_row flex-column flex-md-row justify-content-md-between align-items-start">
                {* Footer contacts *}
                <div class="f_col-lg">
                    <div class="footer__title d-flex align-items-center justify-content-between">
                        <span data-language="index_contacts">{$lang->index_contacts}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div class="footer__content hidden-md-down">
                        <div class="footer__contact_item">
                            <a class="phone mdi mdi-phone-in-talk" href="tel:{preg_replace('~[^0-9]~', '', $lang->company_phone_1)}" data-language="company_phone_1">
                                <span>{$lang->company_phone_1}</span>
                            </a>
                        </div>
                        <div class="footer__contact_item">
                            <a class="phone mdi mdi-phone-in-talk" href="tel:{preg_replace('~[^0-9]~', '', $lang->company_phone_2)}" data-language="company_phone_2">
                                <span>{$lang->company_phone_2}</span>
                            </a>
                        </div>
                        <div class="footer__contact_item">
                            <a class="email mdi mdi-email-outline" href="mailto:{$lang->company_email}" data-language="company_email">
                                <span>{$lang->company_email}</span>
                            </a>
                         </div>
                        <div class="footer__contact_item">
                            <span class="open_hours mdi mdi-calendar-clock" data-language="company_email">
                                <div class="open_hours_title" data-language="index_we_open">{$lang->index_we_open}</div>
                                <div data-language="company_open_hours">{$lang->company_open_hours}</div>
                            </span>
                        </div>
                        <div class="footer__contact_item">
                            <span class="location mdi mdi-map-marker-outline" data-language="company_email">
                                <div data-language="company_location">{$lang->company_location}</div>
                            </span>
                        </div>
                        <div class="footer__contact_item">
                            <a class="fn_callback callback mdi mdi-phone-return" href="#fn_callback" data-language="index_back_call">
                                <span>{$lang->index_back_call}</span>
                            </a>
                        </div>
                    </div>
                </div>
                {* Main menu *}
                <div class="f_col-lg">
                    <div class="footer__title d-flex align-items-center justify-content-between">
                        <span data-language="index_about_store">{$lang->index_about_store}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div class="footer__content footer__menu hidden-md-down">
                        {$menu_footer}
                    </div>
                </div>
                {* Categories menu *}
                <div class="f_col-lg">
                    <div class="footer__title footer__title d-flex align-items-center justify-content-between">
                        <span data-language="index_categories">{$lang->index_categories}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div class="footer__content footer__menu hidden-md-down">
                        {foreach $categories as $c}
                            {if $c->visible}
                                <div class="footer__menu_item">
                                    <a class="footer__menu_link" href="{url_generator route="category" url=$c->url}">{$c->name|escape}</a>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
                {* Subscribing *}
                <div class="f_col-lg">
                    <div class="footer__title footer__title d-flex align-items-center justify-content-between">
                        <span data-language="subscribe_heading">{$lang->subscribe_heading}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div id="subscribe_container" class="footer__content hidden-md-down">
                        <div class="subscribe__title">
                            <span data-language="subscribe_promotext">{$lang->subscribe_promotext}</span>
                        </div>
                        <form class="subscribe_form fn_validate_subscribe" method="post">
                            <div class="subscribe_form__group">
                                <div class="form__group form__group--subscribe">
                                    <input type="hidden" name="subscribe" value="1"/>
                                    <input class="form__input form__input--subscribe" type="email" name="subscribe_email" value="" data-format="email" placeholder="{$lang->form_email}"/>
                                </div>
                                <button class="form__button form__button--subscribe" type="submit"><span data-language="subscribe_button">{$lang->subscribe_button}</span></button>
                            </div>
                            {if !empty($subscribe_error)}
                                <div id="subscribe_error" class="popup">
                                    <div class="popup__heading">
                                        {include file="svg.tpl" svgId="success_icon"}
                                        {if $subscribe_error == 'email_exist'}
                                            <span data-language="subscribe_already">{$lang->index_subscribe_already}</span>
                                        {/if}
                                        {if $subscribe_error == 'empty_email'}
                                            <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            {if !empty($subscribe_success)}
                                <div id="fn_subscribe_sent" class="popup">
                                    <div class="popup__heading">
                                        {include file="svg.tpl" svgId="success_icon"}
                                        <span data-language="subscribe_sent">{$lang->index_subscribe_sent}</span>
                                    </div>

                                </div>
                            {/if}
                        </form>
                    </div>
                    {* Social buttons *}
                    {if $settings->site_social_links}
                        <div class="footer__title d-flex align-items-center justify-content-between">
                            <span data-language="index_in_networks">{$lang->index_in_networks}</span>
                            <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                        </div>
                        <div class="footer__content footer__social social hidden-md-down">
                            {*Домен некоторых соц. сетей не соответствует стилям font-awesome, для них сделаны эти алиасы*}
                            {$social_aliases.ok = 'odnoklassniki'}

                            {foreach $settings->site_social_links as $social_link}
                            {$social_domain = preg_replace('~(https?://)?(www\.)?([^\.]+)?\..*~', '$3', $social_link)}
                            {if isset($social_aliases.$social_domain) || $social_domain}
                            <a class="social__link {if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}" href="{if !preg_match('~^https?://.*$~', $social_link)}https://{/if}{$social_link|escape}" target="_blank" title="{$social_domain}">
                                <i class="mdi mdi-{if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}"></i>
                            </a>
                            {/if}
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <div class="footer__copyright">
            <div class="container">
                <div class="f_row flex-column flex-md-row justify-content-center justify-content-md-between align-items-center">
                    {* Payments *}
                    <div class="f_col-md footer__payments payments">
                        <ul class="payments__list d-flex justify-content-md-end align-items-center">
                            <li class="payments__item">
                                <div class="payments_visa" title="VISA">{include file="svg.tpl" svgId="visacard_icon"}</div>
                            </li>
                            <li class="payments__item">
                                <div class="payments_master" title="Master card">{include file="svg.tpl" svgId="mastercard_icon"}</div>
                            </li>
                            <li class="payments__item">
                                <div class="payments_yamoney" title="Yandex money">{include file="svg.tpl" svgId="yandex_money_icon"}</div>
                            </li>
                            <li class="payments__item">
                                <div class="payments_kiwi" title="Kiwi">{include file="svg.tpl" svgId="kiwi_icon"}</div>
                            </li>
                            <li class="payments__item">
                                <div class="payments_sberbank" title="Sberbank">{include file="svg.tpl" svgId="sberbank_icon"}</div>
                            </li>
                        </ul>
                    </div>
                    {* Copyright *}
                    <div class="f_col-md flex-md-first copyright">
                        <span>© {$smarty.now|date_format:"%Y"}</span>
                        <a href="https://okay-cms.com" target="_blank">
                        <span data-language="index_copyright">{$lang->index_copyright}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    {*if $is_mobile === true || $is_tablet === true*}
    <div class="fn_mobile_menu hidden">
        {include file="mobile_menu.tpl"}
    </div>
    {*/if*}
    {* Форма обратного звонка *}
    {include file='callback.tpl'}

    <script>ut_tracker.start('parsing:body_bottom:scripts');</script>

    {if $controller == 'ProductController' || $controller == "BlogController"}
        {js file="jssocials.min.js" dir='js_libraries/js_socials/js' defer=true}
    {/if}

    {$ok_footer}
    {$admintooltip}

    {if $controller == 'ProductController' || $controller == "BlogController"}
        {css file='jssocials.css' dir='js_libraries/js_socials/css'}
        {if $settings->social_share_theme}
            {css file="jssocials-theme-{$settings->social_share_theme|escape}.css" dir='js_libraries/js_socials/css'}
        {/if}
    {/if}
    <script>ut_tracker.end('parsing:body_bottom:scripts');</script>

    {if !empty($counters['body_bottom'])}
        <script>ut_tracker.start('parsing:body_bottom:counters');</script>
        {foreach $counters['body_bottom'] as $counter}
            {$counter->code}
        {/foreach}
        <script>ut_tracker.end('parsing:body_bottom:counters');</script>
    {/if}

    {if $controller == 'UserController'}
        <script src="//ulogin.ru/js/ulogin.js"></script>
    {/if}
    <script>ut_tracker.end('parsing:page');</script>
</body>
</html>
