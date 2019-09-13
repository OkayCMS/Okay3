<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\InternalServerError;

require_once __DIR__ . '/AbstractApiRequestExceptionTest.php';

class InternalServerErrorTest extends AbstractApiRequestExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new InternalServerError($responseHeaders, $responseBody);
    }

    public function expectedHttpCode()
    {
        return InternalServerError::HTTP_CODE;
    }
}
