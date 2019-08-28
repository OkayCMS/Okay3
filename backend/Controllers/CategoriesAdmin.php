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
                    case 'in_feed': {
                        /*Выгрузка товаров категории в файл feed.xml*/
                        foreach($ids as $id) {
                            $category = $categoriesEntity->get(intval($id));
                            
                            $select = $queryFactory->newSelect();
                            $select->from('__categories AS c')
                                ->cols(['v.id'])
                                ->join('right', '__products_categories AS pc', 'c.id=pc.category_id')
                                ->join('right', '__variants AS v', 'v.product_id=pc.product_id')
                                ->where('c.id IN (:categories_ids)')
                                ->bindValue('categories_ids', $category->children);
                            
                            $this->db->query($select);
                            $vIds = $this->db->results('id');
                            
                            if (count($vIds) > 0) {
                                $variantsEntity->update($vIds, ['feed' => 1]);
                            }
                        }
                        break;
                    }
                    case 'out_feed': {
                        /*Снятие товаров категории с выгрузки файла feed.xml*/
                        foreach($ids as $id) {
                            $category = $categoriesEntity->get(intval($id));
                            $select = $queryFactory->newSelect();
                            $select->from('__categories AS c')
                                ->cols(['v.id'])
                                ->join('right', '__products_categories AS pc', 'c.id=pc.category_id')
                                ->join('right', '__variants AS v', 'v.product_id=pc.product_id')
                                ->where('c.id IN (:categories_ids)')
                                ->bindValue('categories_ids', $category->children);
                            $this->db->query($select);
                            $vIds = $this->db->results('id');
                            
                            if (count($vIds) > 0) {
                                $variantsEntity->update($vIds, ['feed' => 0]);
                            }
                        }
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
        
        $this->design->assign('categories', $categories);
        $this->response->setContent($this->design->fetch('categories.tpl'));
    }
    
}
