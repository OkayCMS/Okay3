<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Notify;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\OrderLabelsEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\UserGroupsEntity;
use Okay\Entities\UsersEntity;
use Okay\Entities\VariantsEntity;

class OrderAdmin extends IndexAdmin
{
    
    public function fetch(
        OrdersEntity $ordersEntity,
        OrderLabelsEntity $orderLabelsEntity,
        OrderStatusEntity $orderStatusEntity,
        PurchasesEntity $purchasesEntity,
        ProductsEntity $productsEntity,
        VariantsEntity $variantsEntity,
        ImagesEntity $imagesEntity,
        DeliveriesEntity $deliveriesEntity,
        PaymentsEntity $paymentsEntity,
        CurrenciesEntity $currenciesEntity,
        UsersEntity $usersEntity,
        UserGroupsEntity $userGroupsEntity,
        Notify $notify
    ) {
        $order = new \stdClass;
        /*Прием информации о заказе*/
        if ($this->request->method('post')) {
            $order->id = $this->request->post('id', 'integer');
            $order->name = $this->request->post('name');
            $order->email = $this->request->post('email');
            $order->phone = $this->request->post('phone');
            $order->address = $this->request->post('address');
            $order->comment = $this->request->post('comment');
            $order->note = $this->request->post('note');
            $order->discount = $this->request->post('discount', 'float');
            $order->coupon_discount = $this->request->post('coupon_discount', 'float');
            $order->delivery_id = $this->request->post('delivery_id', 'integer');
            $order->delivery_price = $this->request->post('delivery_price', 'float');
            $order->payment_method_id = $this->request->post('payment_method_id', 'integer');
            $order->paid = $this->request->post('paid', 'integer');
            $order->user_id = $this->request->post('user_id', 'integer');
            $order->lang_id = $this->request->post('entity_lang_id', 'integer');
            
            if (!$orderLabels = $this->request->post('order_labels')) {
                $orderLabels = [];
            }

            $purchases = [];
            if ($this->request->post('purchases')) {
                foreach ($this->request->post('purchases') as $n => $va) foreach ($va as $i => $v) {
                    if (empty($purchases[$i])) {
                        $purchases[$i] = new \stdClass;
                    }
                    $purchases[$i]->$n = $v;
                }
            }

            if (empty($purchases)) {
                $this->design->assign('message_error', 'empty_purchase');
            } else {
                /*Добавление/Обновление заказа*/
                if (empty($order->id)) {
                    $order->id = $ordersEntity->add($order);
                    $this->design->assign('message_success', 'added');
                } else {
                    $ordersEntity->update($order->id, $order);
                    $this->design->assign('message_success', 'updated');
                }

                $orderLabelsEntity->updateOrderLabels($order->id, $orderLabels);

                if ($order->id) {
                    /*Работа с покупками заказа*/
                    $postedPurchasesIds = [];
                    foreach ($purchases as $purchase) {
                        $variant = $variantsEntity->get($purchase->variant_id);

                        if (!empty($purchase->id)) {
                            if (!empty($variant)) {
                                $purchasesEntity->update($purchase->id, [
                                    'variant_id' => $purchase->variant_id,
                                    'variant_name' => $variant->name,
                                    'sku' => $variant->sku,
                                    'price' => $purchase->price,
                                    'amount' => $purchase->amount,
                                ]);
                            } else {
                                $purchasesEntity->update($purchase->id, ['price' => $purchase->price, 'amount' => $purchase->amount]);
                            }
                        } elseif (!$purchase->id = $purchasesEntity->add(['order_id' => $order->id, 'variant_id' => $purchase->variant_id, 'price' => $purchase->price, 'amount' => $purchase->amount])) {
                            $this->design->assign('message_error', 'error_closing');
                        }

                        $postedPurchasesIds[] = $purchase->id;
                    }

                    // Удалить непереданные товары
                    foreach ($purchasesEntity->find(['order_id' => $order->id]) as $p) {
                        if (!in_array($p->id, $postedPurchasesIds)) {
                            $purchasesEntity->delete($p->id);
                        }
                    }

                    $newStatusId = $this->request->post('status_id', 'integer');
                    $newStatusInfo = $orderStatusEntity->get(intval($newStatusId));

                    if ($newStatusInfo->is_close == 1) {
                        if (!$ordersEntity->close(intval($order->id))) {
                            $this->design->assign('message_error', 'error_closing');
                        } else {
                            $ordersEntity->update($order->id, ['status_id' => $newStatusId]);
                        }
                    } else {
                        if ($ordersEntity->open(intval($order->id))) {
                            $ordersEntity->update($order->id, ['status_id' => $newStatusId]);
                        }
                    }

                    $d = $deliveriesEntity->cols(['separate_payment'])->get((int)$order->delivery_id);
                    $o = $ordersEntity->cols(['separate_delivery'])->get((int)$order->id);
                    if ($d && $o && $d->separate_payment != $o->separate_delivery) {
                        $ordersEntity->update($order->id, ['separate_delivery'=>$d->separate_payment]);
                    }

                    // Обновим итоговую стоимость заказа
                    $ordersEntity->updateTotalPrice($order->id);
                    $order = $ordersEntity->get((int)$order->id);

                    // Отправляем письмо пользователю
                    if ($this->request->post('notify_user')) {
                        $notify->emailOrderUser($order->id);
                    }
                }
            }
        } else {
            $order->id = $this->request->get('id', 'integer');
            $order = $ordersEntity->get(intval($order->id));
            // Метки заказа
            $orderLabels = [];
            if(isset($order->id)) {
                $orderLabels = $orderLabelsEntity->find(['order_id', $order->id]);
                if($orderLabels) {
                    foreach ($orderLabels as $orderLabel) {
                        $orderLabels[] = $orderLabel->id;
                    }
                }
            }
        }
        
        
        $subtotal = 0;
        $purchases_count = 0;
        if (!empty($order->id) && ($purchases = $purchasesEntity->find(['order_id'=>$order->id]))) {
            // Покупки
            $productsIds = [];
            $variantsIds = [];
            $imagesIds = [];
            foreach ($purchases as $purchase) {
                $productsIds[] = $purchase->product_id;
                $variantsIds[] = $purchase->variant_id;
            }
            
            $products = [];
            foreach ($productsEntity->find(['id'=>$productsIds, 'limit' => count($productsIds)]) as $p) {
                $products[$p->id] = $p;
                $imagesIds[] = $p->main_image_id;
            }

            if (!empty($imagesIds)) {
                $images = $imagesEntity->find(['id'=>$imagesIds]);
                foreach ($images as $image) {
                    if (isset($products[$image->product_id])) {
                        $products[$image->product_id]->image = $image;
                    }
                }
            }
            
            $variants = [];
            foreach ($variantsEntity->find(['product_id'=>$productsIds]) as $v) {
                if ($v->rate_from != $v->rate_to && $v->currency_id) {
                    $v->price = number_format($v->price*$v->rate_to/$v->rate_from, 2, '.', '');
                    $v->compare_price = number_format($v->compare_price*$v->rate_to/$v->rate_from, 2, '.', '');
                }
                $v->units = $v->units ? $v->units : $this->settings->units;
                $variants[$v->id] = $v;
            }
            
            foreach ($variants as $variant) {
                if (!empty($products[$variant->product_id])) {
                    $products[$variant->product_id]->variants[] = $variant;
                }
            }

            /*Определение, есть ли товары с количеством 0*/
            $hasVariantNotInStock = false;
            foreach ($purchases as $purchase) {
                if(!empty($products[$purchase->product_id])) {
                    $purchase->product = $products[$purchase->product_id];
                }
                if (!empty($variants[$purchase->variant_id])) {
                    $purchase->variant = $variants[$purchase->variant_id];
                }
                if (($purchase->amount > $purchase->variant->stock || !$purchase->variant->stock) && !$hasVariantNotInStock) {
                    $hasVariantNotInStock = true;
                }
                $subtotal += $purchase->price*$purchase->amount;
                $purchases_count += $purchase->amount;
            }
            $this->design->assign('hasVariantNotInStock', $hasVariantNotInStock);
        } else {
            $purchases = [];
        }
        
        // Если новый заказ и передали get параметры
        if (empty($order->id)) {
            $order = new \stdClass;
            if (empty($order->phone)) {
                $order->phone = $this->request->get('phone', 'string');
            }
            if (empty($order->name)) {
                $order->name = $this->request->get('name', 'string');
            }
            if (empty($order->address)) {
                $order->address = $this->request->get('address', 'string');
            }
            if (empty($order->email)) {
                $order->email = $this->request->get('email', 'string');
            }
        }
        
        $this->design->assign('purchases', $purchases);
        $this->design->assign('purchases_count', $purchases_count);
        $this->design->assign('subtotal', $subtotal);
        $this->design->assign('order', $order);
        
        if (!empty($order->id)) {
            // Способ доставки
            $delivery = $deliveriesEntity->get($order->delivery_id);
            $this->design->assign('delivery', $delivery);

            // Способ оплаты
            $paymentMethod = $paymentsEntity->get($order->payment_method_id);
            
            if (!empty($paymentMethod)) {
                $this->design->assign('payment_method', $paymentMethod);
                // Валюта оплаты
                $paymentCurrency = $currenciesEntity->get(intval($paymentMethod->currency_id));
                $this->design->assign('payment_currency', $paymentCurrency);
            }
            // Пользователь
            if (!empty($order->user_id)) {
                $orderUser = $usersEntity->get(intval($order->user_id));
                $orderUser->group = $userGroupsEntity->get(intval($orderUser->group_id));
                $this->design->assign('user', $orderUser);
            }
        }

        if (!empty($order->id)) {
            $neighborsFilter = [];
            $neighborsFilter['id'] = $order->id;
            $neighborsFilter['status_id'] = $this->request->get('status');
            $neighborsFilter['label_id'] = $this->request->get('label');
            $this->design->assign('neighbors_orders', $ordersEntity->getNeighborsOrders($neighborsFilter));
        }

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
