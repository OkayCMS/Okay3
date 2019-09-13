<?php

namespace Tests\YandexCheckout\Request\Payments;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesExternal;
use YandexCheckout\Model\ConfirmationAttributes\ConfirmationAttributesRedirect;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\PaymentData\PaymentDataAlfabank;
use YandexCheckout\Model\PaymentData\PaymentDataAndroidPay;
use YandexCheckout\Model\PaymentData\PaymentDataApplePay;
use YandexCheckout\Model\PaymentData\PaymentDataBankCard;
use YandexCheckout\Model\PaymentData\PaymentDataBankCardCard;
use YandexCheckout\Model\PaymentData\PaymentDataMobileBalance;
use YandexCheckout\Model\PaymentData\PaymentDataInstallments;
use YandexCheckout\Model\PaymentData\PaymentDataQiwi;
use YandexCheckout\Model\PaymentData\PaymentDataSberbank;
use YandexCheckout\Model\PaymentData\PaymentDataWebmoney;
use YandexCheckout\Model\PaymentData\PaymentDataYandexWallet;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Request\Payments\CreatePaymentRequestSerializer;

class CreatePaymentRequestSerializerTest extends TestCase
{
    private $fieldMap = array(
        'payment_token'       => 'paymentToken',
        'payment_method_id'   => 'paymentMethodId',
        'client_ip'           => 'clientIp',
    );

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSerialize($options)
    {
        $serializer = new CreatePaymentRequestSerializer();
        $instance = CreatePaymentRequest::builder()->build($options);
        $data = $serializer->serialize($instance);

        $expected = array(
            'amount' => array(
                'value' => $options['amount'],
                'currency' => isset($options['currency']) ? $options['currency'] : CurrencyCode::RUB,
            ),
        );
        foreach ($this->fieldMap as $mapped => $field) {
            if (isset($options[$field])) {
                $value = $options[$field];
                if (!empty($value)) {
                    $expected[$mapped] = $value instanceof \DateTime ? $value->format(DATE_ATOM) : $value;
                }
            }
        }
        if (!empty($options['accountId']) && !empty($options['gatewayId'])) {
            $expected['recipient'] = array(
                'account_id' => $options['accountId'],
                'gateway_id' => $options['gatewayId'],
            );
        }
        if (!empty($options['confirmation'])) {
            $expected['confirmation'] = array(
                'type' => $options['confirmation']->getType(),
            );
            if ($options['confirmation']->getType() === ConfirmationType::REDIRECT) {
                $expected['confirmation']['enforce'] = $options['confirmation']->enforce;
                $expected['confirmation']['return_url'] = $options['confirmation']->returnUrl;
            }
        }
        if (!empty($options['paymentMethodData'])) {
            $expected['payment_method_data'] = array(
                'type' => $options['paymentMethodData']->getType(),
            );
            switch ($options['paymentMethodData']['type']) {
                case PaymentMethodType::ALFABANK:
                    $expected['payment_method_data']['login'] = $options['paymentMethodData']->getLogin();
                    break;
                case PaymentMethodType::APPLE_PAY:
                case PaymentMethodType::ANDROID_PAY:
                    $expected['payment_method_data']['payment_data'] = $options['paymentMethodData']->getPaymentData();
                    break;
                case PaymentMethodType::BANK_CARD:
                    $expected['payment_method_data']['card'] = array(
                        'number'       => $options['paymentMethodData']->getCard()->getNumber(),
                        'expiry_year'  => $options['paymentMethodData']->getCard()->getExpiryYear(),
                        'expiry_month' => $options['paymentMethodData']->getCard()->getExpiryMonth(),
                        'csc'          => $options['paymentMethodData']->getCard()->getCsc(),
                        'cardholder'   => $options['paymentMethodData']->getCard()->getCardholder(),
                    );
                    break;
                case PaymentMethodType::MOBILE_BALANCE:
                case PaymentMethodType::CASH:
                    $expected['payment_method_data']['phone'] = $options['paymentMethodData']->getPhone();
                    break;
                case PaymentMethodType::SBERBANK:
                    $expected['payment_method_data']['phone'] = $options['paymentMethodData']->getPhone();
                    break;
            }
        }
        if (!empty($options['metadata'])) {
            $expected['metadata'] = array();
            foreach ($options['metadata'] as $key => $value) {
                $expected['metadata'][$key] = $value;
            }
        }
        if (!empty($options['receiptItems'])) {
            foreach ($options['receiptItems'] as $item) {
                $expected['receipt']['items'][] = array(
                    'description' => $item['title'],
                    'quantity' => empty($item['quantity']) ? 1 : $item['quantity'],
                    'amount' => array(
                        'value' => $item['price'],
                        'currency' => isset($options['currency']) ? $options['currency'] : CurrencyCode::RUB,
                    ),
                    'vat_code' => empty($item['vatCode']) ? $options['taxSystemCode'] : $item['vatCode'],
                );
            }
        }
        if (!empty($options['receiptEmail'])) {
            $expected['receipt']['email'] = $options['receiptEmail'];
        }
        if (!empty($options['receiptPhone'])) {
            $expected['receipt']['phone'] = $options['receiptPhone'];
        }
        if (!empty($options['taxSystemCode'])) {
            $expected['receipt']['tax_system_code'] = $options['taxSystemCode'];
        }
        if (array_key_exists('capture', $options)) {
            $expected['capture'] = (bool)$options['capture'];
        }
        if (array_key_exists('savePaymentMethod', $options)) {
            $expected['save_payment_method'] = (bool)$options['savePaymentMethod'];
        }
        self::assertEquals($expected, $data);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'amount' => mt_rand(10, 100000),
                    'paymentToken' => Random::str(36),
                    'receiptItems' => array(
                        array(
                            'title' => Random::str(10),
                            'quantity' => Random::int(1, 10),
                            'price' => Random::int(100, 100),
                            'vatCode' => Random::int(1, 6),
                        ),
                        array(
                            'title' => Random::str(10),
                            'price' => Random::int(100, 100),
                        ),
                    ),
                    'receiptEmail' => Random::str(10),
                    'taxSystemCode' => Random::int(1, 6),
                ),
            ),
        );
        $confirmations = array(
            new ConfirmationAttributesExternal(),
            new ConfirmationAttributesRedirect(),
        );
        $paymentData = array(
            new PaymentDataAlfabank(),
            new PaymentDataApplePay(),
            new PaymentDataAndroidPay(),
            new PaymentDataBankCard(),
            new PaymentDataMobileBalance(),
            new PaymentDataQiwi(),
            new PaymentDataSberbank(),
            new PaymentDataWebmoney(),
            new PaymentDataYandexWallet(),
            new PaymentDataInstallments(),
        );
        $paymentData[0]->setLogin(Random::str(10));

        $paymentData[1]->setPaymentData(Random::str(10));
        $paymentData[2]->setPaymentData(Random::str(10));

        $card = new PaymentDataBankCardCard();
        $card->setNumber(Random::str(16, '0123456789'));
        $card->setExpiryYear(Random::int(2000, 2200));
        $card->setExpiryMonth(Random::value(array('01', '02', '03', '04', '05', '06', '07', '08', '09', '11', '12')));
        $card->setCsc(Random::str(4, '0123456789'));
        $card->setCardholder(Random::str(26, 'abcdefghijklmnopqrstuvwxyz'));
        $paymentData[3]->setCard($card);
        $paymentData[4]->setPhone(Random::str(14, '0123456789'));

        $paymentData[6]->setPhone(Random::str(14, '0123456789'));

        $paymentData[9] = Random::value($paymentData);

        $confirmations[1]->setEnforce(true);
        $confirmations[1]->setReturnUrl(Random::str(10));
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'accountId' => uniqid(),
                'gatewayId' => uniqid(),
                'amount' => mt_rand(0, 100000),
                'currency' => CurrencyCode::RUB,
                'referenceId' => uniqid(),
                'paymentMethodData' => $paymentData[$i],
                'confirmation' => Random::value($confirmations),
                'savePaymentMethod' => Random::bool(),
                'capture' => mt_rand(0, 1) ? true : false,
                'clientIp' => long2ip(mt_rand(0, pow(2, 32))),
                'metadata' => array('test' => uniqid()),
                'receiptItems' => $this->getReceipt($i + 1),
                'receiptEmail' => Random::str(10),
                'receiptPhone' => Random::str(12, '0123456789'),
                'taxSystemCode' => Random::int(1, 6),
            );
            $result[] = array($request);
        }
        return $result;
    }

    private function getReceipt($count)
    {
        $result = array();
        for ($i = 0; $i < $count; $i++) {
            $result[] = array(
                'title' => Random::str(10),
                'quantity' => Random::float(1, 100),
                'price' => Random::int(1, 100),
                'vatCode' => Random::int(1, 6),
            );
        }
        return $result;
    }
}