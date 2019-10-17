<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesRedirect;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesRedirectTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesRedirect
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesRedirect();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::REDIRECT;
    }

    /**
     * @dataProvider validEnforceDataProvider
     * @param $value
     */
    public function testGetSetEnforce($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getEnforce());
        self::assertNull($instance->enforce);

        $instance->setEnforce($value);
        if ($value === null || $value === '') {
            self::assertNull($instance->getEnforce());
            self::assertNull($instance->enforce);
        } else {
            self::assertEquals((bool)$value, $instance->getEnforce());
            self::assertEquals((bool)$value, $instance->enforce);
        }

        $instance = $this->getTestInstance();
        $instance->enforce = $value;
        if ($value === null || $value === '') {
            self::assertNull($instance->getEnforce());
            self::assertNull($instance->enforce);
        } else {
            self::assertEquals((bool)$value, $instance->getEnforce());
            self::assertEquals((bool)$value, $instance->enforce);
        }
    }

    /**
     * @dataProvider invalidEnforceDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidEnforce($value)
    {
        $instance = $this->getTestInstance();
        $instance->setEnforce($value);
    }

    /**
     * @dataProvider invalidEnforceDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidEnforce($value)
    {
        $instance = $this->getTestInstance();
        $instance->enforce = $value;
    }

    /**
     * @dataProvider validUrlDataProvider
     * @param $value
     */
    public function testGetSetReturnUrl($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getReturnUrl());
        self::assertNull($instance->returnUrl);
        self::assertNull($instance->return_url);

        $instance->setReturnUrl($value);
        if ($value === null || $value === '') {
            self::assertNull($instance->getReturnUrl());
            self::assertNull($instance->returnUrl);
            self::assertNull($instance->return_url);
        } else {
            self::assertEquals($value, $instance->getReturnUrl());
            self::assertEquals($value, $instance->returnUrl);
            self::assertEquals($value, $instance->return_url);
        }

        $instance->setReturnUrl(null);
        self::assertNull($instance->getReturnUrl());
        self::assertNull($instance->returnUrl);
        self::assertNull($instance->return_url);

        $instance->returnUrl = $value;
        if ($value === null || $value === '') {
            self::assertNull($instance->getReturnUrl());
            self::assertNull($instance->returnUrl);
            self::assertNull($instance->return_url);
        } else {
            self::assertEquals($value, $instance->getReturnUrl());
            self::assertEquals($value, $instance->returnUrl);
            self::assertEquals($value, $instance->return_url);
        }

        $instance->setReturnUrl(null);
        self::assertNull($instance->getReturnUrl());
        self::assertNull($instance->returnUrl);
        self::assertNull($instance->return_url);

        $instance->return_url = $value;
        if ($value === null || $value === '') {
            self::assertNull($instance->getReturnUrl());
            self::assertNull($instance->returnUrl);
            self::assertNull($instance->return_url);
        } else {
            self::assertEquals($value, $instance->getReturnUrl());
            self::assertEquals($value, $instance->returnUrl);
            self::assertEquals($value, $instance->return_url);
        }
    }

    /**
     * @dataProvider invalidUrlDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidReturnUrl($value)
    {
        $instance = $this->getTestInstance();
        $instance->setReturnUrl($value);
    }

    /**
     * @dataProvider invalidUrlDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidReturnUrl($value)
    {
        $instance = $this->getTestInstance();
        $instance->returnUrl = $value;
    }

    /**
     * @dataProvider invalidUrlDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidReturn_url($value)
    {
        $instance = $this->getTestInstance();
        $instance->return_url = $value;
    }

    public function validEnforceDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array(''),
            array(0),
            array(1),
            array(100),
        );
    }

    public function invalidEnforceDataProvider()
    {
        return array(
            array('true'),
            array('false'),
            array(array()),
            array(new \stdClass()),
        );
    }

    public function validUrlDataProvider()
    {
        return array(
            array('http://test.ru'),
            array(null),
            array(''),
        );
    }

    public function invalidUrlDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
        );
    }
}