<style type="text/css">
    .yamoney_kassa_buttons {
        display: flex;
        margin-bottom: 20px;
    }

    .ya_kassa_installments_button_container {
        margin-right: 20px;
    }

    .yamoney-pay-button {
        position: relative;
        height: 60px;
        width: 155px;
        border-radius: 4px;
        font-family: YandexSansTextApp-Regular, Arial, Helvetica, sans-serif;
        text-align: center;
    }

    .yamoney-pay-button button {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 4px;
        transition: 0.1s ease-out 0s;
        color: #000;
        box-sizing: border-box;
        outline: 0;
        border: 0;
        background: #FFDB4D;
        cursor: pointer;
        font-size: 12px;
    }

    .yamoney-pay-button button:hover, .yamoney-pay-button button:active {
        background: #f2c200;
    }

    .yamoney-pay-button button span {
        display: block;
        font-size: 20px;
        line-height: 20px;
    }

    .yamoney-pay-button_type_fly {
        box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.12), 0 5px 10px -3px rgba(0, 0, 0, 0.3);
    }

    .ya_checkout_button {
        cursor: pointer;
    }

    .ya_checkout_button:hover {
        background-color: #abff87;
    }
</style>
<div class="row">
    <form class="col-lg-7" method="POST">
        <input type="hidden" name="payment_submit"/>
        <input type="hidden" name="payment_type" id="pm_yandex_money_payment_type" value="{$payment_type|escape}"/>
        {$onKassaSide = $settings_pay['yandex_api_paymode'] === 'kassa'}
        {$showInstallmentsButton = false}
        {$showPayWithYandexButton = false}

        {if $onKassaSide }
            {$showInstallmentsButton = $settings_pay['yandex_show_installments_button']}
            {$showPayWithYandexButton = $settings_pay['yandex_show_pay_with_yandex_button']}
            {if $showInstallmentsButton || $showPayWithYandexButton}
                <div class="yamoney_kassa_buttons">
                    {if $showInstallmentsButton}
                        <div class="ya_kassa_installments_button_container"></div>
                    {/if}
                    {if $showPayWithYandexButton}
                        <div class="yamoney-pay-button {if !$showInstallmentsButton} yamoney-pay-button_type_fly{/if}">
                            <button type="submit"><span>Заплатить</span>через Яндекс</button>
                        </div>
                    {/if}
                </div>
            {/if}

        {/if}

        {if !$onKassaSide || ($onKassaSide && !$showPayWithYandexButton)}
            <input type="submit" name="submit-button" value="{$button_text}" class="btn_order">
        {/if}

    </form>
</div>
{if $onKassaSide && $showInstallmentsButton}
{literal}
    <script src="https://static.yandex.net/kassa/pay-in-parts/ui/v1/"></script>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            const yaShopId = {/literal}{$settings_pay['yandex_api_shopid']} {literal};
            const yaAmount = {/literal}{$amount}{literal};

            function createCheckoutCreditUI() {
                if (!YandexCheckoutCreditUI) {
                    setTimeout(createCheckoutCreditUI, 200);
                }
                const checkoutCreditUI = YandexCheckoutCreditUI({
                    shopId: yaShopId,
                    sum: yaAmount
                });
                const checkoutCreditButton = checkoutCreditUI({
                    type: 'button',
                    domSelector: '.ya_kassa_installments_button_container'
                });
                checkoutCreditButton.on('click', function () {
                    jQuery('#pm_yandex_money_payment_type').val('installments');
                });
            };
            setTimeout(createCheckoutCreditUI, 200);
        });
    </script>
{/literal}
{/if}
