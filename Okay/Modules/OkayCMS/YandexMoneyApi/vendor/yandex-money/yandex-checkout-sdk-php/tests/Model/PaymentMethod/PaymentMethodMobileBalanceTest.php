<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodMobileBalance;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodMobileBalanceTest extends AbstractPaymentMethodPhoneTest
{
    /**
     * @return PaymentMethodMobileBalance
     */
    protected function getTestInstance()
    {
        return new PaymentMethodMobileBalance();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::MOBILE_BALANCE;
    }
}