<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataApplePay;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataApplePayTest extends AbstractPaymentDataApplePayTest
{
    /**
     * @return PaymentDataApplePay
     */
    protected function getTestInstance()
    {
        return new PaymentDataApplePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::APPLE_PAY;
    }
}