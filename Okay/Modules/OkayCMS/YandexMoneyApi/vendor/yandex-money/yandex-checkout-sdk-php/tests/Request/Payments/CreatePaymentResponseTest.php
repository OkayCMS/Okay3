<?php

namespace Tests\YandexCheckout\Request\Payments;

use YandexCheckout\Request\Payments\CreatePaymentResponse;

class CreatePaymentResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CreatePaymentResponse($options);
    }
}