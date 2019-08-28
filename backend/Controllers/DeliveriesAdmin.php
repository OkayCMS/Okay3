<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\DeliveriesEntity;

class DeliveriesAdmin extends IndexAdmin
{
    
    public function fetch(DeliveriesEntity $deliveriesEntity)
    {
        // Обработка действий
        if($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            
            if(is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        /*Выключить способ доставки*/
                        $deliveriesEntity->update($ids, ['enabled'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включить сопсоб доставки*/
                        $deliveriesEntity->update($ids, ['enabled'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удалить способ доставки*/
                        $deliveriesEntity->delete($ids);
                        break;
                    }
                }
            }
            
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $deliveriesEntity->update($ids[$i], array('position'=>$position));
            }
        }
        
        // Отображение
        $deliveries = $deliveriesEntity->find();
        $this->design->assign('deliveries', $deliveries);
        $this->response->setContent($this->design->fetch('deliveries.tpl'));
    }
    
}
