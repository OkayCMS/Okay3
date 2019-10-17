<?php

namespace Model\ConfirmationAttributes;


use Tests\YandexCheckout\Model\ConfirmationAttributes\AbstractConfirmationAttributesTest;
use YandexCheckout\Model\ConfirmationAttributes\AbstractConfirmationAttributes;
use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesEmbedded;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesEmbeddedTest extends AbstractConfirmationAttributesTest
{

    /**
     * @return AbstractConfirmationAttributes
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesEmbedded();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::EMBEDDED;
    }
}