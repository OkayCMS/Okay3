<?php

namespace Okay\Modules\OkayCMS\YandexMoneyApi;

define('YAMONEY_MODULE_VERSION', '1.0.10');

use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Model\Payment;
use YandexCheckout\Client;
use Okay\Core\Modules\Payments\PaymentFormInterface;
use Okay\Core\Modules\AbstractModule;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Core\EntityFactory;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Money;

class PaymentForm extends AbstractModule implements PaymentFormInterface
{
    const DEFAULT_TAX_RATE_ID = 1;

    const INSTALLMENTS_MIN_AMOUNT = 3000;

    private $deliveriesEntity;
    private $purchasesEntity;
    private $paymentsEntity;
    private $featuresEntity;
    private $ordersEntity;
    private $response;
    private $request;
    private $money;

    public function __construct(
        EntityFactory $entityFactory,
        Response      $response,
        Request       $request,
        Money         $money
    ){
        parent::__construct();
        $this->deliveriesEntity = $entityFactory->get(DeliveriesEntity::class);
        $this->purchasesEntity  = $entityFactory->get(PurchasesEntity::class);
        $this->paymentsEntity   = $entityFactory->get(PaymentsEntity::class);
        $this->featuresEntity   = $entityFactory->get(FeaturesEntity::class);
        $this->ordersEntity     = $entityFactory->get(OrdersEntity::class);
        $this->response         = $response;
        $this->request          = $request;
        $this->money            = $money;
    }

