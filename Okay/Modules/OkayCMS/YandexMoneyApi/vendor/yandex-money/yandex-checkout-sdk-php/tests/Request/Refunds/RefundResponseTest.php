<?php

namespace Tests\YandexCheckout\Request\Refunds;

use YandexCheckout\Request\Refunds\RefundResponse;

require_once __DIR__ . '/AbstractRefundResponseTest.php';

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