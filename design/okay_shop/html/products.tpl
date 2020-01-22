{* The Categories page *}
{* The canonical address of the page *}
{if $set_canonical || $current_page_num > 1 || $is_all_pages}
    {if $category}
        {$canonical="{if $cannonical}{$cannonical}{else}{url_generator route='category' url=$category->url absolute=1}{/if}" scope=global}
    {elseif $brand}
        {$canonical="{url_generator route='brand' url=$brand->url absolute=1}" scope=global}
    {elseif $route_name == 'discounted'}
        {$canonical="{url_generator route='discounted' absolute=1}" scope=global}
    {elseif $route_name == 'bestsellers'}
        {$canonical="{url_generator route='bestsellers' absolute=1}" scope=global}
    {elseif $route_name == 'search'}
        {$canonical="{url_generator route='search' absolute=1}" scope=global}
    {/if}
{/if}
<div class="clearfix">
    {* Sidebar with filters *}
    <div class="fn_mobile_toogle sidebar d-lg-flex flex-lg-column">
        <div class="fn_mobile_toogle sidebar__header sidebar__boxed hidden-lg-up">
            <div class="fn_switch_mobile_filter sidebar__header--close">
                {include file="svg.tpl" svgId="remove_icon"}
                <span data-language="mobile_filter_close">{$lang->mobile_filter_close}</span>
            </div>
            {if $category}
                <div class="sidebar__header--reset">
                    <form method="post">
                        <button type="submit" name="prg_seo_hide" class="fn_filter_reset mobile_filter__reset" value="{url_generator route="category" url=$category->url absolute=1}">
                            {include file="svg.tpl" svgId="reset_icon"}
                            <span>{$lang->mobile_filter_reset}</span>
                        </button>
                    </form>
                </div>
            {elseif $brand}
                <div class="sidebar__header--reset">
                    <form method="post">
                        <button type="submit" name="prg_seo_hide" class="fn_filter_reset mobile_filter__reset" value="{url_generator route="brand" url=$brand->url absolute=1}">
                            {include file="svg.tpl" svgId="reset_icon"}
                            <span>{$lang->mobile_filter_reset}</span>
                        </button>
                    </form>
                </div>
            {/if}
        </div>

        <div class="fn_selected_features">
            {include 'selected_features.tpl'}
        </div>

        <div class="fn_features">
            {include file='features.tpl'}
        </div>

        {* Browsed products *}
        <div class="browsed products">
            {include file='browsed_products.tpl'}
        </div>
    </div>

    <div class="products_container d-flex flex-column">
        <div class="products_container__boxed">
            <h1 class="h1"{if $category} data-category="{$category->id}"{/if}{if $brand} data-brand="{$brand->id}"{/if}>{$h1|escape}</h1>

            {if $current_page_num == 1 && (!empty($category->annotation) || !empty($brand->annotation)) && !$is_filter_page && !$smarty.get.page && !$smarty.get.sort}
                <div class="boxed boxed--big">
                    <div class="">
                        <div class="fn_readmore">
                            <div class="block__description">
                                {* Краткое описание категории *}
                                {if !empty($category->annotation)}
                                {$category->annotation}
                                {/if}

                                {* Краткое описание бренда *}
                                {if !empty($brand->annotation)}
                                {$brand->annotation}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            {if $products}
                <div class="products_container__sort d-flex align-items-center justify-content-between">
                    {* Product Sorting *}
                    <div class="fn_products_sort">
                        {include file="products_sort.tpl"}
                    </div>
                    {* Mobile button filters *}
                    <div class="fn_switch_mobile_filter switch_mobile_filter hidden-lg-up">
                        {include file="svg.tpl" svgId="filter_icon"}
                        <span data-language="filters">{$lang->filters}</span>
                    </div>
                </div>
            {/if}

            {* Product list *}
            <div id="fn_products_content" class="fn_categories products_list row">
                {include file="products_content.tpl"}
            </div>

            {if $products}
                {* Friendly URLs Pagination *}
                <div class="fn_pagination products_pagination">
                    {include file='chpu_pagination.tpl'}
                </div>
            {/if}

            {if $description}
                <div class="boxed boxed--big">
                    <div class="">
                        <div class="fn_readmore">
                            <div class="block__description">{$description}</div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>