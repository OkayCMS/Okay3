<?xml version='1.0' encoding='UTF-8'?>
<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>
    <channel>
        {if $settings->okaycms__google_merchant__company}
            <title>{$settings->okaycms__google_merchant__company}</title>
        {/if}

        <link>{$rootUrl}</link>

            {foreach $products as $p}
                {foreach $p->variants as $v}
                    {if !$settings->okaycms__google_merchant__upload_non_exists_products_to_google && $v->stock === '0'}{continue}{/if}
                    {if $settings->okaycms__google_merchant__no_export_without_price == 1 && $v->price == 0}{continue}{/if}

                    <item>
                        {if $settings->okaycms__google_merchant__use_variant_name_like_size}
                            <title>{$p->name|escape}</title>
                            {if $v->name}<g:size>{$v->name|escape}</g:size>{/if}
                        {else}
                            <title>{$p->name|escape}{if !empty($v->name)} {$v->name|escape}{/if}</title>
                        {/if}

                        <link>{url_generator route="product" url=$p->url absolute=1}{if !$v@first}?variant={$v->id}{/if}</link>
                        <description>{if $settings->okaycms__google_merchant__use_full_description_to_google}{$p->description|strip_tags|escape}{else}{$p->annotation|strip_tags|escape}{/if}</description>
                        <g:id>{$v->id}</g:id>
                        <g:condition>new</g:condition>

                        {if round($v->compare_price|convert:$main_currency->id:false, 2) > round($v->price|convert:$main_currency->id:false, 2)}
                            <g:price>{round($v->compare_price|convert:$main_currency->id:false, 2)} {$main_currency->code}</g:price>
                            <g:sale_price>{round($v->price|convert:$main_currency->id:false, 2)} {$main_currency->code}</g:sale_price>
                        {else}
                            <g:price>{round($v->price|convert:$main_currency->id:false, 2)} {$main_currency->code}</g:price>
                        {/if}

                        <g:availability>{if $v->stock !== 0}in stock{else}not in stock{/if}</g:availability>

                        {if isset($all_brands[$p->brand_id])}
                            <g:brand>{$all_brands[$p->brand_id]->name|escape}</g:brand>
                        {/if}

                        <g:adult>{if $settings->okaycms__google_merchant__adult}true{else}false{/if}</g:adult>

                        {if $p->color}
                            <g:color>{$p->color}</g:color>
                        {/if}

                        {if $p->product_type}
                            <g:product_type>{$p->product_type}</g:product_type>
                        {/if}

                        {if !empty($p->images)}
                            {foreach $p->images as $image}
                                {if $image@first}
                                    <g:image_link>{$image->filename|resize:800:600}</g:image_link>
                                {else}
                                    <g:additional_image_link>{$image->filename|resize:800:600}</g:additional_image_link>
                                {/if}
                                {if $image@iteration == 10}{break}{/if}
                            {/foreach}
                        {/if}
                    </item>
                {/foreach}
            {/foreach}
    </channel>
</rss>