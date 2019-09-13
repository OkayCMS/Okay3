<?php

namespace Tests\YandexCheckout\Request\Payments\Payment;

use Tests\YandexCheckout\Request\Payments\AbstractPaymentResponseTest;
use YandexCheckout\Request\Payments\Payment\CancelResponse;

require_once __DIR__ . '/../AbstractPaymentResponseTest.php';

class CancelResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CancelResponse($options);
    }
}