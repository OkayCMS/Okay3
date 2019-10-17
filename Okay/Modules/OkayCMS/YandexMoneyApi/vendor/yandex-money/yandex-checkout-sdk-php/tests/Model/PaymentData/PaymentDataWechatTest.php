<?php

namespace Tests\YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentData\PaymentDataWechat;
use YandexCheckout\Model\PaymentMethodType;

class PaymentDataWechatTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataWechat
     */
    protected function getTestInstance()
    {
        return new PaymentDataWechat();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WECHAT;
    }
}