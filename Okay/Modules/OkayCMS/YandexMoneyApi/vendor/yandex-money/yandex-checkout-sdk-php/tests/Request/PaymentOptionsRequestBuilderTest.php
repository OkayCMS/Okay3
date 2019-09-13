<?php

namespace Tests\YandexCheckout\Request;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Request\PaymentOptionsRequestBuilder;

class PaymentOptionsRequestBuilderTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAccountId($options)
    {
        $builder = new PaymentOptionsRequestBuilder();
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
    public function testSetGatewayId($options)
    {
        $builder = new PaymentOptionsRequestBuilder();

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
    public function testSetAmount($options)
    {
        $builder = new PaymentOptionsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getAmount());

        $builder->setAmount($options['amount']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['amount'])) {
            self::assertNull($instance->getAmount());
        } elseif (is_numeric($options['amount'])) {
            self::assertEquals(number_format($options['amount'], 2, '.', ''), $instance->getAmount());
        } else {
            self::assertEquals($instance->getAmount(), $instance->getAmount());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetCurrency($options)
    {
        $builder = new PaymentOptionsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getCurrency());

        $builder->setCurrency($options['currency']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['currency'])) {
            self::assertNull($instance->getCurrency());
        } else {
            self::assertEquals($options['currency'], $instance->getCurrency());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetConfirmationType($options)
    {
        $builder = new PaymentOptionsRequestBuilder();

        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        self::assertNull($instance->getConfirmationType());

        $builder->setConfirmationType($options['confirmationType']);
        $instance = $builder->build(array('accountId' => 'valid_shopId'));
        if (empty($options['confirmationType'])) {
            self::assertNull($instance->getConfirmationType());
        } else {
            self::assertEquals($options['confirmationType'], $instance->getConfirmationType());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testBuild($options)
    {
        if (empty($options['accountId'])) {
            $options['accountId'] = uniqid();
        }

        $builder = new PaymentOptionsRequestBuilder();
        $instance = $builder->build($options);

        self::assertEquals($options['accountId'], $instance->getAccountId());

        if (empty($options['gatewayId'])) {
            self::assertNull($instance->getGatewayId());
        } else {
            self::assertEquals($options['gatewayId'], $instance->getGatewayId());
        }

        if (empty($options['amount'])) {
            self::assertNull($instance->getAmount());
        } elseif (is_numeric($options['amount'])) {
            self::assertEquals(number_format($options['amount'], 2, '.', ''), $instance->getAmount());
        } else {
            self::assertEquals($options['amount']->getValue(), $instance->getAmount());
        }

        if (empty($options['currency'])) {
            self::assertNull($instance->getCurrency());
        } else {
            self::assertEquals($options['currency'], $instance->getCurrency());
        }

        if (empty($options['confirmationType'])) {
            self::assertNull($instance->getConfirmationType());
        } else {
            self::assertEquals($options['confirmationType'], $instance->getConfirmationType());
        }
    }

    /**
     * @return array
     */
    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'accountId' => null,
                    'gatewayId' => null,
                    'amount' => null,
                    'currency' => null,
                    'confirmationType' => null,
                ),
            ),
            array(
                array(
                    'accountId' => '',
                    'gatewayId' => '',
                    'amount' => '',
                    'currency' => '',
                    'confirmationType' => '',
                ),
            ),
            array(
                array(
                    'accountId' => '',
                    'gatewayId' => '',
                    'amount' => new MonetaryAmount(Random::int(1, 100)),
                    'currency' => '',
                    'confirmationType' => '',
                ),
            ),
        );
        $currencies = CurrencyCode::getValidValues();
        $confirmations = ConfirmationType::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'accountId' => uniqid(),
                'gatewayId' => uniqid(),
                'amount' => Random::float(0.01, 1e+9),
                'currency' => $currencies[mt_rand(0, count($currencies) - 1)],
                'confirmationType' => $confirmations[mt_rand(0, count($confirmations) - 1)],
            );
            $result[] = array($request);
        }
        return $result;
    }
}
