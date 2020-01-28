{if !empty($products)}
    {foreach $products as $p}
        {foreach $p->variants as $v}
            {if $settings->okaycms__yandex_xml_vendor_model__no_export_without_price == 1 && $v->price == 0}{continue}{/if}

            <offer id="{$v->id}" type="vendor.model" available="{if $v->stock > 0 || $v->stock === null}true{else}false{/if}">

                <model>{$p->name|escape}{if !empty($v->name)} {$v->name|escape}{/if}</model>

                {if isset($all_brands[$p->brand_id])}
                    <vendor>{$all_brands[$p->brand_id]->name|escape}</vendor>
                {/if}

                <url>{url_generator route="product" url=$p->url absolute=1}{if !$v@first}?variant={$v->id}{/if}</url>
                <price>{round($v->price|convert:$main_currency->id:false, 2)}</price>

                {if !empty($v->compare_price)}
                    <oldprice>{round($v->compare_price|convert:$main_currency->id:false, 2)}</oldprice>
                {/if}

                {if !empty($all_categories[$p->main_category_id]->yandex_name)}
                    <market_category>{$all_categories[$p->main_category_id]->yandex_name|escape}</market_category>
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

                <store>{if $settings->okaycms__yandex_xml_vendor_model__store}true{else}false{/if}</store>
                <delivery>{if $settings->okaycms__yandex_xml_vendor_model__delivery_disallow}true{else}false{/if}</delivery>
                <pickup>{if $settings->okaycms__yandex_xml_vendor_model__pickup == 1}true{else}false{/if}</pickup>
                <adult>{if $settings->okaycms__yandex_xml_vendor_model__adult == 1}true{else}false{/if}</adult>

                {if $p->country_of_origin}
                    <country_of_origin>{$p->country_of_origin}</country_of_origin>
                {/if}

                {if $v->weight > 0}
                    <weight>{$v->weight}</weight>
                {/if}

                {if $settings->okaycms__yandex_xml_vendor_model__sales_notes}
                    <sales_notes>{$settings->okaycms__yandex_xml_vendor_model__sales_notes|strip_tags|escape}</sales_notes>
                {/if}

                {if !empty($v->sku)}
                    <vendorCode>{$v->sku|escape}</vendorCode>
                {/if}

                <description>{if $settings->okaycms__yandex_xml_vendor_model__use_full_description_to_yandex}{$p->description|strip_tags|escape}{else}{$p->annotation|strip_tags|escape}{/if}</description>
                <manufacturer_warranty>{if $settings->okaycms__yandex_xml_vendor_model__has_manufacturer_warranty}true{else}false{/if}</manufacturer_warranty>

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