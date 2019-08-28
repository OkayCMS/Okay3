
{function name=categories_tree3}
    {if $categories}
        <div class="level_{$level} {if $level == 1}wrap_categories_menu{else}wrap_subcategory{/if}">
        <ul class="fn_category_scroll {if $level == 1}categories_menu {else}subcategory {/if}">
            {foreach $categories as $c}
                {if $c->visible}
                    {if $c->subcategories && $c->count_children_visible}
                        <li class="category_item has_child">
                            <a class="category_link{if $category->id == $c->id} selected{/if}" href="{url_generator route="category" url=$c->url}" data-category="{$c->id}">
                                {if $c->image}
                                    {if $level == 1 || $level == 2}
                                        <span class="category_icon" style="background-image: url({$c->image|resize:30:30:false:$config->resized_categories_dir})"></span>
                                    {/if}
                                {/if}
                                <span>{$c->name|escape}</span>
                                {include file='svg.tpl' svgId='arrow_right'}
                            </a>
                            {categories_tree3 categories=$c->subcategories level=$level + 1}
                        </li>
                    {else}
                        <li class="category_item">
                            <a class="category_link{if $category->id == $c->id} selected{/if}" href="{url_generator route="category" url=$c->url}" data-category="{$c->id}">
                                {if $level == 3}
                                    <div class="category_img">
                                        {if $c->image}
                                            <img src="{$c->image|resize:80:80:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                                        {else}
                                            <img class="fn_img preview_img" src="design/{get_theme}/images/no_image.png" width="80" height="80" alt="{$chld->name|escape}"/>
                                        {/if}
                                    </div>
                                {else}
                                    {if $c->image}
                                        <span class="category_icon" style="background-image: url({$c->image|resize:32:32:false:$config->resized_categories_dir})"></span>
                                    {/if}
                                {/if}
                                <span class="category_name">{$c->name|escape}</span>
                            </a>
                        </li>
                    {/if}
                {/if}
            {/foreach}
        </ul>
        </div>
    {/if}
{/function}
{categories_tree3 categories=$categories level=1}

