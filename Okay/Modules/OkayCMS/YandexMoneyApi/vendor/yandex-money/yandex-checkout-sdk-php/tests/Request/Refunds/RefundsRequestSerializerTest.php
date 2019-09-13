<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\RefundStatus;
use YandexCheckout\Request\Refunds\RefundsRequest;
use YandexCheckout\Request\Refunds\RefundsRequestSerializer;

class RefundsRequestSerializerTest extends TestCase
{
    private $fieldMap = array(
        'refundId'       => 'refund_id',
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
        $serializer = new RefundsRequestSerializer();
        $data = $serializer->serialize(RefundsRequest::builder()->build($options));

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
                    'refundId' => '',
                    'paymentId' => '',
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
                'accountId' => uniqid(),
                'gatewayId' => uniqid(),
                'refundId' => $this->randomString(36),
                'paymentId' => $this->randomString(36),
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