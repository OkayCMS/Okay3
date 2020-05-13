{if $deliveries}
   	<div class="block form form_cart">
                              
		{* Delivery *}
		<div class="form__header">
			<div class="form__title">
				{include file="svg.tpl" svgId="delivery_icon"}
				<span data-language="cart_delivery">{$lang->cart_delivery}</span>
			</div>
		</div>
		<div class="delivery form__group">
			{foreach $deliveries as $delivery}
				<div class="delivery__item fn_delivery_item">
					<label class="checkbox delivery__label{if $active_delivery->id == $delivery->id} active{/if}" for="deliveries_{$delivery->id}">
						{*NOTICE: Обратите внимание, data-total_price хранится в основной валюте сайта*}
						<input class="checkbox__input delivery__input" 
							    id="deliveries_{$delivery->id}"
							   onchange="okay.change_payment_method(); update_delivery_module_data();"
							   data-module_id="{$delivery->module_id}"
							   data-payment_method_ids="{implode(',', $delivery->payment_methods_ids)}"
							   data-total_price="{$delivery->total_price_with_delivery}"
							   data-delivery_price="{$delivery->price}"
							   data-is_free_delivery="{$delivery->is_free_delivery|intval}"
							   data-separate_payment="{$delivery->separate_payment|intval}"
							   data-hide_front_delivery_price="{$delivery->hide_front_delivery_price|intval}"
							   type="radio"
							   name="delivery_id" 
							   value="{$delivery->id}"
								{if $active_delivery->id == $delivery->id} checked{/if} />
						<svg class="checkbox__icon" viewBox="0 0 20 20">
							<path class="checkbox__mark" fill="none" d="M4 10 l5 4 8-8.5"></path>
						</svg>
						<span class="delivery__name">
							{$delivery->name|escape}
							<span class="delivery__name_price {if $delivery->hide_front_delivery_price}hidden{/if}">(<span class="fn_delivery_price">{$delivery->delivery_price_text}</span>)</span>
						</span>
						{if $delivery->image}
							<span class="delivery__image">
								<img src="{$delivery->image|resize:80:30:false:$config->resized_deliveries_dir}" alt="{$delivery->name|escape}"/>
							</span>
						{/if}
					</label>
					
					{$block = {get_design_block block='front_cart_delivery' vars=['delivery' => $delivery]}}
					{if $delivery->description || $block}
						<div class="delivery__description">
							{$delivery->description}
							{if $block}
								<div class="fn_delivery_module_html">
									{$block}
								</div>
							{/if}
						</div>
					{/if}
				</div>
			{/foreach}
		</div>
    </div> 
    
    {* Payment methods *}
	{if $payment_methods}
		<div class="fn_payments_block"{if !$active_delivery->payment_methods_ids} style="display: none;" {/if}>
		   	<div class="block form form_cart">
				<div class="form__header">
					<div class="form__title">
					{include file="svg.tpl" svgId="money_icon"}
					<span data-language="cart_payment">{$lang->cart_payment}</span>
					</div>
				</div>
				<div class="delivery form__group">
					{foreach $payment_methods as $payment_method}
						<div class="payment_method__item fn_payment_method__item fn_payment_method__item_{$payment_method->id}"{if !in_array($payment_method->id, $active_delivery->payment_methods_ids)} style="display: none;" {/if}>
							<label class="checkbox delivery__label{if $active_payment->id==$payment_method->id} active{/if}" for="payment_{$payment_method->id}">
								<input class="checkbox__input delivery__input" id="payment_{$payment_method->id}" type="radio" name="payment_method_id" data-currency_id="{$payment_method->currency_id}" value="{$payment_method->id}"{if $active_payment->id==$payment_method->id} checked{/if} />
								<svg class="checkbox__icon" viewBox="0 0 20 20">
									<path class="checkbox__mark" fill="none" d="M4 10 l5 4 8-8.5"></path>
								</svg>
								<span class="delivery__name">
	
									{$payment_method->name|escape}{$lang->cart_deliveries_to_pay}
									<span class="delivery__name_price">(<span class="fn_payment_price">{$active_delivery->total_price_with_delivery|convert:$payment_method->currency_id}</span> {$all_currencies[$payment_method->currency_id]->sign|escape})</span>
								</span>
								{if $payment_method->image}
									<span class="delivery__image">
										<img src="{$payment_method->image|resize:80:30:false:$config->resized_payments_dir}" alt="{$payment_method->name|escape}"/>
									</span>
								{/if}
							</label>
							
							{if $payment_method->description}
								<div class="delivery__description">
									{$payment_method->description}
								</div>
							{/if}
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	{/if}
{/if}
