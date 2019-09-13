<?php


namespace Okay\Logic;


use Okay\Core\EntityFactory;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;

class OrdersLogic
{

    private $entityFactory;
    private $productsLogic;
    private $moneyLogic;

    public function __construct(EntityFactory $entityFactory, ProductsLogic $productsLogic, MoneyLogic $moneyLogic)
    {
        $this->entityFactory = $entityFactory;
        $this->productsLogic = $productsLogic;
        $this->moneyLogic = $moneyLogic;
    }
    
    public function getOrderPurchases($orderId)
    {
        /** @var PurchasesEntity $purchasesEntity */
        $purchasesEntity = $this->entityFactory->get(PurchasesEntity::class);
        
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        
        /** @var VariantsEntity $variantsEntity */
        $variantsEntity = $this->entityFactory->get(VariantsEntity::class);
        
        $purchases = $purchasesEntity->find(['order_id'=>intval($orderId)]);
        if (!$purchases) {
            return false;
        }

        $productsIds = [];
        $variantsIds = [];
        foreach ($purchases as $purchase) {
            $productsIds[] = $purchase->product_id;
            $variantsIds[] = $purchase->variant_id;
        }

        $products = $productsEntity->mappedBy('id')->find(['id'=>$productsIds, 'limit' => count($productsIds)]);
        $products = $this->productsLogic->attachVariants($products, ['id'=>$variantsIds]);
        $products = $this->productsLogic->attachMainImages($products);
        $variants = $variantsEntity->mappedBy('id')->find(['id'=>$variantsIds]);
        $variants = $this->moneyLogic->convertVariantsPriceToMainCurrency($variants);
        
        foreach ($purchases as $purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }
            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }
        }
        
        return $purchases;
    }
    
}