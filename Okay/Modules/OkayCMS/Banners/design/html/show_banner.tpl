<div class="fn_banner_{$banner_data->group_name} banner_group banner_group--{$banner_data->id} owl-carousel {if !$banner_data->settings.as_slider}no_js{/if}">
    {foreach $banner_data->items as $bi}
        {if $bi->settings.variant_show == Okay\Modules\OkayCMS\Banners\Entities\BannersImagesEntity::SHOW_DEFAULT}
        <div class="banner_group__item block--border banner_group__variant1">
        {elseif $bi->settings.variant_show == Okay\Modules\OkayCMS\Banners\Entities\BannersImagesEntity::SHOW_DARK}
        <div class="banner_group__item block--border banner_group__variant2">
        {elseif $bi->settings.variant_show == Okay\Modules\OkayCMS\Banners\Entities\BannersImagesEntity::SHOW_IMAGE_LEFT}
        <div class="banner_group__item block--border banner_group__variant3">
        {elseif $bi->settings.variant_show == Okay\Modules\OkayCMS\Banners\Entities\BannersImagesEntity::SHOW_IMAGE_RIGHT}
        <div class="banner_group__item block--border banner_group__variant4">
        {/if}
            {if $bi->url}
            <a class="banner_group__link" aria-label="{$bi->title}" href="{$bi->url}" target="_blank"></a>
            {/if}
            <div class="banner_group__image">
                <picture>
                    <source media="(min-width: 768px)" data-srcset="{$bi->image|resize:$bi->settings.desktop.w:$bi->settings.desktop.h:false:$config->resized_banners_images_dir:center:center}" srcset="{$bi->image|resize:$bi->settings.desktop.w:$bi->settings.desktop.h:false:$config->resized_banners_images_dir:center:center}">
                    <source media="(max-width: 767px)" data-srcset="{$bi->image|resize:$bi->settings.mobile.w:$bi->settings.mobile.h:false:$config->resized_banners_images_dir}" srcset="{$bi->image|resize:$bi->settings.mobile.w:$bi->settings.mobile.h:false:$config->resized_banners_images_dir}">
                    {if $banner_data->settings.as_slider}
                        <img class="owl-lazy" data-src="{$bi->image|resize:$bi->settings.desktop.w:$bi->settings.desktop.h:false:$config->resized_banners_images_dir:center:center}" src="{$bi->image|resize:$bi->settings.desktop.w:$bi->settings.desktop.h:false:$config->resized_banners_images_dir:center:center}" alt="{$bi->alt}" title="{$bi->title}">
                    {else}
                        <img src="{$bi->image|resize:$bi->settings.desktop.w:$bi->settings.desktop.h:false:$config->resized_banners_images_dir:center:center}" alt="{$bi->alt}" title="{$bi->title}">
                    {/if}
                </picture>
            </div>
            <div class="banner_group__content">
                <div class="banner_group__text">
                    {if $bi->title}
                        <div class="banner_group__title">{$bi->title}</div>
                    {/if}

                    {if $bi->description}
                        <div class="banner_group__description">{$bi->description}</div>
                    {/if}
                </div>
            </div>
        </div>
     {/foreach}
</div>

{if $banner_data->settings.as_slider}
<script>
    document.addEventListener('DOMContentLoaded', function(){
        $('.fn_banner_{$banner_data->group_name}').owlCarousel({
            animateOut: "fadeOut",
            loop: {if isset($banner_data->settings.loop) && !empty($banner_data->settings.loop)}true{else}false{/if},
            lazyLoad:true,
            autoplay: {if isset($banner_data->settings.autoplay) && !empty($banner_data->settings.autoplay)}true{else}false{/if},
            autoplayTimeout: {if isset($banner_data->settings.rotation_speed) && !empty($banner_data->settings.rotation_speed)}{$banner_data->settings.rotation_speed|intval}{else}2500{/if},
            nav: {if isset($banner_data->settings.nav) && !empty($banner_data->settings.nav)}true{else}false{/if},
            dots: {if isset($banner_data->settings.dots) && !empty($banner_data->settings.dots)}true{else}false{/if},
            items:1,
            autoplayHoverPause: true,
            responsive: {
                320: {
                    autoHeight:true,
                    autoplay: false
                },
                991: {
                    autoHeight:false
                }
            }
        });
    });
</script>
{/if}