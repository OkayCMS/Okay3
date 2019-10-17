
<a class="fn_fast_order_button fast_order_button icon icon-offline-bolt {if $product->variant->stock < 1 && !$settings->is_preorder}hidden{/if}" href="#fast_order"
   title="{$lang->fast_order}" data-name="{$fast_order_product_name}"></a>