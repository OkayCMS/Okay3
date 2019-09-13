<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\UnauthorizedException;

require_once __DIR__ . '/AbstractApiRequestExceptionTest.php';

class UnauthorizedExceptionTest extends AbstractApiRequestExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new UnauthorizedException($responseHeaders, $responseBody);
    }

    public function expectedHttpCode()
    {
        return UnauthorizedException::HTTP_CODE;
    }
}
