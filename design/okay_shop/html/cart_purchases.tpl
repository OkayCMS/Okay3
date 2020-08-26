{*NOTICE: Обратите внимание, data-total_purchases_price хранится в основной валюте сайта*}
<div class="fn_purchases_wrap" data-total_purchases_price="{$cart->total_price}">
{foreach $cart->purchases as $purchase}
    <div class="purchase__item d-flex align-items-start">
        {* Product image *}
        <div class="purchase__image d-flex">
            <a href="{url_generator route="product" url=$purchase->product->url}">
                {if $purchase->product->image}
                    <img src="{$purchase->product->image->filename|resize:70:70}" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}"/>
                {else}
                    <div class="purchase__no_image d-flex align-items-start">
                        {include file="svg.tpl" svgId="no_image"}
                    </div>
                {/if}
            </a>
        </div>
        <div class="purchase__content">
            {* Product name *}
            <div class="purchase__name">
                <a class="purchase__name_link" href="{url_generator route="product" url=$purchase->product->url}">{$purchase->product->name|escape}</a>
                <i>{$purchase->variant->name|escape}</i>
                {if $purchase->variant->stock == 0}<span class="preorder_label">{$lang->product_pre_order}</span>{/if}
            </div>
            <div class="purchase__group">
                {* Price per unit *}
                <div class="purchase__price hidden-xs-down">
                    <div class="purchase__group_title hidden-xs-down">
                        <span data-language="cart_head_price">{$lang->cart_head_price}</span>
                    </div>
                    <div class="purchase__group_content">{($purchase->variant->price)|convert} <span class="currency">{$currency->sign}</span> {if $purchase->variant->units}/ {$purchase->variant->units|escape}{/if}</div>
                </div>
                <div class="purchase__amount">
                    <div class="purchase__group_title hidden-xs-down">
                        <span data-language="cart_head_amoun">{$lang->cart_head_amoun}</span>
                    </div>
                    <div class="fn_product_amount purchase__group_content{if $settings->is_preorder} fn_is_preorder{/if} amount">
                        <span class="fn_minus amount__minus">&minus;</span>
                        <input class="amount__input" type="text" data-id="{$purchase->variant->id}" name="amounts[{$purchase->variant->id}]" value="{$purchase->amount}" onblur="ajax_change_amount(this, {$purchase->variant->id});" data-max="{$purchase->variant->stock}">
                        <span class="fn_plus amount__plus">&plus;</span>
                    </div>
                </div>
                <div class="purchase__price_total">
                    <div class="purchase__group_title hidden-xs-down">
                        <span data-language="cart_head_total">{$lang->cart_head_total}</span>
                    </div>
                    <div class="purchase__group_content">{$purchase->meta->total_price|convert} <span class="currency">{$currency->sign}</span></div>
                </div>
            </div>
            {* Remove button *}
            <a class="purchase__remove" href="{url_generator route="cart_remove_item" variantId=$purchase->variant->id}" onclick="ajax_remove({$purchase->variant->id});return false;" title="{$lang->cart_remove}">
                {include file='svg.tpl' svgId='remove_icon'}
            </a>
        </div>
    </div>
{/foreach}
</div>