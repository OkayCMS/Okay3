{* Account page *}

{* The page title *}
{$meta_title = $lang->user_title scope=global}

<div class="block">
    {* The page heading*}
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading"><span data-language="user_header">{$lang->user_header}</span></h1>
    </div>
    
    <div class="block block--boxed block--border">
        <div class="f_row flex-lg-row align-items-md-start">
            <div class="form_wrap f_col-lg-5">
                <form method="post" class="fn_validate_register form form--boxed form--account">
                    <div class="d-flex align-items-center form__profile profile">
                        <div class="profile__image">
                            <div class="profile__icon">
                                {include file="svg.tpl" svgId="comment-user_icon"}
                            </div>
                        </div>
                        <div class="profile__information">
                            <div class="profile__name">
                                <span>{$user->name|escape}</span>
                            </div>
                            {* Logout *}
                            <div class="profile__logout">
                                <a href="{url_generator route='logout'}" class="d-flex align-items-center button__logout">
                                    {include file="svg.tpl" svgId="exit_icon"}
                                    <span data-language="user_logout">{$lang->user_logout}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form__body">
                        {* Form error messages *}
                        {if $error}
                        <div class="message_error">
                            {if $error == 'empty_name'}
                                <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                            {elseif $error == 'empty_email'}
                                <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                            {elseif $error == 'empty_password'}
                                <span data-language="form_enter_password">{$lang->form_enter_password}</span>
                            {elseif $error == 'user_exists'}
                                <span data-language="register_user_registered">{$lang->register_user_registered}</span>
                            {else}
                                {$error|escape}
                            {/if}
                        </div>
                        {/if}

                        {* User's name *}
                        <div class="form__group">
                            <input class="form__input form__placeholder--focus" value="{$user->name|escape}" name="name" type="text" data-language="form_name" />
                            <span class="form__placeholder">{$lang->form_name}*</span>
                        </div>

                        {* User's email *}
                        <div class="form__group">
                            <input class="form__input form__placeholder--focus" value="{$user->email|escape}" name="email" type="text" data-language="form_email" />
                            <span class="form__placeholder">{$lang->form_email}*</span>
                        </div>

                        {* User's phone *}
                        <div class="form__group">
                            <input class="form__input form__placeholder--focus" value="{$user->phone|phone}" name="phone" type="text" data-language="form_phone" />
                            <span class="form__placeholder">{$lang->form_phone}</span>
                        </div>

                        {* User's address *}
                        <div class="form__group">
                            <input class="form__input form__placeholder--focus" value="{$user->address|escape}" name="address" type="text" data-language="form_address" />
                            <span class="form__placeholder">{$lang->form_address}</span>
                        </div>

                        {* User's password *}
                        <div class="form__group">
                            <p class="change_pass" onclick="$('#fn_password').toggle().prop('type', 'password').prop('name', 'password');return false;">
                                <span data-language="user_change_password">{$lang->user_change_password}</span>
                                {include file="svg.tpl" svgId="arrow_right2"}
                            </p>
                            <input class="form__input form__placeholder--focus " id="fn_password" value="" name="" type="" style="display:none;" {*placeholder="{$lang->user_change_password}"*}/>
                        </div>
                    </div>

                    <div class="form__footer">
                        {* Submit button *}
                        <button type="submit" class="form__button button--blick" name="user_save" value="{$lang->form_save}">
                            <span data-language="form_save">{$lang->form_save}</span>
                        </button>
                    </div>
                </form>
            </div>
    
            {* Orders history *}
            {if $orders}
                <div class="form_wrap f_col-lg-7">
                    <div class="block_explanation">
                        <div class="block_explanation__header">
                            <span data-language="user_my_orders">{$lang->user_my_orders}</span>
                        </div>
                        <div class="block_explanation__body">
                            <div class="table_wrapper block__description--style">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>
                                            <span data-language="user_number_of_order">{$lang->user_number_of_order}</span>
                                        </th>
                                        <th>
                                            <span data-language="user_order_date">{$lang->user_order_date}</span>
                                        </th>
                                        <th>
                                            <span data-language="user_order_status">{$lang->user_order_status}</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    {foreach $orders as $order}
                                    <tr>
                                        {* Order number *}
                                        <td>
                                            <a href='{url_generator route="order" url=$order->url}'><span data-language="user_order_number">{$lang->user_order_number}</span>{$order->id}</a>
                                        </td>

                                        {* Order date *}
                                        <td>{$order->date|date}</td>

                                        {* Order status *}
                                        <td>
                                            {if $order->paid == 1}
                                            <span data-language="status_paid">{$lang->status_paid}</span>,
                                            {/if}
                                            {$orders_status[$order->status_id]->name|escape}
                                        </td>
                                    </tr>
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>