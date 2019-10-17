<?php

namespace Tests\YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Model\PaymentMethod\PaymentMethodWechat;
use YandexCheckout\Model\PaymentMethodType;

class PaymentMethodWechatTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodWechat
     */
    protected function getTestInstance()
    {
        return new PaymentMethodWechat();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WECHAT;
    }
}