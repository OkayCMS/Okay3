<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataGooglePay;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataGooglePayTest extends AbstractPaymentDataGooglePayTest
{
    /**
     * @return PaymentDataGooglePay
     */
    protected function getTestInstance()
    {
        return new PaymentDataGooglePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::GOOGLE_PAY;
    }
}