<?php

namespace Tests\YandexCheckout\Helpers;


use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\UUID;

class UUIDTest extends TestCase
{
    public function testValidUuid()
    {
        $uuidPattern = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        self::assertTrue(preg_match($uuidPattern , UUID::v4())  === 1);
    }
}