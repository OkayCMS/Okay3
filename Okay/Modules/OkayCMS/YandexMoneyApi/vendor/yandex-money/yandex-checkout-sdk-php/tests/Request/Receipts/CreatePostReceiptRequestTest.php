<?php

namespace Tests\YandexCheckout\Request\Receipts;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\Metadata;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\PaymentData\PaymentDataQiwi;
use YandexCheckout\Model\Receipt;
use YandexCheckout\Model\Receipt\PaymentMode;
use YandexCheckout\Model\Receipt\PaymentSubject;
use YandexCheckout\Model\Receipt\ReceiptItemAmount;
use YandexCheckout\Model\Receipt\SettlementType;
use YandexCheckout\Model\ReceiptCustomer;
use YandexCheckout\Model\ReceiptItem;
use YandexCheckout\Model\ReceiptType;
use YandexCheckout\Model\Settlement;
use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Request\Payments\CreatePaymentRequestBuilder;
use YandexCheckout\Request\Receipts\CreatePostReceiptRequest;

class CreatePostReceiptRequestTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCustomer($options)
    {
        $instance = new CreatePostReceiptRequest();

        self::assertFalse($instance->hasCustomer());
        self::assertNull($instance->getCustomer());
        self::assertNull($instance->customer);

        $instance->setCustomer($options['customer']);
        if (empty($options['customer'])) {
            self::assertFalse($instance->hasCustomer());
            self::assertNull($instance->getCustomer());
            self::assertNull($instance->customer);
        } else {
            self::assertTrue($instance->hasCustomer());
            self::assertSame($options['customer'], $instance->getCustomer()->jsonSerialize());
            self::assertSame($options['customer'], $instance->customer->jsonSerialize());
        }

        $instance->customer = $options['customer'];
        if (empty($options['customer'])) {
            self::assertFalse($instance->hasCustomer());
            self::assertNull($instance->getCustomer());
            self::assertNull($instance->customer);
        } else {
            self::assertTrue($instance->hasCustomer());
            self::assertSame($options['customer'], $instance->getCustomer()->jsonSerialize());
            self::assertSame($options['customer'], $instance->customer->jsonSerialize());
        }
    }

    /**
     * @dataProvider invalidCustomerDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidCustomer($value)
    {
        $instance = new CreatePostReceiptRequest();
        $instance->setCustomer($value);
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testType($options)
    {
        $instance = new CreatePostReceiptRequest();

        self::assertNull($instance->getType());
        self::assertNull($instance->type);

        $instance->setType($options['type']);

        self::assertSame($options['type'], $instance->getType());
        self::assertSame($options['type'], $instance->type);
    }

    /**
     * @dataProvider invalidTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidType($value)
    {
        $instance = new CreatePostReceiptRequest();
        $instance->setType($value);
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSend($options)
    {
        $instance = new CreatePostReceiptRequest();

        self::assertTrue($instance->getSend());
        self::assertTrue($instance->send);

        $instance->setSend($options['send']);

        self::assertSame($options['send'], $instance->getSend());
        self::assertSame($options['send'], $instance->send);
    }

    /**
     * @dataProvider invalidBooleanDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidSend($value)
    {
        $instance = new CreatePostReceiptRequest();
        $instance->setType($value);
    }

    public function testValidate()
    {
        $instance = new CreatePostReceiptRequest();

        self::assertFalse($instance->validate());

        $instance->setCustomer(new ReceiptCustomer());
        self::assertFalse($instance->validate());

        $instance->setCustomer(new ReceiptCustomer(array('email' => 'johndoe@email.com')));
        self::assertFalse($instance->validate());

        $instance->setType(ReceiptType::REFUND);
        self::assertFalse($instance->validate());

        $instance->setType(ReceiptType::PAYMENT);
        self::assertFalse($instance->validate());

        $instance->setSend(false);
        self::assertFalse($instance->validate());

        $instance->setSend(true);
        self::assertFalse($instance->validate());

        $instance->setObjectId(uniqid());
        self::assertFalse($instance->validate());

        $instance->setItems(array());
        self::assertFalse($instance->validate());

        $instance->setSettlements(array());
        self::assertFalse($instance->validate());

        $instance->setItems(array(
            new ReceiptItem(array(
                'description' => 'description',
                'amount' => array(
                    'value' => 10,
                    'currency' => 'RUB',
                ),
                'quantity' => 1,
                'vat_code' => 1
            ))
        ));
        self::assertFalse($instance->validate());

        $instance->setSettlements(array(
            new Settlement(array(
                'type' => SettlementType::PREPAYMENT,
                'amount' => new ReceiptItemAmount(10, 'RUB')))
            )
        );
        self::assertTrue($instance->validate());
    }

    public function testBuilder()
    {
        $builder = CreatePaymentRequest::builder();
        self::assertTrue($builder instanceof CreatePaymentRequestBuilder);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function validDataProvider()
    {
        $type = Random::value(ReceiptType::getValidValues());
        $result = array(

            array(
                array(
                    'customer' => array(
                        'full_name' => Random::str(128),
                        'email' => Random::str(128),
                        'phone' => Random::str(4, 12, '1234567890'),
                        'inn' => '1234567890',
                    ),
                    'items' => array(
                        array(
                            'description' => Random::str(128),
                            'quantity' => Random::int(1, 10),
                            'amount' => array(
                                'value' => Random::float(0.1, 99.99),
                                'currency' => Random::value(CurrencyCode::getValidValues())
                            ),
                            'vat_code' => Random::int(1, 6),
                            'payment_subject' => Random::value(PaymentSubject::getValidValues()),
                            'payment_mode' => Random::value(PaymentMode::getValidValues()),
                            'product_code' => Random::str(128),
                            'country_of_origin_code' => 'RU',
                            'customs_declaration_number' => Random::str(128),
                            'excise' => Random::float(0.0, 99.99),
                        )
                    ),
                    'tax_system_code' => Random::int(1, 6),
                    'type' => $type,
                    'send' => true,
                    'settlements' => array(
                        array(
                            'type' => Random::value(SettlementType::getValidValues()),
                            'amount' => array(
                                'value' => Random::float(0.1, 99.99),
                                'currency' => Random::value(CurrencyCode::getValidValues())
                            )
                        )
                    ),
                    $type . '_id' => uniqid()
                ),
            ),
        );
        for ($i = 0; $i < 10; $i++) {
            $type = Random::value(ReceiptType::getValidValues());
            $request = array(
                'customer' => array(
                    'full_name' => Random::str(128),
                    'email' => Random::str(128),
                    'phone' => Random::str(4, 12, '1234567890'),
                    'inn' => '1234567890',
                ),
                'items' => array(
                    array(
                        'description' => Random::str(128),
                        'quantity' => Random::int(1, 10),
                        'amount' => array(
                            'value' => Random::float(0.1, 99.99),
                            'currency' => Random::value(CurrencyCode::getValidValues())
                        ),
                        'vat_code' => Random::int(1, 6),
                        'payment_subject' => Random::value(PaymentSubject::getValidValues()),
                        'payment_mode' => Random::value(PaymentMode::getValidValues()),
                        'product_code' => Random::str(128),
                        'country_of_origin_code' => 'RU',
                        'customs_declaration_number' => Random::str(128),
                        'excise' => Random::float(0.0, 99.99),
                    )
                ),
                'tax_system_code' => Random::int(1, 6),
                'type' => $type,
                'send' => true,
                'settlements' => array(
                    array(
                        'type' => Random::value(SettlementType::getValidValues()),
                        'amount' => array(
                            'value' => Random::float(0.1, 99.99),
                            'currency' => Random::value(CurrencyCode::getValidValues())
                        )
                    )
                ),
                $type . '_id' => uniqid()
            );
            $result[] = array($request);
        }
        return $result;
    }

    public function invalidCustomerDataProvider()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array(Random::str(10)),
            array(new \stdClass()),
        );
    }

    public function invalidTypeDataProvider()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array(Random::str(10)),
            array(new \stdClass()),
        );
    }

    public function invalidBooleanDataProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
            array('test'),
        );
    }
}