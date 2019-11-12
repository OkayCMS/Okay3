<?php


namespace Okay\Controllers;


use Okay\Helpers\CartHelper;
use Okay\Helpers\CouponHelper;
use Okay\Helpers\MetadataHelpers\CartMetadataHelper;
use Okay\Requests\CartRequest;
use Okay\Core\Notify;
use Okay\Core\Router;
use Okay\Core\TemplateConfig;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\CouponsEntity;
use Okay\Entities\OrdersEntity;
use Okay\Core\Request;
use Okay\Core\Cart;
use Okay\Core\Languages;
use Okay\Core\Money;
use Okay\Helpers\DeliveriesHelper;
use Okay\Helpers\PaymentsHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Helpers\OrdersHelper;
use Psr\Log\LoggerInterface;

class CartController extends AbstractController
{
    /*Отображение заказа*/
    public function render(
        DeliveriesEntity   $deliveriesEntity,
        OrdersEntity       $ordersEntity,
        CouponsEntity      $couponsEntity,
        CurrenciesEntity   $currenciesEntity,
        Languages          $languages,
        Request            $request,
        Notify             $notify,
        Cart               $cartCore,
        DeliveriesHelper   $deliveriesHelper,
        PaymentsHelper     $paymentsHelper,
        OrdersHelper       $ordersHelper,
        CartRequest        $cartRequest,
        CartHelper         $cartHelper,
        ValidateHelper     $validateHelper,
        CouponHelper       $couponHelper,
        CartMetadataHelper $cartMetadataHelper
    ) {

        // Если передан id варианта, добавим его в корзину
        if ($variantId = $request->get('variant', 'integer')) {
            $cartCore->addItem($variantId, $request->get('amount', 'integer'));
            $this->response->redirectTo(Router::generateUrl('cart', [], true));
        }

        $this->setMetadataHelper($cartMetadataHelper);
        
        $cart = $cartCore->get();
        /*Оформление заказа*/
        if (isset($_POST['checkout'])) {
            $order = $cartRequest->postOrder();
            $order = $ordersHelper->attachDiscountIfExists($order, $cart);
            $order = $ordersHelper->attachCouponIfExists($order, $cart);
            $order = $ordersHelper->attachUserIfLogin($order, $this->user);

            if ($error = $validateHelper->getCartValidateError($order)) {
                $this->design->assign('error', $error);
            } else {
                // Добавляем заказ в базу
                $order->lang_id = $languages->getLangId();
                $preparedOrder  = $ordersHelper->prepareAdd($order);
                $orderId        = $ordersHelper->add($preparedOrder);
                $_SESSION['order_id'] = $orderId;

                $couponHelper->registerUseIfExists($cart->coupon);

                $order = $ordersEntity->get((int) $orderId);
                if (!empty($order->delivery_id)) {
                    $delivery          = $deliveriesEntity->get((int) $order->delivery_id);
                    $deliveryPriceInfo = $deliveriesHelper->prepareDeliveryPriceInfo($delivery, $order);
                    $deliveriesHelper->updateDeliveryPriceInfo($deliveryPriceInfo, $order);

                    if (isset($deliveryPriceInfo['delivery_price'])) {
                        $cart->total_price += $deliveryPriceInfo['delivery_price'];
                    }
                }

                $preparedCart = $cartHelper->prepareCart($cart, $orderId);
                $cartHelper->cartToOrder($preparedCart, $orderId);
                $ordersHelper->finalCreateOrderProcedure($order);
                
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
            if ($amounts = $request->post('amounts')) {
                foreach ($amounts as $variantId=>$amount) {
                    $cartCore->updateItem($variantId, $amount);
                }

                $couponCode = $cartRequest->postCoupon();
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

            // Данные пользователя по умолчанию
            $this->design->assign('request_data', $cartHelper->getDefaultCartData($this->user));
        }

        // Способы доставки и оплаты
        $paymentMethods = $paymentsHelper->getCartPaymentsList($cart);
        $deliveries     = $deliveriesHelper->getCartDeliveriesList($cart, $paymentMethods);
        $activeDelivery = $deliveriesHelper->getActiveDeliveryMethod($deliveries);
        $activePayment  = $paymentsHelper->getActivePaymentMethod($paymentMethods, $activeDelivery);

        $this->design->assign('all_currencies', $currenciesEntity->mappedBy('id')->find());
        $this->design->assign('deliveries', $deliveries);
        $this->design->assign('payment_methods', $paymentMethods);
        $this->design->assign('active_delivery', $activeDelivery);
        $this->design->assign('active_payment', $activePayment);
        
        if ($couponsEntity->count(['valid'=>1])>0) {
            $this->design->assign('coupon_request', true);
        }
                
        $this->design->assign('cart', $cartCore->get());
        $this->response->setContent('cart.tpl');
    }
    
    public function cartAjax(
        CouponsEntity    $couponsEntity,
        CurrenciesEntity $currenciesEntity,
        Request          $request,
        Cart             $cartCore,
        Money            $moneyCore,
        DeliveriesHelper $deliveriesHelper,
        PaymentsHelper   $paymentsHelper,
        TemplateConfig   $templateConfig,
        LoggerInterface  $logger
    ) {
        $action     = $request->get('action');
        $variantId  = $request->get('variant_id', 'integer');
        $amount     = $request->get('amount', 'integer');
        
        switch ($action) {
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

        $this->design->assign('all_currencies', $currenciesEntity->mappedBy('id')->find());

        /*Рабтаем с товарами в корзине*/
        if (count($cart->purchases) > 0) {
            if (isset($_GET['coupon_code'])) {
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
            }

            if ($couponsEntity->count(['valid'=>1])>0) {
                $this->design->assign('coupon_request', true);
            }

            $cart = $cartCore->get();
            $this->design->assign('cart', $cart);
            
            $result = ['result' => 1];

            $paymentMethods = $paymentsHelper->getCartPaymentsList($cart);
            $deliveries = $deliveriesHelper->getCartDeliveriesList($cart, $paymentMethods);

            $result['deliveries_data'] = $deliveries;
            $result['payment_methods_data'] = $paymentMethods;
            
            if (is_file('design/' . $templateConfig->getTheme() . '/html/cart_coupon.tpl')) {
                $result['cart_coupon'] = $this->design->fetch('cart_coupon.tpl');
            } else {
                $logger->error('File "design/' . $templateConfig->getTheme() . '/html/cart_coupon.tpl" not found');
            }
            
            if (is_file('design/' . $templateConfig->getTheme() . '/html/cart_purchases.tpl')) {
                $result['cart_purchases'] = $this->design->fetch('cart_purchases.tpl');
            } else {
                $logger->error('File "design/' . $templateConfig->getTheme() . '/html/cart_purchases.tpl" not found');
            }
            
            $result['cart_deliveries'] = 'DEPRECATED DATA';
            $result['currency_sign']   = $this->currency->sign;
            $result['total_price']     = $moneyCore->convert($cart->total_price, $this->currency->id);
            $result['total_products']  = $cart->total_products;
        } else {
            $result = ['result' => 0];
            $result['content']       = $this->design->fetch('cart.tpl');
        }

        if (is_file('design/' . $templateConfig->getTheme() . '/html/cart_informer.tpl')) {
            $result['cart_informer'] = $this->design->fetch('cart_informer.tpl');
        } else {
            $logger->error('File "design/' . $templateConfig->getTheme() . '/html/cart_informer.tpl" not found');
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
