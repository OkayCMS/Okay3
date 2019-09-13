<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\ConfirmationAttributes\AbstractConfirmationAttributes;

abstract class AbstractConfirmationAttributesTest extends TestCase
{
    /**
     * @return AbstractConfirmationAttributes
     */
    abstract protected function getTestInstance();

    /**
     * @return string
     */
    abstract protected function getExpectedType();

    /**
     *
     */
    public function testGetType()
    {
        $instance = $this->getTestInstance();
        self::assertEquals($this->getExpectedType(), $instance->getType());
    }

    /**
     * @dataProvider invalidTypeDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testInvalidType($value)
    {
        new TestConfirmation($value);
    }

    public function invalidTypeDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(Random::str(40)),
            array(0),
            array(array()),
            array(new \stdClass()),
        );
    }
}

class TestConfirmation extends AbstractConfirmationAttributes
{
    public function __construct($type)
    {
        $this->_setType($type);
    }
}