<?php

namespace Tests\YandexCheckout\Common\Exceptions;

use YandexCheckout\Common\Exceptions\ForbiddenException;

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
