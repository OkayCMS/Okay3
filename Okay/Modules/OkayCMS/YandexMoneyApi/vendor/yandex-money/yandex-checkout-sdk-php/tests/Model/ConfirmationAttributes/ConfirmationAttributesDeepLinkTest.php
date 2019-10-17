<?php

namespace Tests\YandexCheckout\Model\ConfirmationAttributes;

use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesDeepLink;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationAttributesDeepLinkTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesDeepLink
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesDeepLink();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::DEEPLINK;
    }
}