<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\ReceiptRegistrationStatus;
use YandexCheckout\Model\RefundStatus;
use YandexCheckout\Request\Refunds\AbstractRefundResponse;

abstract class AbstractRefundResponseTest extends TestCase
{
    /**
     * @param array $options
     * @return AbstractRefundResponse
     */
    abstract protected function getTestInstance($options);

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetId($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertEquals($options['id'], $instance->getId());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetPaymentId($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertEquals($options['payment_id'], $instance->getPaymentId());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetStatus($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertEquals($options['status'], $instance->getStatus());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetCreatedAt($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertTrue($instance->getCreatedAt() instanceof \DateTime);
        self::assertEquals($options['created_at'], $instance->getCreatedAt()->format(DATE_ATOM));
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetAmount($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertTrue($instance->getAmount() instanceof AmountInterface);
        self::assertEquals($options['amount']['value'], $instance->getAmount()->getValue());
        self::assertEquals($options['amount']['currency'], $instance->getAmount()->getCurrency());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetReceiptRegistered($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['receipt_registration'])) {
            self::assertNull($instance->getReceiptRegistration());
        } else {
            self::assertEquals($options['receipt_registration'], $instance->getReceiptRegistration());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetComment($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['comment'])) {
            self::assertNull($instance->getComment());
        } else {
            self::assertEquals($options['comment'], $instance->getComment());
        }
    }

    public function validDataProvider()
    {
        $result = array();
        for ($i = 0; $i < 10; $i++) {
            $payment = array(
                'id' => Random::str(36),
                'payment_id' => Random::str(36),
                'status' => Random::value(RefundStatus::getValidValues()),
                'created_at' => date(DATE_ATOM, mt_rand(1, time())),
                'authorized_at' => date(DATE_ATOM, mt_rand(1, time())),
                'amount' => array(
                    'value' => mt_rand(100, 100000),
                    'currency' => Random::value(CurrencyCode::getValidValues()),
                ),
                'receipt_registration' => Random::value(ReceiptRegistrationStatus::getValidValues()),
                'comment' => uniqid(),
            );
            $result[] = array($payment);
        }
        return $result;
    }
}