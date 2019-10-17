<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\Receipt;
use YandexCheckout\Model\ReceiptItem;
use YandexCheckout\Request\Refunds\CreateRefundRequest;
use YandexCheckout\Request\Refunds\CreateRefundRequestBuilder;

class CreateRefundRequestTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testPaymentId($options)
    {
        $instance = new CreateRefundRequest();

        self::assertNull($instance->getPaymentId());
        self::assertNull($instance->paymentId);

        $instance->setPaymentId($options['paymentId']);

        self::assertEquals($options['paymentId'], $instance->getPaymentId());
        self::assertEquals($options['paymentId'], $instance->paymentId);

        $instance = new CreateRefundRequest();

        self::assertNull($instance->getPaymentId());
        self::assertNull($instance->paymentId);

        $instance->paymentId = $options['paymentId'];

        self::assertEquals($options['paymentId'], $instance->getPaymentId());
        self::assertEquals($options['paymentId'], $instance->paymentId);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidPaymentId($value)
    {
        $instance = new CreateRefundRequest();
        $instance->setPaymentId($value);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidPaymentId($value)
    {
        $instance = new CreateRefundRequest();
        $instance->paymentId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAmount($options)
    {
        $instance = new CreateRefundRequest();

        self::assertNull($instance->getAmount());
        self::assertNull($instance->amount, $instance->getAmount());

        $instance->setAmount($options['amount']);

        self::assertEquals($options['amount']->getValue(), $instance->getAmount()->getValue());
        self::assertEquals($options['amount']->getValue(), $instance->amount->getValue());
        self::assertEquals($options['amount']->getCurrency(), $instance->getAmount()->getCurrency());
        self::assertEquals($options['amount']->getCurrency(), $instance->amount->getCurrency());

        $instance = new CreateRefundRequest();

        $instance->amount = $options['amount'];

        self::assertEquals($options['amount']->getValue(), $instance->getAmount()->getValue());
        self::assertEquals($options['amount']->getValue(), $instance->amount->getValue());
        self::assertEquals($options['amount']->getCurrency(), $instance->getAmount()->getCurrency());
        self::assertEquals($options['amount']->getCurrency(), $instance->amount->getCurrency());
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testComment($options)
    {
        $instance = new CreateRefundRequest();

        self::assertFalse($instance->hasComment());
        self::assertNull($instance->getComment());
        self::assertNull($instance->comment);

        $instance->setComment($options['comment']);
        if (empty($options['comment'])) {
            self::assertFalse($instance->hasComment());
            self::assertNull($instance->getComment());
            self::assertNull($instance->comment);
        } else {
            self::assertTrue($instance->hasComment());
            self::assertEquals($options['comment'], $instance->getComment());
            self::assertEquals($options['comment'], $instance->comment);
        }

        $instance->setComment('');
        self::assertFalse($instance->hasComment());
        self::assertNull($instance->getComment());
        self::assertNull($instance->comment);

        $instance->comment = $options['comment'];
        if (empty($options['comment'])) {
            self::assertFalse($instance->hasComment());
            self::assertNull($instance->getComment());
            self::assertNull($instance->comment);
        } else {
            self::assertTrue($instance->hasComment());
            self::assertEquals($options['comment'], $instance->getComment());
            self::assertEquals($options['comment'], $instance->comment);
        }
    }

    /**
     * @dataProvider invalidCommentDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidComment($value)
    {
        $instance = new CreateRefundRequest();
        $instance->setComment($value);
    }

    /**
     * @dataProvider invalidCommentDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidComment($value)
    {
        $instance = new CreateRefundRequest();
        $instance->comment = $value;
    }

    public function testValidate()
    {
        $instance = new CreateRefundRequest();

        self::assertFalse($instance->validate());
        $instance->setAmount(new MonetaryAmount());
        self::assertFalse($instance->validate());
        $instance->setAmount(new MonetaryAmount(Random::int(1, 100000)));
        self::assertFalse($instance->validate());
        $instance->setPaymentId(Random::str(36));
        self::assertTrue($instance->validate());

        $receipt = new Receipt();
        $instance->setReceipt($receipt);
        $item = new ReceiptItem();
        $item->setPrice(new MonetaryAmount(10));
        $item->setDescription('test');
        $receipt->addItem($item);
        self::assertFalse($instance->validate());
        $receipt->setPhone('123123');
        self::assertTrue($instance->validate());
        $item->setVatCode(3);
        self::assertTrue($instance->validate());
        $receipt->setTaxSystemCode(4);
        self::assertTrue($instance->validate());
    }

    /**
     * @dataProvider invalidReceiptDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidReceipt($value)
    {
        $instance = new CreateRefundRequest();
        $instance->setReceipt($value);
    }

    /**
     * @dataProvider invalidReceiptDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidReceipt($value)
    {
        $instance = new CreateRefundRequest();
        $instance->receipt = $value;
    }

    public function invalidReceiptDataProvider()
    {
        return array(
            array(''),
            array(1),
            array('test'),
            array(true),
            array(false),
            array(new \stdClass()),
        );
    }

    public function testBuilder()
    {
        $builder = CreateRefundRequest::builder();
        self::assertTrue($builder instanceof CreateRefundRequestBuilder);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'paymentId' => Random::str(36),
                    'amount' => new MonetaryAmount(mt_rand(1, 100)),
                    'comment' => null,
                )
            ),
            array(
                array(
                    'paymentId' => Random::str(36),
                    'amount' => new MonetaryAmount(mt_rand(1, 100)),
                    'comment' => '',
                )
            )
        );
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'paymentId' => Random::str(36),
                'amount' => new MonetaryAmount(mt_rand(1, 100)),
                'comment' => uniqid(),
            );
            $result[] = array($request);
        }
        return $result;
    }

    public function invalidPaymentIdDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(array()),
            array(1),
            array(new \stdClass()),
            array(Random::str(35)),
            array(Random::str(37)),
        );
    }

    public function invalidCommentDataProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
            array(Random::str(270)),
        );
    }
}