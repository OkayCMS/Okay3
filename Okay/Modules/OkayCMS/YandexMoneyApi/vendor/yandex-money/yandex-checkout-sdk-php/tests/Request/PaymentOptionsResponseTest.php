<?php

namespace Tests\YandexCheckout\Request;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Request\PaymentOptionsResponse;
use YandexCheckout\Request\PaymentOptionsResponseItem;

class PaymentOptionsResponseTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetItems($options)
    {
        $instance = new PaymentOptionsResponse($options);
        self::assertEquals(count($options['items']), count($instance->getItems()));
        foreach ($instance->getItems() as $index => $item) {
            self::assertTrue($item instanceof PaymentOptionsResponseItem);
            self::assertArrayHasKey($index, $options['items']);
            self::assertEquals($options['items'][$index]['payment_method_type'], $item->getPaymentMethodType());
        }
    }

    /**
     * @return array
     */
    public function validDataProvider()
    {
        $items = array(
            array(
                'payment_method_type' => PaymentMethodType::ALFABANK,
                'confirmation_types' => array(),
                'charge' => array(
                    'value' => mt_rand(1, 100),
                    'currency' => CurrencyCode::RUB,
                ),
            ),
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
        );

        foreach (CurrencyCode::getValidValues() as $currency) {
            $items[] = array(
                'payment_method_type' => PaymentMethodType::YANDEX_MONEY,
                'confirmation_types'  => array(),
                'charge'              => array(
                    'value'    => mt_rand(1, 100),
                    'currency' => $currency,
                ),
                'fee'                 => array(
                    'value'    => mt_rand(1, 100),
                    'currency' => $currency,
                ),
                'extra_fee'           => true,
            );
        }

        $result = array(
            array(
                array(
                    'items' => $items
                ),
            ),
        );
        return $result;
    }
}