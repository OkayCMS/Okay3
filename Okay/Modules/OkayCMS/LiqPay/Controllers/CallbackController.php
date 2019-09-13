<?php


namespace Okay\Modules\OkayCMS\LiqPay\Controllers;


use Okay\Controllers\AbstractController;
use Okay\Core\Money;
use Okay\Core\Notify;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;

class CallbackController extends AbstractController
{
    public function payOrder(
        OrdersEntity $ordersEntity,
        PaymentsEntity $paymentsEntity,
        CurrenciesEntity $currenciesEntity,
        Money $money,
        Notify $notify
    ) {

        $this->response->setContentType(RESPONSE_TEXT);
        
        $publicKey      = $this->request->post('public_key');
        $amount         = $this->request->post('amount');
        $currency       = $this->request->post('currency');
        $description    = $this->request->post('description');
        $liqPayOrderId  = $this->request->post('order_id');
        $orderId        = intval(substr($liqPayOrderId, 0, strpos($liqPayOrderId, '-')));
        $type           = $this->request->post('type');
        $signature      = $this->request->post('signature');
        $status         = $this->request->post('status');
        $transactionId  = $this->request->post('transaction_id');
        $senderPhone    = $this->request->post('sender_phone');

        if ($status !== 'success') {
            $this->response->setContent("bad status")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }

        if ($type !== 'buy') {
            $this->response->setContent("bad type")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }

        // Выберем заказ из базы
        $order = $ordersEntity->get(intval($orderId));
        if (empty($order)) {
            die('Оплачиваемый заказ не найден');
        }

        // Выбираем из базы соответствующий метод оплаты
        $method = $paymentsEntity->get(intval($order->payment_method_id));
        if (empty($method)) {
            $this->response->setContent("Method Not Allowed")->setStatusCode(405);
            $this->response->sendContent();
            exit;
        }
        
        $settings = $paymentsEntity->getPaymentSettings($method->id);
        $payment_currency = $currenciesEntity->get(intval($method->currency_id));

        // Валюта должна совпадать
        if ($currency !== $payment_currency->code) {
            $this->response->setContent("bad currency")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }

        // Проверяем контрольную подпись
        $mySignature = base64_encode(sha1($settings['liq_pay_private_key']
            .$amount
            .$currency
            .$publicKey
            .$liqPayOrderId
            .$type
            .$description
            .$status
            .$transactionId
            .$senderPhone, 1));
        
        if ($mySignature !== $signature) {
            $this->response->setContent("bad sign {$signature}")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }

        // Нельзя оплатить уже оплаченный заказ  
        if ($order->paid) {
            $this->response->setContent("order already paid")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }
            
        if ($amount != round($money->convert($order->total_price, $method->currency_id, false), 2) || $amount<=0) {
            $this->response->setContent("incorrect price")->setStatusCode(400);
            $this->response->sendContent();
            exit;
        }

        // Установим статус оплачен
        $ordersEntity->update(intval($order->id), ['paid'=>1]);

        // Отправим уведомление на email
        $notify->emailOrderUser(intval($order->id));
        $notify->emailOrderAdmin(intval($order->id));

        // Спишем товары  
        $ordersEntity->close(intval($order->id));

    }
}
