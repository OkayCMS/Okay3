<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodInstallments;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodInstallmentsTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodInstallments
     */
    protected function getTestInstance()
    {
        return new PaymentMethodInstallments();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::INSTALLMENTS;
    }
}