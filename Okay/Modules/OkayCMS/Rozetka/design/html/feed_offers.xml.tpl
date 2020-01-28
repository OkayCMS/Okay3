{if !empty($products)}
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
{/if}