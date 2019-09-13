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

use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Model\PaymentMethodType;

/**
 * PaymentDataAndroidPay
 * Платежные данные для проведения оплаты при помощи Android Pay.
 * @property string $paymentData содержимое поля paymentData объекта PKPaymentToken, закодированное в Base64
 * @property string $payment_data содержимое поля paymentData объекта PKPaymentToken, закодированное в Base64
 */
class PaymentDataAndroidPay extends AbstractPaymentData
{
    /**
     * @var string содержимое поля paymentData объекта PKPaymentToken, закодированное в Base64
     */
    private $_paymentData;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::ANDROID_PAY);
    }

    /**
     * @return string содержимое поля paymentData объекта PKPaymentToken, закодированное в Base64
     */
    public function getPaymentData()
    {
        return $this->_paymentData;
    }

    /**
     * @param string $value содержимое поля paymentData объекта PKPaymentToken, закодированное в Base64
     */
    public function setPaymentData($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for paymentData', 0, 'PaymentDataAndroidPay.paymentData'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $this->_paymentData = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for paymentData', 0, 'PaymentDataAndroidPay.paymentData', $value
            );
        }
    }
}
