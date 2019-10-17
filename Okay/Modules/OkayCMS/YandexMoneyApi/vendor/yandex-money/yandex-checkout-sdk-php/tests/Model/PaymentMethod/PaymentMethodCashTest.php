<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodCash;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodCashTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodCash
     */
    protected function getTestInstance()
    {
        return new PaymentMethodCash();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::CASH;
    }
}