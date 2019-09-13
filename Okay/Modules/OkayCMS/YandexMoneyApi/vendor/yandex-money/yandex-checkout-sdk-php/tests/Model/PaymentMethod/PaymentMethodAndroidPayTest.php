<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodAndroidPay;
use YandexCheckout\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodAndroidPayTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodAndroidPay
     */
    protected function getTestInstance()
    {
        return new PaymentMethodAndroidPay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::ANDROID_PAY;
    }
}