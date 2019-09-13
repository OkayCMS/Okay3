{* The blog page template *}

{* The canonical address of the page *}
{$canonical="{url_generator route=$route_name absolute=1}" scope=global}
<div class="block">
    {* The page heading *}
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading">
            <span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span>
        </h1>
    </div>

    {* The list of the blog posts *}
    <div class="block__body block--boxed block--border">
        <div class="article_list row">
            {foreach $posts as $post}
                <div class="article_item col-sm-6 col-md-6 col-lg-4 col-xl-3">
                    {include 'post_list.tpl'}
                 </div>
            {/foreach}
        </div>
        {* Pagination *}
        {include file='pagination.tpl'}
    </div>
</div>