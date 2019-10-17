<?php


namespace Okay\Admin\Helpers;


use Okay\Core\EntityFactory;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\UserGroupsEntity;
use Okay\Entities\UsersEntity;
use Okay\Entities\VariantsEntity;
use Okay\Helpers\MoneyHelper;

class BackendOrdersHelper
{
    /** @var OrdersEntity */
    private $ordersEntity;
    
    /** @var VariantsEntity */
    private $variantsEntity;
    
    /** @var PurchasesEntity */
    private $purchasesEntity;
    
    /** @var OrderStatusEntity */
    private $orderStatusEntity;
    
    /** @var ProductsEntity */
    private $productsEntity;
    
    /** @var ImagesEntity */
    private $imagesEntity;
    
    /** @var DeliveriesEntity */
    private $deliveriesEntity;
    
    /** @var PaymentsEntity */
    private $paymentsEntity;
    
    /** @var UsersEntity */
    private $usersEntity;
    
    /** @var UserGroupsEntity */
    private $userGroupsEntity;
    
    /** @var MoneyHelper */
    private $moneyHelper;
    
    public function __construct(EntityFactory $entityFactory, MoneyHelper $moneyHelper)
    {
        $this->ordersEntity = $entityFactory->get(OrdersEntity::class);
        $this->variantsEntity = $entityFactory->get(VariantsEntity::class);
        $this->purchasesEntity = $entityFactory->get(PurchasesEntity::class);
        $this->orderStatusEntity = $entityFactory->get(OrderStatusEntity::class);
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->imagesEntity = $entityFactory->get(ImagesEntity::class);
        $this->deliveriesEntity = $entityFactory->get(DeliveriesEntity::class);
        $this->paymentsEntity = $entityFactory->get(PaymentsEntity::class);
        $this->usersEntity = $entityFactory->get(UsersEntity::class);
        $this->userGroupsEntity = $entityFactory->get(UserGroupsEntity::class);
        $this->moneyHelper = $moneyHelper;
    }

    /**
     * @var $order
     * Метод заглушка, чтобы модули могли зацепиться
     */
    public function executeCustomPost($order)
    {
        ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }
    
    public function prepareAdd($order)
    {
        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function add($order)
    {
        $insertId = $this->ordersEntity->add($order);
        return ExtenderFacade::execute(__METHOD__, $insertId, func_get_args());
    }

    public function prepareUpdate($order)
    {
        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function update($order)
    {
        $this->ordersEntity->update($order->id, $order);
        ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function updatePurchase($purchase)
    {
        $this->purchasesEntity->update($purchase->id, $purchase);
        ExtenderFacade::execute(__METHOD__, $purchase, func_get_args());
    }

    public function prepareUpdatePurchase($order, $purchase)
    {
        $variant = $this->variantsEntity->get($purchase->variant_id);
        if (!empty($variant)) {
            $purchase->variant_name = $variant->name;
            $purchase->sku = $variant->sku;
        }
        
        return ExtenderFacade::execute(__METHOD__, $purchase, func_get_args());
    }

    public function addPurchase($purchase)
    {
        $purchaseId = $this->purchasesEntity->add($purchase);
        return ExtenderFacade::execute(__METHOD__, $purchaseId, func_get_args());
    }

    public function prepareAddPurchase($order, $purchase)
    {
        $purchase->id = null;
        $purchase->order_id = $order->id;
        return ExtenderFacade::execute(__METHOD__, $purchase, func_get_args());
    }
    
    public function deletePurchases($order, array $postedPurchasesIds)
    {
        foreach ($this->purchasesEntity->find(['order_id' => $order->id]) as $p) {
            if (!in_array($p->id, $postedPurchasesIds)) {
                $this->purchasesEntity->delete($p->id);
            }
        }
        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
    
    public function updateOrderStatus($order, $newStatusId)
    {
        $newStatusInfo = $this->orderStatusEntity->get((int)$newStatusId);

        $result = true;
        if ($newStatusInfo->is_close == 1) {
            if (!$this->ordersEntity->close(intval($order->id))) {
                $result = false;
            } else {
                $this->ordersEntity->update($order->id, ['status_id' => $newStatusId]);
            }
        } else {
            if ($this->ordersEntity->open(intval($order->id))) {
                $this->ordersEntity->update($order->id, ['status_id' => $newStatusId]);
            }
        }
        return ExtenderFacade::execute(__METHOD__, $result, func_get_args());
    }
    
    public function findOrder($orderId)
    {
        $order = $this->ordersEntity->get((int)$orderId);
        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }
    
    public function findOrderDelivery($order)
    {
        $delivery = null;
        if (!empty($order->delivery_id)) {
            $delivery = $this->deliveriesEntity->get($order->delivery_id);
        }
        return ExtenderFacade::execute(__METHOD__, $delivery, func_get_args());
    }
    
    public function findOrderPayment($order)
    {
        $payment = null;
        if (!empty($order->payment_method_id)) {
            $payment = $this->paymentsEntity->get($order->payment_method_id);
        }
        return ExtenderFacade::execute(__METHOD__, $payment, func_get_args());
    }
    
    public function findOrderUser($order)
    {
        $user = null;
        if (!empty($order->user_id)) {
            $user = $this->usersEntity->get((int)$order->user_id);
            $user->group = $this->userGroupsEntity->get((int)$user->group_id);
        }
        return ExtenderFacade::execute(__METHOD__, $user, func_get_args());
    }
    
    public function findNeighborsOrders($order, $labelId = null, $statusId = null)
    {
        $neighborsOrders = null;
        if (!empty($order->id)) {
            $neighborsFilter['id'] = $order->id;
            if ($statusId !== null) {
                $neighborsFilter['status_id'] = $statusId;
            }
            if ($labelId !== null) {
                $neighborsFilter['label_id'] = $labelId;
            }
            $neighborsOrders = $this->ordersEntity->getNeighborsOrders($neighborsFilter);
        }
        
        return ExtenderFacade::execute(__METHOD__, $neighborsOrders, func_get_args());
    }
    
    public function findOrderPurchases($order)
    {
        if ($purchases = $this->purchasesEntity->find(['order_id'=>$order->id])) {
            // Покупки
            $productsIds = [];
            $variantsIds = [];
            $imagesIds = [];
            foreach ($purchases as $purchase) {
                $productsIds[] = $purchase->product_id;
                $variantsIds[] = $purchase->variant_id;
            }

            $products = [];
            foreach ($this->productsEntity->find(['id'=>$productsIds, 'limit' => count($productsIds)]) as $p) {
                $products[$p->id] = $p;
                $imagesIds[] = $p->main_image_id;
            }

            if (!empty($imagesIds)) {
                $images = $this->imagesEntity->find(['id'=>$imagesIds]);
                foreach ($images as $image) {
                    if (isset($products[$image->product_id])) {
                        $products[$image->product_id]->image = $image;
                    }
                }
            }

            $variants = $this->variantsEntity->mappedBy('id')->find(['product_id'=>$productsIds]);
            $variants = $this->moneyHelper->convertVariantsPriceToMainCurrency($variants);

            foreach ($variants as $variant) {
                if (!empty($products[$variant->product_id])) {
                    $products[$variant->product_id]->variants[] = $variant;
                }
            }

            foreach ($purchases as $purchase) {
                if(!empty($products[$purchase->product_id])) {
                    $purchase->product = $products[$purchase->product_id];
                }
                if (!empty($variants[$purchase->variant_id])) {
                    $purchase->variant = $variants[$purchase->variant_id];
                }
            }
        }
        
        return ExtenderFacade::execute(__METHOD__, $purchases, func_get_args());
    }
    
}