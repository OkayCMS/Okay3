{if $purchase->discounts}
    <div class="order_discounted_block" style="display: none;">
        <div class="okay_list_body order_discounted_block__inner sort_extended">
            {foreach $purchase->discounts as $discount}
                <div class="fn_row okay_list_body_item fn_sort_item">
                    <div class="okay_list_row">
                        <input type="hidden" name="discount_positions[{$discount->id}]" value="{$discount->position}" />

                        <div class="okay_list_boding okay_list_drag move_zone">
                            {include file='svg_icon.tpl' svgId='drag_vertical'}
                        </div>

                        <div class="okay_list_boding okay_list_order_discounted_name">
                            <div class="form_create">
                                <input  name="discounts[{$discount->id}][name]" class="form-control input_create text_600" type="text" title="{$discount->name|escape}" value="{$discount->name|escape}">
                            </div>
                            <div class="form_create">
                                <input name="discounts[{$discount->id}][description]" class="form-control input_create text_grey text_400 font_12" type="text" title="{$discount->description|escape}" value="{$discount->description|escape}">
                            </div>
                        </div>
                        <div class="okay_list_boding okay_list_count hidden-md-down">
                            <div class="activity_of_switch">
                                <div class="activity_of_switch_item">
                                    <div class="okay_switch clearfix">
                                        <label class="switch switch-default">
                                            <input class="switch-input" name="discounts[{$discount->id}][from_last_discount]" value="1" type="checkbox" {if $discount->fromLastDiscount}checked{/if}>
                                            <span class="switch-label"></span>
                                            <span class="switch-handle"></span>
                                        </label>
                                        <label class="switch_label m-0" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="okay_list_boding okay_list_price">
                            <div class="input-group">
                                <input type="text" class="form-control" name="discounts[{$discount->id}][value]" value="{$discount->value}" />
                                <span class="input-group-addon p-0">
                                    {if $discount->type == 'absolute'}
                                        {$currency->code|escape}
                                    {else}
                                        %
                                    {/if}
                                </span>
                            </div>
                        </div>

                        <div class="okay_list_boding okay_list_order_amount_price">
                            <div class="text_dark text_warning text_600">
                                <span class="font_16">{$discount->priceAfterDiscount|round:2}</span>
                                <span class="font_12">{$currency->sign|escape}</span>
                            </div>
                        </div>

                        <div class="okay_list_boding okay_list_close">
                            {*delete*}
                            <button data-hint="{$btr->brands_delete_brand|escape}" type="button" class="btn_close hint-bottom-right-t-info-s-small-mobile hint-anim fn_discount_remove">
                                {include file='svg_icon.tpl' svgId='trash'}
                            </button>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}
