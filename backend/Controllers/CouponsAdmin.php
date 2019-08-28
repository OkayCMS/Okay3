<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\CouponsEntity;

class CouponsAdmin extends IndexAdmin
{
    
    public function fetch(CouponsEntity $couponsEntity)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids) && count($ids)>0) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        /*Удаление купонов*/
                        $couponsEntity->delete($ids);
                        break;
                    }
                }
            }

            /*Создание купона*/
            if ($this->request->post("new_code")){
                $newExpire = $this->request->post('new_expire');
                $newCoupon         = new \stdClass();
                $newCoupon->id     = $this->request->post('new_id', 'integer');
                $newCoupon->code   = $this->request->post('new_code', 'string');
                $newCoupon->value  = $this->request->post('new_value', 'float');
                $newCoupon->type   = $this->request->post('new_type', 'string');
                $newCoupon->single = $this->request->post('new_single', 'float');
                $newCoupon->min_order_price = $this->request->post('new_min_order_price', 'float');
                
                if (!empty($newExpire)) {
                    $newCoupon->expire = date('Y-m-d', strtotime($newExpire));
                } else {
                    $newCoupon->expire = null;
                }

                // Не допустить одинаковые коды купонов
                if(($a = $couponsEntity->get((string)$newCoupon->code)) && $a->id != $newCoupon->id) {
                    $this->design->assign('message_error', 'code_exists');
                } elseif(empty($newCoupon->code)) {
                    $this->design->assign('message_error', 'empty_code');
                } else {
                    $newCoupon->id = $couponsEntity->add($newCoupon);
                    $this->design->assign('message_success', 'added');

                }
            }
        }
        
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 20;
        
        // Поиск
        $keyword = $this->request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        $couponsCount = $couponsEntity->count($filter);
        
        $pagesCount = ceil($couponsCount/$filter['limit']);
        $filter['page'] = min($filter['page'], $pagesCount);
        $this->design->assign('coupons_count', $couponsCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        
        
        $coupons = $couponsEntity->find($filter);
        
        $this->design->assign('coupons', $coupons);
        $this->response->setContent($this->design->fetch('coupons.tpl'));
    }
    
}
