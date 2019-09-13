<?php

namespace Tests\YandexCheckout\Model;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\ReceiptItem;

class ReceiptItemTest extends TestCase
{
    protected function getTestInstance()
    {
        return new ReceiptItem();
    }

    /**
     * @dataProvider validDescriptionDataProvider
     * @param $value
     */
    public function testGetSetDescription($value)
    {
        $instance = $this->getTestInstance();
        self::assertNull($instance->getDescription());
        self::assertNull($instance->description);
        $instance->setDescription($value);
        self::assertEquals((string)$value, $instance->getDescription());
        self::assertEquals((string)$value, $instance->description);
    }

    /**
     * @dataProvider validDescriptionDataProvider
     * @param $value
     */
    public function testSetterDescription($value)
    {
        $instance = $this->getTestInstance();
        $instance->description = $value;
        self::assertEquals((string)$value, $instance->getDescription());
        self::assertEquals((string)$value, $instance->description);
    }

    public function validDescriptionDataProvider()
    {
        return array(
            array(Random::str(1)),
            array(Random::str(2, 31)),
            array(Random::str(32)),
            array(new StringObject(Random::str(64))),
            array(123),
            array(45.3),
        );
    }

    /**
     * @dataProvider invalidDescriptionDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidDescription($value)
    {
        $this->getTestInstance()->setDescription($value);
    }

    /**
     * @dataProvider invalidDescriptionDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidDescription($value)
    {
        $this->getTestInstance()->description = $value;
    }

    public function invalidDescriptionDataProvider()
    {
        return array(
            array(null),
            array(''),
            array(new StringObject('')),
            array(true),
            array(false),
            array(new \stdClass()),
            array(array()),
        );
    }

    /**
     * @dataProvider validQuantityDataProvider
     * @param $value
     */
    public function testGetSetQuantity($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getQuantity());
        self::assertNull($instance->quantity);
        $instance->setQuantity($value);
        self::assertEquals((float)$value, $instance->getQuantity());
        self::assertEquals((float)$value, $instance->quantity);
    }

    /**
     * @dataProvider validQuantityDataProvider
     * @param $value
     */
    public function testSetterQuantity($value)
    {
        $instance = $this->getTestInstance();

        $instance->quantity = $value;
        self::assertEquals((float)$value, $instance->getQuantity());
        self::assertEquals((float)$value, $instance->quantity);
    }

    public function validQuantityDataProvider()
    {
        return array(
            array(1),
            array(1.3),
            array(0.001),
            array(10000.001),
            array('3.1415'),
            array(Random::float(0.001, 9999.999)),
            array(Random::int(1, 9999)),
        );
    }

    /**
     * @dataProvider invalidQuantityDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidQuantity($value)
    {
        $this->getTestInstance()->setQuantity($value);
    }

    /**
     * @dataProvider invalidQuantityDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidQuantity($value)
    {
        $this->getTestInstance()->quantity = $value;
    }

    public function invalidQuantityDataProvider()
    {
        return array(
            array(null),
            array(''),
            array(0.0),
            array(Random::float(-100, -0.001)),
            array(array()),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider validVatCodeDataProvider
     * @param $value
     */
    public function testGetSetVatCode($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getVatCode());
        self::assertNull($instance->vatCode);
        self::assertNull($instance->vat_code);
        $instance->setVatCode($value);
        if ($value === null || $value === '') {
            self::assertNull($instance->getVatCode());
            self::assertNull($instance->vatCode);
            self::assertNull($instance->vat_code);
        } else {
            self::assertEquals((int)$value, $instance->getVatCode());
            self::assertEquals((int)$value, $instance->vatCode);
            self::assertEquals((int)$value, $instance->vat_code);
        }
    }

    /**
     * @dataProvider validVatCodeDataProvider
     * @param $value
     */
    public function testSetterVatCode($value)
    {
        $instance = $this->getTestInstance();

        $instance->vatCode = $value;
        if ($value === null || $value === '') {
            self::assertNull($instance->getVatCode());
            self::assertNull($instance->vatCode);
            self::assertNull($instance->vat_code);
        } else {
            self::assertEquals((int)$value, $instance->getVatCode());
            self::assertEquals((int)$value, $instance->vatCode);
            self::assertEquals((int)$value, $instance->vat_code);
        }
    }

    /**
     * @dataProvider validVatCodeDataProvider
     * @param $value
     */
    public function testSetterVat_code($value)
    {
        $instance = $this->getTestInstance();

        $instance->vat_code = $value;
        if ($value === null || $value === '') {
            self::assertNull($instance->getVatCode());
            self::assertNull($instance->vatCode);
            self::assertNull($instance->vat_code);
        } else {
            self::assertEquals((int)$value, $instance->getVatCode());
            self::assertEquals((int)$value, $instance->vatCode);
            self::assertEquals((int)$value, $instance->vat_code);
        }
    }

    public function validVatCodeDataProvider()
    {
        return array(
            array(null),
            array(''),
            array(1),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6),
        );
    }

    /**
     * @dataProvider invalidVatCodeDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidVatCode($value)
    {
        $this->getTestInstance()->setVatCode($value);
    }

    /**
     * @dataProvider invalidVatCodeDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidVatCode($value)
    {
        $this->getTestInstance()->vatCode = $value;
    }

    /**
     * @dataProvider invalidVatCodeDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidVat_code($value)
    {
        $this->getTestInstance()->vat_code = $value;
    }

    public function invalidVatCodeDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(0),
            array(7),
            array(Random::int(-100, -1)),
            array(Random::int(8, 100)),
        );
    }

    /**
     * @dataProvider validPriceDataProvider
     * @param AmountInterface $value
     */
    public function testGetSetPrice($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getPrice());
        self::assertNull($instance->price);
        $instance->setPrice($value);
        self::assertSame($value, $instance->getPrice());
        self::assertSame($value, $instance->price);
    }

    /**
     * @dataProvider validPriceDataProvider
     * @param AmountInterface $value
     */
    public function testSetterPrice($value)
    {
        $instance = $this->getTestInstance();
        $instance->price = $value;
        self::assertSame($value, $instance->getPrice());
        self::assertSame($value, $instance->price);
    }

    public function validPriceDataProvider()
    {
        return array(
            array(
                new MonetaryAmount(
                    Random::int(1, 100),
                    Random::value(CurrencyCode::getValidValues())
                ),
            ),
            array(
                new MonetaryAmount(),
            ),
        );
    }

    /**
     * @dataProvider invalidPriceDataProvider
     * @param $value
     */
    public function testSetInvalidPrice($value)
    {
        if (class_exists('TypeError')) {
            self::setExpectedException('TypeError');
            $this->getTestInstance()->setPrice($value);
        }
    }

    /**
     * @dataProvider invalidPriceDataProvider
     * @param $value
     */
    public function testSetterInvalidPrice($value)
    {
        if (class_exists('TypeError')) {
            self::setExpectedException('TypeError');
            $this->getTestInstance()->price = $value;
        }
    }

    public function invalidPriceDataProvider()
    {
        $result = array(
            array(null),
            array(''),
            array(1.0),
            array(1),
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
        );
        return $result;
    }

    /**
     * @dataProvider validIsShippingDataProvider
     * @param $value
     */
    public function testGetSetIsShipping($value)
    {
        $instance = $this->getTestInstance();

        self::assertFalse($instance->isShipping());
        $instance->setIsShipping($value);
        if ($value) {
            self::assertTrue($instance->isShipping());
        } else {
            self::assertFalse($instance->isShipping());
        }
    }

    /**
     * @dataProvider validIsShippingDataProvider
     * @param $value
     */
    public function testSetterIsShipping($value)
    {
        $instance = $this->getTestInstance();

        $instance->isShipping = $value;
        if ($value) {
            self::assertTrue($instance->isShipping());
        } else {
            self::assertFalse($instance->isShipping());
        }
    }

    /**
     * @return array
     */
    public function validIsShippingDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(0),
            array(1),
            array(2),
            array(null),
            array(''),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidIsShippingDataProvider
     * @param mixed $value
     */
    public function testInvalidSetIsShipping($value)
    {
        $this->getTestInstance()->setIsShipping($value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidIsShippingDataProvider
     * @param mixed $value
     */
    public function testInvalidSetterIsShipping($value)
    {
        $this->getTestInstance()->isShipping = $value;
    }

    public function invalidIsShippingDataProvider()
    {
        return array(
            array(array()),
            array('true'),
            array('false'),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider validApplyDiscountCoefficientDataProvider
     * @param $baseValue
     * @param $coefficient
     * @param $expected
     */
    public function testApplyDiscountCoefficient($baseValue, $coefficient, $expected)
    {
        $instance = $this->getTestInstance();

        $instance->setPrice(new MonetaryAmount($baseValue));
        $instance->applyDiscountCoefficient($coefficient);
        self::assertEquals($expected, $instance->getPrice()->getIntegerValue());
    }

    public function validApplyDiscountCoefficientDataProvider()
    {
        return array(
            array(1, 1, 100),
            array(1.01, 1, 101),
            array(1.01, 0.5, 51),
            array(1.01, 0.4, 40),
            array(1.00, 0.5, 50),
            array(1.00, 0.333333333, 33),
            array(2.00, 0.333333333, 67),
        );
    }

    /**
     * @dataProvider invalidApplyDiscountCoefficientDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $coefficient
     */
    public function testInvalidApplyDiscountCoefficient($coefficient)
    {
        $instance = $this->getTestInstance();

        $instance->setPrice(new MonetaryAmount(Random::int(100)));
        $instance->applyDiscountCoefficient($coefficient);
    }

    public function invalidApplyDiscountCoefficientDataProvider()
    {
        return array(
            array(null),
            array(''),
            array('test'),
            array(array()),
            array(new \stdClass()),
            array(-1.4),
            array(-0.01),
            array(-0.0001),
            array(0.0),
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider validAmountDataProvider
     * @param $price
     * @param $quantity
     */
    public function testGetAmount($price, $quantity)
    {
        $instance = $this->getTestInstance();
        $instance->setPrice(new MonetaryAmount($price));
        $instance->setQuantity($quantity);
        $expected = (int)round($price * 100.0 * $quantity);
        self::assertEquals($expected, $instance->getAmount());
    }

    public function validAmountDataProvider()
    {
        return array(
            array(1, 1),
            array(1.01, 1.01),
        );
    }

    /**
     * @dataProvider validIncreasePriceDataProvider
     * @param float $price
     * @param float $value
     * @param int $expected
     */
    public function testIncreasePrice($price, $value, $expected)
    {
        $instance = $this->getTestInstance();
        $instance->setPrice(new MonetaryAmount($price));
        $instance->increasePrice($value);
        self::assertEquals($expected, $instance->getPrice()->getIntegerValue());
    }

    public function validIncreasePriceDataProvider()
    {
        return array(
            array(1, 1, 200),
            array(1.01, 3.03, 404),
            array(1.01, -0.01, 100),
        );
    }

    /**
     * @dataProvider invalidIncreasePriceDataProvider
     * @expectedException \InvalidArgumentException
     * @param float $price
     * @param float $value
     */
    public function testInvalidIncreasePrice($price, $value)
    {
        $instance = $this->getTestInstance();
        $instance->setPrice(new MonetaryAmount($price));
        $instance->increasePrice($value);
    }

    public function invalidIncreasePriceDataProvider()
    {
        return array(
            array(1, -1),
            array(1.01, -1.01),
            array(1.01, -1.02),
            array(1.01, null),
            array(1.01, false),
            array(1.01, true),
            array(1.01, ''),
            array(1.01, 'test'),
            array(1.01, array()),
            array(1.01, new \stdClass()),
        );
    }

    /**
     * @dataProvider validFetchItemDataProvider
     * @param $price
     * @param $quantity
     * @param $fetch
     */
    public function testFetchItem($price, $quantity, $fetch)
    {
        $instance = $this->getTestInstance();
        $instance->setPrice(new MonetaryAmount($price));
        $instance->setQuantity($quantity);

        $fetched = $instance->fetchItem($fetch);
        self::assertTrue($fetched instanceof ReceiptItem);
        self::assertNotSame($fetched->getPrice(), $instance->getPrice());
        self::assertEquals($fetch, $fetched->getQuantity());
        self::assertEquals($quantity - $fetch, $instance->getQuantity());
        self::assertEquals($price, $instance->getPrice()->getValue());
        self::assertEquals($price, $fetched->getPrice()->getValue());
    }

    public function validFetchItemDataProvider()
    {
        return array(
            array(1, 2, 1),
            array(1.01, 2, 1.5),
            array(1.01, 2, 1.99),
            array(1.01, 2, 1.9999),
        );
    }

    /**
     * @dataProvider invalidFetchItemDataProvider
     * @expectedException \InvalidArgumentException
     * @param $quantity
     * @param $fetch
     */
    public function testInvalidFetchItem($quantity, $fetch)
    {
        $instance = $this->getTestInstance();
        $instance->setPrice(new MonetaryAmount(Random::int(1,100)));
        $instance->setQuantity($quantity);
        $instance->fetchItem($fetch);

    }

    public function invalidFetchItemDataProvider()
    {
        return array(
            array(1, 1),
            array(1.01, 1.01),
            array(1.01, 1.02),
            array(1, null),
            array(1, ''),
            array(1, 0.0),
            array(1, -12.3),
            array(1, array()),
            array(1, new \stdClass()),
            array(1, 'test'),
        );
    }
}