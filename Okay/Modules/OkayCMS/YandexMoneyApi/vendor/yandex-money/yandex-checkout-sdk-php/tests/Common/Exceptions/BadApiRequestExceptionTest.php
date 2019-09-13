<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\BadApiRequestException;

require_once __DIR__ . '/AbstractApiRequestExceptionTest.php';

class BadApiRequestExceptionTest extends AbstractApiRequestExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new BadApiRequestException($responseHeaders, $responseBody);
    }

    public function expectedHttpCode()
    {
        return BadApiRequestException::HTTP_CODE;
    }
}
