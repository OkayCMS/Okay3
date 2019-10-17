<?php

namespace Tests\YandexCheckout\Request\Refunds;

use YandexCheckout\Request\Refunds\RefundResponse;

class RefundResponseTest extends AbstractRefundResponseTest
{
    /**
     * @param array $options
     * @return RefundResponse
     */
    protected function getTestInstance($options)
    {
        return new RefundResponse($options);
    }
}