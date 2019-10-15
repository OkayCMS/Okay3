{$meta_title = $btr->rozetka_xml|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->okaycms__integration_ic__description_title|escape}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="boxed">
            <div class="row d_flex">
                <div class="col-lg-12 col-md-12">
                    <div class="boxed boxed_attention">
                        <div class="heading_box">
                            {$btr->general_caution|escape}!
                        </div>
                        <div class="text_box">
                            <div class="mb-1">
                                {$btr->okaycms__integration_ic__description_part_1} (<b>{url_generator route="integration_1c" absolute=1}</b>) {$btr->okaycms__integration_ic__description_part_2}.
                            </div>
                            <div class="mb-1">
                                {$btr->okaycms__integration_ic__description_part_3} <b>OkayCMS</b>: <a href="https://okay-cms.com/article/instruktsiya-po-nastrojke-obmena-dannymi-sajta-s-1s-8h-na-primere-konfiguratsii-ut-23" target="_blank">https://okay-cms.com/article/instruktsiya-po-nastrojke-obmena-dannymi-sajta-s-1s-8h-na-primere-konfiguratsii-ut-23</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {*Блок статусов заказов*}
    <div class="col-lg-12 col-md-12 pr-0">
        <div class="boxed fn_toggle_wrap">
            <div class="toggle_body_wrap on fn_card">
                <form class="fn_form_list" method="post">
                    <input type="hidden" value="status" name="status">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">
                    <div class="okay_list">
                        {*Шапка таблицы*}
                        <div class="okay_list_head">
                            <div class="okay_list_heading okay_list_order_stg_sts_name_1c">{$btr->general_name|escape}</div>
                            <div class="okay_list_heading okay_list_order_stg_sts_status2">{$btr->order_settings_1c_action|escape}</div>
                            <div class="okay_list_heading okay_list_close"></div>
                        </div>
                        <div class="fn_status_list fn_sort_list okay_list_body sortable">
                            {if $orders_statuses}
                                {foreach $orders_statuses as $order_status}
                                    <div class="fn_row okay_list_body_item">
                                        <div class="okay_list_row fn_sort_item">
                                            <input type="hidden" name="id[]" value="{$order_status->id}">

                                            <div class="okay_list_boding okay_list_order_stg_sts_name_1c">
                                                <span>{$order_status->name|escape}</span>

                                                {if $is_mobile == true}
                                                    <div class="hidden-sm-up mt-q">
                                                        <select name="status_1c[{$order_status->id}]" class="selectpicker col-xs-12 px-0">
                                                            <option value="not_use" {if $order_status->status_1c == ''}selected=""{/if}>{$btr->order_settings_1c_action|escape}: {$btr->order_settings_1c_not_use|escape}</option>
                                                            <option value="new" {if $order_status->status_1c == 'new'}selected=""{/if}>{$btr->order_settings_1c_action|escape}: {$btr->order_settings_1c_new|escape}</option>
                                                            <option value="accepted" {if $order_status->status_1c == 'accepted'}selected=""{/if}>{$btr->order_settings_1c_action|escape}: {$btr->order_settings_1c_accepted|escape}</option>
                                                            <option value="to_delete" {if $order_status->status_1c == 'to_delete'}selected=""{/if}>{$btr->order_settings_1c_action|escape}: {$btr->order_settings_1c_to_delete|escape}</option>
                                                        </select>
                                                    </div>
                                                {/if}
                                            </div>

                                            {if $is_mobile == false}
                                                <div class="okay_list_boding okay_list_order_stg_sts_status2">
                                                    <select name="status_1c[{$order_status->id}]" class="selectpicker col-xs-12 px-0">
                                                        <option value="not_use" {if $order_status->status_1c == ''}selected=""{/if}>{$btr->order_settings_1c_not_use|escape}</option>
                                                        <option value="new" {if $order_status->status_1c == 'new'}selected=""{/if}>{$btr->order_settings_1c_new|escape}</option>
                                                        <option value="accepted" {if $order_status->status_1c == 'accepted'}selected=""{/if}>{$btr->order_settings_1c_accepted|escape}</option>
                                                        <option value="to_delete" {if $order_status->status_1c == 'to_delete'}selected=""{/if}>{$btr->order_settings_1c_to_delete|escape}</option>
                                                    </select>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}

                            <div class="fn_row fn_new_status fn_sort_item okay_list_body">
                                <div class="fn_row okay_list_body_item">
                                    <div class="okay_list_row fn_sort_item">
                                        <div class="okay_list_boding okay_list_drag"></div>
                                        <div class="okay_list_boding okay_list_order_stg_sts_name_1c">
                                            <input type="text" class="form-control" name="new_name[]" value="">
                                            {if $is_mobile == true}
                                                <div class="hidden-sm-up mt-q">
                                                    <select name="new_is_close[]" class="selectpicker col-xs-12 px-0">
                                                        <option value="1">{$btr->order_settings_reduse_products|escape}</option>
                                                        <option value="0">{$btr->order_settings_not_reduse_products|escape}</option>
                                                    </select>
                                                </div>
                                                <div class="hidden-sm-up mt-q">
                                                    <select name="new_status_1c[]" class="selectpicker col-xs-12 px-0">
                                                        <option value="not_use">{$btr->order_settings_1c_not_use|escape}</option>
                                                        <option value="new">{$btr->order_settings_1c_new|escape}</option>
                                                        <option value="accepted">{$btr->order_settings_1c_accepted|escape}</option>
                                                        <option value="to_delete">{$btr->order_settings_1c_to_delete|escape}</option>
                                                    </select>
                                                </div>
                                            {/if}
                                        </div>
                                        {if $is_mobile == false}
                                            <div class="okay_list_boding okay_list_order_stg_sts_status">
                                                <select name="new_is_close[]" class="selectpicker">
                                                    <option value="1">{$btr->order_settings_reduse_products|escape}</option>
                                                    <option value="0">{$btr->order_settings_not_reduse_products|escape}</option>
                                                </select>
                                            </div>
                                            <div class="okay_list_boding okay_list_order_stg_sts_status2">
                                                <select name="new_status_1c[]" class="selectpicker col-xs-12 px-0">
                                                    <option value="not_use">{$btr->order_settings_1c_not_use|escape}</option>
                                                    <option value="new">{$btr->order_settings_1c_new|escape}</option>
                                                    <option value="accepted">{$btr->order_settings_1c_accepted|escape}</option>
                                                    <option value="to_delete">{$btr->order_settings_1c_to_delete|escape}</option>
                                                </select>
                                            </div>
                                        {/if}
                                        <div class="okay_list_boding okay_list_order_stg_sts_label">
                                            <input name="new_color[]" value="" class="hidden">
                                            <span data-hint="{$btr->order_settings_select_colour|escape}" class="fn_color label_color_item hint-bottom-middle-t-info-s-small-mobile  hint-anim"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {*Блок массовых действий*}
                        <div class="okay_list_footer">
                            <div class="okay_list_foot_left"></div>
                            <button type="submit" value="labels" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* On document load *}
{literal}
    <link rel="stylesheet" media="screen" type="text/css" href="design/js/colorpicker/css/colorpicker.css" />
    <script type="text/javascript" src="design/js/colorpicker/js/colorpicker.js"></script>
    <script>
        $(function() {
            var new_label = $(".fn_new_label").clone(true);
            $(".fn_new_label").remove();

            var new_status = $(".fn_new_status").clone(true);
            $(".fn_new_status").remove();

            $(document).on("click", ".fn_add_Label", function () {
                clone_label = new_label.clone(true);
                $(".fn_labels_list").append(clone_label);
            });

            $(document).on("click", ".fn_add_status", function () {
                clone_status = new_status.clone(true);
                clone_status.find("select").selectpicker();
                $(".fn_status_list").append(clone_status);
            });

            $(document).on("mouseenter click", ".fn_color", function () {
                var elem = $(this);
                elem.ColorPicker({
                    onChange: function (hsb, hex, rgb) {
                        elem.css('backgroundColor', '#' + hex);
                        elem.prev().val(hex);
                    }
                });
            });

        });
    </script>
{/literal}

