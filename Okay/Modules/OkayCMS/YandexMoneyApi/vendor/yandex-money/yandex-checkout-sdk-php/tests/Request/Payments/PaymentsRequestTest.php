<?php

namespace Tests\YandexCheckout\Request\Payments;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Model\Status;
use YandexCheckout\Request\Payments\PaymentsRequest;
use YandexCheckout\Request\Payments\PaymentsRequestBuilder;

class PaymentsRequestTest extends TestCase
{
    /**
     * @return PaymentsRequest
     */
    protected function getTestInstance()
    {
        return new PaymentsRequest();
    }

    /**
     * @dataProvider validPaymentIdDataProvider
     * @param $value
     */
    public function testPaymentId($value)
    {
        $this->getterAndSetterTest($value, 'paymentId', $value === null ? '' : (string)$value);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidPaymentId($value)
    {
        $this->getTestInstance()->setPaymentId($value);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidPaymentId($value)
    {
        $this->getTestInstance()->paymentId = $value;
    }

    /**
     * @dataProvider validIdDataProvider
     * @param $value
     */
    public function testShopId($value)
    {
        $this->getterAndSetterTest($value, 'accountId', $value === null ? '' : (string)$value);
    }

    /**
     * @dataProvider invalidIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidAccountId($value)
    {
        $this->getTestInstance()->setAccountId($value);
    }

    /**
     * @dataProvider invalidIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidAccountId($value)
    {
        $this->getTestInstance()->accountId = $value;
    }

    /**
     * @dataProvider validIdDataProvider
     * @param $value
     */
    public function testGatewayId($value)
    {
        $this->getterAndSetterTest($value, 'gatewayId', $value === null ? '' : (string)$value);
    }

    /**
     * @dataProvider invalidIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidGatewayId($value)
    {
        $this->getTestInstance()->setGatewayId($value);
    }

    /**
     * @dataProvider invalidIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidGatewayId($value)
    {
        $this->getTestInstance()->gatewayId = $value;
    }

    /**
     * @dataProvider validDateDataProvider
     * @param $value
     */
    public function testDateMethods($value)
    {
        $properties = array(
            'createdGte',
            'createdGt',
            'createdLte',
            'createdLt',
            'authorizedGte',
            'authorizedGt',
            'authorizedLte',
            'authorizedLt',
        );
        $expected = null;
        if ($value instanceof \DateTime) {
            $expected = $value->format(DATE_ATOM);
        } elseif (is_numeric($value)) {
            $expected = date(DATE_ATOM, $value);
        } else {
            $expected = $value;
        }
        foreach ($properties as $property) {
            $this->getterAndSetterTest($value, $property, empty($expected) ? null : new \DateTime($expected));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedGte($value)
    {
        $this->getTestInstance()->setCreatedGte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedGte($value)
    {
        $this->getTestInstance()->createdGte = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedGt($value)
    {
        $this->getTestInstance()->setCreatedGt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedGt($value)
    {
        $this->getTestInstance()->createdGt = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedLte($value)
    {
        $this->getTestInstance()->setCreatedLte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedLte($value)
    {
        $this->getTestInstance()->createdLte = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedLt($value)
    {
        $this->getTestInstance()->setCreatedLt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedLt($value)
    {
        $this->getTestInstance()->createdLt = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedGte($value)
    {
        $this->getTestInstance()->setAuthorizedGte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedGte($value)
    {
        $this->getTestInstance()->authorizedGte = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedGt($value)
    {
        $this->getTestInstance()->setAuthorizedGt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedGt($value)
    {
        $this->getTestInstance()->authorizedGt = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedLte($value)
    {
        $this->getTestInstance()->setAuthorizedLte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedLte($value)
    {
        $this->getTestInstance()->authorizedLte = $value;
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedLt($value)
    {
        $this->getTestInstance()->setAuthorizedLt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedLt($value)
    {
        $this->getTestInstance()->authorizedLt = $value;
    }

    /**
     * @dataProvider validStatusDataProvider
     * @param $value
     */
    public function testStatus($value)
    {
        $this->getterAndSetterTest($value, 'status', $value === null ? '' : (string)$value);
    }

    /**
     * @dataProvider invalidDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidStatus($value)
    {
        $this->getTestInstance()->setStatus($value);
    }

    /**
     * @dataProvider invalidDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidStatus($value)
    {
        $this->getTestInstance()->status = $value;
    }

    /**
     * @dataProvider validNetPageDataProvider
     * @param $value
     */
    public function testNextPage($value)
    {
        $this->getterAndSetterTest($value, 'nextPage', $value === null ? '' : (string)$value);
    }

    /**
     * @dataProvider invalidNextPageDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidNextPage($value)
    {
        $this->getTestInstance()->setNextPage($value);
    }

    /**
     * @dataProvider invalidNextPageDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidNextPage($value)
    {
        $this->getTestInstance()->nextPage = $value;
    }

    public function testValidate()
    {
        $instance = new PaymentsRequest();

        self::assertFalse($instance->validate());
        $instance->setAccountId(Random::str(10));
        self::assertTrue($instance->validate());
        $instance->setAccountId(null);
        self::assertFalse($instance->validate());
    }

    public function testBuilder()
    {
        $builder = PaymentsRequest::builder();
        self::assertTrue($builder instanceof PaymentsRequestBuilder);
    }

    public function validNetPageDataProvider()
    {
        return array(
            array(''),
            array(null),
            array(Random::str(1)),
            array(Random::str(2, 64)),
            array(new StringObject(Random::str(1))),
            array(new StringObject(Random::str(2, 64))),
        );
    }

    public function validPaymentIdDataProvider()
    {
        return array(
            array(null),
            array(''),
            array('b80357ac-0c7e-46f9-8058-5a9e9a993800'),
            array(Random::str(36)),
            array(new StringObject(Random::str(36))),
        );
    }

    public function validIdDataProvider()
    {
        return array(
            array(null),
            array(''),
            array('123'),
            array(Random::str(1,64)),
            array(new StringObject(Random::str(1,64))),
        );
    }

    public function validDateDataProvider()
    {
        return array(
            array(null),
            array(''),
            array(Random::int(0, time())),
            array(date(DATE_ATOM, Random::int(0, time()))),
            array(new \DateTime()),
        );
    }

    public function validStatusDataProvider()
    {
        $result = array(
            array(null),
            array(''),
        );
        foreach (Status::getValidValues() as $value) {
            $result[] = array($value);
            $result[] = array(new StringObject($value));
        }
        return $result;
    }

    public function invalidIdDataProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
            array(true),
            array(false),
        );
    }

    public function invalidDataProvider()
    {
        $result = array(
            array(array()),
            array(new \stdClass()),
            array(Random::str(10)),
        );
        return $result;
    }

    public function invalidPaymentIdDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(35)),
            array(Random::str(37)),
            array(new StringObject(Random::str(10))),
        );
    }

    public function invalidDateDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(35)),
            array(Random::str(37)),
            array(new StringObject(Random::str(10))),
            array(-123),
        );
    }

    public function invalidNextPageDataProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
            array(true),
            array(false),
        );
    }

    private function getterAndSetterTest($value, $property, $expected, $testHas = true)
    {
        $getter = 'get'.ucfirst($property);
        $setter = 'set'.ucfirst($property);
        $has = 'has'.ucfirst($property);

        $instance = $this->getTestInstance();

        if ($testHas) {
            self::assertFalse($instance->{$has}());
        }
        self::assertNull($instance->{$getter}());
        self::assertNull($instance->{$property});

        $instance->{$setter}($value);
        if ($value === null || $value === '') {
            if ($testHas) {
                self::assertFalse($instance->{$has}());
            }
            self::assertNull($instance->{$getter}());
            self::assertNull($instance->{$property});
        } else {
            if ($testHas) {
                self::assertTrue($instance->{$has}());
            }
            if ($expected instanceof \DateTime) {
                self::assertEquals($expected->getTimestamp(), $instance->{$getter}()->getTimestamp());
                self::assertEquals($expected->getTimestamp(), $instance->{$property}->getTimestamp());
            } else {
                self::assertEquals($expected, $instance->{$getter}());
                self::assertEquals($expected, $instance->{$property});
            }
        }

        $instance->{$setter}('');
        if ($testHas) {
            self::assertFalse($instance->{$has}());
        }
        self::assertNull($instance->{$getter}());
        self::assertNull($instance->{$property});

        $instance->{$property} = $value;
        if ($value === null || $value === '') {
            if ($testHas) {
                self::assertFalse($instance->{$has}());
            }
            self::assertNull($instance->{$getter}());
            self::assertNull($instance->{$property});
        } else {
            if ($testHas) {
                self::assertTrue($instance->{$has}());
            }
            if ($expected instanceof \DateTime) {
                self::assertEquals($expected->getTimestamp(), $instance->{$getter}()->getTimestamp());
                self::assertEquals($expected->getTimestamp(), $instance->{$property}->getTimestamp());
            } else {
                self::assertEquals($expected, $instance->{$getter}());
                self::assertEquals($expected, $instance->{$property});
            }
        }
    }
}