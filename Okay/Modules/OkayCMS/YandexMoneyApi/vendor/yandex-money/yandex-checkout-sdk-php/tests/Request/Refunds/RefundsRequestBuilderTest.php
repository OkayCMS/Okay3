<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\RefundStatus;
use YandexCheckout\Request\Refunds\RefundsRequestBuilder;

class RefundsRequestBuilderTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAccountId($options)
    {
        $builder = new RefundsRequestBuilder();
        try {
            $builder->build();
        } catch (\RuntimeException $e) {
            $builder->setAccountId($options['accountId']);
            if (!empty($options['accountId'])) {
                $instance = $builder->build();
                self::assertEquals($options['accountId'], $instance->getAccountId());
                return;
            } else {
                $this->setExpectedException('\RuntimeException');
                $builder->build();
            }
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetRefundId($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getRefundId());

        $builder->setRefundId($options['refundId']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['refundId'])) {
            self::assertNull($instance->getRefundId());
        } else {
            self::assertEquals($options['refundId'], $instance->getRefundId());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetPaymentId($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getPaymentId());

        $builder->setPaymentId($options['paymentId']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['paymentId'])) {
            self::assertNull($instance->getPaymentId());
        } else {
            self::assertEquals($options['paymentId'], $instance->getPaymentId());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetProductGroupId($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getGatewayId());

        $builder->setGatewayId($options['gatewayId']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['gatewayId'])) {
            self::assertNull($instance->getGatewayId());
        } else {
            self::assertEquals($options['gatewayId'], $instance->getGatewayId());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreateGte($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getCreatedGte());

        $builder->setCreatedGte($options['createGte']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['createGte'])) {
            self::assertNull($instance->getCreatedGte());
        } else {
            self::assertEquals($options['createGte'], $instance->getCreatedGte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreateGt($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getCreatedGt());

        $builder->setCreatedGt($options['createGt']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['createGt'])) {
            self::assertNull($instance->getCreatedGt());
        } else {
            self::assertEquals($options['createGt'], $instance->getCreatedGt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreateLte($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getCreatedLte());

        $builder->setCreatedLte($options['createLte']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['createLte'])) {
            self::assertNull($instance->getCreatedLte());
        } else {
            self::assertEquals($options['createLte'], $instance->getCreatedLte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCreateLt($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getCreatedLt());

        $builder->setCreatedLt($options['createLt']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['createLt'])) {
            self::assertNull($instance->getCreatedLt());
        } else {
            self::assertEquals($options['createLt'], $instance->getCreatedLt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAuthorizedGte($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getAuthorizedGte());

        $builder->setAuthorizedGte($options['authorizedGte']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['authorizedGte'])) {
            self::assertNull($instance->getAuthorizedGte());
        } else {
            self::assertEquals($options['authorizedGte'], $instance->getAuthorizedGte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAuthorizedGt($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getAuthorizedGt());

        $builder->setAuthorizedGt($options['authorizedGt']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['authorizedGt'])) {
            self::assertNull($instance->getAuthorizedGt());
        } else {
            self::assertEquals($options['authorizedGt'], $instance->getAuthorizedGt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAuthorizedLte($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getAuthorizedLte());

        $builder->setAuthorizedLte($options['authorizedLte']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['authorizedLte'])) {
            self::assertNull($instance->getAuthorizedLte());
        } else {
            self::assertEquals($options['authorizedLte'], $instance->getAuthorizedLte()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAuthorizedLt($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getAuthorizedLt());

        $builder->setAuthorizedLt($options['authorizedLt']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['authorizedLt'])) {
            self::assertNull($instance->getAuthorizedLt());
        } else {
            self::assertEquals($options['authorizedLt'], $instance->getAuthorizedLt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetStatus($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getStatus());

        $builder->setStatus($options['status']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['status'])) {
            self::assertNull($instance->getStatus());
        } else {
            self::assertEquals($options['status'], $instance->getStatus());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetNextPage($options)
    {
        $builder = new RefundsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getNextPage());

        $builder->setNextPage($options['nextPage']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['nextPage'])) {
            self::assertNull($instance->getNextPage());
        } else {
            self::assertEquals($options['nextPage'], $instance->getNextPage());
        }
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'refundId' => null,
                    'paymentId' => null,
                    'accountId' => null,
                    'gatewayId' => null,
                    'createGte' => null,
                    'createGt' => null,
                    'createLte' => null,
                    'createLt' => null,
                    'authorizedGte' => null,
                    'authorizedGt' => null,
                    'authorizedLte' => null,
                    'authorizedLt' => null,
                    'status' => null,
                    'nextPage' => null,
                ),
            ),
            array(
                array(
                    'refundId' => '',
                    'paymentId' => '',
                    'accountId' => '',
                    'gatewayId' => '',
                    'createGte' => '',
                    'createGt' => '',
                    'createLte' => '',
                    'createLt' => '',
                    'authorizedGte' => '',
                    'authorizedGt' => '',
                    'authorizedLte' => '',
                    'authorizedLt' => '',
                    'status' => '',
                    'nextPage' => '',
                ),
            ),
        );
        $statuses = RefundStatus::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'refundId' => $this->randomString(36),
                'paymentId' => $this->randomString(36),
                'accountId'    => uniqid(),
                'gatewayId' => uniqid(),
                'createGte' => date(DATE_ATOM, mt_rand(1, time())),
                'createGt' => date(DATE_ATOM, mt_rand(1, time())),
                'createLte' => date(DATE_ATOM, mt_rand(1, time())),
                'createLt' => date(DATE_ATOM, mt_rand(1, time())),
                'authorizedGte' => date(DATE_ATOM, mt_rand(1, time())),
                'authorizedGt' => date(DATE_ATOM, mt_rand(1, time())),
                'authorizedLte' => date(DATE_ATOM, mt_rand(1, time())),
                'authorizedLt' => date(DATE_ATOM, mt_rand(1, time())),
                'status' => $statuses[mt_rand(0, count($statuses) - 1)],
                'nextPage' => uniqid(),
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
                $rnd = mt_rand(0, strlen($chars) - 1);
                $char = substr($chars, $rnd, 1);
            }
            $result .= $char;
        }
        return $result;
    }
}