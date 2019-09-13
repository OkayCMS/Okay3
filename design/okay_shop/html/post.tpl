{* Post page *}
{* The canonical address of the page *}

{$canonical="{url_generator route=$route_name url=$post->url absolute=1}" scope=global}

{* The page heading *}
<div class="block__header block__header--boxed block__header--border block__header--promo">
    <h1 class="block__heading">
        <span data-post="{$post->id}">{$post->name|escape}</span>
    </h1>
    {* Post date *}
    <div class="block__header_promo{if !$post->date} hidden{/if}">
        <b>{$post->date|date:"d cFR Y, cD"}</b>
    </div>
</div>
<div class="block__body block--boxed block--border">
    {* Post content *}
    <div class="block__description block__description--style">
        {$post->description}
    </div>

    {* Social share *}
    {* Share buttons *}
    <div class="post_share_boxed">
        <div class="share">
            <div class="share__text post_share__text">
                <span data-language="product_share">{$lang->product_share}:</span>
            </div>
            <div class="fn_share jssocials share__icons"></div>
        </div>
    </div>
</div>

{* Previous/Next posts *}
{if $prev_post || $next_post}
<nav>
    <ol class="pager row">
        <li class="col-xs-12{if $next_post} col-sm-6{else} col-sm-12{/if}">
            {if $prev_post}
                {if $prev_post->type_post == 'blog'}
                    {$prev_post_url = {url_generator route='blog_item' url=$prev_post->url}}
                {elseif $prev_post->type_post == 'news'}
                    {$prev_post_url = {url_generator route='news_item' url=$prev_post->url}}
                {/if}
                <a class="" href="{$prev_post_url}">
                    {include file="svg.tpl" svgId="arrow_up_icon"}
                    <span>{$prev_post->name}</span>
                </a>
            {/if}
        </li>
        <li class="col-xs-12 col-sm-6">
            {if $next_post}
                {if $next_post->type_post == 'blog'}
                    {$next_post_url = {url_generator route='blog_item' url=$next_post->url}}
                {elseif $next_post->type_post == 'news'}
                    {$next_post_url = {url_generator route='news_item' url=$next_post->url}}
                {/if}
                <a href="{$next_post_url}">
                    <span>{$next_post->name}</span>
                    {include file="svg.tpl" svgId="arrow_up_icon"}
                </a>
            {/if}
        </li>
    </ol>
</nav>
{/if}

