<?php


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\VariantsEntity;

class CategoriesAdmin extends IndexAdmin
{
    
    public function fetch(
        CategoriesEntity $categoriesEntity,
        VariantsEntity $variantsEntity,
        QueryFactory $queryFactory
    ) {
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        /*Выключение категории*/
                        $categoriesEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включение категории*/
                        $categoriesEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удаление категории*/
                        $categoriesEntity->delete($ids);
                        break;
                    }
                }
            }
            
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $categoriesEntity->update($ids[$i], ['position'=>$position]);
            }
        }
        /*Выборка дерева категорий*/
        $categories = $categoriesEntity->getCategoriesTree();
        $categoriesCount = $categoriesEntity->count();

        $this->design->assign('categoriesCount', $categoriesCount);
        $this->design->assign('categories', $categories);
        $this->response->setContent($this->design->fetch('categories.tpl'));
    }
    
}
