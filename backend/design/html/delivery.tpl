{if $delivery->id}
    {$meta_title = $delivery->name scope=global}
{else}
    {$meta_title = $btr->delivery_new scope=global}
{/if}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$delivery->id}
                    {$btr->delivery_add|escape}
                {else}
                    {$delivery->name|escape}
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
                        {$btr->delivery_added|escape}
                    {elseif $message_success == 'updated'}
                        {$btr->delivery_updated|escape}
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
<form method="post" enctype="multipart/form-data" class="fn_fast_button">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />

    <div class="row">
        <div class="col-xs-12">
            <div class="boxed">
                <div class="row d_flex">
                    <div class="col-lg-10 col-md-9 col-sm-12">
                        {*Название элемента сайта*}
                        <div class="heading_label">
                            {$btr->general_name|escape}
                            <i class="fn_tooltips" title="{$btr->tooltip_general_name_delivery|escape}">
                                {include file='svg_icon.tpl' svgId='icon_tooltips'}
                            </i>
                        </div>
                        <div class="form-group">
                            <input class="form-control mb-h" name="name" type="text" value="{$delivery->name|escape}"/>
                            <input name="id" type="hidden" value="{$delivery->id|escape}"/>
                        </div>
                        {get_design_block block="delivery_general"}
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-12">
                        <div class="activity_of_switch">
                            <div class="activity_of_switch_item"> {* row block *}
                                <div class="okay_switch clearfix">
                                    <label class="switch_label">{$btr->general_enable|escape}</label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="enabled" value='1' type="checkbox" id="visible_checkbox" {if $delivery->enabled}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        {get_design_block block="delivery_switch_checkboxes"}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-4 col-md-12 pr-0">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->general_image|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <ul class="brand_images_list">
                        <li class="brand_image_item fn_image_block">
                            {if $delivery->image}
                                <input type="hidden" class="fn_accept_delete" name="delete_image" value="">
                                <div class="fn_parent_image">
                                    <div class="category_image image_wrapper fn_image_wrapper text-xs-center">
                                        <a href="javascript:;" class="fn_delete_item remove_image"></a>
                                        <img src="{$delivery->image|resize:300:120:false:$config->resized_deliveries_dir}" alt="" />
                                    </div>
                                </div>
                            {else}
                                <div class="fn_parent_image"></div>
                            {/if}
                            <div class="fn_upload_image dropzone_block_image {if $delivery->image} hidden{/if}">
                                <i class="fa fa-plus font-5xl" aria-hidden="true"></i>
                                <input class="dropzone_image" name="image" type="file" />
                            </div>
                            <div class="category_image image_wrapper fn_image_wrapper fn_new_image text-xs-center hidden">
                                <a href="javascript:;" class="fn_delete_item remove_image"></a>
                                <img src="" alt="" />
                            </div>
                        </li>
                    </ul>
                </div>
                {get_design_block block="delivery_image"}
            </div>
        </div>
        <div class="col-lg-8 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->delivery_type|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <button type="button" class="btn btn-small btn-outline-warning fn_type_delivery delivery_type {if $delivery->price > 0}active{/if}" data-type="paid">
                                {$btr->delivery_paid|escape}
                            </button>
                            <button type="button" class="btn btn-small btn-outline-warning fn_type_delivery delivery_type {if $delivery->price == 0 && !$delivery->separate_payment}active{/if}" data-type="free">
                                {$btr->deliveries_free|escape}
                            </button>
                            <button type="button" class="btn btn-small btn-outline-warning fn_type_delivery delivery_type {if $delivery->separate_payment}active{/if}" data-type="delivery">
                                {$btr->general_paid_separately|escape}
                            </button>
                            <input type="hidden" name="delivery_type" />
                        </div>
                    </div>
                    <div class="row fn_delivery_option {if $delivery->price == 0}hidden{/if} mt-1">
                        <div class="col-lg-12 col-md-12">
                            <div class="delivery_inline_block mt-1">
                                <div class="input-group">
                                    <span class="boxes_inline heading_label">{$btr->delivery_cost|escape}</span>
                                    <span class="boxes_inline bnr_dl_price">
                                        <div class="input-group">
                                            <input name="price" class="form-control" type="text" value="{$delivery->price|escape}" />
                                            <span class="input-group-addon">{$currency->sign|escape}</span>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="delivery_inline_block mt-1">
                                <div class="input-group">
                                    <span class="boxes_inline heading_label">{$btr->deliveries_free_from|escape}</span>
                                    <span class="boxes_inline bnr_dl_price">
                                        <div class="input-group">
                                            <div class="input-group">
                                                <input name="free_from" class="form-control" type="text" value="{$delivery->free_from|escape}" />
                                                <span class="input-group-addon">{$currency->sign|escape}</span>
                                            </div>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input class="hidden" name="separate_payment" type="checkbox" value="1" {if $delivery->separate_payment}checked{/if} />
                    {get_design_block block="delivery_price_block"}
                </div>
            </div>
        </div>
    </div>

    {$block = {get_design_block block="delivery_custom_block"}}
    {if !empty($block)}
        <div class="row custom_block">
            {$block}
        </div>
    {/if}
    
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->delivery_type|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                <div class="toggle_body_wrap on fn_card">

                    <div class="row">
                        <div class="col-lg-6 pr-0">
                            <div class="form-group clearfix">
                                <div class="heading_label" >{$btr->payment_method_type|escape}</div>
                                <select name="module_id" class="selectpicker">
                                    <option value='null'>{$btr->payment_method_manual|escape}</option>
                                    {foreach $delivery_modules as $delivery_module}
                                        <option value="{$delivery_module->id}" {if $delivery->module_id == $delivery_module->id}selected{/if} >{$delivery_module->vendor|escape}/{$delivery_module->module_name|escape}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    {foreach $delivery_modules as $delivery_module}
                        <div class="row fn_module_settings" {if $delivery_module->id != $delivery->module_id}style="display:none;"{/if} data-module_id="{$delivery_module->id}">
                            <div class="col-lg-12 col-md-12 heading_box">{$delivery_module->vendor|escape}/{$delivery_module->module_name|escape}</div>
                            {foreach $delivery_module->settings as $setting}
                                {$variable_name = $setting->variable}
                                {if !empty($setting->options) && $setting->options|@count>1}
                                    <div class="col-lg-6">
                                        <div class="form-group clearfix">
                                            <div class="heading_label" >{$setting->name|escape}</div>
                                            <div class="">
                                                <select name="delivery_settings[{$setting->variable}]" class="selectpicker">
                                                    {foreach $setting->options as $option}
                                                        <option value="{$option->value}" {if isset($delivery->delivery_settings[$setting->variable]) && $option->value==$delivery->delivery_settings[$setting->variable]}selected{/if}>{$option->name|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                {else}
                                    <div class="col-lg-6" style="height: 75px;">
                                        <div class="form-group clearfix">
                                            
                                            {if $setting->type == 'checkbox'}
                                                <label class="heading_label" for="{$setting->variable}">{$setting->name|escape}</label>
                                                <div class="boxes_inline">
                                                    
                                                    <input name="delivery_settings[{$setting->variable}]" class="hidden_check" type="{$setting->type|escape}" value="{$setting->value|escape}" {if $setting->value == $delivery->delivery_settings[$setting->variable]}checked{/if} id="{$setting->variable}"/>
                                                    <label class="okay_ckeckbox" for="{$setting->variable}"></label>
                                                </div>
                                            {else}
                                                <label class="heading_label" for="{$setting->variable}">{$setting->name|escape}</label>
                                                <div>
                                                    <input name="delivery_settings[{$setting->variable}]" class="form-control" type="{$setting->type|escape}" value="{if isset($delivery->delivery_settings[$setting->variable])}{$delivery->delivery_settings[$setting->variable]|escape}{/if}" id="{$setting->variable}"/>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->delivery_payments|escape}
                    <i class="fn_tooltips" title="{$btr->tooltip_delivery_payments|escape}">
                        {include file='svg_icon.tpl' svgId='icon_tooltips'}
                    </i>
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row wrap_payment_item">
                        {foreach $payment_methods as $payment_method}
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="payment_item">
                                    <input class="hidden_check" id="id_{$payment_method->id}" value="{$payment_method->id}" {if in_array($payment_method->id, $delivery->delivery_payments)}checked{/if} type="checkbox" name="delivery_payments[]">
                                    <label for="id_{$payment_method->id}" class="okay_ckeckbox {if in_array($payment_method->id, $delivery->delivery_payments)}active_payment{/if}">
                                        <span class="payment_img_wrap">
                                            {if $payment_method->image}
                                                <img src="{$payment_method->image|resize:50:50:false:$config->resized_payments_dir}">
                                            {else}
                                                <img width="50" src="design/images/no_image.png"/>
                                            {/if}
                                        </span>
                                        <span class="payment_name_wrap">{$payment_method->name|escape}</span>

                                    </label>
                                </div>
                            </div>
                            {if $payment_method@iteration %3 == 0}
                                <div class="col-xs-12 clearfix"></div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Описание элемента*}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed match fn_toggle_wrap tabs">
                <div class="heading_tabs">
                    <div class="tab_navigation">
                        <a href="#tab1" class="tab_navigation_link">{$btr->delivery_description|escape}</a>
                    </div>
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="icon-arrow-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="tab_container">
                        <div id="tab1" class="tab">
                            <textarea name="description" class="editor_small">{$delivery->description|escape}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                   <div class="col-lg-12 col-md-12 mt-1">
                        <button type="submit" class="btn btn_small btn_blue float-md-right">
                            {include file='svg_icon.tpl' svgId='checked'}
                            <span>{$btr->general_apply|escape}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
{* Подключаем Tiny MCE *}
{include file='tinymce_init.tpl'}

