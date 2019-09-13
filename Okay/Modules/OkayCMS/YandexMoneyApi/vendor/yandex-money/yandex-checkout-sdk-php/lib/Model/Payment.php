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

namespace YandexCheckout\Model;

use YandexCheckout\Common\AbstractObject;
use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Model\PaymentMethod\AbstractPaymentMethod;

/**
 * Payment - Данные о платеже
 *
 * @property string $id Идентификатор платежа
 * @property string $status Текущее состояние платежа
 * @property RecipientInterface $recipient  Получатель платежа
 * @property AmountInterface $amount Сумма заказа
 * @property string $description Описание транзакци
 * @property AbstractPaymentMethod $paymentMethod Способ проведения платежа
 * @property AbstractPaymentMethod $payment_method Способ проведения платежа
 * @property \DateTime $createdAt Время создания заказа
 * @property \DateTime $created_at Время создания заказа
 * @property \DateTime $capturedAt Время подтверждения платежа магазином
 * @property \DateTime $captured_at Время подтверждения платежа магазином
 * @property \DateTime $expiresAt Время, до которого можно бесплатно отменить или подтвердить платеж
 * @property \DateTime $expires_at Время, до которого можно бесплатно отменить или подтвердить платеж
 * @property Confirmation\AbstractConfirmation $confirmation Способ подтверждения платежа
 * @property AmountInterface $refundedAmount Сумма возвращенных средств платежа
 * @property AmountInterface $refunded_amount Сумма возвращенных средств платежа
 * @property bool $paid Признак оплаты заказа
 * @property string $receiptRegistration Состояние регистрации фискального чека
 * @property string $receipt_registration Состояние регистрации фискального чека
 * @property Metadata $metadata Метаданные платежа указанные мерчантом
 */
class Payment extends AbstractObject implements PaymentInterface
{
    const MAX_LENGTH_DESCRIPTION = 128;

    /**
     * @var string Идентификатор платежа
     */
    private $_id;

    /**
     * @var string Текущее состояние платежа
     */
    private $_status;

    /**
     * @var RecipientInterface|null Получатель платежа
     */
    private $_recipient;

    /**
     * @var AmountInterface
     */
    private $_amount;

    /**
     * @var string
     */
    private $_description;

    /**
     * @var AbstractPaymentMethod Способ проведения платежа
     */
    private $_paymentMethod;

    /**
     * @var \DateTime Время создания заказа
     */
    private $_createdAt;

    /**
     * @var \DateTime Время подтверждения платежа магазином
     */
    private $_capturedAt;

    /**
     * @var Confirmation\AbstractConfirmation Способ подтверждения платежа
     */
    private $_confirmation;

    /**
     * @var AmountInterface Сумма возвращенных средств платежа
     */
    private $_refundedAmount;

    /**
     * @var bool Признак оплаты заказа
     */
    private $_paid;

    /**
     * @var string Состояние регистрации фискального чека
     */
    private $_receiptRegistration;

    /**
     * @var Metadata Метаданные платежа указанные мерчантом
     */
    private $_metadata;

    /**
     * Время, до которого можно бесплатно отменить или подтвердить платеж. В указанное время платеж в статусе
     * `waiting_for_capture` будет автоматически отменен.
     *
     * @var \DateTime Время, до которого можно бесплатно отменить или подтвердить платеж
     * @since 1.0.2
     */
    private $_expiresAt;

    /**
     * Возвращает идентификатор платежа
     * @return string Идентификатор платежа
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор платежа
     * @param string $value Идентификатор платежа
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setId($value)
    {
        if (TypeCast::canCastToString($value)) {
            $length = mb_strlen($value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException('Invalid payment id value', 0, 'Payment.id', $value);
            }
            $this->_id = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid payment id value type', 0, 'Payment.id', $value);
        }
    }

    /**
     * Возвращает состояние платежа
     * @return string Текущее состояние платежа
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус платежа
     * @param string $value Статус платежа
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!PaymentStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid payment status value', 0, 'Payment.status', $value);
            }
            $this->_status = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment status value type', 0, 'Payment.status', $value
            );
        }
    }

    /**
     * Возвращает получателя платежа
     * @return RecipientInterface|null Получатель платежа или null если получатель не задан
     */
    public function getRecipient()
    {
        return $this->_recipient;
    }

    /**
     * Устанавливает получателя платежа
     * @param RecipientInterface $value Объект с информацией о получателе платежа
     */
    public function setRecipient(RecipientInterface $value)
    {
        $this->_recipient = $value;
    }

    /**
     * Возвращает сумму
     * @return AmountInterface Сумма платежа
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму платежа
     * @param AmountInterface $value Сумма платежа
     */
    public function setAmount(AmountInterface $value)
    {
        $this->_amount = $value;
    }

    /**
     * Возвращает описание транзакции
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает описание транзакции
     * @param string $value
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            $this->_description = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > self::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'Invalid description value', 0, 'CreatePaymentRequest.description', $value
                );
            }
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type', 0, 'CreatePaymentRequest.description', $value
            );
        }
    }

    /**
     * Возвращает используемый способ проведения платежа
     * @return AbstractPaymentMethod Способ проведения платежа
     */
    public function getPaymentMethod()
    {
        return $this->_paymentMethod;
    }

