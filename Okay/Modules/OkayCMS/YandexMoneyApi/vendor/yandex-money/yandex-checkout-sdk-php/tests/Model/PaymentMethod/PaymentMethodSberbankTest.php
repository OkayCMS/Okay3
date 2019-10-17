<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodSberbank;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodSberbankTest extends AbstractPaymentMethodPhoneTest
{
    /**
     * @return PaymentMethodSberbank
     */
    protected function getTestInstance()
    {
        return new PaymentMethodSberbank();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::SBERBANK;
    }
}