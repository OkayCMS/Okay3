<?php

namespace Tests\YandexCheckout\Request\Payments\Payment;

use Tests\YandexCheckout\Request\Payments\AbstractPaymentResponseTest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;

class CreateCaptureResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CreateCaptureResponse($options);
    }
}
