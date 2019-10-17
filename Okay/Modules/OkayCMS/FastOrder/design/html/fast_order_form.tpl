<div class="hidden">
    <form id="fn_fast_order" class="form form--boxed popup fn_validate_fast_order" method="post" action="{url_generator route="OkayCMS.FastOrder.CreateOrder" absolute=1}">
        {* The form heading *}
        <div class="form__header">
            <div class="form__title">
                <span data-language="fast_order">{$lang->fast_order}</span>
            </div>
        </div>

        <div class="form__body">
            <input id="fast_order_variant_id" value="" name="variant_id" type="hidden"/>
            <input id="fast_order_variant_id" value="" name="amount" type="hidden"/>
            <input type="hidden" name="IsFastOrder" value="true">

            <h6 id="fast_order_product_name"></h6>

            <div class="form__group">
                <input class="fn_validate_fast_name form__input form__placeholder--focus" type="text" name="name" value="" />
                <span class="form__placeholder" data-language="form_name">{$lang->form_name}*</span>
                {*<span id="fast_order_name_error"></span>*}
            </div>

            <div class="form__group">
                <input  class="fn_validate_fast_phone form__input form__placeholder--focus" type="text" name="phone" value="" />
                <span class="form__placeholder" data-language="form_phone">{$lang->form_phone}*</span>
                {*<span id="fast_order_phone_error"></span>*}
            </div>
         </div>

        <div class="form__footer">
            <input class="form__button button--blick fn_fast_order_submit" type="submit" name="checkout" data-language="callback_order" value="{$lang->callback_order}"/>
        </div>
     </form>
</div>