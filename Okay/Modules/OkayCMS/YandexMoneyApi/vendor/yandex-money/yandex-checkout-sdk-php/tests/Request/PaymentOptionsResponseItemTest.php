<?php

namespace Tests\YandexCheckout\Request;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Request\PaymentOptionsResponseItem;

class PaymentOptionsResponseItemTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testConstructor($options)
    {
        $instance = new PaymentOptionsResponseItem($options);

        self::assertEquals($options['payment_method_type'], $instance->getPaymentMethodType());
        self::assertEquals($options['confirmation_types'], $instance->getConfirmationTypes());
        self::assertEquals($options['charge']['value'], $instance->getCharge()->getValue());
        self::assertEquals($options['charge']['currency'], $instance->getCharge()->getCurrency());
        self::assertNotNull($instance->getFee());
        self::assertNotNull($instance->getExtraFee());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetPaymentMethod($options)
    {
        $instance = new PaymentOptionsResponseItem($options);
        self::assertEquals($options['payment_method_type'], $instance->getPaymentMethodType());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetConfirmationTypes($options)
    {
        $instance = new PaymentOptionsResponseItem($options);
        self::assertEquals($options['confirmation_types'], $instance->getConfirmationTypes());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetCharge($options)
    {
        $instance = new PaymentOptionsResponseItem($options);
        self::assertEquals($options['charge']['value'], $instance->getCharge()->getValue());
        self::assertEquals($options['charge']['currency'], $instance->getCharge()->getCurrency());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetFee($options)
    {
        $instance = new PaymentOptionsResponseItem($options);
        if (empty($options['fee'])) {
            self::assertEquals(0, $instance->getFee()->getValue());
            self::assertEquals($options['charge']['currency'], $instance->getFee()->getCurrency());
        } else {
            self::assertEquals($options['fee']['value'], $instance->getFee()->getValue());
            self::assertEquals($options['fee']['currency'], $instance->getFee()->getCurrency());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetExtraFee($options)
    {
        $instance = new PaymentOptionsResponseItem($options);
        if (empty($options['extra_fee'])) {
            self::assertFalse($instance->getExtraFee());
        } else {
            self::assertTrue($instance->getExtraFee());
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
                    'payment_method_type' => PaymentMethodType::ALFABANK,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::RUB,
                    ),
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::GOOGLE_PAY,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::EUR,
                    ),
                    'fee' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::EUR,
                    ),
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::YANDEX_MONEY,
                    'confirmation_types' => array(
                        ConfirmationType::REDIRECT,
                        ConfirmationType::EXTERNAL,
                    ),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::USD,
                    ),
                    'fee' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::USD,
                    ),
                    'extra_fee' => true,
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::ALFABANK,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::BYN,
                    ),
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::SBERBANK,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::CNY,
                    ),
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::CASH,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::KZT,
                    ),
                ),
            ),
            array(
                array(
                    'payment_method_type' => PaymentMethodType::BANK_CARD,
                    'confirmation_types' => array(),
                    'charge' => array(
                        'value' => mt_rand(1, 100),
                        'currency' => CurrencyCode::UAH,
                    ),
                ),
            ),
        );
        return $result;
    }
}