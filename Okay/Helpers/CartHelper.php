<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PurchasesEntity;

class CartHelper
{
    /**
     * @var EntityFactory
     */
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
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
                $defaultData['phone'] = $lastOrder->phone;
                $defaultData['address'] = $lastOrder->address;
            } else {
                $defaultData['name'] = $user->name;
                $defaultData['email'] = $user->email;
                $defaultData['phone'] = $user->phone;
                $defaultData['address'] = $user->address;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $defaultData, func_get_args());
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