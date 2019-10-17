<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesCodeVerification;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesCodeVerificationTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesCodeVerification
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesCodeVerification();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::CODE_VERIFICATION;
    }
}
