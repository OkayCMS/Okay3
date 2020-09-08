<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Phone;
use Okay\Core\UserReferer\UserReferer;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class OrdersHelper
{

    private $entityFactory;
    private $productsHelper;
    private $moneyHelper;

    public function __construct(EntityFactory $entityFactory, ProductsHelper $productsHelper, MoneyHelper $moneyHelper)
    {
        $this->entityFactory = $entityFactory;
        $this->productsHelper = $productsHelper;
        $this->moneyHelper = $moneyHelper;
    }

    /**
     * @param $order
     * Метод вызывается после оформления заказа, перед отправкой пользователя на страницу заказа и очисткой корзины.
     * Нужен чтобы модули могли расширять эту процедуру 
     */
    public function finalCreateOrderProcedure($order)
    {
        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
    
    public function getOrderPaymentMethodsList($order)
    {
        /** @var PaymentsEntity $paymentsEntity */
        $paymentsEntity = $this->entityFactory->get(PaymentsEntity::class);
        
        $paymentMethods = $paymentsEntity->find([
            'delivery_id' => $order->delivery_id,
            'enabled' => 1,
        ]);

        return ExtenderFacade::execute(__METHOD__, $paymentMethods, func_get_args());
    }
    
    public function getOrderPurchasesList($orderId)
    {
        /** @var PurchasesEntity $purchasesEntity */
        $purchasesEntity = $this->entityFactory->get(PurchasesEntity::class);
        
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        
        /** @var VariantsEntity $variantsEntity */
        $variantsEntity = $this->entityFactory->get(VariantsEntity::class);
        
        $purchases = $purchasesEntity->find(['order_id'=>intval($orderId)]);
        if (!$purchases) {
            return ExtenderFacade::execute(__METHOD__, false, func_get_args());
        }

        $productsIds = [];
        $variantsIds = [];
        foreach ($purchases as $purchase) {
            $productsIds[] = $purchase->product_id;
            $variantsIds[] = $purchase->variant_id;
        }

        $products = $productsEntity->mappedBy('id')->find(['id'=>$productsIds, 'limit' => count($productsIds)]);
        $products = $this->productsHelper->attachVariants($products, ['id'=>$variantsIds]);
        $products = $this->productsHelper->attachMainImages($products);
        $variants = $variantsEntity->mappedBy('id')->find(['id'=>$variantsIds]);
        $variants = $this->moneyHelper->convertVariantsPriceToMainCurrency($variants);
        
        foreach ($purchases as $purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }
            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }
        }

        return ExtenderFacade::execute(__METHOD__, $purchases, func_get_args());
    }

    public function prepareAdd($order)
    {
        if (!empty($order->phone)) {
            $order->phone = Phone::toSave($order->phone);
        }
        
        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function add($order)
    {
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);

        // Добавим источник, с которого пришел пользователь
        if ($referer = UserReferer::getUserReferer()) {
            $order->referer_channel = $referer['medium'];
            $order->referer_source = $referer['source'];
        }
        
        $result = $ordersEntity->add($order);
        return ExtenderFacade::execute(__METHOD__, $result, func_get_args());
    }

    public function attachDiscountIfExists($order, $cart)
    {
        $order->discount = $cart->discount;
        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function attachCouponIfExists($order, $cart)
    {
        $order->discount = $cart->discount;

        if ($cart->coupon) {
            $order->coupon_discount = $cart->coupon_discount;
            $order->coupon_code     = $cart->coupon->code;
        }

        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function attachUserIfLogin($order, $user)
    {
        if (!empty($user->id)) {
            $order->user_id = $user->id;
        }

        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }
}