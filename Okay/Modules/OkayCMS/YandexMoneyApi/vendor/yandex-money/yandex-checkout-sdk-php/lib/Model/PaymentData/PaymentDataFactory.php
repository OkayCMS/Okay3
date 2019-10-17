<?php

/**
 * The MIT License
 *
 * Copyright (c) 2017 NBCO Yandex.Money LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YandexCheckout\Model\PaymentData;

use YandexCheckout\Model\PaymentMethodType;

class PaymentDataFactory
{
    private $typeClassMap = array(
        PaymentMethodType::YANDEX_MONEY   => 'PaymentDataYandexWallet',
        PaymentMethodType::BANK_CARD      => 'PaymentDataBankCard',
        PaymentMethodType::SBERBANK       => 'PaymentDataSberbank',
        PaymentMethodType::CASH           => 'PaymentDataCash',
        PaymentMethodType::MOBILE_BALANCE => 'PaymentDataMobileBalance',
        PaymentMethodType::APPLE_PAY      => 'PaymentDataApplePay',
        PaymentMethodType::GOOGLE_PAY     => 'PaymentDataGooglePay',
        PaymentMethodType::QIWI           => 'PaymentDataQiwi',
        PaymentMethodType::WEBMONEY       => 'PaymentDataWebmoney',
        PaymentMethodType::ALFABANK       => 'PaymentDataAlfabank',
        PaymentMethodType::INSTALLMENTS   => 'PaymentDataInstallments',
        PaymentMethodType::B2B_SBERBANK   => 'PaymentDataB2bSberbank',
        PaymentMethodType::TINKOFF_BANK   => 'PaymentDataTinkoffBank',
        PaymentMethodType::WECHAT         => 'PaymentDataWechat',
    );

    /**
     * @param string $type
     *
     * @return AbstractPaymentData
     */
    public function factory($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Invalid payment type value in payment factory');
        }
        if (!array_key_exists($type, $this->typeClassMap)) {
            throw new \InvalidArgumentException('Invalid payment data type "'.$type.'"');
        }
        $className = __NAMESPACE__.'\\'.$this->typeClassMap[$type];

        return new $className();
    }

    /**
     * @param array $data
     * @param string|null $type
     *
     * @return AbstractPaymentData
     */
    public function factoryFromArray(array $data, $type = null)
    {
        if ($type === null) {
            if (array_key_exists('type', $data)) {
                $type = $data['type'];
                unset($data['type']);
            } else {
                throw new \InvalidArgumentException(
                    'Parameter type not specified in PaymentDataFactory.factoryFromArray()'
                );
            }
        }
        $paymentData = $this->factory($type);
        foreach ($data as $key => $value) {
            if ($paymentData->offsetExists($key)) {
                $paymentData->offsetSet($key, $value);
            }
        }

        return $paymentData;
    }
}
