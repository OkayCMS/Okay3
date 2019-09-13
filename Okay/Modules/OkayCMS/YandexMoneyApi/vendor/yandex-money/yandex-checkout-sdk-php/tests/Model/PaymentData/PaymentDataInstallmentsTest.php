<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataInstallments;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataInstallmentsTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataInstallments
     */
    protected function getTestInstance()
    {
        return new PaymentDataInstallments();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::INSTALLMENTS;
    }
}