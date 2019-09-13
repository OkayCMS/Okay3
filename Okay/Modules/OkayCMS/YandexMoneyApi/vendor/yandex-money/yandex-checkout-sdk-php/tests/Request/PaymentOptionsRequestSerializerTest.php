<?php

namespace Tests\YandexCheckout\Request;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Request\PaymentOptionsRequest;
use YandexCheckout\Request\PaymentOptionsRequestSerializer;

class PaymentOptionsRequestSerializerTest extends TestCase
{
    private $fieldMap = array(
        'gatewayId'        => 'gateway_id',
        'amount'           => 'amount',
        'currency'         => 'currency',
        'confirmationType' => 'confirmation_types',
    );

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSerialize($options)
    {
        $serializer = new PaymentOptionsRequestSerializer();
        $data = $serializer->serialize(PaymentOptionsRequest::builder()->build($options));

        $expected = array(
            'account_id' => $options['accountId'],
        );
        foreach ($this->fieldMap as $field => $mapped) {
            if (isset($options[$field])) {
                $value = $options[$field];
                if (!empty($value)) {
                    if ($mapped === 'amount') {
                        $expected[$mapped] = (string)number_format($options['amount'], 2, '.', '');
                    } else {
                        $expected[$mapped] = $value;
                    }
                }
            }
        }
        self::assertEquals($expected, $data);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'accountId' => uniqid(),
                ),
            ),
            array(
                array(
                    'accountId' => uniqid(),
                    'gatewayId' => '',
                    'amount' => '',
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