<div class="block block--boxed block--border">
    <div class="block__header">
        <div class="block__title">
            <span data-language="post_comments">{$lang->post_comments}</span>
        </div>
    </div>
    <div class="block__body">
        <div id="comments">
            <div class="comment-wrap d-lg-flex flex-lg-row align-items-md-start">
                <div class="comment f_col-lg-7">
                    {if $comments}
                        {function name=comments_tree level=0}
                        {foreach $comments as $comment}
                        <div class="comment__item {if $level > 0} admin_note{/if}">
                            {* Comment anchor *}
                            <a name="comment_{$comment->id}"></a>
                            {* Comment list *}
                            <div class="comment__inner">
                                <div class="comment__icon">
                                    {if $level > 0}
                                    {include file="svg.tpl" svgId="comment-admin_icon"}
                                    {else}
                                    {include file="svg.tpl" svgId="comment-user_icon"}
                                    {/if}
                                </div>
                                <div class="comment__boxed">
                                    <div class="comment__header">
                                        {* Comment name *}
                                        <div class="comment__author">
                                            <span class="comment__name">{$comment->name|escape}</span>
                                            {* Comment status *}
                                            {if !$comment->approved}
                                            <span class="comment__status" data-language="post_comment_status">({$lang->post_comment_status})</span>
                                            {/if}
                                        </div>
                                        {* Comment date *}
                                        <div class="comment__date">
                                            <span>{$comment->date|date}, {$comment->date|time}</span>
                                        </div>
                                    </div>

                                    {* Comment content *}
                                    <div class="comment__body">
                                        {$comment->text|escape|nl2br}
                                    </div>
                                </div>
                            </div>
                            {if isset($children[$comment->id])}
                            {comments_tree comments=$children[$comment->id] level=$level+1}
                            {/if}
                        </div>
                        {/foreach}
                        {/function}
                        {comments_tree comments=$comments}
                    {else}
                        <div class="boxed boxed--big boxed--notify">
                            <span data-language="product_no_comments">{$lang->product_no_comments}</span>
                        </div>
                    {/if}
                </div>
                <div class="form_wrap f_col-lg-5">
                    {* Comment form *}
                    <form id="fn_blog_comment" class="fn_validate_post form form--boxed"  method="post" action="">
                        <div class="form__header">
                            <div class="form__title">
                                {include file="svg.tpl" svgId="comment_icon"}
                                <span data-language="post_write_comment">{$lang->post_write_comment}</span>
                            </div>
                        </div>
                        <div class="form__body">
                            {* Form error messages *}
                            {if $error}
                            <div class="message_error">
                                {if $error=='captcha'}
                                <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                                {elseif $error=='empty_name'}
                                <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                                {elseif $error=='empty_comment'}
                                <span data-language="form_enter_comment">{$lang->form_enter_comment}</span>
                                {elseif $error=='empty_email'}
                                <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                {/if}
                            </div>
                            {/if}

                            {* User's name *}
                            <div class="form__group">
                                <input class="form__input form__placeholder--focus" type="text" name="name" value="{$comment_name|escape}" />
                                <span class="form__placeholder">{$lang->form_name}*</span>
                            </div>

                            {* User's email *}
                            <div class="form__group">
                                <input class="form__input form__placeholder--focus" type="text" name="email" value="{$comment_email|escape}" data-language="form_email" />
                                <span class="form__placeholder">{$lang->form_email}</span>
                            </div>

                            {* User's comment *}
                            <div class="form__group">
                                <textarea class="form__textarea form__placeholder--focus" rows="3" name="text" >{$comment_text}</textarea>
                                <span class="form__placeholder">{$lang->form_enter_comment}*</span>
                            </div>
                        </div>
                        <div class="form__footer">
                            {* Captcha *}
                            {if $settings->captcha_post}
                                {if $settings->captcha_type == "v2"}
                                    <div class="captcha">
                                        <div id="recaptcha1"></div>
                                    </div>
                                {elseif $settings->captcha_type == "default"}
                                    {get_captcha var="captcha_post"}
                                    <div class="captcha">
                                        <div class="secret_number">{$captcha_post[0]|escape} + ? =  {$captcha_post[1]|escape}</div>
                                        <div class="form__captcha">
                                            <input class="form__input form__input_captcha form__placeholder--focus" type="text" name="captcha_code" value="" />
                                            <span class="form__placeholder">{$lang->form_enter_captcha}*</span>
                                        </div>
                                    </div>
                                {/if}
                            {/if}

                            <input type="hidden" name="comment" value="1">
                            {* Submit button *}
                            <button class="form__button button--blick g-recaptcha" type="submit" name="comment" {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}" data-badge='bottomleft' data-callback="onSubmit"{/if} value="{$lang->form_send}">
                                <span  data-language="form_send">{$lang->form_send}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{* Related products *}
{if $related_products}
    <div class="block block--boxed block--border">
        <div class="block__header">
            <div class="block__title">
                <span data-language="product_recommended_products">{$lang->product_recommended_products}</span>
            </div>
        </div>

        <div class="block__body">
            <div class="products_list row">
                {foreach $related_products as $p}
                    <div class="product_item col-xs-6 col-sm-4 col-md-4 col-xl-25">
                        {include "product_list.tpl" product = $p}
                    </div>
                {/foreach}
            </div>
        </div>
     </div>
{/if}
