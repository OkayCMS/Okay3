<?php

namespace Tests\YandexCheckout\Model\Confirmation;

use YandexCheckout\Model\Confirmation\ConfirmationCodeVerification;
use YandexCheckout\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationTest.php';

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