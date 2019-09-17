<script>

okay.max_order_amount = {$settings->max_order_amount};

</script>

<div class="fn_cart_sticky block--cart_purchases block--boxed block--border" data-margin-top="75" data-sticky-for="1024" data-sticky-class="is-sticky">
<div class="h6" data-language="cart_purchase_title">{$lang->cart_purchase_title}</div>

<div class="purchase ">
    {foreach $cart->purchases as $purchase}
        <div class="purchase__item d-flex align-items-start">
            {* Product image *}
            <div class="purchase__image d-flex">
                <a href="{url_generator route="product" url=$purchase->product->url}">
                    {if $purchase->product->image}
                        <img class="" alt="{$purchase->product->name|escape}" src="{$purchase->product->image->filename|resize:70:70}">
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
                        <div class="purchase__group_content">{($purchase->variant->price*$purchase->amount)|convert} <span class="currency">{$currency->sign}</span></div>
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

{* Coupon *}
{if $coupon_request}
    <div class="coupon">
        <div class="fn_switch coupon__title">Скидочный купон</div>
        {* Coupon error messages *}
        {if $coupon_error}
        <div class="message_error">
            {if $coupon_error == 'invalid'}
                {$lang->cart_coupon_error}
            {elseif $coupon_error == 'empty'}
                {$lang->cart_empty_coupon_error}
            {/if}
        </div>
        {/if}

        {if $cart->coupon->min_order_price > 0}
        <div class="message_success">
            {$lang->cart_coupon} {$cart->coupon->code|escape} {$lang->cart_coupon_min} {$cart->coupon->min_order_price|convert} {$currency->sign|escape}
        </div>
        {/if}

        <div class="coupon__group">
            <div class="form__group form__group--coupon {if !$coupon_error}filled{/if}">
                <input class="fn_coupon form__input form__input--coupon form__placeholder--focus" type="text" name="coupon_code" value="{$cart->coupon->code|escape}">
                <span class="form__placeholder">{$lang->cart_coupon}</span>
            </div>
            <input class="form__button form__button--coupon fn_sub_coupon" type="button" value="{$lang->cart_purchases_coupon_apply}">
        </div>
    </div>
{/if}

<div class="purchase_detail">
    {* Discount *}
    {if $user->discount}
        <div class="purchase_detail__item">
            <div class="purchase_detail__column_name">
                <div class="purchase_detail__name" data-language="cart_discount">{$lang->cart_discount}:</div>
            </div>
            <div class="purchase_detail__column_value">
                <div class="purchase_detail__price">{$user->discount}%</div>
            </div>
        </div>
    {/if}

    {* Coupon *}
    {if $coupon_request}
    {if $cart->coupon_discount > 0}
        <div class="purchase_detail__item">
            <div class="purchase_detail__column_name">
                <div class="purchase_detail__name" data-language="cart_coupon">{$lang->cart_coupon}:</div>
            </div>
            <div class="purchase_detail__column_value">
                <div class="purchase_detail__price">
                    <i>{$cart->coupon->coupon_percent|escape} %</i>
                    &minus; {$cart->coupon_discount|convert} <span class="currency">{$currency->sign|escape}</span>
                </div>
            </div>
        </div>
    {/if}
    {/if}

    <div class="purchase_detail__item">
        <div class="purchase_detail__column_name">
            <div class="purchase_detail__name purchase_detail__name--total" data-language="cart_total_price">{$lang->cart_total_price}:</div>
        </div>
        <div class="purchase_detail__column_value">
            <div class="purchase_detail__price purchase_detail__price--total">
                <span>{$cart->total_price|convert} <span class="currency">{$currency->sign|escape}</span></span>
            </div>
        </div>
    </div>
</div>
</div>