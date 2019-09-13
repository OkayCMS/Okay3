{$meta_title = $btr->rozetka_xml|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->rozetka_xml|escape}
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
                            {$btr->rozetka_xml_generation_url|escape}
                        </div>
                        <div class="form-group">
                            <a href="{url_generator route='OkayCMS_Rozetka_feed' absolute=1}" target="_blank">{url_generator route='OkayCMS_Rozetka_feed' absolute=1}</a>
                        </div>
                    </div>

                    <div class="activity_of_switch" style="width: 600px !important;">
                        <div class="activity_of_switch_item"> {* row block *}
                            <div class="okay_switch clearfix">
                                <label class="switch_label">{$btr->upload_non_exists_products_to_rozetka|escape}</label>
                                <label class="switch switch-default">
                                    <input class="switch-input" name="upload_non_available" value='1' type="checkbox" id="visible_checkbox" {if $settings->upload_only_available_to_rozetka}checked=""{/if}/>
                                    <span class="switch-label"></span>
                                    <span class="switch-handle"></span>
                                </label>
                            </div>
                        </div>
                        <div class="activity_of_switch_item"> {* row block *}
                            <div class="okay_switch clearfix">
                                <label class="switch_label">{$btr->use_full_description_to_rozetka|escape}</label>
                                <label class="switch switch-default">
                                    <input class="switch-input" name="full_description" value="1" type="checkbox" id="featured_checkbox" {if $settings->use_full_description_in_upload_rozetka}checked=""{/if}/>
                                    <span class="switch-label"></span>
                                    <span class="switch-handle"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="boxed match fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->rozetka_xml_categories}
                    <button class="btn btn_small btn-info" name="add_all_categories" value="1">{$btr->rozetka_xml_select_all}</button>
                    <button class="btn btn_small" name="remove_all_categories" value="1">{$btr->rozetka_xml_select_none}</button>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <select style="opacity: 0;" class="selectpicker_categories col-xs-12 px-0" multiple name="categories[]" size="10" data-selected-text-format="count" >
                        {function name=category_select selected_id=$product_category level=0}
                            {foreach $categories as $category}
                                <option value='{$category->id}' class="category_to_xml" {if $category->to_rozetka}selected=""{/if}>{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
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
                    {$btr->rozetka_xml_brands}
                    <button class="btn btn_small btn-info" name="add_all_brands" value="1">{$btr->rozetka_xml_select_all}</button>
                    <button class="btn btn_small" name="remove_all_brands" value="1">{$btr->rozetka_xml_select_none}</button>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <select style="opacity: 0;" class="selectpicker_brands col-xs-12 px-0" multiple name="brands[]" size="10" data-selected-text-format="count" >
                        {foreach $brands as $brand}
                            <option value='{$brand->id}' class="brand_to_xml" {if $brand->to_rozetka}selected{/if}>{$brand->name|escape}</option>
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
            title=$btr->products_for_upload
            name='related_products'
            products=$related_products
            label=$btr->add_products
            placeholder=$btr->select_products
        }

        {backend_compact_product_list
            title=$btr->products_not_for_upload
            name='not_related_products'
            products=$not_related_products
            label=$btr->add_products
            placeholder=$btr->select_products
        }
    </div>

    <div class="row" style="margin-bottom: 200px;">
        <div class="col-lg-12 col-md-12 ">
            <button type="submit" class="btn btn_small btn_blue float-md-right">
                {*{include file='svg_icon.tpl' svgId='checked'}*}
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>
</form>