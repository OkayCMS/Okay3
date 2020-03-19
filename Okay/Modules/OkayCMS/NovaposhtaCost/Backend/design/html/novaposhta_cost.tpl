{$meta_title = $btr->settings_np scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="heading_page">{$btr->settings_np|escape}</div>
    </div>
    <div class="col-lg-5 col-md-5 float-xs-right"></div>
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
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_np_key}</div>
                            <div class="mb-1">
                                <input type="text" name="newpost_key" value="{$settings->newpost_key}" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_np_weight}*</div>
                            <div class="mb-1">
                                <input type="text" name="newpost_weight" value="{$settings->newpost_weight}" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_np_volume}</div>
                            <div class="mb-1">
                                <input type="text" name="newpost_volume" value="{$settings->newpost_volume}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_np_city}*</div>
                            <div class="mb-1">
                                <select name="newpost_city" class="selectpicker form-control" data-live-search="true">
                                    {$newpost_cities}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_np_currency}*</div>
                            <div class="mb-1">
                                <select name="currency_id" class="selectpicker form-control" data-live-search="false">
                                    {foreach $all_currencies as $c}
                                        <option value="{$c->id}"{if $c->id == $settings->newpost_currency_id} selected{/if}>{$c->name} ({$c->code})</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label boxes_inline">{$btr->settings_np_include_volume}</div>
                            <div class="boxes_inline">
                                <div class="okay_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="newpost_use_volume" value='1' type="checkbox" {if $settings->newpost_use_volume}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label boxes_inline">{$btr->settings_np_include_assessed}</div>
                            <div class="boxes_inline">
                                <div class="okay_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="newpost_use_assessed_value" value='1' type="checkbox" {if $settings->newpost_use_assessed_value}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

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

        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="toggle_body_wrap on fn_card">

                    <div class="heading_page">{$btr->payment_np_cash_on_delivery_type}</div>

                    <div class="okay_list products_list fn_sort_list">

                        {*Шапка таблицы*}
                        <div class="okay_list_head">
                            <div class="okay_list_boding okay_list_drag"></div>
                            <div class="okay_list_heading okay_list_photo">{$btr->general_photo|escape}</div>
                            <div class="okay_list_heading okay_list_brands_name">{$btr->payment_np_payment_method_name|escape}</div>
                            <div class="okay_list_heading okay_list_close"></div>
                            <div class="okay_list_heading okay_list_setting"></div>
                            <div class="okay_list_heading okay_list_status" style="width: 200px;">{$btr->payment_np_cash_on_delivery|escape}</div>
                        </div>

                        <div class="okay_list_body sort_extended">
                            {foreach $payment_methods as $payment_method}
                                <div class="fn_step-1 fn_row okay_list_body_item fn_sort_item">
                                    <div class="okay_list_row ">
                                        <div class="okay_list_boding okay_list_drag"></div>

                                        <div class="okay_list_boding okay_list_photo">
                                            {if $payment_method->image}
                                                <img src="{$payment_method->image|resize:55:55:false:$config->resized_payments_dir}" alt="" /></a>
                                            {else}
                                                <img height="55" width="55" src="design/images/no_image.png"/>
                                            {/if}
                                        </div>

                                        <div class="okay_list_boding okay_list_brands_name">
                                            {$payment_method->name|escape}
                                        </div>

                                        <div class="okay_list_boding okay_list_close"></div>
                                        <div class="okay_list_setting"></div>

                                        <div class="okay_list_boding okay_list_status" style="width: 200px;">
                                            <label class="switch switch-default ">
                                                <input class="switch-input fn_ajax_action {if $payment_method->novaposhta_cost__cash_on_delivery}fn_active_class{/if}" data-controller="payment" data-action="novaposhta_cost__cash_on_delivery" data-id="{$payment_method->id}" name="novaposhta_cost__cash_on_delivery" value="1" type="checkbox"  {if $payment_method->novaposhta_cost__cash_on_delivery}checked=""{/if}/>
                                                <span class="switch-label"></span>
                                                <span class="switch-handle"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>