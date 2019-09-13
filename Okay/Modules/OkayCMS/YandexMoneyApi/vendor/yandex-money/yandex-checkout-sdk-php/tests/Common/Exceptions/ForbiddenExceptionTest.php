<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\ForbiddenException;

require_once __DIR__ . '/AbstractApiRequestExceptionTest.php';

class ForbiddenExceptionTest extends AbstractApiRequestExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new ForbiddenException($responseHeaders, $responseBody);
    }

    public function expectedHttpCode()
    {
        return ForbiddenException::HTTP_CODE;
    }
}
