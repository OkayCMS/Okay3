<?php

namespace Tests\YandexCheckout\Request\Payments\Payment;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequestSerializer;

class CreateCaptureRequestSerializerTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testSerialize($options)
    {
        $serializer = new CreateCaptureRequestSerializer();
        $data = $serializer->serialize(CreateCaptureRequest::builder()->build($options));

        $expected = array();
        if (isset($options['amount'])) {
            $expected = array(
                'amount' => $options['amount'],
            );
        }
        if (!empty($options['receiptItems'])) {
            foreach ($options['receiptItems'] as $item) {
                $expected['receipt']['items'][] = array(
                    'description' => $item['title'],
                    'quantity' => empty($item['quantity']) ? 1 : $item['quantity'],
                    'amount' => array(
                        'value' => $item['price'],
                        'currency' => isset($options['currency']) ? $options['currency'] : CurrencyCode::RUB,
                    ),
                    'vat_code' => empty($item['vatCode']) ? $options['taxSystemCode'] : $item['vatCode'],
                );
            }
            if (!empty($options['receiptEmail'])) {
                $expected['receipt']['email'] = $options['receiptEmail'];
            }
            if (!empty($options['receiptPhone'])) {
                $expected['receipt']['phone'] = $options['receiptPhone'];
            }
            if (!empty($options['taxSystemCode'])) {
                $expected['receipt']['tax_system_code'] = $options['taxSystemCode'];
            }
        } elseif (!empty($options['receipt'])) {
            $expected['receipt'] = $options['receipt'];
        }
        self::assertEquals($expected, $data);
    }

    public function validDataProvider()
    {
        $currencies = CurrencyCode::getValidValues();

        $result = array(
            array(
                array()
            ),
            array(
                array(
                    'receiptItems' => array(
                        array(
                            'title' => Random::str(10),
                            'quantity' => Random::int(1, 10),
                            'price' => Random::int(100, 100),
                            'vatCode' => Random::int(1, 6),
                        ),
                        array(
                            'title' => Random::str(10),
                            'price' => Random::int(100, 100),
                        ),
                    ),
                    'receiptEmail' => Random::str(10),
                    'taxSystemCode' => Random::int(1, 6),
                )
            ),
            array(
                array(
                    'receipt' => array(
                        'items' => array(
                            array(
                                'description' => Random::str(10),
                                'quantity' => Random::int(1, 10),
                                'amount' => array(
                                    'value' => Random::int(100, 100),
                                    'currency' => $currencies[mt_rand(0, count($currencies) - 1)],
                                ),
                                'vat_code' => Random::int(1, 6),
                            ),
                            array(
                                'description' => Random::str(10),
                                'amount' => array(
                                    'value' => Random::int(100, 100),
                                    'currency' => $currencies[mt_rand(0, count($currencies) - 1)],
                                ),
                                'quantity' => Random::int(1, 10),
                                'vat_code' => Random::int(1, 6),
                            ),
                        ),
                        'phone' => Random::str(12, '0123456789'),
                        'tax_system_code' => Random::int(1, 6),
                    ),
                ),
            ),
        );
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'amount'   => array(
                    'value' => mt_rand(1, 1000000),
                    'currency' => $currencies[mt_rand(0, count($currencies) - 1)],
                ),
            );
            $result[] = array($request);
        }
        return $result;
    }
}