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

namespace YandexCheckout\Request\Payments\Payment;

use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\ReceiptItem;

/**
 * Класс объекта осуществляющего сериализацию запроса к API на подтверждение заказа
 *
 * @package YandexCheckout\Request\Payments\Payment
 */
class CreateCaptureRequestSerializer
{
    /**
     * Сериализует объект запроса к API на подтверждение заказа в ассоциативный массив
     *
     * @param CreateCaptureRequestInterface $request Сериализуемый объект запроса
     *
     * @return array Ассоциативный массив содержащий информацию для отправки в API
     */
    public function serialize(CreateCaptureRequestInterface $request)
    {
        $result = array();
        if ($request->hasAmount()) {
            $result['amount'] = $this->serializeAmount($request->getAmount());
        }
        if ($request->hasReceipt()) {
            $receipt = $request->getReceipt();
            if ($receipt->notEmpty()) {
                $result['receipt'] = array();
                /** @var ReceiptItem $item */
                foreach ($receipt->getItems() as $item) {
                    $itemArray = array(
                        'description'     => $item->getDescription(),
                        'amount'          => array(
                            'value'    => $item->getPrice()->getValue(),
                            'currency' => $item->getPrice()->getCurrency(),
                        ),
                        'quantity'        => $item->getQuantity(),
                        'vat_code'        => $item->getVatCode(),
                    );

                    if ($value = $item->getPaymentSubject()) {
                        $itemArray['payment_subject'] = $value;
                    }

                    if ($value = $item->getPaymentMode()) {
                        $itemArray['payment_mode'] = $value;
                    }

                    $result['receipt']['items'][] = $itemArray;
                }

                if ($customer = $receipt->getCustomer()) {
                    $customerArray = array();

                    if ($value = $customer->getEmail()) {
                        $customerArray['email'] = $value;
                    }
                    if ($value = $customer->getPhone()) {
                        $customerArray['phone'] = $value;
                    }
                    if ($value = $customer->getFullName()) {
                        $customerArray['full_name'] = $value;
                    }
                    if ($value = $customer->getInn()) {
                        $customerArray['inn'] = $value;
                    }
                    $result['receipt']['customer'] = $customerArray;
                }

                if ($value = $receipt->getTaxSystemCode()) {
                    $result['receipt']['tax_system_code'] = $value;
                }
            }
        }

        return $result;
    }

    private function serializeAmount(AmountInterface $amount)
    {
        return array(
            'value'    => $amount->getValue(),
            'currency' => $amount->getCurrency(),
        );
    }
}