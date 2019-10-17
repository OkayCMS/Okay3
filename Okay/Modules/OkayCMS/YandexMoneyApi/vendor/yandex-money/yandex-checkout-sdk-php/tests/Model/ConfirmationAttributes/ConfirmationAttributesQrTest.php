<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesQr;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesQrTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesQr
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesQr();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::QR;
    }
}