<script>

    $(function() {
        $('div.fn_module_settings').filter(':hidden').find("input, select, textarea").attr("disabled", true);

        $('select[name=module_id]').on('change',function(){
            $('div.fn_module_settings').hide().find("input, select, textarea").attr("disabled", true);
            $('div.fn_module_settings[data-module_id="'+$(this).val()+'"]').show().find("input, select, textarea").attr("disabled", false);
            $('div.fn_module_settings[data-module_id="'+$(this).val()+'"]').find('select').selectpicker('refresh');
        });
    });
    
    $(document).on("click", ".fn_type_delivery", function () {
        var action = $(this).data("type");
        $(".delivery_type").removeClass("active");

        switch(action) {
            case 'paid':
                $(".fn_delivery_option").removeClass("hidden");
                $("input[name=separate_payment]").removeAttr("checked");
                $("input[name=delivery_type]").val('paid');
                $(this).addClass("active");
               break;
            case 'free':
                $(".fn_delivery_option").addClass("hidden");
                $("input[name=separate_payment]").removeAttr("checked");
                $("input[name=delivery_type]").val('free');
                $(this).addClass("active");
                break;
            case 'delivery':
                $(".fn_delivery_option").addClass("hidden");
                $("input[name=delivery_type]").val('separate_payment');
                $("input[name=separate_payment]").trigger("click");
                $(this).addClass("active");
                break;
        }
    });
</script>
