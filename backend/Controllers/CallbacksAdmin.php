<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\CallbacksEntity;

class CallbacksAdmin extends IndexAdmin
{
    
    public function fetch(CallbacksEntity $callbacksEntity)
    {
        // Обработка действий
        if($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (!empty($ids)) {
                switch($this->request->post('action')) {
                    case 'delete': {
                        /*Удаление заявок на обратный звонок*/
                        $callbacksEntity->delete($ids);
                        break;
                    }
                    case 'processed': {
                        /*Модерация заявок на обратный звонок*/
                        $callbacksEntity->update($ids, ['processed'=>1]);
                        break;
                    }
                    case 'unprocessed': {
                        /*Модерация заявок на обратный звонок*/
                        $callbacksEntity->update($ids, ['processed'=>0]);
                        break;
                    }
                }
            }
        }
        
        // Отображение
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['callback_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['callback_num_admin'])) {
            $filter['limit'] = $_SESSION['callback_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);
        
        // Сортировка по статусу
        $status = $this->request->get('status', 'string');
        if ($status == 'processed') {
            $filter['processed'] = 1;
        } elseif ($status == 'unprocessed') {
            $filter['processed'] = 0;
        }
        $this->design->assign('status', $status);

        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        $callbacksCount = $callbacksEntity->count($filter);
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $callbacksCount;
        }
        /*Выборка заявок на обратный звонок*/
        $callbacks = $callbacksEntity->find($filter);

        $this->design->assign('pages_count', ceil($callbacksCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);

        $this->design->assign('callbacks', $callbacks);
        $this->design->assign('callbacks_count', $callbacksCount);

        $this->response->setContent($this->design->fetch('callbacks.tpl'));
    }
    
}
