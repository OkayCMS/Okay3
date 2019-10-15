<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\OrderLabelsEntity;
use Okay\Entities\OrderStatusEntity;

class OrderSettingsAdmin extends IndexAdmin
{

    public function fetch(OrderStatusEntity $orderStatusEntity, OrderLabelsEntity $orderLabelsEntity)
    {

        /*Статусы заказов*/
        if ($this->request->post('status')) {
            // Сортировка
            if($this->request->post('positions')){
                $positions = $this->request->post('positions');
                $ids = array_keys($positions);
                sort($positions);
                foreach ($positions as $i=>$position) {
                    $orderStatusEntity->update($ids[$i], array('position'=>$position));
                }
            }

            /*Создание статуса*/
            if ($this->request->post('new_name')){
                $new_status = $this->request->post('new_name');
                $new_params = $this->request->post('new_is_close');
                $new_colors = $this->request->post('new_color');

                foreach ($new_status as $id=>$value) {
                    if (!empty($value)) {
                        $new_stat = new \stdClass();
                        $new_stat->name = $value;
                        $new_stat->is_close = $new_params[$id];
                        $new_stat->color = $new_colors[$id];
                        $orderStatusEntity->add($new_stat);
                    }
                }
            }

            /*Обновление статуса*/
            if($this->request->post('name')) {
                $current_status = $this->request->post('name');
                $is_close = $this->request->post('is_close');
                $colors_status = $this->request->post('color');
                foreach ($current_status as $id=>$value) {
                    $update_status = new \stdClass();
                    $update_status->name = $value;
                    $update_status->is_close = $is_close[$id];
                    $update_status->color = $colors_status[$id];
                    $orderStatusEntity->update($id,$update_status);
                }
            }

            $idsToDelete = $this->request->post('check');
            if (!empty($idsToDelete) && $orderStatusEntity->count() > 1) {
                $result = $orderStatusEntity->delete($idsToDelete);
                $this->design->assign("error_status", $result);
            }
        }
        // Отображение
        $ordersStatuses = $orderStatusEntity->find();
        $this->design->assign('orders_statuses', $ordersStatuses);

        /*Метки заказов*/
        if ($this->request->post('labels')) {
            // Сортировка
            if ($this->request->post('positions')){
                $positions = $this->request->post('positions');
                $ids = array_keys($positions);
                sort($positions);
                foreach ($positions as $i=>$position) {
                    $orderLabelsEntity->update($ids[$i], ['position'=>$position]);
                }
            }

            /*Добавление метки*/
            if ($this->request->post('new_name')){
                $new_labels = $this->request->post('new_name');
                $new_colors = $this->request->post('new_color');
                foreach ($new_labels as $id=>$value) {
                    if (!empty($value)) {
                        $new_label = new \stdClass();
                        $new_label->name = $value;
                        $new_label->color = $new_colors[$id];
                        $orderLabelsEntity->add($new_label);
                    }
                }
            }

            /*Обновление метки*/
            if ($this->request->post('name')) {
                $current_labels = $this->request->post('name');
                $colors = $this->request->post('color');
                $ids = $this->request->post('id');
                foreach ($current_labels as $id=>$value) {
                    $update_label = new \stdClass();
                    $update_label->name = $value;
                    $update_label->color = $colors[$id];
                    $orderLabelsEntity->update($ids[$id],$update_label);
                }
            }

            // Действия с выбранными
            $idToDelete = $this->request->post('check');
            if (!empty($ids)) {
                $orderLabelsEntity->delete($idToDelete);
            }
        }
        // Отображение
        $labels = $orderLabelsEntity->find();
        $this->design->assign('labels', $labels);

        $this->response->setContent($this->design->fetch('order_settings.tpl'));
    }

}

