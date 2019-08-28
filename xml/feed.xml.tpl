<?xml version='1.0' encoding='UTF-8'?>
<!DOCTYPE yml_catalog SYSTEM 'shops.dtd'>
<yml_catalog date="{date('Y-m-d H:i')}">
<shop>
    <name>{$settings->site_name}</name>
    <company>{$settings->site_name}</company>
    <url>{$rootUrl}</url>
    <platform>OkayCMS</platform>
    <version>{$config->version} {$config->version_type}</version>
    <currencies>
        {foreach $currencies as $c}
            <currency id="{$c->code}" rate="{$c->rate_to/$c->rate_from*$main_currency->rate_from/$main_currency->rate_to}"/>
        {/foreach}
    </currencies>

    <categories>
    {function name=categories_tree}
        {if $categories}
            {foreach $categories as $c}
                <category id="{$c->id}"{if $c->parent_id>0} parentId="{$c->parent_id}"{/if}>{$c->name|escape}</category>
                {if $c->subcategories && $c->count_children_visible && $level < 3}
                    {categories_tree categories=$c->subcategories}
                {/if}
            {/foreach}
        {/if}
    {/function}
    {categories_tree categories=$categories}
    </categories>

    {if !empty($products)}
        <offers>
            {foreach $products as $p}
                {foreach $p->variants as $v}
                    <offer id="{$v->id}" type="vendor.model" available="{if $v->stock > 0 || $v->stock === null}true{else}false{/if}">
                        <url>{url_generator route="product" url=$p->url absolute=1}{if !$v@first}?variant={$v->id}{/if}</url>
                        <model>{$p->name|escape}{if !empty($v->name)} {$v->name|escape}{/if}</model>
                        <price>{round($v->price|convert:$main_currency->id:false, 2)}</price>
                        {if !empty($v->compare_price)}
                            <oldprice>{round($v->compare_price|convert:$main_currency->id:false, 2)}</oldprice>
                        {/if}
                        <currencyId>{$main_currency->code}</currencyId>
                        <categoryId>{$p->main_category_id}</categoryId>
    
                        {if !empty($all_categories[$p->main_category_id]->yandex_name)}
                            <market_category>{$all_categories[$p->main_category_id]->yandex_name|escape}</market_category>
                        {/if}
                        
                        {if !empty($p->images)}
                            {foreach $p->images as $image}
                                <picture>{$image->filename|resize:800:600}</picture>
                            {/foreach}
                        {/if}
    
                        <store>{if $settings->yandex_available_for_retail_store}true{else}false{/if}</store>
                        <pickup>{if $settings->yandex_available_for_reservation}true{else}false{/if}</pickup>
                        <delivery>true</delivery>
                        
                        {if isset($all_brands[$p->brand_id])}
                            <vendor>{$all_brands[$p->brand_id]->name|escape}</vendor>
                        {/if}
                        {if !empty($v->sku)}
                            <vendorCode>{$v->sku|escape}</vendorCode>
                        {/if}
                        <description>{if $settings->yandex_short_description}{$p->description|strip_tags|escape}{else}{$p->annotation|strip_tags|escape}{/if}</description>
    
                        {if $settings->yandex_sales_notes}
                            <sales_notes>{$settings->yandex_sales_notes|strip_tags|escape}</sales_notes>
                        {/if}
                        
                        <manufacturer_warranty>{if $settings->yandex_has_manufacturer_warranty}true{else}false{/if}</manufacturer_warranty>
                        <seller_warranty>{if $settings->yandex_has_seller_warranty}true{else}false{/if}</seller_warranty>
                        
                        {if !empty($p->features)}
                            {foreach $p->features as $feature}
                                {foreach $feature->values as $val}
                                    <param name="{$feature->name|escape}">{$val->value|escape}</param>
                                {/foreach}
                            {/foreach}
                        {/if}
                        
                    </offer>
                {/foreach}
            {/foreach}
        </offers>
    {/if}
</shop>
</yml_catalog>