<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataWebmoney;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataWebmoneyTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataWebmoney
     */
    protected function getTestInstance()
    {
        return new PaymentDataWebmoney();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WEBMONEY;
    }
}