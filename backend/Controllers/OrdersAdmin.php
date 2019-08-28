<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\OrderLabelsEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\PurchasesEntity;

class OrdersAdmin extends IndexAdmin
{
    
    public function fetch(
        OrdersEntity $ordersEntity,
        PurchasesEntity $purchasesEntity,
        OrderLabelsEntity $orderLabelsEntity,
        OrderStatusEntity $orderStatusEntity
    ) {
        $filter = [];
        $ordersStatuses = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 40;
        
        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        // Фильтр по метке
        $label = $orderLabelsEntity->get($this->request->get('label'));
        if (!empty($label)) {
            $filter['label'] = $label->id;
            $this->design->assign('label', $label);
        }

        $allStatuses = $orderStatusEntity->find();
        if ($allStatuses) {
            $ordersStatuses = [];
            foreach ($allStatuses as $statusItem) {
                $ordersStatuses[$statusItem->id] = $statusItem;
            }
        }

        // Обработка действий

        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        /*Удалить заказ*/
                        $ordersEntity->delete($ids);
                        break;
                    }
                    case 'change_status': {
                        /*Смена статуса заказа*/
                        if($this->request->post("change_status_id")) {
                            $newStatus = $orderStatusEntity->find(["status"=>$this->request->post("change_status_id","integer")]);
                            $errorOrders = [];
                            foreach($ids as $id) {
                                if($newStatus[0]->is_close == 1){
                                    if (!$ordersEntity->close(intval($id))) {
                                        $errorOrders[] = $id;
                                        $this->design->assign('error_orders', $errorOrders);
                                        $this->design->assign('message_error', 'error_closing');
                                    } else {
                                        $ordersEntity->update($id, ['status_id'=>$this->request->post("change_status_id","integer")]);
                                    }
                                } else {
                                    if ($ordersEntity->open(intval($id))) {
                                        $ordersEntity->update($id, ['status_id'=>$this->request->post("change_status_id","integer")]);
                                    }
                                }

                            }
                        }
                        break;
                    }
                    case 'set_label': {
                        /*Добавить метку к заказу*/
                        if($this->request->post("change_label_id")) {
                            foreach($ids as $id) {
                                $orderLabelsEntity->addOrderLabels($id, [$this->request->post("change_label_id","integer")]);
                            }
                        }
                        break;
                    }
                    case 'unset_label': {
                        /*Удалить метку из заказа*/
                        if($this->request->post("change_label_id")) {
                            foreach($ids as $id) {
                                $orderLabelsEntity->deleteOrderLabels($id, [$this->request->post("change_label_id","integer")]);
                            }
                        }
                        break;
                    }
                }
            }
        }
        
        if (empty($keyword)) {
            if ($this->request->get('status')) {
                $filter['status_id'] = $statusId = $this->request->get('status', 'integer');
            } else {
                $statusId = 0;
            }

            $this->design->assign('status', $statusId);
        }

        //Поиск до дате заказа
        $fromDate = $this->request->get('from_date');
        $toDate = $this->request->get('to_date');
        if (!empty($fromDate) || !empty($toDate)){
            $filter['from_date'] = $fromDate;
            $filter['to_date'] = $toDate;
            $this->design->assign('from_date', $fromDate);
            $this->design->assign('to_date', $toDate);
        }

        $ordersCount = $ordersEntity->count($filter);
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $ordersCount;
        }

        // Отображение
        $orders = [];
        foreach($ordersEntity->find($filter) as $o) {
            $orders[$o->id] = $o;
            $orders[$o->id]->purchases = $purchasesEntity->find(['order_id'=>$o->id]);
        }
        // Метки заказов
        if (!empty($orders)) {
            $ordersLabels = $orderLabelsEntity->getOrdersLabels(array_keys($orders));
            if ($ordersLabels) {
                foreach ($ordersLabels as $ordersLabel) {
                    $orders[$ordersLabel->order_id]->labels[] = $ordersLabel;
                    $orders[$ordersLabel->order_id]->labels_ids[] = $ordersLabel->id;
                }
            }
        }

        $this->design->assign('pages_count', ceil($ordersCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        
        $this->design->assign('orders_count', $ordersCount);
        
        $this->design->assign('orders', $orders);
        $this->design->assign('all_status', $allStatuses);
        $this->design->assign('orders_status', $ordersStatuses);
        
        // Метки заказов
        $labels = $orderLabelsEntity->find();
        $this->design->assign('labels', $labels);

        $this->response->setContent($this->design->fetch('orders.tpl'));
    }
    
}