    /**
     * @param AbstractPaymentMethod $value
     */
    public function setPaymentMethod(AbstractPaymentMethod $value)
    {
        $this->_paymentMethod = $value;
    }

    /**
     * Возвращает время создания заказа
     * @return \DateTime Время создания заказа
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * Устанавливает время создания заказа
     * @param \DateTime|string|int $value Время создания заказа
     *
     * @throws EmptyPropertyValueException Выбрасывается если в метод была передана пустая дата
     * @throws InvalidPropertyValueException Выбрасвается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCreatedAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty created_at value', 0, 'payment.createdAt');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid created_at value', 0, 'payment.createdAt', $value);
            }
            $this->_createdAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid created_at value', 0, 'payment.createdAt', $value);
        }
    }

    /**
     * Возвращает время подтверждения платежа магазином или null если если время не задано
     * @return \DateTime|null Время подтверждения платежа магазином
     */
    public function getCapturedAt()
    {
        return $this->_capturedAt;
    }

    /**
     * Устанавливает время подтверждения платежа магазином
     * @param \DateTime|string|int|null $value Время подтверждения платежа магазином
     *
     * @throws InvalidPropertyValueException Выбрасвается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCapturedAt($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid captured_at value', 0, 'payment.capturedAt', $value);
            }
            $this->_capturedAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid captured_at value', 0, 'payment.capturedAt', $value);
        }
    }

    /**
     * Возвращает способ подтверждения платежа
     * @return Confirmation\AbstractConfirmation Способ подтверждения платежа
     */
    public function getConfirmation()
    {
        return $this->_confirmation;
    }

    /**
     * Устанавливает способ подтверждения платежа
     * @param Confirmation\AbstractConfirmation $value Способ подтверждения платежа
     */
    public function setConfirmation(Confirmation\AbstractConfirmation $value)
    {
        $this->_confirmation = $value;
    }

    /**
     * Возвращает сумму возвращенных средств
     * @return AmountInterface Сумма возвращенных средств платежа
     */
    public function getRefundedAmount()
    {
        return $this->_refundedAmount;
    }

    /**
     * Устанавливает сумму возвращенных средств
     * @param AmountInterface $value Сумма возвращенных средств платежа
     */
    public function setRefundedAmount(AmountInterface $value)
    {
        $this->_refundedAmount = $value;
    }

    /**
     * Проверяет был ли уже оплачен заказ
     * @return bool Признак оплаты заказа, true если заказ оплачен, false если нет
     */
    public function getPaid()
    {
        return $this->_paid;
    }

    /**
     * Устанавливает флаг оплаты заказа
     * @param bool $value Признак оплаты заказа
     *
     * @throws EmptyPropertyValueException Выбрасывается если переданный аргумент пуст
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не кастится в булево значение
     */
    public function setPaid($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty payment paid flag value', 0, 'Payment.paid');
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_paid = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment paid flag value type', 0, 'Payment.paid', $value
            );
        }
    }

    /**
     * Возвращает состояние регистрации фискального чека
     * @return string Состояние регистрации фискального чека
     */
    public function getReceiptRegistration()
    {
        return $this->_receiptRegistration;
    }

    /**
     * Устанавливает состояние регистрации фискального чека
     * @param string $value Состояние регистрации фискального чека
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное состояние регистрации не существует
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не строка
     */
    public function setReceiptRegistration($value)
    {
        if ($value === null || $value === '') {
            $this->_receiptRegistration = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (ReceiptRegistrationStatus::valueExists($value)) {
                $this->_receiptRegistration = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid receipt_registration value', 0, 'payment.receiptRegistration', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt_registration value type', 0, 'payment.receiptRegistration', $value
            );
        }
    }

    /**
     * Возвращает метаданные платежа установленные мерчантом
     * @return Metadata Метаданные платежа указанные мерчантом
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Устанавливает метаданные платежа
     * @param Metadata $value Метаданные платежа указанные мерчантом
     */
    public function setMetadata(Metadata $value)
    {
        $this->_metadata = $value;
    }

    /**
     * Возвращает время до которого можно бесплатно отменить или подтвердить платеж или null если оно не задано
     * @return \DateTime|null Время, до которого можно бесплатно отменить или подтвердить платеж
     *
     * @since 1.0.2
     */
    public function getExpiresAt()
    {
        return $this->_expiresAt;
    }

    /**
     * Устанавливает время до которого можно бесплатно отменить или подтвердить платеж
     * @param \DateTime|string|int|null $value Время, до которого можно бесплатно отменить или подтвердить платеж
     *
     * @throws InvalidPropertyValueException Выбрасывается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     *
     * @since 1.0.2
     */
    public function setExpiresAt($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid expires_at value', 0, 'payment.expires_at', $value);
            }
            $this->_expiresAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid expires_at value', 0, 'payment.expires_at', $value);
        }
    }
}
