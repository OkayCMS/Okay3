<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodWebmoney;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodWebmoneyTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodWebmoney
     */
    protected function getTestInstance()
    {
        return new PaymentMethodWebmoney();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WEBMONEY;
    }
}