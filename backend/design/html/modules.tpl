{* Title *}
{$meta_title=$btr->modules_list_title scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->modules_list_title|escape}
            </div>
        </div>
    </div>
</div>

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    {if $modules}
    <form class="fn_form_list" method="post">
        <div class="okay_list products_list fn_sort_list">
            <input type="hidden" name="session_id" value="{$smarty.session.id}">
            {*Шапка таблицы*}
            <div class="okay_list_head">
                <div class="okay_list_boding okay_list_drag"></div>
                <div class="okay_list_heading okay_list_check">
                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                    <label class="okay_ckeckbox" for="check_all_1"></label>
                </div>
                <div class="okay_list_heading okay_list_delivery_name">{$btr->general_name|escape}</div>
                <div class="okay_list_heading okay_list_delivery_condit"></div>
                <div class="okay_list_heading okay_list_delivery_photo">{$btr->module_type|escape}</div>
                <div class="okay_list_heading okay_list_status">{$btr->general_enable|escape}</div>
                <div class="okay_list_heading okay_list_close"></div>
            </div>

            {*Параметры элемента*}
            <div class="deliveries_wrap okay_list_body sortable">
                {foreach $modules as $module}
                <div class="fn_row okay_list_body_item fn_sort_item">
                    <div class="okay_list_row">
                        <input type="hidden" name="positions[{$module->id}]" value="{$module->position}">

                        <div class="okay_list_boding okay_list_drag move_zone">
                            {if $module->status !== 'Not Installed'}{include file='svg_icon.tpl' svgId='drag_vertical'}{/if}
                        </div>

                        <div class="okay_list_boding okay_list_check">
                            <input class="hidden_check" type="checkbox" id="id_{$module->id}" name="check[]" value="{$module->id}"/>
                            <label class="okay_ckeckbox" for="id_{$module->id}"></label>
                        </div>
                        <div class="okay_list_boding okay_list_delivery_name">
                            {if $module->backend_main_controller}
                                <a href="{url controller=[{$module->vendor},{$module->module_name},{$module->backend_main_controller}] id=$module->id return=$smarty.server.REQUEST_URI}">
                                    {$module->vendor|escape}/{$module->module_name|escape}
                                </a>
                            {else}
                                {$module->vendor|escape}/{$module->module_name|escape}
                            {/if}
                        </div>
                        <div class="okay_list_boding okay_list_delivery_condit"></div>
                        <div class="okay_list_boding okay_list_delivery_photo">{if $module->type}{$module->type}{else}{$btr->not_used_module_type}{/if}</div>
                        <div class="okay_list_boding okay_list_status">
                            {*visible*}
                            {if $module->status === 'Not Installed'}
                                <button class="btn" name="install_module" value="{$module->vendor}/{$module->module_name}">{$btr->install_module}</button>
                            {else}
                            <label class="switch switch-default">
                                <input class="switch-input fn_ajax_action {if $module->enabled}fn_active_class{/if}" data-controller="module" data-action="enabled" data-id="{$module->id}" name="enabled" value="1" type="checkbox"  {if $module->enabled}checked=""{/if}/>
                                <span class="switch-label"></span>
                                <span class="switch-handle"></span>
                            </label>
                            {/if}
                        </div>

                        <div class="okay_list_boding okay_list_close">
                            {*delete*}
                            <button data-hint="{$btr->general_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                {include file='svg_icon.tpl' svgId='delete'}
                            </button>
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>

            {*Блок массовых действий*}
            <div class="okay_list_footer fn_action_block">
                <div class="okay_list_foot_left">
                    <div class="okay_list_boding okay_list_drag"></div>
                    <div class="okay_list_heading okay_list_check">
                        <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                        <label class="okay_ckeckbox" for="check_all_2"></label>
                    </div>
                    <div class="okay_list_option">
                        <select name="action" class="selectpicker">
                            <option value="enable">{$btr->general_do_enable|escape}</option>
                            <option value="disable">{$btr->general_do_disable|escape}</option>
                            <option value="delete">{$btr->general_delete|escape}</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn_small btn_blue">
                    {include file='svg_icon.tpl' svgId='checked'}
                    <span>{$btr->general_apply|escape}</span>
                </button>
            </div>
        </div>
    </form>
    {else}
    <div class="heading_box mt-1">
        <div class="text_grey">{$btr->payment_methods_no|escape}</div>
    </div>
    {/if}
</div>
