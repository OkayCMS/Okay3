{* Product preview *}
<div class="product_preview fn_product">
    <div class="fn_transfer clearfix">
        <div class="product_preview__center">
            <div class="product_preview__image fn_loader_image">
                <a class="" href="{if $controller=='Comparison'}{$product->image->filename|resize:800:600:w}{else}{url_generator route='product' url=$product->url}{/if}" {if $controller=='Comparison'}data-fancybox="group" data-caption="{$product->name|escape}"{/if}>
                    {if $product->image->filename}
                        <img class="fn_img preview_img lazy" data-src="{$product->image->filename|resize:300:180}" alt="{$product->name|escape}" title="{$product->name|escape}"/>
                    {else}
                        <div class="fn_img product_preview__no_image d-flex align-items-center justify-content-center" title="{$product->name|escape}">
                            {include file="svg.tpl" svgId="no_image"}
                        </div>
                    {/if}
                </a>

                {if $product->featured || $product->special}
                    <div class="stickers">
                        {if $product->featured}
                        <span class="sticker sticker--hit" data-language="product_sticker_hit">{$lang->product_sticker_hit}</span>
                        {/if}
                        {if $product->variant->compare_price}
                        <span class="sticker sticker--discount" data-language="product_sticker_discount">{$lang->product_sticker_discount}</span>
                        {/if}
                        {if $product->special}
                            <span class="sticker sticker--special">
                                <img class="sticker__image" src='files/special/{$product->special}' alt='{$product->special|escape}' title="{$product->special|escape}"/>
                            </span>
                        {/if}
                    </div>
                {/if}

                <div class="fn_discount_label sticker_pct{if $product->variant->price>0 && $product->variant->compare_price>0 && $product->variant->compare_price>$product->variant->price}{else} hidden{/if}">
                    {if $product->variant->price>0 && $product->variant->compare_price>0 && $product->variant->compare_price>$product->variant->price}
                    {round((($product->variant->price-$product->variant->compare_price)/$product->variant->compare_price)*100, 2)}&nbsp;%
                    {/if}
                </div>
            </div>
            <div class="product_preview__name">
                {* Product name *}
                <a class="product_preview__name_link" data-product="{$product->id}" href="{url_generator route="product" url=$product->url}">
                    {$product->name|escape}
                    <div class="product_preview__sku {if !$product->variant->sku} hidden{/if}">
                        <span data-language="product_sku">{$lang->product_sku}:</span>
                        <span class="fn_sku sku__nubmer">{$product->variant->sku|escape}</span>
                    </div>
                </a>
            </div>
            <div class="product_preview__prices">
                <div class="old_price {if !$product->variant->compare_price} hidden{/if}">
                    <span class="fn_old_price">{$product->variant->compare_price|convert}</span> <span class="currency">{$currency->sign|escape}</span>
                </div>
                <div class="price {if $product->variant->compare_price} price--red{/if}">
                    <span class="fn_price">{$product->variant->price|convert}</span> <span class="currency">{$currency->sign|escape}</span>
                </div>
            </div>
        </div>
        <div class="product_preview__bottom">
            <form class="fn_variants preview_form" action="{url_generator route="cart"}">
                <div class="product_preview__buttons">
                    {if !$settings->is_preorder}
                            {* Out of stock *}
                            <p class="fn_not_preorder product_preview__out_stock {if $product->variant->stock > 0} hidden{/if}">
                                <span data-language="out_of_stock">{$lang->out_of_stock}</span>
                            </p>
                    {else}
                        {* Pre-order *}
                        <button class="product_preview__button product_preview__button--pre_order fn_is_preorder{if $product->variant->stock > 0} hidden{/if}" type="submit" data-language="pre_order">
                            <span class="product_preview__button_text">{$lang->pre_order}</span>
                        </button>
                    {/if}
                    {* Submit cart button *}
                    <button class="product_preview__button product_preview__button--buy button--blick fn_is_stock{if $product->variant->stock < 1} hidden{/if}" type="submit">
                        <i class="mdi mdi-cart-outline"></i>
                        <span class="product_preview__button_text" data-language="add_to_cart">{$lang->add_to_cart}</span>
                    </button>
                        
                    {* Comparison *}
                    {if $controller != "ComparisonController"}
                        {if is_array($comparison->ids) && in_array($product->id, $comparison->ids)}
                            <a class="fn_comparison comparison_button mdi mdi-scale-balance selected" href="#" data-id="{$product->id}" title="{$lang->remove_comparison}" data-result-text="{$lang->add_comparison}"></a>
                            
                        {else}
                            <a class="fn_comparison mdi mdi-scale-balance comparison_button" href="#" data-id="{$product->id}" title="{$lang->add_comparison}" data-result-text="{$lang->remove_comparison}"></a>
                        {/if}
                    {/if}
                    
                    {* Wishlist *}
                    {if $controller != "WishListController"}
                        {if is_array($wishlist->ids) && in_array($product->id, $wishlist->ids)}
                            <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button mdi mdi-heart selected" title="{$lang->remove_favorite}" data-result-text="{$lang->add_favorite}"></a>
                        {else}
                            <a href="#" data-id="{$product->id}" class="fn_wishlist mdi mdi-heart-outline wishlist_button" title="{$lang->add_favorite}" data-result-text="{$lang->remove_favorite}"></a>
                        {/if}
                    {/if}

                    {if $controller == "ComparisonController"}
                        <a href="#" class="fn_comparison selected remove_link" title="{$lang->remove_comparison}" data-id="{$product->id}">
                            {include file='svg.tpl' svgId='delete_icon'}
                        </a>
                    {/if}

                    {if $controller == "WishListController"}
                        <a href="#" class="fn_wishlist selected remove_link" title="{$lang->remove_favorite}" data-id="{$product->id}">
                            {include file='svg.tpl' svgId='delete_icon'}
                        </a>
                    {/if}
                </div>
                {* Product variants *}
                <div class="product_preview__variants {if $product->variants|count == 1}hidden{/if}">
                    <select name="variant" class="fn_variant  variant_select {if $product->variants|count == 1}hidden{else}fn_select2{/if}">
                        {foreach $product->variants as $v}
                            <option value="{$v->id}" data-price="{$v->price|convert}" data-stock="{$v->stock}"{if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}"{if $v->compare_price>$v->price && $v->price>0} data-discount="-{round((($v->price-$v->compare_price)/$v->compare_price)*100, 2)}&nbsp;%"{/if}{/if}{if $v->sku} data-sku="{$v->sku|escape}"{/if}>{if $v->name}{$v->name|escape}{else}{$product->name|escape}{/if}</option>
                        {/foreach}
                    </select>
                    <div class="dropDownSelect2"></div>
                </div>
            </form>
            {if $product->annotation && $controller != "MainController"}
                <div class="product_preview__annotation">
                    {$product->annotation}
                </div>
            {/if}
        </div>
    </div>
</div>
