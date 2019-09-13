<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;

require_once __DIR__ . '/InvalidPropertyExceptionTest.php';

class EmptyPropertyValueExceptionTest extends InvalidPropertyExceptionTest
{
    /**
     * @param string $message
     * @param string $property
     * @return EmptyPropertyValueException
     */
    protected function getTestInstance($message, $property)
    {
        return new EmptyPropertyValueException($message, 0, $property);
    }
}
