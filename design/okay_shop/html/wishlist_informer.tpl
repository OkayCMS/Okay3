{* Информер избранного (отдаётся аяксом) *}
{if $wishlist->products|count > 0}
    <a class="header_informers__link" href="{url_generator route="wishlist"}">
        {*include file="svg.tpl" svgId="wishlist_icon"*}
        <i class="mdi mdi-heart-outline"></i>
        {*<span class="informer_name tablet-hidden" data-language="wishlist_header">{$lang->wishlist_header}</span> <span class="informer_counter">({$wished_products|count})</span>*}
        <span class="wishlist_counter">{$wishlist->products|count}</span>
    </a>
{else}
    <span class="header_informers__link">
        {*include file="svg.tpl" svgId="wishlist_icon"*}
        <i class="mdi mdi-heart-outline"></i>
        {*<span class="informer_name tablet-hidden" data-language="wishlist_header">{$lang->wishlist_header}</span>*}
    </span>
{/if}
