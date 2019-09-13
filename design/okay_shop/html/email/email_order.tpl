{$subject = "`$lang->email_order_title` `$order->id`" scope=global}

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{$btr->email_new_order|escape} № {$order->id}</title>
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">

    {include "email/email_head.tpl"}
</head>
<body>
<div class="es-wrapper-color">
    <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td class="es-p0t es-p0b" valign="center">

                {* Header email *}
                {include "email/email_header.tpl"}

                <table class="es-content" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center">
                                <tbody>
                                <tr>
                                    <td class="es-p10t es-p10b es-p20r es-p20l" align="center">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-p10t es-p15b" align="center">
                                                                <h1>{$lang->email_order_heading} <span class="es-number-order">№ {$order->id}</span><br></h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="es-p10t es-p0b es-p30r es-p30l" align="center">
                                                                <p>{$lang->email_comment_hello} <i>{$order->name|escape}</i>. {$lang->email_order_order_message} <strong>№{$order->id}</strong> {$lang->email_order_text_of} <strong>{$order->date|date}:{$order->date|time}.</strong> {$lang->email_order_text_status}
                                                                    <span class="es-status-color">{$order_status->name|escape}</span></p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="es-p15t es-p10b" align="center">
                                                                <a href="{url_generator route="order" url=$order->url absolute=1}" class="es-button" target="_blank">{$lang->email_order_button}</a>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="es-content" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center">
                                <tbody>
                                <tr>
                                    <td class="es-p30b es-p20r es-p20l" align="left">
                                        <table width="100%" cellspacing="0" cellpadding="0" align="left">
                                            <tbody>
                                            <tr>
                                                <td class="es-p20t es-p10b" align="left">
                                                    <table class="es-left" cellspacing="0" cellpadding="0" align="left">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-m-p0r es-m-p20b" width="100%" valign="top" align="center">
                                                                <table width="100%" cellspacing="0" cellpadding="0">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="esd-block-text" align="left">
                                                                            <h4>{$lang->email_order_details_title}</h4>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="es-m-p20b" width="100%" align="left">
                                                    <table class="es-table-infobox" cellspacing="1" cellpadding="1" border="0" align="left">
                                                        <tbody>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->user_number_of_order}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>№ {$order->id}</span></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_date}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->date|date}:{$order->date|time}</span></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_status}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order_status->name|escape}</span></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_payment}:</span></td>
                                                            <td class="es-p5t es-p5b">
                                                                    <span>
                                                                        {if $order->paid == 1}
                                                                             {$lang->email_order_paid}
                                                                         {else}
                                                                             {$lang->email_order_not_paid}
                                                                         {/if}
                                                                    </span>
                                                            </td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_name}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->name|escape}</span></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_email}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->email|escape}</span></td>
                                                        </tr>
                                                        {if $order->phone}
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_phone}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->phone|escape}</span></td>
                                                        </tr>
                                                        {/if}
                                                        {if $order->address}
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_address}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->address|escape}</span></td>
                                                        </tr>
                                                        {/if}
                                                        {if $order->comment}
                                                        <tr valign="top">
                                                            <td class="es-p5t es-p5b" width="180px"><span>{$lang->email_order_comment}:</span></td>
                                                            <td class="es-p5t es-p5b"><span>{$order->comment|escape|nl2br}</span></td>
                                                        </tr>
                                                        {/if}
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <table class="es-content" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center">
                                <tbody>
                                <tr>
                                    <td class="es-p10t es-p0b es-p20r es-p20l" align="left">
                                        <table class="es-left" cellspacing="0" cellpadding="0" align="left">
                                            <tbody>
                                            <tr>
                                                <td class="es-p20t es-p10b" align="left">
                                                    <table class="es-left" cellspacing="0" cellpadding="0" align="left">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-m-p0r es-m-p10b" width="100%" valign="top" align="center">
                                                                <table width="100%" cellspacing="0" cellpadding="0">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="esd-block-text" align="left">
                                                                            <h4>{$lang->email_order_booking_title}</h4>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="es-p20r es-p20l" align="left">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td width="100%" valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-p10b" align="center">
                                                                <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="border-bottom: 1px solid #dbdbdb; background: #dbdbdb; height: 1px; width: 100%; margin: 0px;"></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                {foreach $purchases as $purchase}
                                <tr>
                                    <td class="es-p5t es-p10b es-p20r es-p20l" align="left">
                                        <table class="es-left" cellspacing="0" cellpadding="0" align="left">
                                            <tbody>
                                            <tr>
                                                <td class="es-m-p0r es-m-p20b" width="178" valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td align="center">
                                                                <a href="{url_generator route="product" url=$purchase->product->url absolute=1}">
                                                                    {if $purchase->product->image}
                                                                    <img align="middle" src="{$purchase->product->image->filename|resize:120:120}" />
                                                                    {else}
                                                                    <img width="100" height="100" src="{$rootUrl}/design/{get_theme}/images/no_image.png" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                                                                    {/if}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <table width="380px" cellspacing="0" cellpadding="0" align="right">
                                            <tbody>
                                            <tr>
                                                <td width="100%" align="left">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td align="left">
                                                                <p><br></p>
                                                                <table style="width: 100%;" cellspacing="1" cellpadding="1" border="0">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <a href="{url_generator route="product" url=$purchase->product->url absolute=1}" style="font-family: 'Trebuchet MS';font-size: 16px;color: #222;text-decoration: none;line-height: normal;">{$purchase->product_name|escape}</a><br />
                                                                            <span class="es-p5t"><em><span style="color: rgb(128, 128, 128); font-size: 12px;">{$purchase->variant_name|escape}</span></em></span>
                                                                            {if $purchase->variant->stock == 0}
                                                                            <div class="es-p5t" style="color: #000; font-size: 12px;font-weight: 600">{$lang->product_pre_order}</div>
                                                                            {/if}
                                                                        </td>
                                                                        <td style="text-align: center;" width="60">
                                                                            {$purchase->amount} {if $purchase->units}{$purchase->units|escape}{else}{$settings->units}{/if}
                                                                        </td>
                                                                        <td style="text-align: center;" width="100">
                                                                            <b>{$purchase->price|convert:$currency->id}&nbsp;{$currency->sign}</b>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="es-p20r es-p20l" align="left">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td width="560" valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-p10b" align="center">
                                                                <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="border-bottom: 1px solid #dbdbdb; background: #dbdbdb; height: 1px; width: 100%; margin: 0px;"></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                {/foreach}
                                <tr>
                                    <td class="es-p15t es-p30b es-p20r es-p0l" align="left">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td align="right">
                                                                <table style="width: 500px;" cellspacing="1" cellpadding="1" border="0" align="right">
                                                                    <tbody>
                                                                    {if $order->discount}
                                                                    <tr>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%;">{$lang->email_order_discount}:</td>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%; color: #000;">{$order->discount}&nbsp;%</td>
                                                                    </tr>
                                                                    {/if}

                                                                    {if $order->coupon_discount>0}
                                                                    <tr>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%;">{$lang->email_order_coupon} {$order->coupon_code}:</td>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%; color: #000;">&minus;{$order->coupon_discount}&nbsp;{$currency->sign}</td>
                                                                    </tr>
                                                                    {/if}

                                                                    {if $order->separate_delivery || !$order->separate_delivery && $order->delivery_price > 0}
                                                                    <tr>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%;">{$delivery->name|escape}:</td>
                                                                        <td style="text-align: right; font-size: 18px; line-height: 150%; color: #000;">
                                                                            {if !$order->separate_delivery}{$order->delivery_price|convert:$currency->id}&nbsp;{$currency->sign} {else}{/if}
                                                                        </td>
                                                                    </tr>
                                                                    {/if}

                                                                    <tr class="es-p5t">
                                                                        <td style="text-align: right; font-size: 20px; line-height: 150%;"><strong>{$lang->email_order_total}:</strong></td>
                                                                        <td style="text-align: right; font-size: 20px; line-height: 150%; color: #F36D17;"><strong>{$order->total_price|convert:$currency->id}&nbsp;{$currency->sign}</strong></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>

                {* Footer email *}
                {include "email/email_footer.tpl"}

            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
