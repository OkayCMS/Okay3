<?php

namespace Tests\YandexCheckout\Request\Payments\Payment;

use Tests\YandexCheckout\Request\Payments\AbstractPaymentResponseTest;
use YandexCheckout\Request\Payments\Payment\CancelResponse;

class CancelResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CancelResponse($options);
    }
}