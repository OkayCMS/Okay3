{$meta_title = $btr->okaycms__google_merchant__title|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->okaycms__google_merchant__title|escape}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success=='added'}
                        {$btr->discount_added|escape}
                    {elseif $message_success=='updated'}
                        {$btr->discount_updated|escape}
                    {else}
                        {$message_success|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {*{include file='svg_icon.tpl' svgId='return'}*}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Вывод ошибок*}
{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_warning">
                <div class="heading_box">
                    {if $message_error=='empty_name'}
                        {$btr->general_enter_title|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data" class="fn_fast_button fn_is_translit_alpha">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />

    <div class="row">
        <div class="col-xs-12">
            <div class="boxed">
                {*Название элемента сайта*}
                <div class="row d_flex">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->okaycms__google_merchant__generation_url|escape}
                        </div>
                        <div class="form-group">
                            <a href="{url_generator route='OkayCMS_GoogleMerchant_feed' absolute=1}" target="_blank">{url_generator route='OkayCMS_GoogleMerchant_feed' absolute=1}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->okaycms__google_merchant__params|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="permission_block">
                        <div class="permission_boxes row">
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="permission_box">
                                    <span>{$btr->okaycms__google_merchant__upload_non_exists_products_to_google|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="okaycms__google_merchant__upload_non_exists_products_to_google" value='1' type="checkbox" {if $settings->okaycms__google_merchant__upload_non_exists_products_to_google}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="permission_box">
                                    <span>{$btr->okaycms__google_merchant__use_full_description_to_google|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="okaycms__google_merchant__use_full_description_to_google" value='1' type="checkbox" {if $settings->okaycms__google_merchant__use_full_description_to_google}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="permission_box">
                                    <span>{$btr->okaycms__google_merchant__no_export_without_price|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="okaycms__google_merchant__no_export_without_price" value='1' type="checkbox" {if $settings->okaycms__google_merchant__no_export_without_price}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="permission_box">
                                    <span>{$btr->okaycms__google_merchant__adult|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="okaycms__google_merchant__adult" value='1' type="checkbox" {if $settings->okaycms__google_merchant__adult}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="permission_box">
                                    <span>{$btr->okaycms__google_merchant__use_variant_name_like_size|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="okaycms__google_merchant__use_variant_name_like_size" value='1' type="checkbox" {if $settings->okaycms__google_merchant__use_variant_name_like_size}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <div class="heading_label">
                                <strong>{$btr->okaycms__google_merchant__company}</strong>
                            </div>
                            <div class="mb-1">
                                <input class="form-control" type="text" name="okaycms__google_merchant__company" value="{$settings->okaycms__google_merchant__company}" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-1">
                            <div class="heading_label">
                                <strong>{$btr->okaycms__google_merchant__color}</strong> <span>({$btr->okaycms__google_merchant__color_notify})</span>
                            </div>
                            <div class="mb-1">
                                <select name="okaycms__google_merchant__color" class="selectpicker">
                                    <option {if $settings->okaycms__google_merchant__color == 0}selected=""{/if} value=""></option>
                                    {foreach $features as $feature}
                                        <option {if $settings->okaycms__google_merchant__color == $feature->id}selected=""{/if} value="{$feature->id}">{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 ">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->okaycms__google_merchant__upload_products|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                {*Параметры элемента*}
                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="boxed match fn_toggle_wrap">
                            <div class="heading_box">
                                {$btr->okaycms__google_merchant__categories}
                                <button class="btn btn_small btn-info" name="add_all_categories" value="1">{$btr->okaycms__google_merchant__select_all}</button>
                                <button class="btn btn_small" name="remove_all_categories" value="1">{$btr->okaycms__google_merchant__select_none}</button>
                            </div>
                            <div class="toggle_body_wrap on fn_card">
                                <select style="opacity: 0;" class="selectpicker_categories col-xs-12 px-0" multiple name="categories[]" size="10" data-selected-text-format="count" >
                                    {function name=category_select selected_id=$product_category level=0}
                                        {foreach $categories as $category}
                                            <option value='{$category->id}' class="category_to_xml" {if $category->to__okaycms__google_merchant}selected=""{/if}>{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
                                            {category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
                                        {/foreach}
                                    {/function}
                                    {category_select categories=$categories}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <div class="boxed match fn_toggle_wrap">
                            <div class="heading_box">
                                {$btr->okaycms__google_merchant__brands}
                                <button class="btn btn_small btn-info" name="add_all_brands" value="1">{$btr->okaycms__google_merchant__select_all}</button>
                                <button class="btn btn_small" name="remove_all_brands" value="1">{$btr->okaycms__google_merchant__select_none}</button>
                            </div>
                            <div class="toggle_body_wrap on fn_card">
                                <select style="opacity: 0;" class="selectpicker_brands col-xs-12 px-0" multiple name="brands[]" size="10" data-selected-text-format="count" >
                                    {foreach $brands as $brand}
                                        <option value='{$brand->id}' class="brand_to_xml" {if $brand->to__okaycms__google_merchant}selected{/if}>{$brand->name|escape}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                    {literal}
                        <script>
                            $('.selectpicker_categories').selectpicker();
                            $('.selectpicker_brands').selectpicker();
                        </script>
                    {/literal}

                    {backend_compact_product_list
                        title=$btr->okaycms__google_merchant__products_for_upload
                        name='related_products'
                        products=$related_products
                        label=$btr->okaycms__google_merchant__add_products
                        placeholder=$btr->okaycms__google_merchant__select_products
                    }

                    {backend_compact_product_list
                        title=$btr->okaycms__google_merchant__products_not_for_upload
                        name='not_related_products'
                        products=$not_related_products
                        label=$btr->okaycms__google_merchant__add_products
                        placeholder=$btr->okaycms__google_merchant__select_products
                    }
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-bottom: 200px;">
        <div class="col-lg-12 col-md-12 ">
            <button type="submit" class="btn btn_small btn_blue float-md-right">
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>
</form>