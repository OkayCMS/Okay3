<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\PaymentsEntity;

class PaymentMethodsAdmin extends IndexAdmin
{
    
    public function fetch(PaymentsEntity $paymentsEntity)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i=>$position) {
                $paymentsEntity->update($ids[$i], ['position'=>$position]);
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        /*Выключение способ оплаты*/
                        $paymentsEntity->update($ids, ['enabled'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включение способа оплаты*/
                        $paymentsEntity->update($ids, ['enabled'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удаление способа оплаты*/
                        $paymentsEntity->delete($ids);
                        break;
                    }
                }
            }
        }
        
        // Отображение
        $paymentMethods = $paymentsEntity->find();
        $this->design->assign('payment_methods', $paymentMethods);
        $this->response->setContent($this->design->fetch('payment_methods.tpl'));
    }
    
}

?>