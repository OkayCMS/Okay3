{* Advantages block *}
{if $advantages}
    <div class="block block--boxed block--border section_advantages">
        <div class="advantages f_row no_gutters">
            {foreach $advantages as $advantage}
            <div class="advantages__item f_col-6 f_col-md-3">
                <div class="advantages__preview d-flex align-items-center">
                    {if $advantage->filename}
                        <div class="advantages__icon advantages__icon--delivery d-flex align-items-center justify-content-center">
                            <img src="{$advantage->filename|resize:50:50:false:$config->resized_advantages_dir}">
                        </div>
                    {/if}
                    <div class="advantages__title" data-language="advantage_1">{$advantage->text}</div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
{/if}