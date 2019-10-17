<?php

namespace Tests\YandexCheckout\Request\Payments;

use YandexCheckout\Request\Payments\PaymentResponse;

class PaymentResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new PaymentResponse($options);
    }
}