<?php

namespace Tests\YandexCheckout\Request\Payments\Payment;

use Tests\YandexCheckout\Request\Payments\AbstractPaymentResponseTest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;

require_once __DIR__ . '/../AbstractPaymentResponseTest.php';

class CreateCaptureResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CreateCaptureResponse($options);
    }
}
