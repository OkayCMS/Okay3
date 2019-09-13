<?php

namespace Common\Exceptions;

use YandexCheckout\Common\Exceptions\NotFoundException;

require_once __DIR__ . '/AbstractApiRequestExceptionTest.php';

class NotFoundExceptionTest extends AbstractApiRequestExceptionTest
{
    public function getTestInstance($message = '', $code = 0, $responseHeaders = array(), $responseBody = null)
    {
        return new NotFoundException($responseHeaders, $responseBody);
    }

    public function expectedHttpCode()
    {
        return NotFoundException::HTTP_CODE;
    }
}
