<?php

namespace Tests\YandexCheckout\Request\Refunds;

use YandexCheckout\Request\Refunds\CreateRefundResponse;

class CreateRefundResponseTest extends AbstractRefundResponseTest
{
    /**
     * @param array $options
     * @return CreateRefundResponse
     */
    protected function getTestInstance($options)
    {
        return new CreateRefundResponse($options);
    }
}