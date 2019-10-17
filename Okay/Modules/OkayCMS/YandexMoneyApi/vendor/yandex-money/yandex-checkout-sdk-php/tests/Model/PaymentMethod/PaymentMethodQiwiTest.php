<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodQiwi;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodQiwiTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodQiwi
     */
    protected function getTestInstance()
    {
        return new PaymentMethodQiwi();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::QIWI;
    }
}