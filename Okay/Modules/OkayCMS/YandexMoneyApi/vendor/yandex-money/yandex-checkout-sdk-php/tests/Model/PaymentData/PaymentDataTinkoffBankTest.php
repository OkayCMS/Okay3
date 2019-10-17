<?php


namespace Tests\YandexCheckout\Model\PaymentData;


use YandexCheckout\Model\PaymentData\AbstractPaymentData;
use YandexCheckout\Model\PaymentData\PaymentDataTinkoffBank;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataTinkoffBankTest extends AbstractPaymentDataTest
{

    /**
     * @return AbstractPaymentData
     */
    protected function getTestInstance()
    {
        return new PaymentDataTinkoffBank();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::TINKOFF_BANK;

    }
}