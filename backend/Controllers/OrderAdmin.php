<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Requests\OrdersRequest;
use Okay\Core\Notify;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\OrderLabelsEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\PaymentsEntity;

class OrderAdmin extends IndexAdmin
{
    
    public function fetch(
        OrdersEntity $ordersEntity,
        OrderLabelsEntity $orderLabelsEntity,
        OrderStatusEntity $orderStatusEntity,
        DeliveriesEntity $deliveriesEntity,
        PaymentsEntity $paymentsEntity,
        CurrenciesEntity $currenciesEntity,
        Notify $notify,
        OrdersRequest $ordersRequest,
        BackendOrdersHelper $backendOrdersHelper
    ) {
        
        /*Прием информации о заказе*/
        if ($this->request->method('post')) {
            
            $order = $ordersRequest->postOrder();
            $purchases = $ordersRequest->postPurchases();
            
            if (!$orderLabels = $this->request->post('order_labels')) {
                $orderLabels = [];
            }

            // Установим отметку "доставка оплачивается отдельно"
            if ($order->delivery_id) {
                $deliverySeparatePayment = (array)$deliveriesEntity->cols(['separate_payment'])->get((int)$order->delivery_id);
                $order->separate_delivery = $deliverySeparatePayment['separate_payment'];
            }
            
            if (empty($purchases)) {
                $this->design->assign('message_error', 'empty_purchase');
            } else {
                /*Добавление/Обновление заказа*/
                if (empty($order->id)) {
                    $preparedOrder = $backendOrdersHelper->prepareAdd($order);
                    $order->id  = $backendOrdersHelper->add($preparedOrder);
                    $this->design->assign('message_success', 'added');
                } else {
                    $preparedOrder = $backendOrdersHelper->prepareUpdate($order);
                    $backendOrdersHelper->update($preparedOrder);
                    $this->design->assign('message_success', 'updated');
                }
                
                $orderLabelsEntity->updateOrderLabels($order->id, $orderLabels);

                if ($order->id) {
                    /*Работа с покупками заказа*/
                    $postedPurchasesIds = [];
                    foreach ($purchases as $purchase) {
                        if (!empty($purchase->id)) {
                            $preparedPurchase = $backendOrdersHelper->prepareUpdatePurchase($order, $purchase);
                            $backendOrdersHelper->updatePurchase($preparedPurchase);
                        } else {
                            $preparedPurchase = $backendOrdersHelper->prepareAddPurchase($order, $purchase);
                            if (!$purchase->id = $backendOrdersHelper->addPurchase($preparedPurchase)) {
                                $this->design->assign('message_error', 'error_closing');
                            }
                        }
                        $postedPurchasesIds[] = $purchase->id;
                    }

                    // Удалить непереданные товары
                    $backendOrdersHelper->deletePurchases($order, $postedPurchasesIds);

                    // Обновим статус заказа
                    $newStatusId = $this->request->post('status_id', 'integer');
                    if (!$backendOrdersHelper->updateOrderStatus($order, $newStatusId)) {
                        $this->design->assign('message_error', 'error_closing');
                    }

                    // Обновим итоговую стоимость заказа
                    $ordersEntity->updateTotalPrice($order->id);
                    $order = $backendOrdersHelper->findOrder($order->id);

                    // Отправляем письмо пользователю
                    if ($this->request->post('notify_user')) {
                        $notify->emailOrderUser($order->id);
                    }
                }

                // По умолчанию метод ничего не делает, но через него можно зацепиться моделем
                $backendOrdersHelper->executeCustomPost($order);
            }
        } else {
            
            $order = $backendOrdersHelper->findOrder($this->request->get('id', 'integer'));
            // Метки заказа
            $orderLabels = [];
            if (isset($order->id)) {
                $orderLabels = $orderLabelsEntity->find(['order_id' => $order->id]);
                if ($orderLabels) {
                    foreach ($orderLabels as $orderLabel) {
                        $orderLabels[] = $orderLabel->id;
                    }
                }
            }
        }

        $purchases = $backendOrdersHelper->findOrderPurchases($order);

        $subtotal = 0;
        $hasVariantNotInStock = false;
        foreach ($purchases as $purchase) {
            if (($purchase->amount > $purchase->variant->stock || !$purchase->variant->stock) && !$hasVariantNotInStock) {
                $hasVariantNotInStock = true;
            }
            $subtotal += $purchase->price*$purchase->amount;
        }
        // Способ доставки
        $delivery = $backendOrdersHelper->findOrderDelivery($order);
        $this->design->assign('delivery', $delivery);

        // Способ оплаты
        $paymentMethod = $backendOrdersHelper->findOrderPayment($order);
        if (!empty($paymentMethod)) {
            // Валюта оплаты
            $paymentCurrency = $currenciesEntity->get(intval($paymentMethod->currency_id));
            $this->design->assign('payment_currency', $paymentCurrency);
        }

        $user = $backendOrdersHelper->findOrderUser($order);
        $neighborsOrders = $backendOrdersHelper->findNeighborsOrders(
            $order,
            $this->request->get('label', 'integer'),
            $this->request->get('status', 'integer')
        );
        
        $this->design->assign('delivery', $delivery);
        $this->design->assign('payment_method', $paymentMethod);
        $this->design->assign('user', $user);
        $this->design->assign('purchases', $purchases);
        $this->design->assign('subtotal', $subtotal);
        $this->design->assign('order', $order);
        $this->design->assign('hasVariantNotInStock', $hasVariantNotInStock);
        $this->design->assign('neighbors_orders', $neighborsOrders);

        //все статусы
        $allStatuses = $orderStatusEntity->find();
        $this->design->assign('all_status', $allStatuses);
        // Все способы доставки
        $deliveries = $deliveriesEntity->find();
        $this->design->assign('deliveries', $deliveries);
        
        // Все способы оплаты
        $paymentMethods = $paymentsEntity->find();
        $this->design->assign('payment_methods', $paymentMethods);
        
        // Метки заказов
        $labels = $orderLabelsEntity->find();
        $this->design->assign('labels', $labels);
        
        $this->design->assign('order_labels', $orderLabels);
        
        if ($this->request->get('view') == 'print') {
            $this->response->setContent($this->design->fetch('order_print.tpl'));
        } else {
            $this->response->setContent($this->design->fetch('order.tpl'));
        }
    }
    
}
