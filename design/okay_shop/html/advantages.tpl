{* Advantages block *}
{if $advantages}
    <div class="block block--boxed block--border section_advantages">
        <div class="advantages f_row no_gutters">
            {foreach $advantages as $advantage}
            <div class="advantages__item f_col-6 f_col-md-3">
                <div class="advantages__preview d-flex align-items-center">
                    {if $advantage->filename}
                        <div class="advantages__icon advantages__icon--delivery d-flex align-items-center justify-content-center">
                            <picture>
                                {if $settings->support_webp}
                                    <source class="lazy" type="image/webp" data-srcset="{$advantage->filename|resize:50:50:false:$config->resized_advantages_dir}.webp" srcset="{$rootUrl}/design/{get_theme}/images/xloading.gif">
                                {/if}
                                <source class="lazy" data-srcset="{$advantage->filename|resize:50:50:false:$config->resized_advantages_dir}" srcset="{$rootUrl}/design/{get_theme}/images/xloading.gif">
                                <img class="lazy" data-src="{$advantage->filename|resize:50:50:false:$config->resized_advantages_dir}" src="{$rootUrl}/design/{get_theme}/images/xloading.gif" alt="{$advantage->text|escape}" title="{$advantage->text|escape}"/>
                            </picture>
                        </div>
                    {/if}
                    <div class="advantages__title">{$advantage->text}</div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
{/if}