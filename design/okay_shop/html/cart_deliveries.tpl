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
            <div class="delivery__item">
				<label class="checkbox delivery__label{if $delivery@first} active{/if}" for="deliveries_{$delivery->id}">
					<input class="checkbox__input delivery__input" id="deliveries_{$delivery->id}" onclick="change_payment_method({$delivery->id})" type="radio" name="delivery_id" value="{$delivery->id}" {if $delivery_id==$delivery->id || $delivery@first} checked{/if} />
					<svg class="checkbox__icon" viewBox="0 0 20 20">
						<path class="checkbox__mark" fill="none" d="M4 10 l5 4 8-8.5"></path>
					</svg>
                    <div class="delivery__name">
						{$delivery->name|escape}

						{if $cart->total_price < $delivery->free_from && $delivery->price>0 && !$delivery->separate_payment}
							<span class="delivery__name_price">({$delivery->price|convert} {$currency->sign|escape})</span>
						{elseif $delivery->separate_payment}
							<span class="delivery__name_price" data-language="cart_free">({$lang->cart_paid_separate})</span>
						{elseif $cart->total_price >= $delivery->free_from && !$delivery->separate_payment}
							<span class="delivery__name_price" data-language="cart_free">({$lang->cart_free})</span>
						{/if}
					</div>
					{if $delivery->image}
						<div class="delivery__image">
							<img src="{$delivery->image|resize:40:25:false:$config->resized_deliveries_dir}" alt="{$delivery->name|escape}"/>
						</div>
					{/if}
                </label>
                
				{if $delivery->description}
					<div class="delivery__description">
						{$delivery->description}
					</div>
                {/if}
            </div>
        {/foreach}
    </div>
    </div> 
    
    {* Payment methods *}
    {foreach $deliveries as $delivery}
        {if $delivery->payment_methods}
           
            <div class="fn_delivery_payment" id="fn_delivery_payment_{$delivery->id}"{if $delivery@iteration != 1} style="display:none"{/if}>
            <div class="block form form_cart">
				<div class="form__header">
					<div class="form__title">
					{include file="svg.tpl" svgId="money_icon"}
					<span data-language="cart_payment">{$lang->cart_payment}</span>
					</div>
				</div>
                <div class="delivery form__group">
                    {foreach $delivery->payment_methods as $payment_method}
                        <div class="delivery__item">
							<label class="checkbox delivery__label{if $payment_method@first} active{/if}" for="payment_{$delivery->id}_{$payment_method->id}">
								<input class="checkbox__input delivery__input" id="payment_{$delivery->id}_{$payment_method->id}" type="radio" name="payment_method_id" value="{$payment_method->id}"{if $delivery@first && $payment_method@first} checked{/if} />
								<svg class="checkbox__icon" viewBox="0 0 20 20">
									<path class="checkbox__mark" fill="none" d="M4 10 l5 4 8-8.5"></path>
								</svg>
                                <div class="delivery__name">
                                    {$total_price_with_delivery = $cart->total_price}
                                    {if !$delivery->separate_payment && $cart->total_price < $delivery->free_from}
                                        {$total_price_with_delivery = $cart->total_price + $delivery->price}
                                    {/if}

                                    {$payment_method->name|escape} {$lang->cart_deliveries_to_pay}
                                    <span class="delivery__name_price">({$total_price_with_delivery|convert:$payment_method->currency_id} {$all_currencies[$payment_method->currency_id]->sign|escape})</span>
                                </div>
								{if $payment_method->image}
									<div class="delivery__image">
										<img src="{$payment_method->image|resize:40:25:false:$config->resized_payments_dir}" alt="{$payment_method->name|escape}"/>
									</div>
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
    {/foreach}    
{/if}
