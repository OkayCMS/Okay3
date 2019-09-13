<?php


namespace Tests\YandexCheckout\Model;


use YandexCheckout\Model\Airline;
use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\Leg;
use YandexCheckout\Model\LegInterface;
use YandexCheckout\Model\Passenger;
use YandexCheckout\Model\PassengerInterface;

class AirlineTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     *
     * @param $data
     */
    public function testAirlineInstantiate($data)
    {
        $airline = new Airline();

        self::assertFalse($airline->notEmpty());

        $airline->setBookingReference($data['booking_reference']);
        $airline->setTicketNumber($data['ticket_number']);
        $airline->setPassengers($data['passengers']);
        $airline->setLegs($data['legs']);

        self::assertEquals($airline->getBookingReference(), $data['booking_reference']);
        self::assertEquals($airline->getTicketNumber(), $data['ticket_number']);
        self::assertTrue(is_array($airline->getPassengers()));
        self::assertTrue(is_array($airline->getLegs()));

        foreach ($airline->getLegs() as $leg) {
            self::assertTrue($leg instanceof LegInterface);
        }

        foreach ($airline->getPassengers() as $passenger) {
            self::assertTrue($passenger instanceof PassengerInterface);
        }

        self::assertTrue($airline->notEmpty());
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param $data
     */
    public function testFromArrayInstantiate($data)
    {
        $airline = new Airline();
        $airline->fromArray($data);

        self::assertEquals($airline->getBookingReference(), $data['booking_reference']);
        self::assertEquals($airline->getTicketNumber(), $data['ticket_number']);
        self::assertTrue(is_array($airline->getPassengers()));
        self::assertTrue(is_array($airline->getLegs()));

        foreach ($airline->getLegs() as $leg) {
            self::assertTrue($leg instanceof LegInterface);
        }

        foreach ($airline->getPassengers() as $passenger) {
            self::assertTrue($passenger instanceof PassengerInterface);
        }

        self::assertTrue($airline->notEmpty());
    }


    /**
     * @dataProvider exceptionDataProvider
     */
    public function testAirlinePassengersDataValidate($data)
    {
        $airline = new Airline();

        $this->setExpectedException($data['exception']);

        $airline->setPassengers($data['value']);
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testAirlineLegsDataValidate($data)
    {
        $airline = new Airline();

        $this->setExpectedException($data['exception']);

        $airline->setLegs($data['value']);
    }

    /**
     * @dataProvider stringsExceptionDataProvider
     */
    public function testBookingReferenceValidate($data)
    {
        $airline = new Airline();

        $this->setExpectedException($data['exception']);

        $airline->setBookingReference($data['value']);
    }

    /**
     * @dataProvider stringsExceptionDataProvider
     */
    public function testTicketNumberValidate($data)
    {
        $airline = new Airline();

        $this->setExpectedException($data['exception']);

        $airline->setTicketNumber($data['value']);
    }

    public function validDataProvider()
    {
        $passenger = new Passenger();
        $passenger->setFirstName("SERGEI");
        $passenger->setLastName("IVANOV");

        $leg = new Leg();
        $leg->setDepartureAirport('LED');
        $leg->setDestinationAirport("AMS");
        $leg->setDepartureDate("2018-06-20");

        return array(
            array(
                array(
                    "booking_reference" => "IIIKRV",
                    "ticket_number"     => '12342123413',
                    "passengers"        => array(
                        array(
                            "first_name" => "SERGEI",
                            "last_name"  => "IVANOV",
                        ),
                    ),
                    "legs"              => array(
                        array(
                            "departure_airport"   => "LED",
                            "destination_airport" => "AMS",
                            "departure_date"      => "2018-06-20",
                        ),
                    ),
                ),
            ),
            array(
                array(
                    "booking_reference" => "",
                    "ticket_number"     => '',
                    "passengers"        => array(
                        $passenger,
                    ),
                    "legs"              => array(
                        $leg,
                    ),
                ),
            ),
        );
    }

    public function exceptionDataProvider()
    {
        return array(
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\EmptyPropertyValueException',
                    "value"     => '',
                ),
            ),
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\EmptyPropertyValueException',
                    "value"     => null,
                ),
            ),
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException',
                    "value"     => new \stdClass(),
                ),
            ),
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException',
                    "value"     => array(1, 2, 3),
                ),
            ),
        );
    }

    public function stringsExceptionDataProvider()
    {
        return array(
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException',
                    "value"     => array(),
                ),
            ),
            array(
                array(
                    "exception" => 'YandexCheckout\Common\Exceptions\InvalidPropertyValueException',
                    "value"     => 'stringThatGreaterThanNeededCharsLongAndActuallyNotValidAtAll123',
                ),
            ),
        );
    }
}
