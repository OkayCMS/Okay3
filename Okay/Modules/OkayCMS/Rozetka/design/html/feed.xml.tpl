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
                    <offer id="{$v->id}" available="{if $v->stock > 0 || $v->stock === null}true{else}false{/if}">
                        <url>{url_generator route="product" url=$p->url absolute=1}{if !$v@first}?variant={$v->id}{/if}</url>
                        <name>{$p->name|escape}{if !empty($v->name)} {$v->name|escape}{/if}</name>
                        <price>{round($v->price|convert:$main_currency->id:false, 2)}</price>
                        {if !empty($v->compare_price)}
                            <oldprice>{round($v->compare_price|convert:$main_currency->id:false, 2)}</oldprice>
                        {/if}
                        <currencyId>{$main_currency->code}</currencyId>
                        <categoryId>{$p->main_category_id}</categoryId>
    
                        {if !empty($p->images)}
                            {foreach $p->images as $image}
                                <picture>{$image->filename|resize:800:600}</picture>
                                {if $image@iteration == 15}
                                    {break}
                                {/if}
                            {/foreach}
                        {/if}
    
                        <stock_quantity>{$v->stock}</stock_quantity>
                        <delivery>true</delivery>
                        
                        {if isset($all_brands[$p->brand_id])}
                            <vendor>{$all_brands[$p->brand_id]->name|escape}</vendor>
                        {/if}
                        {if !empty($v->sku)}
                            <vendorCode>{$v->sku|escape}</vendorCode>
                        {/if}
                        <description>{if $settings->use_full_description_in_upload_rozetka}{$p->description|strip_tags|escape}{else}{$p->annotation|strip_tags|escape}{/if}</description>
                        
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