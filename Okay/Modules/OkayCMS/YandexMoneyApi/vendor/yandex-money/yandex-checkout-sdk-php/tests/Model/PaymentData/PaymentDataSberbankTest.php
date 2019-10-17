<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataSberbank;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataSberbankTest extends AbstractPaymentDataPhoneTest
{
    /**
     * @return PaymentDataSberbank
     */
    protected function getTestInstance()
    {
        return new PaymentDataSberbank();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::SBERBANK;
    }
}