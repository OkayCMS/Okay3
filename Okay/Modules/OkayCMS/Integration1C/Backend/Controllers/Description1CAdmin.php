<?php


namespace Okay\Modules\OkayCMS\Integration1C\Backend\Controllers;


use Okay\Core\EntityFactory;
use Okay\Entities\OrderStatusEntity;
use Okay\Admin\Controllers\IndexAdmin;

class Description1CAdmin extends IndexAdmin
{
    public function fetch(EntityFactory $entityFactory)
    {
        $orderStatusEntity = $entityFactory->get(OrderStatusEntity::class);

        if ($this->request->method('post') && $this->request->post('status_1c')) {
            $statuses = $this->request->post('status_1c');
            foreach($statuses as $id => $status) {
                $orderStatusEntity->update($id, ['status_1c' => $status]);
            }
        }

        $ordersStatuses = $orderStatusEntity->find();
        $this->design->assign('orders_statuses', $ordersStatuses);

        $this->response->setContent($this->design->fetch('description.tpl'));
    }
}