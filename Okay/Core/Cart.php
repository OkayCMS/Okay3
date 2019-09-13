<?php


namespace Okay\Core;


use Okay\Entities\VariantsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\CouponsEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\UsersEntity;
use Okay\Logic\ProductsLogic;
use Okay\Logic\MoneyLogic;

class Cart
{
    private $productsEntity;
    private $variantsEntity;
    private $couponsEntity;
    private $imagesEntity;
    private $usersEntity;
    private $settings;
    private $productsLogic;
    private $moneyLogic;

    public function __construct(
        EntityFactory $entityFactory,
        Settings      $settings,
        ProductsLogic $productsLogic,
        MoneyLogic    $moneyLogic
    ){
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->variantsEntity = $entityFactory->get(VariantsEntity::class);
        $this->couponsEntity  = $entityFactory->get(CouponsEntity::class);
        $this->imagesEntity   = $entityFactory->get(ImagesEntity::class);
        $this->usersEntity    = $entityFactory->get(UsersEntity::class);
        $this->settings       = $settings;
        $this->productsLogic  = $productsLogic;
        $this->moneyLogic     = $moneyLogic;
    }

    public function get() {
        $cart = new \stdClass();
        $cart->purchases       = [];
        $cart->total_price     = 0;
        $cart->total_products  = 0;
        $cart->coupon          = null;
        $cart->discount        = 0;
        $cart->coupon_discount = 0;
        
        if (empty($_SESSION['shopping_cart'])) {
            return $cart;
        }

        $variants = $this->variantsEntity->find(['id' => $this->getVariantsIdsByCart($_SESSION['shopping_cart'])]);
        if (empty($variants)) {
            return $cart;
        }
        
        $variants = $this->moneyLogic->convertVariantsPriceToMainCurrency($variants);
        
        $products = $this->getProductsByVariants($variants);
        $products = $this->productsLogic->attachImages($products);

        // TODO: собирать целостно в одном методе
        $items = $this->buildItemsByVariants($variants);
        foreach($items as $variantId=>$item) {
            $purchase = null;
            if(!empty($products[$item->variant->product_id])) {
                $purchase = new \stdClass();
                $purchase->product = $products[$item->variant->product_id];
                $purchase->variant = $item->variant;
                $purchase->amount = $item->amount;
                
                $cart->purchases[] = $purchase;
                $cart->total_price += $item->variant->price*$item->amount;
                $cart->total_products += $item->amount;
            }
        }
        
        //TODO: В отдельный модуль
        if($this->couponCodeExists()) {
            $cart->coupon = $this->couponsEntity->get($_SESSION['coupon_code']);
            if($cart->coupon && $cart->coupon->valid && $cart->total_price >= $cart->coupon->min_order_price) {
                if($cart->coupon->type == 'absolute') {
                    // Абсолютная скидка не более суммы заказа
                    $cart->coupon_discount        = $cart->total_price>$cart->coupon->value?$cart->coupon->value:$cart->total_price;
                    $cart->total_price            = max(0, $cart->total_price-$cart->coupon->value);
                    $cart->coupon->coupon_percent = round(100-($cart->total_price*100)/($cart->total_price+$cart->coupon->value),2);
                } else {
                    $cart->coupon->coupon_percent = $cart->coupon->value;
                    $cart->coupon_discount        = $cart->total_price * ($cart->coupon->value)/100;
                    $cart->total_price            = $cart->total_price-$cart->coupon_discount;
                }
            } else {
                unset($_SESSION['coupon_code']);
            }
        }

        //TODO: В отдельный модуль
        $cart->discount = 0;
        if(isset($_SESSION['user_id']) && ($user = $this->usersEntity->get(intval($_SESSION['user_id'])))) {
            $cart->discount = $user->discount;
        }

        $cart->total_price *= (100 - $cart->discount)/100;
        return $cart;
    }

    public function addItem($variantId, $amount = 1)
    {
        $variant = $this->variantsEntity->get(intval($variantId));
        if (!empty($variant) && ($variant->stock>0 || $this->settings->is_preorder)) {
            $amount = max(1, $amount);
            if (isset($_SESSION['shopping_cart'][$variantId])) {
                $amount = max(1, $amount + $_SESSION['shopping_cart'][$variantId]);
            }

            $amount = min($amount, ($variant->stock ? $variant->stock : min($this->settings->max_order_amount, $amount)));
            $_SESSION['shopping_cart'][$variantId] = intval($amount);
        }
    }

    public function updateItem($variantId, $amount = 1)
    {
        $variant = $this->variantsEntity->get(intval($variantId));
        if (!empty($variant) && ($variant->stock>0 || $this->settings->is_preorder)) {
            $amount = max(1, $amount);
            $amount = min($amount, ($variant->stock ? $variant->stock : min($this->settings->max_order_amount, $amount)));
            $_SESSION['shopping_cart'][$variantId] = intval($amount);
        }
    }

    /*Удаление товара из корзины*/
    public function deleteItem($variantId)
    {
        unset($_SESSION['shopping_cart'][$variantId]);
    }

    /*Очистка корзины*/
    public function clear()
    {
        unset($_SESSION['shopping_cart']);
        unset($_SESSION['coupon_code']);
    }

    /*Применение купона в корзине*/
    public function applyCoupon($coupon_code)
    {
        $coupon = $this->couponsEntity->get((string)$coupon_code);
        if($coupon && $coupon->valid) {
            $_SESSION['coupon_code'] = $coupon->code;
        } else {
            unset($_SESSION['coupon_code']);
        }
    }

    private function getVariantsIdsByCart($sessionCart)
    {
        return array_keys($sessionCart);
    }

    private function couponCodeExists()
    {
        if(empty($_SESSION['coupon_code'])) {
            return false;
        }

        return true;
    }

    private function getProductsByVariants($variants)
    {
        $productsIds = $this->getProductsIdsByVariants($variants);
        $products = $this->productsEntity->mappedBy('id')->find([
            'id'    => $productsIds, 
            'limit' => count($productsIds)
        ]);

        return $products;
    }

    private function buildItemsByVariants($variants)
    {
        $items = [];
        if (empty($variants)) {
            return $items;
        }

        foreach($variants as $variant) {
            $item = new \stdClass();
            $item->variant = $variant;
            $item->amount  = $_SESSION['shopping_cart'][$variant->id];

            $items[$variant->id] = $item;
        }

        return $items;
    }

    private function getProductsIdsByVariants($variants)
    {
        $productsIds = [];
        foreach($variants as $variant) {
            $productsIds[] = $variant->product_id;
        }

        return $productsIds;
    }
}
