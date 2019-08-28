<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\MenuEntity;

class MenusAdmin extends IndexAdmin
{

    public function fetch(MenuEntity $menuEntity)
    {
        /*Принимаем выбранные меню*/
        if ($this->request->method('post')) {
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        /*Выключаем меню*/
                        $menuEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включаем меню*/
                        $menuEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удаляем меню*/
                        $menuEntity->delete($ids);
                        break;
                    }
                }
            }

            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $menuEntity->update($ids[$i], ['position'=>$position]);
            }
        }

        $menus = $menuEntity->find();
        $this->design->assign('menus', $menus);
        $this->response->setContent($this->design->fetch('menus.tpl'));
    }

}
