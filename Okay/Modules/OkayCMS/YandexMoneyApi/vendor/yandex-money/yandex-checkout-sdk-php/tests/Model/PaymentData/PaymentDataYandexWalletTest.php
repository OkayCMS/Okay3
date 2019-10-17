<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataYandexWallet;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataYandexWalletTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataYandexWallet
     */
    protected function getTestInstance()
    {
        return new PaymentDataYandexWallet();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::YANDEX_MONEY;
    }
}