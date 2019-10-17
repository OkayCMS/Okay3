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

namespace YandexCheckout\Request\Receipts;


use YandexCheckout\Model\ReceiptType;

/**
 * Class ReceiptResponseFactory
 * @package YandexCheckout\Request\Receipts
 */
class ReceiptResponseFactory
{
    private $typeClassMap = array(
        ReceiptType::PAYMENT => 'PaymentReceiptResponse',
        ReceiptType::REFUND  => 'RefundReceiptResponse',
    );

    /**
     * @param array $data
     *
     * @return AbstractReceiptResponse
     */
    public function factory($data)
    {
        if (array_key_exists('type', $data)) {
            $type = $data['type'];
        } else {
            throw new \InvalidArgumentException(
                'Parameter type not specified in ReceiptResponseFactory.factory()'
            );
        }
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Invalid receipt type value in receipt factory');
        }
        if (!in_array($type, ReceiptType::getValidValues())) {
            throw new \InvalidArgumentException('Invalid receipt data type "' . $type . '"');
        }
        $className = __NAMESPACE__ . '\\' . $this->typeClassMap[$type];

        return new $className($data);
    }
}
