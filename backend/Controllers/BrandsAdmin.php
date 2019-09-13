<?php


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\VariantsEntity;

class BrandsAdmin extends IndexAdmin
{
    
    public function fetch(
        BrandsEntity $brandsEntity,
        VariantsEntity $variantsEntity,
        QueryFactory $queryFactory
    ) {

        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['brands_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['brands_num_admin'])) {
            $filter['limit'] = $_SESSION['brands_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);

        // Обработка действий
        if ($this->request->method('post')) {

            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i=>$position) {
                $brandsEntity->update($ids[$i], ['position'=>$position]);
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        $brandsEntity->delete($ids);
                        break;
                    }
                    case 'move_to_page': {
                        /*Переместить на страницу*/
                        $targetPage = $this->request->post('target_page', 'integer');

                        // Сразу потом откроем эту страницу
                        $filter['page'] = $targetPage;

                        // До какого бренда перемещать
                        $limit = $filter['limit']*($targetPage-1);
                        if ($targetPage > $this->request->get('page', 'integer')) {
                            $limit += count($ids)-1;
                        } else {
                            $ids = array_reverse($ids, true);
                        }
                        
                        $tempFilter = $filter;
                        $tempFilter['page'] = $limit+1;
                        $tempFilter['limit'] = 1;
                        $tmp = $brandsEntity->find($tempFilter);
                        $targetBrand = array_pop($tmp);
                        $targetPosition = $targetBrand->position;

                        // Если вылезли за последний бренд - берем позицию последнего бренда в качестве цели перемещения
                        if ($targetPage > $this->request->get('page', 'integer') && !$targetPosition) {

                            $select = $queryFactory->newSelect();
                            $select->from('__brands')
                                ->cols(['distinct position AS target'])
                                ->orderBy(['position DESC'])
                                ->limit(1);
                            
                            $this->db->query($select);
                            $targetPosition = $this->db->result('target');
                        }
                        
                        foreach ($ids as $id) {
                            $initialPosition = $brandsEntity->cols(['position'])->get((int)$id)->position;

                            $update = $queryFactory->newUpdate();
                            if ($targetPosition > $initialPosition) {
                                $update->table('__brands')
                                    ->set('position', 'position-1')
                                    ->where('position > :initial_position')
                                    ->where('position <= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            } else {
                                $update->table('__brands')
                                    ->set('position', 'position+1')
                                    ->where('position < :initial_position')
                                    ->where('position >= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            }
                            $this->db->query($update);

                            $brandsEntity->update($id, ['position' => $targetPosition]);
                        }
                        break;
                    }
                }
            }
        }

        $brandsCount = $brandsEntity->count($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $brandsCount;
        }

        if ($filter['limit']>0) {
            $pagesCount = ceil($brandsCount/$filter['limit']);
        } else {
            $pagesCount = 0;
        }
        $filter['page'] = min($filter['page'], $pagesCount);
        $this->design->assign('brands_count', $brandsCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);

        $brands = $brandsEntity->find($filter);
        
        $this->design->assign('brands', $brands);
        $this->response->setContent($this->design->fetch('brands.tpl'));
    }
    
}
