{* Breadcrumb navigation *}
{if $controller != "MainController"}
    <ol class="breadcrumbs d-flex flex-wrap align-items-center">
        {* The link to the homepage *}
        <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
            <a itemprop="url" aria-label="{$lang->breadcrumb_home}" href="{url_generator route='main'}" >
                <span itemprop="title" data-language="breadcrumb_home" title="{$lang->breadcrumb_home}">
                    {include file="svg.tpl" svgId="home_icon"}
                </span>
            </a>
        </li>

        {* Categories page *}
        {if $controller == "CategoryController"}
            {if $category}
                {foreach from=$category->path item=cat}
                    {if !$cat@last}
                        {if $cat->visible}
                            <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                                <a itemprop="url" href="{url_generator route='category' url=$cat->url}">
                                    <span itemprop="title">{$cat->name|escape}</span>
                                </a>
                            </li>
                        {/if}
                    {else}
                        <li class="d-inline-flex align-items-center breadcrumbs__item">{$cat->name|escape}</li>
                    {/if}
                {/foreach}
            {/if}

        {* Products list page *}
        {elseif $controller == "ProductsController"}
            {if !empty($keyword)}
                <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="general_search">{$lang->general_search}</li>
            {else}
                <li class="d-inline-flex align-items-center breadcrumbs__item">{$h1|escape}</li>
            {/if}
            
        {* Brand list page *}
        {elseif $controller == "BrandController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                <a itemprop="url" href="{url_generator route='brands'}" data-language="breadcrumb_brands">
                    <span itemprop="title">{$lang->breadcrumb_brands}</span>
                </a>
            </li>
            <li class="d-inline-flex align-items-center breadcrumbs__item">{$brand->name|escape}</li>

        {* Brand list page *}
        {elseif $controller == "BrandsController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item">{$page->name|escape}</li>

        {* Product page *}
        {elseif $controller == "ProductController"}
            {foreach from=$category->path item=cat}
                {if $cat->visible}
                    <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                        <a itemprop="url" href="{url_generator route='category' url=$cat->url}">
                            <span itemprop="title">{$cat->name|escape}</span>
                        </a>
                    </li>
                {/if}
            {/foreach}
            <li class="d-inline-flex align-items-center breadcrumbs__item">{$product->name|escape}</li>

        {* Page *}
        {elseif $controller == "FeedbackController" || $controller == "PageController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item">{$page->name|escape}</li>

        {* Cart page *}
        {elseif $controller == "CartController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumb_cart">{$lang->breadcrumb_cart}</li>

        {* Order page *}
        {elseif $controller == "OrderController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumb_order">{$lang->breadcrumb_order} {$order->id}</li>

        {* Password remind page *}
        {elseif $controller == "LoginController" && $smarty.get.action == "password_remind"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumbs_password_remind">{$lang->breadcrumbs_password_remind}</li>

        {* Login page *}
        {elseif $controller == "LoginController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumbs_enter">{$lang->breadcrumbs_enter}</li>

        {* Register page *}
        {elseif $controller == "RegisterController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumbs_registration">{$lang->breadcrumbs_registration}</li>

        {* User account page *}
        {elseif $controller == "UserController"}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumbs_user">{$lang->breadcrumbs_user}</li>

        {* Blog page *}
        {elseif $controller == "BlogController"}
            {if $post}
                <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">

                    {if $post->type_post == "news"}
                        {$prev_url = {url_generator route='news'}}
                    {else}
                        {$prev_url = {url_generator route='blog'}}
                    {/if}
                    
                    <a itemprop="url" href="{$prev_url}">
                        <span itemprop="title">
                            {if $post->type_post == "news"}
                                <span data-language="main_news">{$lang->main_news}</span>
                            {else}
                                <span data-language="breadcrumbs_blog">{$lang->breadcrumbs_blog}</span>
                            {/if}
                        </span>
                    </a>
                </li>
                <li class="d-inline-flex align-items-center breadcrumbs__item">
                    {$post->name|escape}
                </li>
            {else}
                <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumbs_blog">
                    {if $typePost == "news"}
                        {$lang->main_news}
                    {else}
                        {$lang->breadcrumbs_blog}
                    {/if}
                </li>
            {/if}
        {elseif $controller == 'ComparisonController'}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumb_comparison">{$lang->breadcrumb_comparison}</li>
        {elseif $controller == 'WishListController'}
            <li class="d-inline-flex align-items-center breadcrumbs__item" data-language="breadcrumb_wishlist">{$lang->breadcrumb_wishlist}</li>
        {elseif !empty($breadcrumbs) && is_array($breadcrumbs)}
            {foreach $breadcrumbs as $url => $name}
                {if !$name@last}
                    <li class="d-inline-flex align-items-center breadcrumbs__item" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">
                        <a itemprop="url" href="{$url|escape}">
                            <span itemprop="title">{$name|escape}</span>
                        </a>
                    </li>
                {else}
                    <li class="d-inline-flex align-items-center breadcrumbs__item">{$name|escape}</li>
                {/if}
            {/foreach}
        {/if}
    </ol>
{/if}
