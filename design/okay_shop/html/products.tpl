{* The Categories page *}
{* The canonical address of the page *}
{if $set_canonical || $current_page > 1}
    {if $category}
        {$canonical="{url_generator route='category' url=$category->url absolute=1}" scope=global}
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
                        <button type="submit" name="prg_seo_hide" class="fn_filter_reset mobile_filter__reset" value="{url_generator route="category" url=$category->url}">
                            {include file="svg.tpl" svgId="reset_icon"}
                            <span>{$lang->mobile_filter_reset}</span>
                        </button>
                    </form>
                </div>
            {elseif $brand}
                <div class="sidebar__header--reset">
                    <form method="post">
                        <button type="submit" name="prg_seo_hide" class="fn_filter_reset mobile_filter__reset" value="{url_generator route="brand" url=$brand->url}">
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
            {* The page heading *}
            {if !empty($keyword)}
                <h1 class="h1"><span data-language="products_search">{$lang->products_search}</span> {$keyword|escape}</h1>
            {elseif $page}
                <h1 class="h1">
                    <span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span>
                </h1>
            {elseif !empty($seo_filter_pattern->h1)}
                <h1 class="h1">{$seo_filter_pattern->h1|escape}</h1>
            {else}
                <h1 class="h1">
                    {if !empty($category)}
                        <span data-category="{$category->id}">{if !empty($category->name_h1)}{$category->name_h1|escape}{else}{$category->name|escape}{/if}</span>
                    {/if}
                    {if !empty($brand->name)}
                        {$brand->name|escape}
                    {/if}
                    {if !empty($filter_meta->h1)}
                        {$filter_meta->h1|escape}
                    {/if}
                </h1>
            {/if}

            {if $current_page_num == 1 && (!empty($category->annotation) || !empty($brand->annotation)) && !$is_filter_page && !$smarty.get.page && !$smarty.get.sort}
                <div class="boxed boxed--big">
                    <div class="">
                        <div class="fn_reedmore">
                            <div class="page-description__text boxed__description">
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

            {if $current_page_num == 1 && $page->description}
            <div class="boxed boxed--big">
                <div class="">
                    <div class="fn_reedmore">
                        <div class="page-description__text boxed__description">{$page->description}</div>
                    </div>
                </div>
            </div>
            {/if}

            {if $current_page_num == 1}
                {*SEO шаблон описания страницы фильтра*}
                {if $seo_filter_pattern->description}
                    <div class="boxed boxed--big">
                        <div class="">
                            <div class="fn_reedmore">
                                <div class="page-description__text boxed__description">{$seo_filter_pattern->description}</div>
                            </div>
                        </div>
                    </div>
                {elseif (empty($category) || empty($brand)) && ($category->description || $brand->description) && !$is_filter_page && !$smarty.get.page && !$smarty.get.sort}
                    <div class="boxed boxed--big">
                        <div class="">
                            <div class="fn_reedmore">
                                <div class="page-description__text boxed__description">
                                    {* Описание категории *}
                                    {$category->description}

                                    {* Описание бренда *}
                                    {$brand->description}
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>
</div>