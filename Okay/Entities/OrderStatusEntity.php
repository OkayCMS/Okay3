<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class OrderStatusEntity extends Entity
{

    protected static $fields = [
        'id',
        'is_close',
        'color',
        'position',
        'status_1c',
    ];

    protected static $langFields = [
        'name',
    ];

    protected static $defaultOrderFields = [
        'position ASC',
    ];

    protected static $table = '__orders_status';
    protected static $langObject = 'order_status';
    protected static $langTable = 'orders_status';
    protected static $tableAlias = 'os';
    
    public function delete($ids)
    {
        $ids = (array)$ids;
        if (!empty($ids)) {
            
            /** @var OrdersEntity $ordersEntity */
            $ordersEntity = $this->entity->get(OrdersEntity::class);
            $checkCnt = $ordersEntity->count(['status_id'=>$ids]);

            if ($checkCnt == 0) {
                return parent::delete($ids);
            }
        }
        return false;
    }

}
