<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodGooglePay;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodGooglePayTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodGooglePay
     */
    protected function getTestInstance()
    {
        return new PaymentMethodGooglePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::GOOGLE_PAY;
    }
}