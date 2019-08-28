<div class="article__preview">
    <div class="article__body">
        <div class="article__image">
            {if $post->type_post == 'blog'}
                {$url = {url_generator route='blog_item' url=$post->url}}
            {elseif $post->type_post == 'news'}
                {$url = {url_generator route='news_item' url=$post->url}}
            {/if}
            <a class="" href="{$url}">
                {if $post->image}
                    <img class="lazy" data-src="{$post->image|resize:420:220:false:$config->resized_blog_dir:center:center}" src="{$post->image|resize:420:220:false:$config->resized_blog_dir:center:center}"  alt="{$post->name|escape}" title="{$post->name|escape}"/>
                {else}
                    <div class="article__no_image d-flex align-items-start">
                        {include file="svg.tpl" svgId="no_image"}
                    </div>
                {/if}
            </a>
        </div>

        <a class="article__title theme_link--color" href="{$url}" data-post="{$post->id}">{$post->name|escape}</a>

        {if $post->annotation}
            <div class="article__annotation">{$post->annotation}</div>
        {/if}
    </div>
    <div class="article__footer d-flex justify-content-between align-items-center">
        <a class="article__button" href="{$url}" data-language="main_article_read">
            {$lang->main_article_read} {include file="svg.tpl" svgId="arrow_right2"}
        </a>
        <div class="article__date">{$post->date|date:"d cFR Y, cD"}</div>
    </div>
</div>