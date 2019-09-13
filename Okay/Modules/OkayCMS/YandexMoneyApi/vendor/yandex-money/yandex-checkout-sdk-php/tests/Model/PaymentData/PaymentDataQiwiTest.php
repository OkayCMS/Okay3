<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataQiwi;
use YandexCheckout\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataTest.php';

class PaymentDataQiwiTest extends AbstractPaymentDataPhoneTest
{
    /**
     * @return PaymentDataQiwi
     */
    protected function getTestInstance()
    {
        return new PaymentDataQiwi();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::QIWI;
    }
}