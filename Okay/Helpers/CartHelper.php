<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\OrdersEntity;

class CartHelper
{

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
}