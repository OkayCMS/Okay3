<?php

namespace Tests\YandexCheckout\Request;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Request\PaymentOptionsRequest;
use YandexCheckout\Request\PaymentOptionsRequestBuilder;

class PaymentOptionsRequestTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAccountId($options)
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->hasAccountId());
        self::assertNull($instance->getAccountId());
        self::assertNull($instance->accountId);

        $instance->setAccountId($options['account_id']);
        if ($options['account_id'] === null || $options['account_id'] === '') {
            self::assertFalse($instance->hasAccountId());
            self::assertNull($instance->getAccountId());
            self::assertNull($instance->accountId);
        } else {
            self::assertTrue($instance->hasAccountId());
            self::assertTrue(is_string($instance->getAccountId()));
            self::assertTrue(is_string($instance->accountId));
            self::assertEquals($options['account_id'], $instance->getAccountId());
            self::assertEquals($options['account_id'], $instance->accountId);
        }

        $instance->setAccountId('');
        self::assertFalse($instance->hasAccountId());
        self::assertNull($instance->getAccountId());
        self::assertNull($instance->accountId);

        $instance->accountId = $options['account_id'];
        if ($options['account_id'] === null || $options['account_id'] === '') {
            self::assertFalse($instance->hasAccountId());
            self::assertNull($instance->getAccountId());
            self::assertNull($instance->accountId);
        } else {
            self::assertTrue($instance->hasAccountId());
            self::assertTrue(is_string($instance->getAccountId()));
            self::assertTrue(is_string($instance->accountId));
            self::assertEquals($options['account_id'], $instance->getAccountId());
            self::assertEquals($options['account_id'], $instance->accountId);
        }
    }

    /**
     * @dataProvider invalidShopIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidAccountId($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->setAccountId($value);
    }

    /**
     * @dataProvider invalidShopIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidAccountId($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->accountId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testGatewayId($options)
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->hasGatewayId());
        self::assertNull($instance->getGatewayId());
        self::assertNull($instance->gatewayId);

        $instance->setGatewayId($options['gateway_id']);
        if (empty($options['gateway_id'])) {
            self::assertFalse($instance->hasGatewayId());
            self::assertNull($instance->getGatewayId());
            self::assertNull($instance->gatewayId);
        } else {
            self::assertTrue($instance->hasGatewayId());
            self::assertTrue(is_string($instance->getGatewayId()));
            self::assertTrue(is_string($instance->gatewayId));
            self::assertEquals($options['gateway_id'], $instance->getGatewayId());
            self::assertEquals($options['gateway_id'], $instance->gatewayId);
        }

        $instance->setGatewayId('');
        self::assertFalse($instance->hasGatewayId());
        self::assertNull($instance->getGatewayId());
        self::assertNull($instance->gatewayId);

        $instance->gatewayId = $options['gateway_id'];
        if (empty($options['gateway_id'])) {
            self::assertFalse($instance->hasGatewayId());
            self::assertNull($instance->getGatewayId());
            self::assertNull($instance->gatewayId);
        } else {
            self::assertTrue($instance->hasGatewayId());
            self::assertTrue(is_string($instance->getGatewayId()));
            self::assertTrue(is_string($instance->gatewayId));
            self::assertEquals($options['gateway_id'], $instance->getGatewayId());
            self::assertEquals($options['gateway_id'], $instance->gatewayId);
        }
    }

    /**
     * @dataProvider invalidShopIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidGatewayId($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->setGatewayId($value);
    }

    /**
     * @dataProvider invalidShopIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidGatewayId($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->gatewayId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAmount($options)
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->hasAmount());
        self::assertNull($instance->getAmount());
        self::assertNull($instance->amount);

        $expected = 0.0;
        if (is_object($options['amount'])) {
            $expected = (string)$options['amount'];
        } elseif (!empty($options['amount'])) {
            $expected = number_format($options['amount'], 2, '.', '');
        }
        if ($expected <= 0.01) {
            $expected = 0.0;
        }

        $instance->setAmount($options['amount']);
        if ($options['amount'] === null || $options['amount'] === '' || $expected === 0.0) {
            self::assertFalse($instance->hasAmount());
            self::assertNull($instance->getAmount());
            self::assertNull($instance->amount);
        } else {
            self::assertTrue($instance->hasAmount());
            self::assertEquals($expected, $instance->getAmount());
            self::assertEquals($expected, $instance->amount);
        }

        $instance->setAmount('');
        self::assertFalse($instance->hasAmount());
        self::assertNull($instance->getAmount());
        self::assertNull($instance->amount);

        $instance->amount = $options['amount'];
        if ($options['amount'] === null || $options['amount'] === '' || $expected < 0.001) {
            self::assertFalse($instance->hasAmount());
            self::assertNull($instance->getAmount());
            self::assertNull($instance->amount);
        } else {
            self::assertTrue($instance->hasAmount());
            self::assertEquals($expected, $instance->getAmount());
            self::assertEquals($expected, $instance->amount);
        }
    }

    /**
     * @dataProvider invalidAmountDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidAmount($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->setAmount($value);
    }

    /**
     * @dataProvider invalidAmountDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidAmount($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->amount = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCurrency($options)
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->hasCurrency());
        self::assertNull($instance->getCurrency());
        self::assertNull($instance->currency);

        $instance->setCurrency($options['currency']);
        if (empty($options['currency'])) {
            self::assertFalse($instance->hasCurrency());
            self::assertNull($instance->getCurrency());
            self::assertNull($instance->currency);
        } else {
            self::assertTrue($instance->hasCurrency());
            self::assertEquals($options['currency'], $instance->getCurrency());
            self::assertEquals($options['currency'], $instance->currency);
        }

        $instance->setCurrency('');
        self::assertFalse($instance->hasCurrency());
        self::assertNull($instance->getCurrency());
        self::assertNull($instance->currency);

        $instance->currency = $options['currency'];
        if (empty($options['currency'])) {
            self::assertFalse($instance->hasCurrency());
            self::assertNull($instance->getCurrency());
            self::assertNull($instance->currency);
        } else {
            self::assertTrue($instance->hasCurrency());
            self::assertEquals($options['currency'], $instance->getCurrency());
            self::assertEquals($options['currency'], $instance->currency);
        }
    }

    /**
     * @dataProvider invalidCurrencyDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidCurrency($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->setCurrency($value);
    }

    /**
     * @dataProvider invalidCurrencyDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidCurrency($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->currency = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testConfirmationType($options)
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->hasConfirmationType());
        self::assertNull($instance->getConfirmationType());
        self::assertNull($instance->confirmationType);

        $instance->setConfirmationType($options['confirmation_types']);
        if (empty($options['confirmation_types'])) {
            self::assertFalse($instance->hasConfirmationType());
            self::assertNull($instance->getConfirmationType());
            self::assertNull($instance->confirmationType);
        } else {
            self::assertTrue($instance->hasConfirmationType());
            self::assertEquals($options['confirmation_types'], $instance->getConfirmationType());
            self::assertEquals($options['confirmation_types'], $instance->confirmationType);
        }

        $instance->setConfirmationType('');
        self::assertFalse($instance->hasConfirmationType());
        self::assertNull($instance->getConfirmationType());
        self::assertNull($instance->confirmationType);

        $instance->confirmationType = $options['confirmation_types'];
        if (empty($options['confirmation_types'])) {
            self::assertFalse($instance->hasConfirmationType());
            self::assertNull($instance->getConfirmationType());
            self::assertNull($instance->confirmationType);
        } else {
            self::assertTrue($instance->hasConfirmationType());
            self::assertEquals($options['confirmation_types'], $instance->getConfirmationType());
            self::assertEquals($options['confirmation_types'], $instance->confirmationType);
        }
    }

    /**
     * @dataProvider invalidConfirmationTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidConfirmationType($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->setConfirmationType($value);
    }

    /**
     * @dataProvider invalidConfirmationTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidConfirmationType($value)
    {
        $instance = new PaymentOptionsRequest();
        $instance->confirmationType = $value;
    }

    /**
     * @throws \Exception
     */
    public function testValidate()
    {
        $instance = new PaymentOptionsRequest();

        self::assertFalse($instance->validate());
        $instance->setAccountId(Random::str(10));
        self::assertTrue($instance->validate());
        $instance->setAccountId(null);
        self::assertFalse($instance->validate());
    }

    public function testBuilder()
    {
        $builder = PaymentOptionsRequest::builder();
        self::assertTrue($builder instanceof PaymentOptionsRequestBuilder);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'account_id' => null,
                    'gateway_id' => null,
                    'amount' => null,
                    'currency' => null,
                    'confirmation_types' => null,
                ),
            ),
            array(
                array(
                    'account_id' => '',
                    'gateway_id' => '',
                    'amount' => '',
                    'currency' => '',
                    'confirmation_types' => '',
                ),
            ),
            array(
                array(
                    'account_id' => '',
                    'gateway_id' => '',
                    'amount' => 0.001,
                    'currency' => '',
                    'confirmation_types' => '',
                ),
            ),
            array(
                array(
                    'account_id' => new StringObject(uniqid()),
                    'gateway_id' => new StringObject(uniqid()),
                    'amount' => new StringObject('1.45'),
                    'currency' => new StringObject(CurrencyCode::EUR),
                    'confirmation_types' => new StringObject(ConfirmationType::REDIRECT),
                ),
            ),
        );
        $currencies = CurrencyCode::getValidValues();
        $countCurrencies = count($currencies);
        $confirmations = ConfirmationType::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'account_id' => ($i < 3 ? $i : ($i < 6 ? (float)$i : uniqid())),
                'gateway_id' => uniqid(),
                'amount' => ($i < 4 ? mt_rand(1, 999999999) : mt_rand(1, 999999999) / 13.0),
                'currency' => $currencies[$i % $countCurrencies],
                'confirmation_types' => $confirmations[mt_rand(0, count($confirmations) - 1)],
            );
            $result[] = array($request);
        }
        return $result;
    }

    public function invalidShopIdDataProvider()
    {
        $result = array(
            array(array()),
            array(new \stdClass()),
            array(true),
            array(false),
        );
        return $result;
    }

    public function invalidAmountDataProvider()
    {
        $result = array(
            array(array()),
            array(new \stdClass()),
            array('invalid_value'),
            array(true),
            array(false),
            array(-1),
            array(-mt_rand(2, 1000000)),
        );
        return $result;
    }

    public function invalidCurrencyDataProvider()
    {
        $result = array(
            array(array()),
            array(new \stdClass()),
            array('invalid_value'),
            array(0),
            array(1),
            array(0.0),
            array(1.3),
            array(true),
            array(false),
        );
        return $result;
    }

    public function invalidConfirmationTypeDataProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
            array('invalid_value'),
            array(0),
            array(1),
            array(0.0),
            array(1.3),
            array(true),
            array(false),
        );
    }
}
