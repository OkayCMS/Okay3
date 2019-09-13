<?php

namespace Tests\YandexCheckout\Client;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Client;
use YandexCheckout\Client\YandexMoneyApi;

class YandexMoneyApiTest extends TestCase
{
    public function testInstance()
    {
        $instance = new YandexMoneyApi();
        self::assertTrue($instance instanceof Client);
    }
}
