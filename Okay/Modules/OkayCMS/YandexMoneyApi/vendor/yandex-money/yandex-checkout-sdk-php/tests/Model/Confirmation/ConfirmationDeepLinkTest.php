<?php

namespace Tests\YandexCheckout\Model\Confirmation;

use YandexCheckout\Model\Confirmation\ConfirmationDeepLink;
use YandexCheckout\Model\ConfirmationType;

class ConfirmationDeepLinkTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationDeepLink
     */
    protected function getTestInstance()
    {
        return new ConfirmationDeepLink();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::DEEPLINK;
    }
}