<?php

namespace Tests\YandexCheckout\Request\Payments;

use YandexCheckout\Request\Payments\PaymentResponse;

require_once __DIR__ . '/AbstractPaymentResponseTest.php';

class PaymentResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new PaymentResponse($options);
    }
}