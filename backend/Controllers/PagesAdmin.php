<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\PagesEntity;

class PagesAdmin extends IndexAdmin
{
    
    public function fetch(PagesEntity $pagesEntity)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i=>$position) {
                $pagesEntity->update($ids[$i], array('position'=>$position));
            }

            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        /*Выключить страницу*/
                        $pagesEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включить страницу*/
                        $pagesEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удалить страницу*/
                        if (!$pagesEntity->delete($ids)) {
                            $this->design->assign('message_error', 'url_system');
                        }
                        break;
                    }
                }
            }
        }
        
        // Отображение
        $pages = $pagesEntity->find();
        
        $this->design->assign('pages', $pages);
        $this->response->setContent($this->design->fetch('pages.tpl'));
    }
    
}
