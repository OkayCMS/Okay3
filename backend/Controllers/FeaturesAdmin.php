<?php


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\FeaturesEntity;

class FeaturesAdmin extends IndexAdmin
{
    
    public function fetch(
        FeaturesEntity $featuresEntity,
        CategoriesEntity $categoriesEntity,
        QueryFactory $queryFactory
    ) {

        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['features_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['features_num_admin'])) {
            $filter['limit'] = $_SESSION['features_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);

        if ($this->request->method('post')) {

            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i=>$position) {
                $featuresEntity->update($ids[$i], ['position'=>$position]);
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'set_in_filter': {
                        /*Отображать в фильтре*/
                        $featuresEntity->update($ids, ['in_filter'=>1]);
                        break;
                    }
                    case 'unset_in_filter': {
                        /*Не отображать в фильтре*/
                        $featuresEntity->update($ids, ['in_filter'=>0]);
                        break;
                    }
                    case 'delete': {
                        /*Удалить свойство*/
                        $currentCategoryId = $this->request->get('category_id', 'integer');
                        foreach ($ids as $id) {
                            // текущие категории
                            $featureCategories = $featuresEntity->getFeatureCategories($id);
                            
                            // В каких категориях оставлять
                            $diffCategoriesIds = array_diff($featureCategories, (array)$currentCategoryId); // todo протестить
                            if (!empty($currentCategoryId) && !empty($diffCategoriesIds)) {
                                $featuresEntity->updateFeatureCategories($id, $diffCategoriesIds);
                            } else {
                                $featuresEntity->delete($id);
                            }
                        }
                        break;
                    }
                    case 'move_to_page': { // todo Просится в Logic
                        /*Переместить на страницу*/
                        $targetPage = $this->request->post('target_page', 'integer');
    
                        // Сразу потом откроем эту страницу
                        $filter['page'] = $targetPage;
    
                        // До какого свойства перемещать
                        $limit = $filter['limit']*($targetPage-1);
                        if ($targetPage > $this->request->get('page', 'integer')) {
                            $limit += count($ids)-1;
                        } else {
                            $ids = array_reverse($ids, true);
                        }
    
                        $tempFilter = $filter;
                        $tempFilter['page'] = $limit+1;
                        $tempFilter['limit'] = 1;
                        $tmp = $featuresEntity->find($tempFilter);
                        $targetFeature = array_pop($tmp);
                        $targetPosition = $targetFeature->position;
    
                        // Если вылезли за последнее свойство - берем позицию последнего свойства в качестве цели перемещения
                        if ($targetPage > $this->request->get('page', 'integer') && !$targetPosition) {
                            $select = $queryFactory->newSelect();
                            $select->from('__features')
                                ->cols(['distinct position AS target'])
                                ->orderBy(['position DESC'])
                                ->limit(1);

                            $this->db->query($select);
                            $targetPosition = $this->db->result('target');
                        }
    
                        foreach ($ids as $id) {
                            $initialPosition = $featuresEntity->cols(['position'])->get((int)$id)->position;

                            $update = $queryFactory->newUpdate();
                            if ($targetPosition > $initialPosition) {
                                $update->table('__features')
                                    ->set('position', 'position-1')
                                    ->where('position > :initial_position')
                                    ->where('position <= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            } else {
                                $update->table('__features')
                                    ->set('position', 'position+1')
                                    ->where('position < :initial_position')
                                    ->where('position >= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            }
                            $this->db->query($update);

                            $featuresEntity->update($id, ['position' => $targetPosition]);
                        }
                        break;
                    }
                }
            }
        }
        
        $categories = $categoriesEntity->find();
        $category = null;

        $categoryId = $this->request->get('category_id', 'integer');
        if ($categoryId) {
            $category = $categoriesEntity->get($categoryId);
            $filter['category_id'] = $category->id;
        }

        $featuresCount = $featuresEntity->count($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $featuresCount;
        }

        if ($filter['limit']>0) {
            $pagesCount = ceil($featuresCount/$filter['limit']);
        } else {
            $pagesCount = 0;
        }
        $filter['page'] = min($filter['page'], $pagesCount);
        $this->design->assign('features_count', $featuresCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);

        $features = $featuresEntity->find($filter);
        foreach ($features as $f) {
            $f->features_categories = $featuresEntity->getFeatureCategories($f->id);
        }
        
        $this->design->assign('categories', $categories);
        $this->design->assign('categories_tree', $categoriesEntity->getCategoriesTree());
        $this->design->assign('category', $category);
        $this->design->assign('features', $features);

        $this->response->setContent($this->design->fetch('features.tpl'));
    }
    
}
