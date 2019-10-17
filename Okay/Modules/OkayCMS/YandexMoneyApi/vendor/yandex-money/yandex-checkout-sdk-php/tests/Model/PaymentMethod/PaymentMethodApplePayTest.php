<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodApplePay;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodApplePayTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodApplePay
     */
    protected function getTestInstance()
    {
        return new PaymentMethodApplePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::APPLE_PAY;
    }
}