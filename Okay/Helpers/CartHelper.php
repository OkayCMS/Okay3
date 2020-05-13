<?php


namespace Okay\Helpers;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Money;
use Okay\Core\Phone;
use Okay\Core\TemplateConfig;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PurchasesEntity;
use Psr\Log\LoggerInterface;

class CartHelper
{
    /** @var EntityFactory */
    private $entityFactory;
    
    /** @var Money */
    private $moneyCore;
    
    /** @var TemplateConfig */
    private $templateConfig;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var Design */
    private $design;

    public function __construct(
        EntityFactory $entityFactory,
        Money            $moneyCore,
        TemplateConfig   $templateConfig,
        LoggerInterface  $logger,
        Design  $design
    ) {
        $this->entityFactory = $entityFactory;
        $this->moneyCore = $moneyCore;
        $this->templateConfig = $templateConfig;
        $this->logger = $logger;
        $this->design = $design;
    }

    public function getDefaultCartData($user)
    {
        $defaultData = [];
        if (!empty($user->id)) {

            /** @var OrdersEntity $ordersEntity */
            $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
            
            $lastOrder = $ordersEntity->findOne(['user_id'=>$user->id]);
            if ($lastOrder) {
                $defaultData['name'] = $lastOrder->name;
                $defaultData['email'] = $lastOrder->email;
                $defaultData['phone'] = Phone::format($lastOrder->phone);
                $defaultData['address'] = $lastOrder->address;
            } else {
                $defaultData['name'] = $user->name;
                $defaultData['email'] = $user->email;
                $defaultData['phone'] = Phone::format($user->phone);
                $defaultData['address'] = $user->address;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $defaultData, func_get_args());
    }

    public function getAjaxCartResult($cart, $currency, $paymentMethods, $deliveries, $action, $variantId, $amount = 0)
    {
        $this->design->assign('cart', $cart);
        
        if ($cart->isEmpty === false) {
            $result = ['result' => 1];
            
            $result['deliveries_data'] = $deliveries;
            $result['payment_methods_data'] = $paymentMethods;

            if (is_file('design/' . $this->templateConfig->getTheme() . '/html/cart_coupon.tpl')) {
                $result['cart_coupon'] = $this->design->fetch('cart_coupon.tpl');
            } else {
                $this->logger->error('File "design/' . $this->templateConfig->getTheme() . '/html/cart_coupon.tpl" not found');
            }

            if (is_file('design/' . $this->templateConfig->getTheme() . '/html/cart_purchases.tpl')) {
                $result['cart_purchases'] = $this->design->fetch('cart_purchases.tpl');
            } else {
                $this->logger->error('File "design/' . $this->templateConfig->getTheme() . '/html/cart_purchases.tpl" not found');
            }

            $result['cart_deliveries'] = 'DEPRECATED DATA';
            $result['currency_sign']   = $currency->sign;
            $result['total_price']     = $this->moneyCore->convert($cart->total_price, $currency->id);
            $result['total_products']  = $cart->total_products;
        } else {
            $result = ['result' => 0];
            $result['content']       = $this->design->fetch('cart.tpl');
        }

        if (is_file('design/' . $this->templateConfig->getTheme() . '/html/cart_informer.tpl')) {
            $result['cart_informer'] = $this->design->fetch('cart_informer.tpl');
        } else {
            $this->logger->error('File "design/' . $this->templateConfig->getTheme() . '/html/cart_informer.tpl" not found');
        }
        
        return ExtenderFacade::execute(__METHOD__, $result, func_get_args());
    }

    public function cartToOrder($cart, $orderId)
    {
        $purchasesEntity = $this->entityFactory->get(PurchasesEntity::class);

        foreach($cart->purchases as $purchase) {
            $purchasesEntity->add($purchase);
        }

        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
        $ordersEntity->update($orderId, [
            'total_price' => $cart->total_price,
        ]);

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function prepareCart($cart, $orderId)
    {
        $preparedCart = clone $cart;

        foreach($preparedCart->purchases as $purchase) {
            $purchase->order_id = $orderId;
            unset($purchase->variant);
            unset($purchase->product);
            unset($purchase->meta);
        }

        return ExtenderFacade::execute(__METHOD__, $preparedCart, func_get_args());
    }
}