<?php

namespace Tests\YandexCheckout\Model\Confirmation;

use YandexCheckout\Model\Confirmation\ConfirmationCodeVerification;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationCodeVerificationTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationCodeVerification
     */
    protected function getTestInstance()
    {
        return new ConfirmationCodeVerification();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::CODE_VERIFICATION;
    }
}