    public function checkoutForm($orderId)
    {
        if (empty($buttonText)) {
            $buttonText = 'Перейти к оплате';
        }

        $order            = $this->ordersEntity->get((int) $orderId);
        $paymentMethod    = $this->paymentsEntity->get((int) $order->payment_method_id);
        $settings         = $this->paymentsEntity->getPaymentSettings($paymentMethod->id);
        $amount           = round($this->money->convert($order->total_price, $paymentMethod->currency_id, false), 2);
        $result_url       = $this->request->getRootUrl().'/payment/YandexMoneyApi/callback.php?order='.$order->id.'&action=return';
        $paymentSiteMode  = ($settings['yandex_api_paymode'] == 'site') ? true : false;
        $paymentType     = (!empty($paymentSiteMode)) ? $settings['yandex_api_paymenttype'] : '';

        if (($paymentType == \YandexCheckout\Model\PaymentMethodType::INSTALLMENTS) && ($amount < self::INSTALLMENTS_MIN_AMOUNT)) {
            $messageError = [];
            $messageError[0] = 'Заплатить этим способом не получится: сумма должна быть больше ' . self::INSTALLMENTS_MIN_AMOUNT . ' рублей.';
            $this->design->assign('message_error', $messageError);
            return $this->design->fetch('form.tpl');
        }

        if ($paymentType == \YandexCheckout\Model\PaymentMethodType::ALFABANK) {
            if (isset($_POST['alfabak_login']) && !empty($_POST['alfabak_login'])) {
                $paymentType = new \YandexCheckout\Model\PaymentData\PaymentDataAlfabank();
                try {
                    $paymentType->setLogin($_POST['alfabak_login']);
                } catch (\Exception $e) {
                    $this->design->assign('button_text', $buttonText);
                    $this->design->assign('payment_type', 'alfabank');
                    $this->design->assign('error', true);
                    return $this->design->fetch('form.tpl');
                }
            } else {
                $this->design->assign('button_text', $buttonText);
                $this->design->assign('payment_type', 'alfabank');
                $this->design->assign('error', true);
                return $this->design->fetch('form.tpl');
            }
        }

        if ($paymentType == \YandexCheckout\Model\PaymentMethodType::QIWI) {
            if (isset($_POST['qiwi_phone']) && !empty($_POST['qiwi_phone'])) {

                $paymentType = new \YandexCheckout\Model\PaymentData\PaymentDataQiwi();
                $phone        = preg_replace('/[^\d]/', '', $_POST['qiwi_phone']);
                try {
                    $paymentType->setPhone($phone);
                } catch (\Exception $e) {
                    $this->design->assign('button_text', $buttonText);
                    $this->design->assign('payment_type', 'qiwi');
                    $this->design->assign('error', true);
                    return $this->design->fetch('form.tpl');
                }
            } else {
                $this->design->assign('button_text', $buttonText);
                $this->design->assign('payment_type', 'qiwi');
                $this->design->assign('error', true);
                return $this->design->fetch('form.tpl');
            }
        }

        if (!isset($_POST['payment_submit'])) {
            $this->design->assign('button_text', $buttonText);
            $this->design->assign('settings_pay', $settings);
            $this->design->assign('amount', $amount);
            $this->design->assign('payment_type', $paymentType);
            return $this->design->fetch('form.tpl');
        }

        if (!empty($_POST['payment_type'])) {
            $paymentType = $_POST['payment_type'];
        }
        
        $apiClient = new Client();
        $apiClient->setAuth($settings['yandex_api_shopid'], $settings['yandex_api_password']);
        //$apiClient->setLogger(new YandexMoneyLogger($settings['ya_kassa_debug']));
        $builder = CreatePaymentRequest::builder()
            ->setAmount($amount)
            ->setPaymentMethodData($paymentType)
            ->setCapture(true)
            ->setDescription($this->createDescription($order, $settings))
            ->setConfirmation([
                'type'      => \YandexCheckout\Model\ConfirmationType::REDIRECT,
                'returnUrl' => $result_url,
            ])->setMetadata([
                'cms_name'       => 'ya_api_okay',
                'module_version' => YAMONEY_MODULE_VERSION,
                'order_id'       => $orderId,
            ]);

        if (isset($settings['ya_kassa_api_send_check']) && $settings['ya_kassa_api_send_check']) {

            $purchases = $this->purchasesEntity->find(['order_id' => (int) $order->id]);

            $builder->setReceiptEmail($order->email);

            $idTax = (isset($settings['ya_kassa_api_tax']) && $settings['ya_kassa_api_tax'] ? $settings['ya_kassa_api_tax'] : self::DEFAULT_TAX_RATE_ID);

            foreach ($purchases as $purchase) {
                $builder->addReceiptItem(
                    $purchase->product_name, 
                    $purchase->price, 
                    $purchase->amount,
                    $idTax
                );
            }

            if ($order->delivery_id && $order->delivery_price > 0) {
                $delivery = $this->deliveriesEntity->get((int) $order->delivery_id);
                $builder->addReceiptShipping(
                    $delivery->name, 
                    $order->delivery_price,
                    $idTax
                );
            }
        }

        $paymentRequest = $builder->build();
        $idempotencyKey = base64_encode($order->id.microtime());
        try {
            $response = $apiClient->createPayment($paymentRequest, $idempotencyKey);
        } catch (\Exception $exception) {
            //$logger = new YandexMoneyLogger($settings['ya_kassa_debug']);
            //$logger->error($exception->getMessage());
        }

        if (!empty($response)) {
            $order->payment_details = $response->getId();
            $this->ordersEntity->update($order->id, $order);
            $confirmationUrl = $response->confirmation->confirmationUrl;
            $this->response->redirectTo($confirmationUrl);    
        }

        $messageError = [];
        $messageError[1] = 'Платеж не прошел. Попробуйте еще или выберите другой способ оплаты.';
        $this->design->assign('message_error', $messageError);
        return $this->design->fetch('form.tpl');
    }

    private function createDescription($orderInfo, $config)
    {
        $descriptionTemplate = !empty($config['yandex_description_template'])
            ? $config['yandex_description_template']
            : 'Оплата заказа №%id%';

        $replace = [];
        foreach ($orderInfo as $key => $value) {
            if (is_scalar($value)) {
                $replace['%'.$key.'%'] = $value;
            }
        }

        $description = strtr($descriptionTemplate, $replace);
        return (string) mb_substr($description, 0, Payment::MAX_LENGTH_DESCRIPTION);
    }
}

