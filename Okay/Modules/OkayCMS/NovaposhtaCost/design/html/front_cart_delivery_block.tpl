{if $delivery->module_id == $np_delivery_module_id}
    <div class="m-l-2 novaposhta_div fn_delivery_novaposhta">
        <div class="np_preloader"></div>
        
        <div style="margin-bottom: 10px;">
            <label style="display: inline-block; width: 100px;"><span class="labelcity_novaposhta">{$lang->np_cart_city} </span></label>
            <input class="city_novaposhta form__input form__placeholder--focus" name="novaposhta_city" autocomplete="off" type="text" value="{$request_data.novaposhta_city|escape}" >
        </div>
        {if $np_redelivery_payments_ids}
        <div style="padding: 5px 0;">
            <label for="redelivery">{$lang->np_cart_cod} </label>
            <input name="novaposhta_redelivery" id="redelivery" value="1" type="checkbox" {if $request_data.novaposhta_redelivery == true}checked{/if} />
        </div>
        {/if}
        <div class="warehouses_novaposhta">
            <label class="" style="display: inline-block; width: 100px;">{$lang->np_cart_warehouse}</label>
            <select name="novaposhta_warehouses" tabindex="1" class="fn_select_warehouses_novaposhta" style="width: 100%;"></select>
        </div>
    
        <div class="term_novaposhta">{$lang->np_cart_term} <span></span></div>
    
        <input name="is_novaposhta_delivery" type="hidden" value="1"/>
        <input name="novaposhta_delivery_price" type="hidden" value="{$request_data.novaposhta_delivery_price}"/>
        <input name="novaposhta_delivery_term" type="hidden" value="{$request_data.novaposhta_delivery_term}"/>
        <input name="novaposhta_delivery_city_id" type="hidden" value="{$request_data.novaposhta_delivery_city_id}"/>
        <input name="novaposhta_delivery_warehouse_id" type="hidden" value="{$request_data.novaposhta_delivery_warehouse_id}"/>
    </div>
{/if}
