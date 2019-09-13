{* The main page template *}
{* The canonical address of the page *}
{$canonical="{url_generator route="main" absolute=1}" scope=global}

{* Featured products *}
{get_featured_products var=featured_products limit=6}
{if $featured_products}
    <div class="main-products main-products__featured container">
        <div class="block block--boxed block--border">
            <div class="block__header block__header--promo">
                <div class="block__title">
                    <span data-language="main_recommended_products">{$lang->main_recommended_products}</span>
                </div>
                <div class="block__header_button">
                    <a class="block__more d-flex align-items-center" href="{url_generator route="bestsellers"}">
                        <span data-language="main_look_all">{$lang->main_look_all} </span>
                        {include file="svg.tpl" svgId="arrow_right2"}
                    </a>
                </div>
            </div>
            <div class="block__body">
                <div class="fn_products_slide products_list row no_gutters owl-carousel">
                    {foreach $featured_products as $product}
                        <div class="item product_item no_hover">
                            {include "product_list.tpl"}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="scrollbar"><div class="handle"><div class="mousearea"></div></div></div>
        </div>
    </div>
{/if}

{* New products *}
{get_new_products var=new_products limit=5}
{if $new_products}
    <div class="main-products main-products__new container">
        <div class="block block--boxed block--border">
            <div class="block__header">
                <div class="block__title">
                    <span data-language="main_new_products">{$lang->main_new_products}</span>
                </div>
            </div>
            <div class="block__body">
                <div class="fn_products_slide products_list row no_gutters owl-carousel">
                    {foreach $new_products as $product}
                        <div class="product_item no_hover">
                            {include "product_list.tpl"}
                        </div>
                    {/foreach}
                </div>
            </div>
         </div>
    </div>
{/if}

{* Discount products *}
{get_discounted_products var=discounted_products limit=6}
{if $discounted_products}
    <div class="main-products main-products__new container">
        <div class="block block--boxed block--border">
            <div class="block__header block__header--promo">
                <div class="block__title">
                    <span data-language="main_discount_products">{$lang->main_discount_products}</span>
                </div>
                <div class="block__header_button">
                    <a class="block__more d-flex align-items-center" href="{url_generator route="discounted"}">
                        <span data-language="main_look_all">{$lang->main_look_all} </span>
                        {include file="svg.tpl" svgId="arrow_right2"}
                    </a>
                </div>
            </div>
            <div class="block__body">
                <div class="fn_products_slide products_list row no_gutters owl-carousel">
                    {foreach $discounted_products as $product}
                        <div class="product_item no_hover">
                            {include "product_list.tpl"}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="scrollbar"><div class="handle"><div class="mousearea"></div></div></div>
        </div>
    </div>
{/if}

{get_brands var=all_brands visible_brand=1 limit=9}
{if $page->description || $all_brands}
    <div class="container section_about_&_brands">
        <div class="block block--boxed block--border">
            <div class="row">
                {if $page->description}
                    <div class="col-lg-7 col-xl-8">
                        <div class="block__abouts_us">
                            <div class="block__header">
                                <h1 class="about_us__heading">
                                    <span>{$page->name|escape}</span>
                                </h1>
                            </div>
                            <div class="block__body">
                                <div class="fn_reedmore">
                                    <div class="page-description__text boxed__description">{$page->description}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                {* Brand list *}
                {if $all_brands}
                    <div class="col-lg-5 col-xl-4">
                        <div class="block__header block__header--promo">
                            <div class="block__title">
                                <span data-language="main_recommended_products">Бренды</span>
                            </div>
                            <div class="block__header_button">
                                <a class="block__more d-flex align-items-center" href="{url_generator route="brands"}">
                                    <span data-language="main_look_all">{$lang->main_look_all} </span>
                                    {include file="svg.tpl" svgId="arrow_right2"}
                                </a>
                            </div>
                        </div>
                        <div class="block__body">
                            <div class="main_brands f_row no_gutters">
                                {foreach $all_brands as $b}
                                <div class="main_brands__item f_col-4 f_col-md-2 f_col-lg-4 f_col-xl-4">
                                    <a class="main_brands__link " href="{url_generator route="brand" url=$b->url}" data-brand="{$b->id}">
                                        {if $b->image}
                                            <div class="main_brands__image">
                                                <img class="main_brands_img lazy" data-src="{$b->image|resize:100:50:false:$config->resized_brands_dir}" alt="{$b->name|escape}" title="{$b->name|escape}">
                                            </div>
                                        {else}
                                            <div class="main_brands__name">
                                                <span>{$b->name|escape}</span>
                                            </div>
                                        {/if}
                                    </a>
                                </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/if}

{* Last_posts *} 
{get_posts var=last_posts limit=4 type_post="news"}
{if $last_posts}
    <div class="main-articles container">
        <div class="block block--boxed block--border">
            <div class="block__header block__header--promo">
                <div class="block__title">
                    <span data-language="main_news">{$lang->main_news}</span>
                </div>
                <div class="block__header_button">
                    <a class="block__more d-flex align-items-center" href="{url_generator route="news"}">
                        <span data-language="main_all_news">{$lang->main_all_news} </span>
                        {include file="svg.tpl" svgId="arrow_right2"}
                    </a>
                </div>
            </div>
            <div class="block__body">
                <div class="fn_articles_slide article_list row no_gutters">
                    {foreach $last_posts as $post}
                        <div class="article_item no_hover col-sm-6 col-md-6 col-lg-3 col-xl-3">
                            {include 'post_list.tpl'}
                        </div>
                    {/foreach}
                </div>
            </div>   
        </div>
    </div>
{/if}

{* Преимущества магазина
<div class="container">{include file='advantages.tpl'}</div>*}
