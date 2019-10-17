<?php


namespace Tests\YandexCheckout\Model\PaymentMethod;


use YandexCheckout\Model\PaymentMethod\AbstractPaymentMethod;
use YandexCheckout\Model\PaymentMethod\PaymentMethodTinkoffBank;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodTinkoffBankTest extends AbstractPaymentMethodTest
{

    /**
     * @return AbstractPaymentMethod
     */
    protected function getTestInstance()
    {
        return new PaymentMethodTinkoffBank();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::TINKOFF_BANK;
    }
}