<?xml version='1.0' encoding='UTF-8'?>

{$UAH_currency = false}
{$USD_currency = false}
{foreach $currencies as $currency}
    {if $currency->code === "UAH"}
        {$UAH_currency = $currency}
    {elseif $currency->code === "USD"}
        {$USD_currency = $currency}
    {/if}
{/foreach}

<price>

    {if $settings->okaycms__hotline__company}
        <firmName>{$settings->okaycms__hotline__company|escape}</firmName>
    {else}
        <firmName>{$settings->site_name}</firmName>
    {/if}

    <categories>
    {function name=categories_tree}
        {if $categories}
            {foreach $categories as $c}
                <category>
                    <id>{$c->id}</id>
                    {if $c->parent_id}
                        <parentId>{$c->parent_id}</parentId>
                    {/if}
                    <name>{$c->name|escape}</name>
                </category>
                {if $c->subcategories && $c->count_children_visible && $level < 3}
                    {categories_tree categories=$c->subcategories}
                {/if}
            {/foreach}
        {/if}
    {/function}
    {categories_tree categories=$categories}
    </categories>

    {if !empty($products)}
        <items>
            {foreach $products as $p}
                {foreach $p->variants as $v}
                    {if $settings->okaycms__hotline__no_export_without_price == 1 && $v->price == 0}{continue}{/if}

                    <item>
                        <id>{$v->id}</id>

                        <group_id>{$p->id}</group_id>

                        <categoryId>{$p->main_category_id}</categoryId>
                        <code>{$v->sku}</code>
                        <name>{$p->name|escape}{if !empty($v->name)} {$v->name|escape}{/if}</name>
                        {if isset($all_brands[$p->brand_id])}
                            <vendor>{$all_brands[$p->brand_id]->name|escape}</vendor>
                        {/if}
                        <description>{if $settings->okaycms__hotline__use_full_description_to_hotline}{$p->description|strip_tags|escape}{else}{$p->annotation|strip_tags|escape}{/if}</description>
                        <url>{url_generator route="product" url=$p->url absolute=1}{if !$v@first}?variant={$v->id}{/if}</url>

                        <stock>{if $v->stock || $v->stock === null}В наличии{else}Под заказ{/if}</stock>

                        {if $UAH_currency}
                            <priceRUAH>{round($v->price|convert:$UAH_currency->id:false, 2)}</priceRUAH>
                        {else}
                            <priceRUAH>{round($v->price|convert:$main_currency->id:false, 2)}</priceRUAH>
                        {/if}

                        {if $USD_currency}
                            <priceRUSD>{round($v->price|convert:$USD_currency->id:false, 2)}</priceRUSD>
                        {/if}

                        {if !empty($p->images)}
                            {foreach $p->images as $image}
                                <image>{$image->filename|resize:800:600}</image>
                                {if $image@iteration == 15}{break}{/if}
                            {/foreach}
                        {/if}

                        {if $p->guarantee_manufacturer}<guarantee type="manufacturer">{$p->guarantee_manufacturer}</guarantee>{/if}
                        {if $p->guarantee_shop}<guarantee type="shop">{$p->guarantee_shop}</guarantee>{/if}

                        {if $p->country_of_origin}
                            <param name="Країна виготовлення">{$p->country_of_origin}</param>
                        {/if}

                        {if !empty($p->features)}
                            {foreach $p->features as $feature}
                                {foreach $feature->values as $val}
                                    <param name="{$feature->name|escape}">{$val->value|escape}</param>
                                {/foreach}
                            {/foreach}
                        {/if}
                    </item>
                {/foreach}
            {/foreach}
        </items>
    {/if}
</price>
