{if $banner->id}
    {$meta_title = $banner->name scope=global}
{else}
    {$meta_title = $btr->banner_new_group scope=global}
{/if}
{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$banner->id}
                    {$btr->banner_new_group|escape}
                {else}
                    {$banner->name|escape}
                {/if}
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
                    {if $message_success == 'added'}
                        {$btr->general_group_added|escape}
                    {elseif $message_success == 'updated'}
                        {$btr->banner_updated|escape}
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

{*Вывод ошибок*}
{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_warning">
                <div class="heading_box">
                    {$message_error|escape}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data" class="fn_fast_button">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />
    <div class="row">
        <div class="col-xs-12">
            <div class="boxed">
                <div class="row d_flex">
                    {*Название элемента сайта*}
                    <div class="col-lg-10 col-md-9 col-sm-12">
                        <div class="heading_label">
                            {$btr->general_name|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control mb-h" name="name" type="text" value="{$banner->name|escape}"/>
                            <input name="id" type="hidden" value="{$banner->id|escape}"/>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-sm-12">
                                <div class="mt-h">
                                    <span class="boxes_inline">
                                        <label class="switch switch-default switch-pill switch-primary-outline-alt boxes_inline">
                                        <input class="switch-input" name="show_all_pages" value='1' type="checkbox" {if $banner->show_all_pages}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                    </span>
                                    <span class="boxes_inline heading_label">{$btr->banner_show_group|escape}</span>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12">
                                <div class="mt-h">
                                    <span class="boxes_inline">
                                        <label class="switch switch-default switch-pill switch-primary-outline-alt boxes_inline">
                                        <input class="switch-input" name="individual_shortcode" value='1' type="checkbox" {if $banner->individual_shortcode}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                    </span>
                                    <span class="boxes_inline heading_label">
                                        {$btr->banner_individual_shortcode|escape}
                                        <i class="fn_tooltips" title="{$btr->banner_individual_shortcode_description|escape}">
                                            {include file='svg_icon.tpl' svgId='icon_tooltips'}
                                        </i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {*Видимость элемента*}
                    <div class="col-lg-2 col-md-3 col-sm-12">
                        <div class="activity_of_switch">
                            <div class="activity_of_switch_item"> {* row block *}
                                <div class="okay_switch clearfix">
                                    <label class="switch_label">{$btr->general_enable|escape}</label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="visible" value='1' type="checkbox" id="visible_checkbox" {if $banner->visible}checked=""{/if}/>
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
    </div>
    <div class="row">
        <div class="col-md-12 ">
            <div class="boxed boxed_attention">
                <div class="heading_box">
                    {$btr->banner_instruction_head|escape}
                </div>
                <div class="text_box">
                    <div>
                        {$btr->banner_instruction_global_shortcode_part_1|escape}
                        <span style="background: #383838; color: #d8d8d8;">{literal}{$global_banners}{/literal}</span>.
                        {$btr->banner_instruction_global_shortcode_part_2|escape}
                    </div>
                    {if $banner->individual_shortcode}
                    <div>
                        {$btr->banner_instruction_shortcode_part_1|escape}
                        <span style="background: #383838; color: #d8d8d8;">{literal}{${/literal}{$banner->individual_shortcode}{literal}}{/literal}</span>
                        {$btr->banner_instruction_shortcode_part_2|escape}
                        <br>
                        {$btr->banner_instruction_shortcode_part_3|escape}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {*Параметры элемента*}
    <div class="row">
        <div class="col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->banner_show_banner|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                <div class="toggle_body_wrap fn_card on">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="banner_card">
                                <div class="banner_card_header">
                                    <span class="font-weight-bold">{$btr->general_pages|escape}</span>
                                </div>
                                <div class="banner_card_block">
                                    <select name="pages[]" class="selectpicker fn_action_select" multiple="multiple" data-selected-text-format="count">
                                        <option value="0" {if !$banner->page_selected || 0|in_array:$banner->page_selected}selected{/if}>{$btr->banner_hide|escape}</option>
                                        {foreach from=$pages item=page}
                                            {if $page->name != ''}
                                                <option value="{$page->id}" {if $banner->page_selected && $page->id|in_array:$banner->page_selected}selected{/if}>{$page->name|escape}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="banner_card">
                                <div class="banner_card_header">
                                    <span class="font-weight-bold">{$btr->general_categories|escape}</span>
                                </div>
                                <div class="banner_card_block">
                                    <select name="categories[]" class="selectpicker" multiple="multiple" data-selected-text-format="count">
                                        <option value='0' {if !$banner->category_selected || 0|in_array:$banner->category_selected}selected{/if}>{$btr->banner_hide|escape}</option>
                                        {function name=category_select level=0}
                                            {foreach from=$categories item=category}
                                                <option value="{$category->id}" {if $selected && $category->id|in_array:$selected}selected{/if}>{section name=sp loop=$level}&nbsp;{/section}{$category->name|escape}</option>
                                                {category_select categories=$category->subcategories selected=$banner->category_selected  level=$level+1}
                                            {/foreach}
                                        {/function}
                                        {category_select categories=$categories selected=$banner->category_selected}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="banner_card">
                                <div class="banner_card_header">
                                    <span class="font-weight-bold">{$btr->general_brands|escape}</span>
                                </div>
                                <div class="banner_card_block">
                                    <select name="brands[]" class="selectpicker" multiple="multiple" data-selected-text-format="count">
                                        <option value='0' {if !$banner->brand_selected || 0|in_array:$banner->brand_selected}selected{/if}>{$btr->banner_hide|escape}</option>
                                        {foreach from=$brands item=brand}
                                            <option value='{$brand->id}' {if $banner->brand_selected && $brand->id|in_array:$banner->brand_selected}selected{/if}>{$brand->name|escape}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->banner_settings_head|escape}
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="permission_block">
                        <div class="permission_boxes row">
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_as_slider|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="settings[as_slider]" value='1' type="checkbox" {if (isset($banner->settings.as_slider) && !empty($banner->settings.as_slider)) || !$banner->id}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_autoplay|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="settings[autoplay]" value='1' type="checkbox" {if (isset($banner->settings.autoplay) && !empty($banner->settings.autoplay)) || !$banner->id}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_loop|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="settings[loop]" value='1' type="checkbox" {if isset($banner->settings.loop) && !empty($banner->settings.loop)}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_nav|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="settings[nav]" value='1' type="checkbox" {if isset($banner->settings.nav) && !empty($banner->settings.nav)}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_dots|escape}</span>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="settings[dots]" value='1' type="checkbox" {if isset($banner->settings.dots) && !empty($banner->settings.dots)}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 text-muted">
                                <div class="permission_box">
                                    <span class="switch_label">{$btr->banner_settings_rotation_speed}</span>
                                    <i class="fn_tooltips" title="{$btr->banner_settings_rotation_speed_title|escape}">
                                        {include file='svg_icon.tpl' svgId='icon_tooltips'}
                                    </i>
                                    <input class="form-control" style="width: 80px;margin-left: 10px" name="settings[rotation_speed]" type="text" pattern="^[0-9]+$" required
                                           value="{if isset($banner->settings.rotation_speed) && !empty($banner->settings.rotation_speed)}{$banner->settings.rotation_speed}{else}2500{/if}" />
                                </div>
                            </div>
                         </div>
                    </div>
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