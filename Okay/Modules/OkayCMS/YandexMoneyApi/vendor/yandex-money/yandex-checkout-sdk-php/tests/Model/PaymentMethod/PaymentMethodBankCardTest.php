<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Model\PaymentMethod\PaymentMethodBankCard;
use YandexCheckout\Model\PaymentMethod\PaymentMethodCardType;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodBankCardTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodBankCard
     */
    protected function getTestInstance()
    {
        return new PaymentMethodBankCard();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::BANK_CARD;
    }

    /**
     * @dataProvider validLast4DataProvider
     * @param string $value
     */
    public function testGetSetLast4($value)
    {
        $this->getAndSetTest($value, 'last4');
    }

    /**
     * @dataProvider invalidLast4DataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidNumber($value)
    {
        $this->getTestInstance()->setLast4($value);
    }

    /**
     * @dataProvider invalidLast4DataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidNumber($value)
    {
        $this->getTestInstance()->last4 = $value;
    }

    /**
     * @dataProvider validFirst6DataProvider
     * @param string $value
     */
    public function testGetSetFirst6($value)
    {
        $this->getAndSetTest($value, 'first6');
    }

    /**
     * @dataProvider invalidFirst6DataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetFirst6InvalidNumber($value)
    {
        $this->getTestInstance()->setFirst6($value);
    }

    /**
     * @dataProvider invalidFirst6DataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterFirst6InvalidNumber($value)
    {
        $this->getTestInstance()->first6 = $value;
    }

    /**
     * @dataProvider validExpiryYearDataProvider
     * @param $value
     */
    public function testGetSetExpiryYear($value)
    {
        $this->getAndSetTest($value, 'expiryYear', 'expiry_year');
    }

    /**
     * @dataProvider invalidYearDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidYear($value)
    {
        $this->getTestInstance()->setExpiryYear($value);
    }

    /**
     * @dataProvider invalidYearDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidYear($value)
    {
        $this->getTestInstance()->expiryYear = $value;
    }

    /**
     * @dataProvider invalidYearDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalid_year($value)
    {
        $this->getTestInstance()->expiry_year = $value;
    }

    /**
     * @dataProvider invalidMonthDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidMonth($value)
    {
        $this->getTestInstance()->expiryMonth = $value;
    }

    /**
     * @dataProvider invalidMonthDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalid_month($value)
    {
        $this->getTestInstance()->expiry_month = $value;
    }

    /**
     * @dataProvider validExpiryMonthDataProvider
     * @param $value
     */
    public function testGetSetExpiryMonth($value)
    {
        $this->getAndSetTest($value, 'expiryMonth', 'expiry_month');
    }

    /**
     * @dataProvider invalidMonthDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidMonth($value)
    {
        $this->getTestInstance()->setExpiryMonth($value);
    }

    /**
     * @dataProvider validCardTypeDataProvider
     * @param $value
     */
    public function testGetSetCardType($value)
    {
        $this->getAndSetTest($value, 'cardType', 'card_type');
    }

    /**
     * @dataProvider invalidCardTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCardType($value)
    {
        $this->getTestInstance()->setCardType($value);
    }

    /**
     * @dataProvider invalidCardTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCardType($value)
    {
        $this->getTestInstance()->cardType = $value;
    }

    /**
     * @dataProvider invalidCardTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCard_type($value)
    {
        $this->getTestInstance()->card_type = $value;
    }

    /**
     * @return array
     */
    public function validLast4DataProvider()
    {
        $result = array();
        for ($i = 0; $i < 10; $i++) {
            $result[] = array(Random::str(4, '0123456789'));
        }
        return $result;
    }

    /**
     * @return array
     */
    public function validFirst6DataProvider()
    {
        $result = array();
        for ($i = 0; $i < 10; $i++) {
            $result[] = array(Random::str(6, '0123456789'));
        }
        return $result;
    }

    /**
     * @return array
     */
    public function validExpiryYearDataProvider()
    {
        $result = array();
        for ($i = 0; $i < 10; $i++) {
            $result[] = array(Random::int(2000, 2200));
        }
        return $result;
    }

    /**
     * @return array
     */
    public function validExpiryMonthDataProvider()
    {
        return array(
            array('01'),
            array('02'),
            array('03'),
            array('04'),
            array('05'),
            array('06'),
            array('07'),
            array('08'),
            array('09'),
            array('10'),
            array('11'),
            array('12'),
        );
    }

    public function validCardTypeDataProvider()
    {
        $result = array();
        foreach (PaymentMethodCardType::getValidValues() as $value) {
            $result[] = array($value);
        }
        return $result;
    }

    public function invalidLast4DataProvider()
    {
        return array(
            array(''),
            array(null),
            array(0),
            array(1),
            array(-1),
            array(array()),
            array(new \stdClass()),
            array(Random::str(3, '0123456789')),
            array(Random::str(5, '0123456789')),
        );
    }

    public function invalidFirst6DataProvider()
    {
        return array(
            array(''),
            array(null),
            array(0),
            array(1),
            array(-1),
            array(array()),
            array(new \stdClass()),
            array(Random::str(3, '0123456789')),
            array(Random::str(5, '0123456789')),
        );
    }

    public function invalidYearDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(0),
            array(1),
            array(-1),
            array('5'),
            array(array()),
            array(new \stdClass()),
            array(Random::str(1, '0123456789')),
            array(Random::str(2, '0123456789')),
            array(Random::str(3, '0123456789')),
        );
    }

    public function invalidMonthDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(0),
            array(1),
            array(-1),
            array('5'),
            array(array()),
            array(new \stdClass()),
            array(Random::str(1, '0123456789')),
            array(Random::str(3, '0123456789')),
            array('13'),
            array('16'),
        );
    }

    public function invalidCardTypeDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(1, 10)),
            array(new StringObject(Random::str(1, 10))),
        );
    }

    protected function getAndSetTest($value, $property, $snakeCase = null)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);

        $instance = $this->getTestInstance();

        self::assertNull($instance->{$getter}());
        self::assertNull($instance->{$property});
        if ($snakeCase !== null) {
            self::assertNull($instance->{$snakeCase});
        }

        $instance->{$setter}($value);

        self::assertEquals($value, $instance->{$getter}());
        self::assertEquals($value, $instance->{$property});
        if ($snakeCase !== null) {
            self::assertEquals($value, $instance->{$snakeCase});
        }

        $instance = $this->getTestInstance();

        $instance->{$property} = $value;

        self::assertEquals($value, $instance->{$getter}());
        self::assertEquals($value, $instance->{$property});
        if ($snakeCase !== null) {
            self::assertEquals($value, $instance->{$snakeCase});
        }

        if ($snakeCase !== null) {
            $instance = $this->getTestInstance();

            $instance->{$snakeCase} = $value;

            self::assertEquals($value, $instance->{$getter}());
            self::assertEquals($value, $instance->{$property});
            self::assertEquals($value, $instance->{$snakeCase});
        }
    }
}