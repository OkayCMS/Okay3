{$meta_title = $btr->left_setting_router_title scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
            {$btr->left_setting_router_title|escape}
            <i class="fn_tooltips" title="{$btr->tooltip_title_chpu|escape}">
                {include file='svg_icon.tpl' svgId='icon_tooltips'}
            </i>
            </div>
            <div class="boxes_inline">

            </div>
        </div>

    </div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'saved'}
                        {$btr->general_settings_saved|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {include file='svg_icon.tpl' svgId='return'}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data">
    <input type=hidden name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->manager_settings|escape}
                </div>
                <div class="permission_block">
                    <div class="permission_boxes">
                        <div class="activity_of_switch activity_of_switch--left">
                            <div class="activity_of_switch_item"> {* row block *}
                                <div class="okay_switch clearfix">
                                    <label class="switch_label">
                                        {$btr->global_url_label|escape}
                                        <i class="fn_tooltips" title="{$btr->tooltip_settings_router_statuses|escape}">
                                            {include file='svg_icon.tpl' svgId='icon_tooltips'}
                                        </i>
                                    </label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="global_unique_url" value='1' type="checkbox" id="visible_checkbox" {if $settings->global_unique_url}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            {get_design_block block="settings_router_switth_checkboxes"}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap min_height_270px">
                <div class="heading_box">
                    {$btr->category_routing|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="okay_type_radio_wrap">
                        <input id="category_routes_default" class="hidden_check" name="category_routes_template" type="radio" value="default" {if empty($settings->category_routes_template) || $settings->category_routes_template == 'default'} checked="" {/if} />
                        <label for="category_routes_default" class="okay_type_radio">
                            <span>
                                {$rootUrl}/
                                <input name="category_routes_template__default" placeholder="catalog" class="form-control prefix-url-input" type="text" value="{if $settings->category_routes_template__default}{$settings->category_routes_template__default}{else}catalog{/if}" />
                                /category
                            </span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="category_routes_no_prefix" class="hidden_check" name="category_routes_template" type="radio" value="no_prefix" {if $settings->category_routes_template == 'no_prefix'} checked="" {/if} />
                        <label for="category_routes_no_prefix" class="okay_type_radio">
                            <span>{$rootUrl}/category</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="category_routes_prefix_and_path" class="hidden_check" name="category_routes_template" type="radio" value="prefix_and_path" {if $settings->category_routes_template == 'prefix_and_path'} checked="" {/if} />
                        <label for="category_routes_prefix_and_path" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="category_routes_template__prefix_and_path" placeholder="catalog" class="form-control prefix-url-input" type="text" value="{if $settings->category_routes_template__prefix_and_path}{$settings->category_routes_template__prefix_and_path}{else}catalog{/if}" />/category-level-1/.../category</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="category_routes_no_prefix_and_path" class="hidden_check" name="category_routes_template" type="radio" value="no_prefix_and_path" {if $settings->category_routes_template == 'no_prefix_and_path'} checked="" {/if} />
                        <label for="category_routes_no_prefix_and_path" class="okay_type_radio">
                            <span>{$rootUrl}/category-level-1/.../category</span>
                        </label>
                    </div>
                </div>
                {get_design_block block="settings_router_category"}
            </div>
        </div>

        <div class="col-lg-6 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap min_height_270px">
                <div class="heading_box">
                    {$btr->product_routing|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="okay_type_radio_wrap">
                        <input id="product_routes_default" class="hidden_check" name="product_routes_template" type="radio" value="default" {if empty($settings->product_routes_template) || $settings->product_routes_template == 'default'} checked="" {/if} />
                        <label for="product_routes_default" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="product_routes_template__default" placeholder="products" class="form-control prefix-url-input" type="text" value="{if $settings->product_routes_template__default}{$settings->product_routes_template__default}{else}products{/if}" />/product-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="product_routes_prefix_and_all_categories" class="hidden_check" name="product_routes_template" type="radio" value="prefix_and_path" {if $settings->product_routes_template == 'prefix_and_path'} checked="" {/if} />
                        <label for="product_routes_prefix_and_all_categories" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="product_routes_template__prefix_and_path" placeholder="catalog" class="form-control prefix-url-input" type="text" value="{if $settings->product_routes_template__prefix_and_path}{$settings->product_routes_template__prefix_and_path}{else}catalog{/if}" />/category-level-1/.../category/product-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="product_routes_no_prefix_and_path" class="hidden_check" name="product_routes_template" type="radio" value="no_prefix_and_path" {if $settings->product_routes_template == 'no_prefix_and_path'} checked="" {/if} />
                        <label for="product_routes_no_prefix_and_path" class="okay_type_radio">
                            <span>{$rootUrl}/category-level-1/.../category/product-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="product_routes_no_prefix_and_category" class="hidden_check" name="product_routes_template" type="radio" value="no_prefix_and_category" {if $settings->product_routes_template == 'no_prefix_and_category'} checked="" {/if} />
                        <label for="product_routes_no_prefix_and_category" class="okay_type_radio">
                            <span>{$rootUrl}/category/product-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="product_routes_no_prefix" class="hidden_check" name="product_routes_template" type="radio" value="no_prefix" {if $settings->product_routes_template == 'no_prefix'} checked="" {/if} />
                        <label for="product_routes_no_prefix" class="okay_type_radio">
                            <span>{$rootUrl}/product-name</span>
                        </label>
                    </div>
                </div>
                {get_design_block block="settings_router_product"}
            </div>
        </div>

        <div class="col-lg-6 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->brand_routing|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="okay_type_radio_wrap">
                        <input id="brand_routes_default" class="hidden_check" name="brand_routes_template" type="radio" value="default" {if empty($settings->brand_routes_template) || $settings->brand_routes_template == 'default'} checked="" {/if} />
                        <label for="brand_routes_default" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="brand_routes_template__default" placeholder="brand" class="form-control prefix-url-input" type="text" value="{if $settings->brand_routes_template__default}{$settings->brand_routes_template__default}{else}brand{/if}" />/brand-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="brand_routes_no_prefix" class="hidden_check" name="brand_routes_template" type="radio" value="no_prefix" {if $settings->brand_routes_template == 'no_prefix'} checked="" {/if} />
                        <label for="brand_routes_no_prefix" class="okay_type_radio">
                            <span>{$rootUrl}/brand-name</span>
                        </label>
                    </div>
                </div>
                {get_design_block block="settings_router_brand"}
            </div>
        </div>

        <div class="col-lg-6 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->blog_routing|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="okay_type_radio_wrap">
                        <input id="blog_item_routes_default" class="hidden_check" name="blog_item_routes_template" type="radio" value="default" {if empty($settings->blog_item_routes_template) || $settings->blog_item_routes_template == 'default'} checked="" {/if} />
                        <label for="blog_item_routes_default" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="blog_item_routes_template__default" placeholder="blog" class="form-control prefix-url-input" type="text" value="{if $settings->blog_item_routes_template__default}{$settings->blog_item_routes_template__default}{else}blog{/if}" />/post-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="blog_item_routes_no_prefix" class="hidden_check" name="blog_item_routes_template" type="radio" value="no_prefix" {if $settings->blog_item_routes_template == 'no_prefix'} checked="" {/if} />
                        <label for="blog_item_routes_no_prefix" class="okay_type_radio">
                            <span>{$rootUrl}/post-name</span>
                        </label>
                    </div>
                </div>
                {get_design_block block="settings_router_blog"}
            </div>
        </div>

        <div class="col-lg-6 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->news_routing|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="okay_type_radio_wrap">
                        <input id="news_item_routes_default" class="hidden_check" name="news_item_routes_template" type="radio" value="default" {if empty($settings->news_item_routes_template) || $settings->news_item_routes_template == 'default'} checked="" {/if} />
                        <label for="news_item_routes_default" class="okay_type_radio">
                            <span>{$rootUrl}/<input name="news_item_routes_template__default" placeholder="news" class="form-control prefix-url-input" type="text" value="{if $settings->news_item_routes_template__default}{$settings->news_item_routes_template__default}{else}news{/if}" />/news-name</span>
                        </label>
                    </div>

                    <div class="okay_type_radio_wrap">
                        <input id="news_item_routes_no_prefix" class="hidden_check" name="news_item_routes_template" type="radio" value="no_prefix" {if $settings->news_item_routes_template == 'no_prefix'} checked="" {/if} />
                        <label for="news_item_routes_no_prefix" class="okay_type_radio">
                            <span>{$rootUrl}/news-name</span>
                        </label>
                    </div>
                </div>
                {get_design_block block="settings_router_news"}
            </div>
        </div>
    </div>

    {$block = {get_design_block block="settings_router_custom_block"}}
    {if !empty($block)}
        <div class="fn_toggle_wrap custom_block">
            {$block}
        </div>
    {/if}

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 ">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
