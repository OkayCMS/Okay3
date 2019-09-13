<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\AuthorizeException;

require_once __DIR__ . '/ApiExceptionTest.php';

class AuthorizeExceptionTest extends ApiExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new AuthorizeException($message, $code, $responseHeaders, $responseBody);
    }
}
