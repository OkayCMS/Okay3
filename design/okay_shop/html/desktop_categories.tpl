{function name=categories_tree3}
    {if $categories}
        <div class="level_{$level} {if $level == 1}categories_nav__menu{else}categories_nav__subcategory{/if}">
            <ul class="fn_category_scroll {if $level == 1}categories_menu {else}subcategory {/if}">
                {foreach $categories as $c}
                    {if $c->visible}
                        {if $c->subcategories && $c->count_children_visible && $level < 3}
                            <li class="categories_menu__item has_child">
                                <a class="categories_menu__link{if $category->id == $c->id} selected{/if}" href="{url_generator route="category" url=$c->url}" data-category="{$c->id}">
                                    {if $c->image}
                                        {if $level == 1 || $level == 2}
                                            <span class="categories_menu__icon lazy" data-bg="url({$c->image|resize:22:22:false:$config->resized_categories_dir})" ></span>
                                        {/if}
                                    {/if}
                                    <span class="categories_menu__name">{$c->name|escape}</span>
                                    {include file='svg.tpl' svgId='arrow_right'}
                                </a>
                                {categories_tree3 categories=$c->subcategories level=$level + 1}
                            </li>
                        {else}
                            <li class="categories_menu__item">
                                <a class="categories_menu__link{if $category->id == $c->id} selected{/if}" href="{url_generator route='category' url=$c->url}" data-category="{$c->id}">
                                    {if $level >= 3}
                                        <div class="categories_menu__image">
                                            {if $c->image}
                                                <img class="lazy" data-src="{$c->image|resize:90:90:false:$config->resized_categories_dir}" alt="{$c->name|escape}" srcset="{$c->image|resize:90:90:false:$config->resized_categories_dir:null:null:true}"/>
                                            {else}
                                                <div class="categories__no_image d-flex align-items-center justify-content-center" title="{$c->name|escape}">
                                                    {include file="svg.tpl" svgId="no_image"}
                                                </div>
                                            {/if}
                                        </div>
                                    {else}
                                        {if $c->image}
                                            <span class="categories_menu__icon lazy" data-bg="url({$c->image|resize:22:22:false:$config->resized_categories_dir})"></span>
                                        {/if}
                                    {/if}
                                    <span class="categories_menu__name">{$c->name|escape}</span>
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
