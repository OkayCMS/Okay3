<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesExternal;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesExternalTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesExternal
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesExternal();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::EXTERNAL;
    }
}