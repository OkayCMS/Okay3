<ul class="top-nav">
    <li>
        <div class="">
            {if !empty({$settings->site_logo})}
            <a class="mobile__link " href="{if $controller=='MainController'}javascript:;{else}{url_generator route="main"}{/if}">
                <img src="{$rootUrl}/{$config->design_images}{$settings->site_logo}?v={$settings->site_logo_version}" alt="{$settings->site_name|escape}"/>
            </a>
            {/if}
        </div>
        <div class="d-flex align-items-center f_col">
            {include file="user_informer.tpl"}
        </div>
    </li>
</ul>
<ul class="second-nav">
    {if $controller != 'MainController'}
        <li>
            <a href="{url_generator route="main"}">
                {include file="svg.tpl" svgId="home_icon"}
                <span data-language="mobile_menu_home">{$lang->mobile_menu_home}</span>
            </a>
        </li>
    {/if}
    <li>
        <a href="javascript:;">
            {include file="svg.tpl" svgId="catalog_icon"}
            <span data-language="mobile_menu_category">{$lang->mobile_menu_category}</span>
        </a>
        {function name=categories_tree4}
        {if $categories}
            <ul class="">
                {foreach $categories as $c}
                {if $c->visible}
                {if $c->subcategories && $c->count_children_visible}
                <li class="">
                    <a class="{if $category->id == $c->id} selected{/if}" href="{url_generator route='category' url=$c->url}" data-category="{$c->id}">
                        {if $c->image}
                            <span class="nav-icon">
                                <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                            </span>
                        {/if}
                        <span>{$c->name|escape}</span>
                    </a>
                    {categories_tree4 categories=$c->subcategories level=$level + 1}
                </li>
                {else}
                <li class="">
                    <a class="{if $category->id == $c->id} selected{/if}" href="{url_generator route='category' url=$c->url}" data-category="{$c->id}">
                        {if $c->image}
                        <span class="nav-icon">
                                <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                            </span>
                        {/if}
                         <span>{$c->name|escape}</span>
                    </a>
                </li>
                {/if}
                {/if}
                {/foreach}
            </ul>
        {/if}
        {/function}
        {categories_tree4 categories=$categories level=1}
    </li>
</ul>

{$menu_mobile}

{* Currencies *}
{if $currencies|count > 1}
<ul class="currencies-nav">
    <li class="">

        <span class=""><span data-language="mobile_menu_currency">{$lang->mobile_menu_currency}:</span> {$currency->name}</span>
        <ul class="">
            {foreach $currencies as $c}
            {if $c->enabled}
            <li>
                <a class="{if $currency->id== $c->id} active{/if}" href="#" onClick="change_currency({$c->id}); return false;">
                    <span class="">{$c->name} </span> <span class=""> ({$c->sign})</span>
                </a>
            </li>
            {/if}
            {/foreach}
        </ul>
    </li>
</ul>
{/if}

{if $languages|count > 1}
{$cnt = 0}
{foreach $languages as $ln}
{if $ln->enabled}
{$cnt = $cnt+1}
{/if}
{/foreach}
{if $cnt>1}
    <ul class="language-nav">
        <li class="">
            {if is_file("{$config->lang_images_dir}{$language->label}.png")}
            <img alt="{$language->current_name}" src="{("{$language->label}.png")|resize:20:20:false:$config->lang_resized_dir}" />
            {/if}
            <span class="">{$language->name}</span>
            <ul class="">
                {foreach $languages as $l}
                {if $l->enabled}
                <li>
                    <a class=" {if $language->id == $l->id} active{/if}"
                       href="{preg_replace('/^(.+)\/$/', '$1', $l->url)}">
                        {if is_file("{$config->lang_images_dir}{$l->label}.png")}
                        <img alt="{$l->current_name}" src="{("{$l->label}.png")|resize:20:20:false:$config->lang_resized_dir}" />
                        {/if}
                        <span class="">{$l->name}</span>
                        {*<span class="">{$l->label}</span>*}
                    </a>
                </li>
                {/if}
                {/foreach}
            </ul>
        </li>
    </ul>
{/if}
{/if}



<ul class="bottom-nav social">
    {*Домен некоторых соц. сетей не соответствует стилям font-awesome, для них сделаны эти алиасы*}
    {$social_aliases.ok = 'odnoklassniki'}

    {foreach $settings->site_social_links as $social_link}
    {$social_domain = preg_replace('~(https?://)?(www\.)?([^\.]+)?\..*~', '$3', $social_link)}
    {if isset($social_aliases.$social_domain) || $social_domain}
    <li class="">
        <a class="{if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}" href="{if !preg_match('~^https?://.*$~', $social_link)}https://{/if}{$social_link|escape}" target="_blank" title="{$social_domain}">
            <i class="fa fa-{if isset($social_aliases.$social_domain)}{$social_aliases.$social_domain}{else}{$social_domain}{/if}"></i>
        </a>
    </li>
    {/if}
    {/foreach}
</ul>
