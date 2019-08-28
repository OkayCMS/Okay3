{*{get_browsed_products var=browsed_products limit=6}*}

{if $browsed_products}
    <div class="sidebar__boxed browsed">
        <div class="fn_switch filter__name d-flex align-items-center justify-content-between">
            <span data-language="features_browsed">{$lang->features_browsed}</span>
            <span class="filter__name_arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
        </div>

        <div class="browsed__content row">
            {foreach $browsed_products as $browsed_product}
                <div class="browsed__item col-xs-4 col-md-4 col-lg-4">
                    <a class="browsed__link" href="{url_generator route="product" url=$browsed_product->url}">
                        {if $browsed_product->image->filename}
                            <img class="lazy" data-src="{$browsed_product->image->filename|resize:80:80}" src="{$browsed_product->image->filename|resize:80:80}" alt="{$browsed_product->name|escape}" title="{$browsed_product->name|escape}">
                        {else}
                            <div class="browsed__no_image d-flex align-items-center justify-content-center" title="{$browsed_product->name|escape}">
                                {include file="svg.tpl" svgId="no_image"}
                            </div>
                        {/if}
                    </a>
                </div>
            {/foreach}
        </div>
    </div>
{/if}