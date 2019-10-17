<?php

namespace Tests\YandexCheckout\Model\Confirmation;

use YandexCheckout\Model\Confirmation\ConfirmationExternal;
use YandexCheckout\Model\ConfirmationType;


class ConfirmationExternalTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationExternal
     */
    protected function getTestInstance()
    {
        return new ConfirmationExternal();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::EXTERNAL;
    }
}