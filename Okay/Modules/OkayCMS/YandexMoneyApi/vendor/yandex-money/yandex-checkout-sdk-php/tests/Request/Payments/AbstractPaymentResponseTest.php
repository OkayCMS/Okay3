<?php

namespace Tests\YandexCheckout\Request\Payments;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Model\PaymentStatus;
use YandexCheckout\Model\ReceiptRegistrationStatus;
use YandexCheckout\Request\Payments\PaymentResponse;

abstract class AbstractPaymentResponseTest extends TestCase
{
    /**
     * @param $options
     * @return PaymentResponse
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
    public function testGetStatus($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertEquals($options['status'], $instance->getStatus());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetRecipient($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['recipient'])) {
            self::assertNull($instance->getRecipient());
        } else {
            if (!empty($options['recipient']['account_id'])) {
                self::assertEquals($options['recipient']['account_id'], $instance->getRecipient()->getAccountId());
            }
            if (!empty($options['recipient']['gateway_id'])) {
                self::assertEquals($options['recipient']['gateway_id'], $instance->getRecipient()->getGatewayId());
            }
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetAmount($options)
    {
        $instance = $this->getTestInstance($options);
        self::assertEquals(number_format($options['amount']['value'], 2, '.', ''), $instance->getAmount()->getValue());
        self::assertEquals($options['amount']['currency'], $instance->getAmount()->getCurrency());
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetPaymentMethod($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['payment_method'])) {
            self::assertNull($instance->getPaymentMethod());
        } else {
            self::assertEquals($options['payment_method']['type'], $instance->getPaymentMethod()->getType());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetCreatedAt($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['created_at'])) {
            self::assertNull($instance->getCreatedAt());
        } else {
            self::assertEquals($options['created_at'], $instance->getCreatedAt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetCapturedAt($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['captured_at'])) {
            self::assertNull($instance->getCapturedAt());
        } else {
            self::assertEquals($options['captured_at'], $instance->getCapturedAt()->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetConfirmation($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['confirmation'])) {
            self::assertNull($instance->getConfirmation());
        } else {
            self::assertEquals($options['confirmation']['type'], $instance->getConfirmation()->getType());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetRefundedAmount($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['refunded_amount'])) {
            self::assertNull($instance->getRefundedAmount());
        } else {
            self::assertEquals(number_format($options['refunded_amount']['value'], 2, '.', ''), $instance->getRefundedAmount()->getValue());
            self::assertEquals((string)$options['refunded_amount']['currency'], $instance->getRefundedAmount()->getCurrency());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetPaid($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['paid'])) {
            self::assertFalse($instance->getPaid());
        } else {
            self::assertEquals($options['paid'], $instance->getPaid());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetRefundable($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['refundable'])) {
            self::assertFalse($instance->getRefundable());
        } else {
            self::assertEquals($options['refundable'], $instance->getRefundable());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetTest($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['test'])) {
            self::assertNull($instance->getTest());
        } else {
            self::assertEquals($options['test'], $instance->getTest());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetReceiptRegistration($options)
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
    public function testGetMetadata($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['metadata'])) {
            self::assertNull($instance->getMetadata());
        } else {
            self::assertEquals($options['metadata'], $instance->getMetadata()->toArray());
        }
    }

    public function validDataProvider()
    {
        $result = array();
        $statuses = PaymentStatus::getValidValues();
        $receiptRegistrations = ReceiptRegistrationStatus::getValidValues();

        $confirmations = array(
            array(
                'type' => ConfirmationType::REDIRECT,
                'confirmation_url' => Random::str(10),
                'return_url' => Random::str(10),
                'enforce' => false,
            ),
            array(
                'type' => ConfirmationType::EXTERNAL,
            ),
        );

        for ($i = 0; $i < 10; $i++) {
            $payment = array(
                'id' => Random::str(36),
                'status' => Random::value($statuses),
                'description' => Random::str(128),
                'recipient' => array(
                    'account_id' => Random::str(1, 64, '0123456789'),
                    'gateway_id' => Random::str(1, 256),
                ),
                'amount' => array(
                    'value' => Random::float(0.01, 1000000.0),
                    'currency' => Random::value(CurrencyCode::getEnabledValues()),
                ),
                'payment_method' => array(
                    'type' => Random::value(PaymentMethodType::getEnabledValues()),
                ),
                'created_at' => date(DATE_ATOM, Random::int(1, time())),
                'captured_at' => date(DATE_ATOM, Random::int(1, time())),
                'expires_at' => date(DATE_ATOM, Random::int(1, time())),
                'confirmation' => Random::value($confirmations),
                'refunded_amount' => array(
                    'value' => Random::float(0.01, 1000000.0),
                    'currency' => Random::value(CurrencyCode::getValidValues()),
                ),
                'paid' => $i % 2 ? true : false,
                'refundable' => $i % 2 ? true : false,
                'test' => $i % 2 ? true : false,
                'receipt_registration' => Random::value($receiptRegistrations),
                'metadata' => array(
                    'value' => Random::float(0.01, 1000000.0),
                    'currency' => Random::str(1, 256),
                ),
                'authorization_details' => array(
                    'rrn'       => Random::str(10),
                    'auth_code' => Random::str(10),
                ),
            );
            $result[] = array($payment);
        }

        $trueFalse = Random::bool();
        $result[] = array(
            array(
                'id' => Random::str(36),
                'status' => Random::value($statuses),
                'description' => Random::str(128),
                'recipient' => array(
                    'account_id' => Random::str(1, 64, '0123456789'),
                    'gateway_id' => Random::str(1, 256),
                ),
                'amount' => array(
                    'value' => Random::float(0.01, 1000000.0),
                    'currency' => Random::value(CurrencyCode::getValidValues()),
                ),
                'payment_method' => array(
                    'type' => PaymentMethodType::WECHAT,
                ),
                'created_at' => date(DATE_ATOM, Random::int(1, time())),
                'captured_at' => date(DATE_ATOM, Random::int(1, time())),
                'expires_at' => date(DATE_ATOM, Random::int(1, time())),
                'confirmation' => array(
                    'type' => 'qr',
                    'confirmation_data' => 'weixin://wxpay/bizpayurl?pr=SqTE9cX'
                ),
                'paid' => $trueFalse,
                'refundable' => $trueFalse,
                'test' => $trueFalse,
                'metadata' => array(),
            )
        );

        return $result;
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetCancellationDetails($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['cancellation_details'])) {
            self::assertNull($instance->getCancellationDetails());
        } else {
            self::assertEquals(
                $options['cancellation_details']['party'],
                $instance->getCancellationDetails()->getParty()
            );
            self::assertEquals(
                $options['cancellation_details']['reason'],
                $instance->getCancellationDetails()->getReason()
            );
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetAuthorizationDetails($options)
    {
        $instance = $this->getTestInstance($options);
        if (empty($options['authorization_details'])) {
            self::assertNull($instance->getAuthorizationDetails());
        } else {
            self::assertEquals(
                $options['authorization_details']['rrn'],
                $instance->getAuthorizationDetails()->getRrn()
            );
            self::assertEquals(
                $options['authorization_details']['auth_code'],
                $instance->getAuthorizationDetails()->getAuthCode()
            );
        }
    }

}
