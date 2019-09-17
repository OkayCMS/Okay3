<?php


namespace Okay\Controllers;


use Okay\Core\Notify;
use Okay\Core\Router;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\CouponsEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Core\Request;
use Okay\Core\Cart;
use Okay\Core\Validator;
use Okay\Core\Languages;
use Okay\Core\Money;

class CartController extends AbstractController
{
    /*Отображение заказа*/
    public function render(
        DeliveriesEntity $deliveriesEntity,
        PaymentsEntity   $paymentsEntity,
        OrdersEntity     $ordersEntity,
        CouponsEntity    $couponsEntity,
        CurrenciesEntity $currenciesEntity,
        Languages        $languages,
        PurchasesEntity  $purchasesEntity,
        Validator        $validator,
        Request          $request,
        Notify           $notify,
        Cart             $cartCore
    ) {

        // Если передан id варианта, добавим его в корзину
        if ($variantId = $request->get('variant', 'integer')) {
            $cartCore->addItem($variantId, $request->get('amount', 'integer'));
            $this->response->redirectTo(Router::generateUrl('cart', [], true));
        }


        /*Оформление заказа*/
        if (isset($_POST['checkout'])) {
            $order = new \stdClass;
            $order->payment_method_id = $this->request->post('payment_method_id', 'integer');
            $order->delivery_id = $this->request->post('delivery_id', 'integer');
            $order->name        = $this->request->post('name');
            $order->email       = $this->request->post('email');
            $order->address     = $this->request->post('address');
            $order->phone       = $this->request->post('phone');
            $order->comment     = $this->request->post('comment');
            $order->ip          = $_SERVER['REMOTE_ADDR'];

            $this->design->assign('delivery_id', $order->delivery_id);
            $this->design->assign('name', $order->name);
            $this->design->assign('email', $order->email);
            $this->design->assign('phone', $order->phone);
            $this->design->assign('address', $order->address);

            $captchaCode =  $this->request->post('captcha_code', 'string');

            // Скидка
            $cart = $cartCore->get();
            $order->discount = $cart->discount;

            if($cart->coupon) {
                $order->coupon_discount = $cart->coupon_discount;
                $order->coupon_code     = $cart->coupon->code;
            }

            if(!empty($this->user->id)) {
                $order->user_id = $this->user->id;
            }

            /*Валидация данных клиента*/
            if(!$validator->isName($order->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif(!$validator->isEmail($order->email, true)) {
                $this->design->assign('error', 'empty_email');
            } elseif(!$validator->isPhone($order->phone)) {
                $this->design->assign('error', 'empty_phone');
            } elseif(!$validator->isAddress($order->address)) {
                $this->design->assign('error', 'empty_address');
            } elseif(!$validator->isComment($order->comment)) {
                $this->design->assign('error', 'empty_comment');
            } elseif($this->settings->captcha_cart && !$validator->verifyCaptcha('captcha_cart', $captchaCode)) {
                 $this->design->assign('error', 'captcha');
            } else {
                // Добавляем заказ в базу
                $order->lang_id = $languages->getLangId();
                $orderId        = $ordersEntity->add($order);
                $_SESSION['order_id'] = $orderId;

                // Если использовали купон, увеличим количество его использований
                if($cart->coupon) {
                    $couponsEntity->update($cart->coupon->id, [
                        'usages'=>$cart->coupon->usages+1
                    ]);
                }

                // Добавляем товары к заказу
                foreach ($request->post('amounts') as $variantId=>$amount) {
                    $purchasesEntity->add([
                        'order_id'   =>$orderId, 
                        'variant_id' =>intval($variantId), 
                        'amount'     =>intval($amount)
                    ]);
                }
                $order = $ordersEntity->get(intval($orderId));

                // Стоимость доставки
                $delivery = $deliveriesEntity->get(intval($order->delivery_id));
                if(!empty($delivery) && $delivery->free_from > $order->total_price) {
                    $ordersEntity->update($order->id, [
                        'delivery_price'    => $delivery->price, 
                        'separate_delivery' => $delivery->separate_payment
                    ]);
                } elseif ($delivery->separate_payment) {
                    $ordersEntity->update($order->id, [
                        'separate_delivery' => $delivery->separate_payment
                    ]);
                }

                // Обновим итоговую стоимость заказа
                $ordersEntity->updateTotalPrice($orderId);
                
                // Отправляем письмо пользователю
                $notify->emailOrderUser($order->id);

                // Отправляем письмо администратору
                $notify->emailOrderAdmin($order->id);

                $cartCore->clear();
                // Перенаправляем на страницу заказа
                $this->response->redirectTo(Router::generateUrl('order', ['url' => $order->url], true));
            }
        } else {
            // Если нам запостили amounts, обновляем их
            if($amounts = $request->post('amounts')) {
                foreach($amounts as $variantId=>$amount) {
                    $cartCore->updateItem($variantId, $amount);
                }

                $couponCode = trim($request->post('coupon_code', 'string'));
                if(empty($couponCode)) {
                    $cartCore->applyCoupon('');
                    $this->response->redirectTo(Router::generateUrl('cart', [], true));
                } 
                else {
                    $coupon = $couponsEntity->get((string)$couponCode);
                    if(empty($coupon) || !$coupon->valid) {
                        $cartCore->applyCoupon($couponCode);
                        $this->design->assign('coupon_error', 'invalid');
                    } else {
                        $cartCore->applyCoupon($couponCode);
                        $this->response->redirectTo(Router::generateUrl('cart', [], true));
                    }
                }
            }
        }

        // Способы доставки
        $deliveries = $deliveriesEntity->find(['enabled'=>1]);
        foreach($deliveries as $delivery) {
            $delivery->payment_methods = $paymentsEntity->find(['delivery_id'=>$delivery->id, 'enabled'=>1]);
        }

        $this->design->assign('all_currencies', $currenciesEntity->mappedBy('id')->find());
        $this->design->assign('deliveries', $deliveries);
        
        // Данные пользователя
        if ($this->user) {
            $last_order = $ordersEntity->find(['user_id'=>$this->user->id, 'limit'=>1]);
            $last_order = reset($last_order);
            if ($last_order) {
                $this->design->assign('name', $last_order->name);
                $this->design->assign('email', $last_order->email);
                $this->design->assign('phone', $last_order->phone);
                $this->design->assign('address', $last_order->address);
            } else {
                $this->design->assign('name', $this->user->name);
                $this->design->assign('email', $this->user->email);
                $this->design->assign('phone', $this->user->phone);
                $this->design->assign('address', $this->user->address);
            }
        }
        
        if($couponsEntity->count(['valid'=>1])>0) {
            $this->design->assign('coupon_request', true);
        }
                
        $this->design->assign('cart', $cartCore->get());
        $this->response->setContent($this->design->fetch('cart.tpl'));
    }
    
    public function cartAjax(
        DeliveriesEntity $deliveriesEntity,
        PaymentsEntity   $paymentsEntity,
        CouponsEntity    $couponsEntity,
        CurrenciesEntity $currenciesEntity,
        Request          $request,
        Cart             $cartCore,
        Money            $moneyCore
    ) {
        $action     = $request->get('action');
        $variantId  = $request->get('variant_id', 'integer');
        $amount     = $request->get('amount', 'integer');
        
        switch($action) {
            case 'update_citem':
                $cartCore->updateItem($variantId, $amount);
                break;
            case 'remove_citem':
                $cartCore->deleteItem($variantId);
                break;
            case 'add_citem':
                $cartCore->addItem($variantId, $amount);
                break;
            default:
                break;
        }

        $cart = $cartCore->get();
        $this->design->assign('cart', $cart);

        $this->design->assign('all_currencies', $currenciesEntity->find());
        $deliveries = $deliveriesEntity->find(['enabled'=>1]);
        $this->design->assign('deliveries', $deliveries);
        foreach ($deliveries as $delivery) {
            $delivery->payment_methods = $paymentsEntity->find(['delivery_id'=>$delivery->id, 'enabled'=>1]);
        }

        /*Рабтаем с товарами в корзине*/
        if (count($cart->purchases) > 0) {
            $couponCode = trim($request->get('coupon_code', 'string'));
            if (empty($couponCode)) {
                $cartCore->applyCoupon('');
                if ($this->request->get('action') == 'coupon_apply') {
                    $this->design->assign('coupon_error', 'empty');
                }
            } else {
                $coupon = $couponsEntity->get((string)$couponCode);
                if (empty($coupon) || !$coupon->valid) {
                    $cartCore->applyCoupon($couponCode);
                    $this->design->assign('coupon_error', 'invalid');
                } else {
                    $cartCore->applyCoupon($couponCode);
                }
            }

            if ($couponsEntity->count(['valid'=>1])>0) {
                $this->design->assign('coupon_request', true);
            }

            $cart = $cartCore->get();
            $this->design->assign('cart', $cart);
            
            $result = ['result'=>1];
            $result['cart_informer']   = $this->design->fetch('cart_informer.tpl');
            $result['cart_purchases']  = $this->design->fetch('cart_purchases.tpl');
            $result['cart_deliveries'] = $this->design->fetch('cart_deliveries.tpl');
            $result['currency_sign']   = $this->currency->sign;
            $result['total_price']     = $moneyCore->convert($cart->total_price, $this->currency->id);
            $result['total_products']  = $cart->total_products;
        } else {
            $result = ['result'=>0];
            $result['cart_informer'] = $this->design->fetch('cart_informer.tpl');
            $result['content']       = $this->design->fetch('cart.tpl');
        }

        $this->response->setContent(json_encode($result), RESPONSE_JSON);
    }

    public function removeItem(Cart $cartCore, $variantId)
    {
        $cartCore->deleteItem($variantId);
        $this->response->redirectTo(Router::generateUrl('cart', [], true));
    }

    public function addItem(Cart $cartCore, $variantId)
    {
        $cartCore->addItem($variantId);
        $this->response->redirectTo(Router::generateUrl('cart', [], true));
    }
    
}
