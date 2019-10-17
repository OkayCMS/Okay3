<?php

namespace Tests\YandexCheckout\Request\Payments;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\PaymentStatus;
use YandexCheckout\Request\Payments\PaymentsRequestBuilder;

class PaymentsRequestBuilderTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetPage($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getPage());

        $builder->setPage($options['page']);
        $instance = $builder->build();
        if (empty($options['page'])) {
            self::assertNull($instance->getPage());
        } else {
            self::assertEquals($options['page'], $instance->getPage());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreatedAtGte($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getCreatedAtGte());

        $builder->setCreatedAtGte($options['createdAtGte']);
        $instance = $builder->build();
        if (empty($options['createdAtGte'])) {
            self::assertNull($instance->getCreatedAtGte());
        } else {
            self::assertEquals($options['createdAtGte'], $instance->getCreatedAtGte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreatedGt($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getCreatedAtGt());

        $builder->setCreatedAtGt($options['createdAtGt']);
        $instance = $builder->build();
        if (empty($options['createdAtGt'])) {
            self::assertNull($instance->getCreatedAtGt());
        } else {
            self::assertEquals($options['createdAtGt'], $instance->getCreatedAtGt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreatedLte($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getCreatedAtLte());

        $builder->setCreatedAtLte($options['createdAtLte']);
        $instance = $builder->build();
        if (empty($options['createdAtLte'])) {
            self::assertNull($instance->getCreatedAtLte());
        } else {
            self::assertEquals($options['createdAtLte'], $instance->getCreatedAtLte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreatedLt($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getCreatedAtLt());

        $builder->setCreatedAtLt($options['createdAtLt']);
        $instance = $builder->build();
        if (empty($options['createdAtLt'])) {
            self::assertNull($instance->getCreatedAtLt());
        } else {
            self::assertEquals($options['createdAtLt'], $instance->getCreatedAtLt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetLimit($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getLimit());

        $builder->setLimit($options['limit']);
        $instance = $builder->build();
        if (is_null($options['limit'])) {
            self::assertNull($instance->getLimit());
        } else {
            self::assertEquals($options['limit'], $instance->getLimit());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetRecipientGatewayId($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build(array());
        self::assertNull($instance->getRecipientGatewayId());

        $builder->setRecipientGatewayId(!empty($options['recipientGatewayId']) ? $options['recipientGatewayId'] : null);
        $instance = $builder->build();
        if (empty($options['recipientGatewayId'])) {
            self::assertNull($instance->getRecipientGatewayId());
        } else {
            self::assertEquals($options['recipientGatewayId'], $instance->getRecipientGatewayId());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetStatus($options)
    {
        $builder = new PaymentsRequestBuilder();

        $instance = $builder->build();
        self::assertNull($instance->getStatus());

        $builder->setStatus($options['status']);
        $instance = $builder->build();
        if (empty($options['status'])) {
            self::assertNull($instance->getStatus());
        } else {
            self::assertEquals($options['status'], $instance->getStatus());
        }
    }

    public function validDataProvider()
    {
        $result   = array(
            array(
                array(
                    'page'               => null,
                    'createdAtGte'       => null,
                    'createdAtGt'        => null,
                    'createdAtLte'       => null,
                    'createdAtLt'        => null,
                    'limit'              => null,
                    'recipientGatewayId' => null,
                    'status'             => null,
                ),
            ),
            array(
                array(
                    'page'               => '',
                    'createdAtGte'       => '',
                    'createdAtGt'        => '',
                    'createdAtLte'       => '',
                    'createdAtLt'        => '',
                    'limit'              => 0,
                    'recipientGatewayId' => '',
                    'status'             => '',
                ),
            ),
        );
        $statuses = PaymentStatus::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request  = array(
                'page'               => $this->randomString(mt_rand(1, 30)),
                'createdAtGte'       => date(DATE_ATOM, mt_rand(1, time())),
                'createdAtGt'        => date(DATE_ATOM, mt_rand(1, time())),
                'createdAtLte'       => date(DATE_ATOM, mt_rand(1, time())),
                'createdAtLt'        => date(DATE_ATOM, mt_rand(1, time())),
                'limit'              => mt_rand(1, 100),
                'recipientGatewayId' => $this->randomString(mt_rand(1, 10)),
                'status'             => $statuses[mt_rand(0, count($statuses) - 1)],
            );
            $result[] = array($request);
        }
        return $result;
    }

    private function randomString($length, $any = true)
    {
        static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+_.';

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($any) {
                $char = chr(mt_rand(32, 126));
            } else {
                $rnd  = mt_rand(0, strlen($chars) - 1);
                $char = substr($chars, $rnd, 1);
            }
            $result .= $char;
        }
        return $result;
    }
}