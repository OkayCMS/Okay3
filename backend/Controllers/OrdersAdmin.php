<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Entities\OrderLabelsEntity;

class OrdersAdmin extends IndexAdmin
{
    
    public function fetch(
        OrderLabelsEntity   $orderLabelsEntity,
        BackendOrdersHelper $backendOrdersHelper
    ) {
        //> Обработка действий
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        $backendOrdersHelper->delete($ids);
                        break;
                    }
                    case 'change_status': {
                        $backendOrdersHelper->changeStatus($ids);
                        break;
                    }
                    case 'set_label': {
                        $backendOrdersHelper->setLabel($ids);
                        break;
                    }
                    case 'unset_label': {
                        $backendOrdersHelper->unsetLabel($ids);
                        break;
                    }
                }
            }
        }

        $filter      = $backendOrdersHelper->buildFilter();
        $orders      = $backendOrdersHelper->findOrders($filter);
        $orders      = $backendOrdersHelper->attachLabels($orders);
        $allStatuses = $backendOrdersHelper->findStatuses();
        $ordersCount = $backendOrdersHelper->count($filter);

        if (isset($filter['keyword'])) {
            $this->design->assign('keyword', $filter['keyword']);
        }

        if (isset($filter['status'])) {
            $this->design->assign('status', $filter['status']);
        }

        if (isset($filter['from_date'])) {
            $this->design->assign('from_date', $filter['from_date']);
        }

        if (isset($filter['to_date'])) {
            $this->design->assign('to_date', $filter['to_date']);
        }

        $this->design->assign('pages_count',   ceil($ordersCount/$filter['limit']));
        $this->design->assign('current_page',  $filter['page']);
        $this->design->assign('orders_count',  $ordersCount);
        $this->design->assign('orders',        $orders);
        $this->design->assign('all_status',    $allStatuses);
        $this->design->assign('orders_status', $allStatuses);
        
        // Метки заказов
        $labels = $orderLabelsEntity->find();
        $this->design->assign('labels', $labels);

        $this->response->setContent($this->design->fetch('orders.tpl'));
    }
    
}
