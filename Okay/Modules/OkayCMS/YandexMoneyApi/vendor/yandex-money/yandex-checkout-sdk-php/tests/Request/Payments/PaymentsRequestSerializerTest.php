<?php

namespace Tests\YandexCheckout\Request\Payments;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\Status;
use YandexCheckout\Request\Payments\PaymentsRequest;
use YandexCheckout\Request\Payments\PaymentsRequestSerializer;

class PaymentsRequestSerializerTest extends TestCase
{
    private $fieldMap = array(
        'paymentId'      => 'payment_id',
        'gatewayId'      => 'gateway_id',
        'createdGte'     => 'created_gte',
        'createdGt'      => 'created_gt',
        'createdLte'     => 'created_lte',
        'createdLt'      => 'created_lt',
        'authorizedGte'  => 'authorized_gte',
        'authorizedGt'   => 'authorized_gt',
        'authorizedLte'  => 'authorized_lte',
        'authorizedLt'   => 'authorized_lt',
        'status'         => 'status',
        'nextPage'       => 'next_page',
    );

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSerialize($options)
    {
        $serializer = new PaymentsRequestSerializer();
        $data = $serializer->serialize(PaymentsRequest::builder()->build($options));

        $expected = array(
            'account_id' => $options['accountId'],
        );
        foreach ($this->fieldMap as $field => $mapped) {
            if (isset($options[$field])) {
                $value = $options[$field];
                if (!empty($value)) {
                    $expected[$mapped] = $value instanceof \DateTime ? $value->format(DATE_ATOM) : $value;
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
        $statuses = Status::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'accountId' => uniqid(),
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
}