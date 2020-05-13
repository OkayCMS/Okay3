<!DOCTYPE html>
<html {if $language->href_lang} lang="{$language->href_lang|escape}"{/if} prefix="og: http://ogp.me/ns#">
<head>
    {* Meta data *}
    {get_design_block block="front_before_head_content"}
    {include "head.tpl"}
    {get_design_block block="front_after_head_content"}
</head>

<body class="d-flex flex-column {if $controller == 'MainController'}main_page{else}other_page{/if}">
    <div>
        {get_design_block block="front_start_body_content"}
    </div>

    {if !empty($counters['body_top'])}
        <script>ut_tracker.start('parsing:body_top:counters');</script>
        {foreach $counters['body_top'] as $counter}
        {$counter->code}
        {/foreach}
        <script>ut_tracker.end('parsing:body_top:counters');</script>
    {/if}

    <header class="header">
        {if $is_mobile == false || $is_tablet == true}
        <div class="header__top hidden-md-down">
            <div class="container">
                <div class="f_row align-items-center flex-nowrap justify-content-between">
                    {* Account *}
                    <div id="account" class="d-flex align-items-center f_col">
                        {include file="user_informer.tpl"}
                    </div>
                    <div class="d-flex align-items-center f_col justify-content-end">
                        {* Callback *}
                        <a class="fn_callback callback d-inline-flex align-items-center" href="#fn_callback" data-language="index_back_call">
                            {include file="svg.tpl" svgId="support_icon"}
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
        <div class="header__center hidden-md-down" >
            <div class="container">
                <div class="f_row no_gutters flex-nowrap align-items-center justify-content-between">
                    {* Menu button*}
                    <div class="fn_menu_switch menu_switcher"></div>
                    {* Logo *}
                    <div class="header__logo logo">
                        {if !empty({$settings->site_logo})}
                        <a class="logo__link " href="{if $controller=='MainController'}javascript:;{else}{url_generator route='main'}{/if}">
                            <img src="{$rootUrl}/{$config->design_images|escape}{$settings->site_logo|escape}?v={$settings->site_logo_version|escape}" alt="{$settings->site_name|escape}"/>
                        </a>
                        {/if}
                    </div>
                    {* Main menu *}
                    <div class="header__menu d-flex flex-wrap">
                        {$menu_header}
                    </div>
                    {* header contacts *}
                    <div class="header-contact">
                        <div class="header-contact__inner {if !$settings->site_phones && !$settings->site_email} header-contact__inner--adress{/if}">
                            {if $settings->site_phones}
                                {foreach $settings->site_phones as $phone}
                                    <div class="header-contact__item header-contact--phone{if $phone@first} header-contact__item--visible{/if}">
                                        <a class="d-flex align-items-center header-contact__section" href="tel:{preg_replace('~[^0-9\+]~', '', $phone)}">
                                            {include file="svg.tpl" svgId="phone_icon"}
                                            <span>{$phone|escape}</span>
                                        </a>
                                    </div>
                                {/foreach}
                            {/if}
                            {if $settings->site_email}
                                <div class="header-contact__item header-contact--email {if !$settings->site_phones} header-contact__item--visible{/if}">
                                    <a class="d-flex align-items-center header-contact__section" href="mailto:{$settings->site_email|escape}" >
                                        <span>{$settings->site_email|escape}</span>
                                    </a>
                                </div>
                            {/if}
                            {if $settings->site_working_hours}
                                <div class="header-contact__item header-contact--time {if !$settings->site_phones && !$settings->site_email} header-contact__item--visible{/if}">
                                    <div class="d-flex align-items-center header-contact__section">
                                        <div class="header-contact__title-s">{$settings->site_working_hours}</div>
                                    </div>
                                </div>
                            {/if}
                         </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="header__bottom">
            <div class="{if $controller != 'MainController'}fn_header__sticky {/if}" data-margin-top="0" data-sticky-for="1024" data-sticky-class="is-sticky">
                <div class="container">
                    <div class="header__bottom_panel f_row no_gutters flex-nowrap align-content-stretch justify-content-between">
                        {* Mobile menu button*}
                        <div class="fn_menu_switch menu_switcher hidden-lg-up">
                            <div class="menu_switcher__heading d-flex align-items-center">
                                <i class="fa fa-bars catalog_icon"></i>
                                <span class="" data-language="index_categories">{$lang->index_mobile_menu}</span>
                            </div>
                        </div>
                        {* Catalog heading *}
                        <div class="{if $controller != 'MainController' || empty($global_banners)}fn_catalog_switch button--blick{/if} catalog_button d-lg-flex hidden-md-down ">
                            <div class="catalog_button__heading d-flex align-items-center ">
                                <i class="fa fa-bars catalog_icon"></i>
                                <span class="" data-language="index_categories">{$lang->index_categories}</span>
                                {if $controller != 'MainController' || empty($global_banners)}
                                    <span class="catalog_button__arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
                                {/if}
                            </div>
                         </div>
                        {* Search form *}
                        <form id="fn_search" class="fn_search_mob search d-md-flex" action="{url_generator route='search'}">
                            <input class="fn_search search__input" type="text" name="keyword" value="{$keyword|escape}" aria-label="search" data-language="index_search" placeholder="{$lang->index_search}"/>
                            <button class="search__button d-flex align-items-center justify-content-center" aria-label="search" type="submit"></button>
                        </form>
                        <div class="header_informers d-flex align-items-center">
                            {* Mobile search toggle *}
                            <div class="fn_search_toggle header_informers__item d-flex align-items-center justify-content-center hidden-md-up">{include file="svg.tpl" svgId="search_icon"}</div>
                            {* Wishlist informer *}
                            <div id="wishlist" class="header_informers__item d-flex align-items-center justify-content-center">{include file="wishlist_informer.tpl"}</div>
                            {* Comparison informer *}
                            <div id="comparison" class="header_informers__item d-flex align-items-center justify-content-center">{include "comparison_informer.tpl"}</div>
                            {* Cart informer*}
                            <div id="cart_informer" class="header_informers__item d-flex align-items-center justify-content-center">{include file='cart_informer.tpl'}</div>
                        </div>
                        {* Categories menu *}
                        {if $is_mobile == false || $is_tablet == true}
                            <nav class="fn_catalog_menu categories_nav hidden-md-down {if $controller == 'MainController' && !empty($global_banners)}categories_nav--show{/if}">
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
        {* Include module banner *}
        {if !empty($global_banners)}
            <div class="container">
                <div class="{if $controller == 'MainController'}d-flex main_banner{/if}">
                    {$global_banners}
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

    <div>
        {get_design_block block="front_before_footer_content"}
    </div>

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
                    <div class="footer__content footer__hidden">
                        {if $settings->site_phones}
                            {foreach $settings->site_phones as $phone}
                                <div class="footer__contact_item">
                                    <a class="d-flex align-items-start phone" href="tel:{preg_replace('~[^0-9\+]~', '', $phone)}">
                                        {include file="svg.tpl" svgId="phone_icon"}
                                        <span>{$phone|escape}</span>
                                    </a>
                                </div>
                            {/foreach}
                        {/if}
                        {if $settings->site_email}
                            <div class="footer__contact_item">
                                <a class="d-flex align-items-start email " href="mailto:{$settings->site_email|escape}">
                                    {include file="svg.tpl" svgId="email_icon"}
                                    <span>{$settings->site_email|escape}</span>
                                </a>
                            </div>
                        {/if}
                        {if $settings->site_working_hours}
                            <div class="footer__contact_item">
                                <div class="d-flex align-items-start open_hours">
                                    {include file="svg.tpl" svgId="time_icon"}
                                    {$settings->site_working_hours}
                                </div>
                            </div>
                        {/if}
                        <div class="footer__contact_item">
                            <a class="fn_callback callback d-inline-flex align-items-center" href="#fn_callback" data-language="index_back_call">
                                {include file="svg.tpl" svgId="support_icon"}
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
                    <div class="footer__content footer__menu footer__hidden">
                        {$menu_footer}
                    </div>
                </div>
                {* Categories menu *}
                <div class="f_col-lg">
                    <div class="footer__title footer__title d-flex align-items-center justify-content-between">
                        <span data-language="index_categories">{$lang->index_categories}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div class="fn_view_content footer__content footer__menu footer__hidden">
                        {$c_count = 0}
                        {foreach $categories as $c}
                            {$c_count = $c_count+1}
                            {if $c->visible && ($c->has_products || $settings->show_empty_categories)}
                                <div class="footer__menu_item {if $c_count > 5}closed{else}opened{/if}">
                                    <a class="footer__menu_link" href="{url_generator route='category' url=$c->url}">{$c->name|escape}</a>
                                </div>
                            {/if}
                        {/foreach}
                        {if $c_count > 5}
                            <a class="fn_view_all footer__view_all" href="">{$lang->filter_view_show|escape}</a>
                        {/if}
                    </div>
                </div>
                {* Subscribing *}
                <div class="f_col-lg">
                    <div class="footer__title footer__title d-flex align-items-center justify-content-between">
                        <span data-language="subscribe_heading">{$lang->subscribe_heading}</span>
                        <span class="fn_switch_parent footer__title_arrow hidden-lg-up">{include file="svg.tpl" svgId="arrow_right"}</span>
                    </div>
                    <div id="subscribe_container" class="footer__content footer__hidden">
                        <div class="subscribe__title">
                            <span data-language="subscribe_promotext">{$lang->subscribe_promotext}</span>
                        </div>
                        <form class="subscribe_form fn_validate_subscribe" method="post">
                            <div class="d-flex align-items-center subscribe_form__group">
                                <div class="form__group form__group--subscribe">
                                    <input type="hidden" name="subscribe" value="1"/>
                                    <input class="form__input form__input_subscribe" aria-label="subscribe" type="email" name="subscribe_email" value="" data-format="email" placeholder="{$lang->form_email}"/>
                                </div>
                                <button class="form__button form__button--subscribe" type="submit"><span data-language="subscribe_button">{$lang->subscribe_button}</span></button>
                            </div>
                            {if !empty($subscribe_error)}
                                <div id="subscribe_error" class="popup">
                                    <div class="popup__heading">
                                        {include file="svg.tpl" svgId="success_icon"}
                                        {if $subscribe_error == 'email_exist'}
                                            <span data-language="subscribe_already">{$lang->index_subscribe_already}</span>
                                        {elseif $subscribe_error == 'empty_email'}
                                            <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                        {else}
                                            <span>{$subscribe_error|escape}</span>
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
                        <div class="footer__content footer__social social footer__hidden">
                            {*Домен некоторых соц. сетей не соответствует стилям font-awesome, для них сделаны эти алиасы*}
                            {$social_aliases.ok = 'odnoklassniki'}

                            {foreach $settings->site_social_links as $social_link}
                            {$social_domain = preg_replace('~(https?://)?(www\.)?([^\.]+)?\..*~', '$3', $social_link)}
                            {if isset($social_aliases.$social_domain) || $social_domain}
                            <a class="social__link {if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}" rel="noreferrer" aria-label="{$social_domain}" href="{if !preg_match('~^https?://.*$~', $social_link)}https://{/if}{$social_link|escape}" target="_blank" title="{$social_domain}">
                                <i class="fa fa-{if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}"></i>
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
                            {foreach $payment_methods as $payment_method}
                                {if !$payment_method->image}{continue}{/if}
                                <li class="d-flex justify-content-center align-items-center payments__item" title="{$payment_method->name|escape}">
                                    <img src="{$payment_method->image|resize:80:30:false:$config->resized_payments_dir}" alt="{$payment_method->name|escape}" />
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    {* Copyright *}
                    <div class="f_col-md flex-md-first copyright">
                        <span>© {$smarty.now|date_format:"%Y"}</span>
                        <a href="https://okay-cms.com" rel="noreferrer" target="_blank">
                        <span data-language="index_copyright">{$lang->index_copyright}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {if $is_mobile === true || $is_tablet === true}
    <div class="fn_mobile_menu hidden">
        {include file="mobile_menu.tpl"}
    </div>
    {/if}
    {* Форма обратного звонка *}
    {include file='callback.tpl'}

    <script>ut_tracker.start('parsing:body_bottom:scripts');</script>

    {if $controller == 'ProductController' || $controller == "BlogController"}
        {js file="jssocials.min.js" dir='js_libraries/js_socials/js' defer=true}
    {/if}

    {$ok_footer}

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

    <div>
        {get_design_block block="front_after_footer_content"}
    </div>
</body>
</html>
