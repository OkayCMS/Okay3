<?php


namespace Okay\Modules\OkayCMS\WayForPay\Controllers;


use Okay\Core\Money;
use Okay\Core\Notify;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Controllers\AbstractController;

class CallbackController extends AbstractController
{
    public function payOrder(
        Money $money,
        Notify $notify,
        OrdersEntity $ordersEntity,
        PaymentsEntity $paymentsEntity
    ) {
        $keysForSignature = [
            'merchantAccount',
            'orderReference',
            'amount',
            'currency',
            'authCode',
            'cardPan',
            'transactionStatus',
            'reasonCode'
        ];

        $data = json_decode(file_get_contents("php://input"), true);

        $orderId = $this->matchOrderIdFromInputData($data);

        $order = $ordersEntity->get((int) $orderId);
        if (empty($order)) {
            throw new \Exception('Order not found');
        }

        $method = $paymentsEntity->get((int) $order->payment_method_id);
        if (empty($method)) {
            throw new \Exception('Invalid payment method');
        }

        $amount = !empty($data['amount']) ? $data['amount'] : null;
        $w4pAmount = round($amount, 2);
        $orderAmount = round($money->convert($order->total_price, $method->currency_id, false), 2);
        if ($orderAmount != $w4pAmount) {
            throw new \Exception('Invalid total order price');
        }

        $settings = unserialize($method->settings);

        $sign = array();
        foreach ($keysForSignature as $dataKey) {
            if (array_key_exists($dataKey, $data)) {
                $sign [] = $data[$dataKey];
            }
        }

        $sign = implode(';', $sign);
        $sign = hash_hmac('md5', $sign, $settings['wayforpay_secretkey']);
        if (!empty($data["merchantSignature"]) && $data["merchantSignature"] != $sign) {
            throw new \Exception('Invalid merchant signature');
        }

        $responseToGateway = [
            'orderReference' => $data['orderReference'],
            'status'         => 'accept',
            'time'           => time()
        ];

        $sign = array();
        foreach ($responseToGateway as $dataKey => $dataValue) {
            $sign [] = $dataValue;
        }

        $sign = implode(';', $sign);
        $sign = hash_hmac('md5', $sign, $settings['wayforpay_secretkey']);
        $responseToGateway['signature'] = $sign;

        if (!empty($data['transactionStatus']) &&  $data['transactionStatus'] == 'Approved' && !$order->paid) {
            $ordersEntity->update((int) $order->id, ['paid' => 1]);
            $ordersEntity->close((int) $order->id);
            $notify->emailOrderUser((int) $order->id);
            $notify->emailOrderAdmin((int) $order->id);
        }

        $this->response->setContent(json_encode($responseToGateway), RESPONSE_JSON);
    }

    private function matchOrderIdFromInputData($data)
    {
        $orderParse = !empty($data['orderReference']) ? explode('#', $data['orderReference']) : null;

        if (is_array($orderParse)) {
            return reset($orderParse);
        }

        return $orderParse;
    }
}