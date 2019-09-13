<?php


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\ImagesEntity;

class ProductsAdmin extends IndexAdmin
{
    
    public function fetch(
        ProductsEntity $productsEntity,
        VariantsEntity $variantsEntity,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        ImagesEntity $imagesEntity,
        CurrenciesEntity $moneyEntity,
        QueryFactory $queryFactory
    ) {
        $filter = array();
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['products_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['products_num_admin'])) {
            $filter['limit'] = $_SESSION['products_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);
        
        // Категории
        $categories = $categoriesEntity->getCategoriesTree();
        $this->design->assign('categories', $categories);
        
        // Текущая категория
        $categoryId = $this->request->get('category_id', 'integer');
        if($categoryId && $category = $categoriesEntity->get($categoryId)) {
            $filter['category_id'] = $category->children;
        } elseif ($categoryId==-1) {
            $filter['without_category'] = 1;
        }
        $this->design->assign('category_id', $categoryId);
        
        // Бренды категории
        $brandsFilter = [];
        if (!empty($filter['category_id'])) {
            $brandsFilter['category_id'] = $filter['category_id'];
        }
        $brandsCount = $brandsEntity->count($brandsFilter);
        $brandsFilter['limit'] = $brandsCount;
        $brands = $brandsEntity->find($brandsFilter);
        $this->design->assign('brands', $brands);
        
        // Все бренды
        $brandsCount = $brandsEntity->count();
        $allBrands = array();
        foreach ($brandsEntity->find(['limit'=>$brandsCount]) as $b) {
            $allBrands[$b->id] = $b;
        }
        $this->design->assign('all_brands', $allBrands);
        
        // Текущий бренд
        $brand_id = $this->request->get('brand_id', 'integer');
        if($brand_id && $brand = $brandsEntity->get($brand_id)) {
            $filter['brand_id'] = $brand->id;
        } elseif ($brand_id==-1) {
            $filter['brand_id'] = array(0);
        }
        $this->design->assign('brand_id', $brand_id);

        if ($features = $this->request->get('features')) {
            $filter['features'] = $features;
        }
        
        /*Фильтр по товарам*/
        if($f = $this->request->get('filter', 'string')) {
            if($f == 'featured') {
                $filter['featured'] = 1;
            } elseif($f == 'discounted') {
                $filter['discounted'] = 1;
            } elseif($f == 'visible') {
                $filter['visible'] = 1;
            } elseif($f == 'hidden') {
                $filter['visible'] = 0;
            } elseif($f == 'outofstock') {
                $filter['in_stock'] = 0;
            } elseif($f == 'without_images') {
                $filter['has_images'] = 0;
            }
            $this->design->assign('filter', $f);
        }
        
        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        // Обработка действий
        if ($this->request->method('post')) {
            // Сохранение цен и наличия
            $prices = $this->request->post('price');
            $stocks = $this->request->post('stock');

            foreach($prices as $id=>$price) {
                $stock = $stocks[$id];
                if($stock == '∞' || $stock == '') {
                    $stock = null;
                }
                $variantsEntity->update($id, ['price'=>str_replace(',', '.', $price), 'stock'=>$stock]);
            }
            
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            $positions = array_reverse($positions);
            foreach($positions as $i=>$position) {
                $productsEntity->update($ids[$i], array('position' => (int)$position));
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            if(!empty($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        /*Выключить товар*/
                        $productsEntity->update($ids, array('visible'=>0));
                        break;
                    }
                    case 'enable': {
                        /*Включить товар*/
                        $productsEntity->update($ids, array('visible'=>1));
                        break;
                    }
                    case 'set_featured': {
                        /*Включить "хит продаж"*/
                        $productsEntity->update($ids, array('featured'=>1));
                        break;
                    }
                    case 'unset_featured': {
                        /*Выключить "хит продаж"*/
                        $productsEntity->update($ids, array('featured'=>0));
                        break;
                    }
                    case 'set_feed': {
                        /*Выгружать в фид*/
                        $update = $queryFactory->newUpdate();
                        $update->table('__variants')
                            ->set('feed', '1')
                            ->where('product_id IN (:products_ids)')
                            ->bindValue('products_ids', (array)$ids);
                        $this->db->query($update);
                        break;
                    }
                    case 'unset_feed': {
                        /*Не выгружать в фид*/
                        $update = $queryFactory->newUpdate();
                        $update->table('__variants')
                            ->set('feed', '0')
                            ->where('product_id IN (:products_ids)')
                            ->bindValue('products_ids', (array)$ids);
                        $this->db->query($update);
                        break;
                    }
                    case 'delete': {
                        /*Удалить товар*/
                        $productsEntity->delete($ids);
                        break;
                    }
                    case 'duplicate': {
                        /*Сделать копию товара*/
                        foreach($ids as $id) {
                            $productsEntity->duplicate((int)$id);
                        }
                        break;
                    }
                    case 'move_to_page': {
                        /*Переместить на страницу*/
                        $targetPage = $this->request->post('target_page', 'integer');
                        
                        // Сразу потом откроем эту страницу
                        $filter['page'] = $targetPage;
                        
                        // До какого товара перемещать
                        $limit = $filter['limit']*($targetPage-1);
                        if($targetPage > $this->request->get('page', 'integer')) {
                            $limit += count($ids)-1;
                        } else {
                            $ids = array_reverse($ids, true);
                        }
                        
                        $tempFilter = $filter;
                        $tempFilter['page'] = $limit+1;
                        $tempFilter['limit'] = 1;
                        $tmp = $productsEntity->find($tempFilter);
                        $targetProduct = array_pop($tmp);
                        $targetPosition = $targetProduct->position;
                        
                        // Если вылезли за последний товар - берем позицию последнего товара в качестве цели перемещения
                        if ($targetPage > $this->request->get('page', 'integer') && !$targetPosition) {

                            $select = $queryFactory->newSelect();
                            $select->cols(['distinct p.position AS target'])
                                ->from('__products AS p')
                                ->join('left', '__products_categories AS pc', 'pc.product_id = p.id')
                                ->orderBy(['p.position DESC'])
                                ->limit(1);
                            
                            $this->db->query($select);
                            $targetPosition = $this->db->result('target');
                        }
                        
                        foreach ($ids as $id) {
                            $initialPosition = $productsEntity->cols(['position'])->get((int)$id)->position;
                            
                            $update = $queryFactory->newUpdate();
                            if ($targetPosition > $initialPosition) {
                                $update->table('__products')
                                    ->set('position', 'position-1')
                                    ->where('position > :initial_position')
                                    ->where('position <= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            } else {
                                $update->table('__products')
                                    ->set('position', 'position+1')
                                    ->where('position < :initial_position')
                                    ->where('position >= :target_position')
                                    ->bindValues([
                                        'initial_position' => $initialPosition,
                                        'target_position' => $targetPosition,
                                    ]);
                            }
                            $this->db->query($update);

                            $productsEntity->update($id, ['position' => $targetPosition]);
                        }
                        break;
                    }
                    case 'move_to_category': {
                        /*Переместить в категорию*/
                        $categoryId = $this->request->post('target_category', 'integer');
                        $filter['page'] = 1;
                        $category = $categoriesEntity->get($categoryId);
                        $filter['category_id'] = $category->children;
                        
                        foreach($ids as $id) {
                            $delete = $queryFactory->newDelete();
                            $delete->from('__products_categories')
                                ->where('category_id=:category_id')
                                ->where('product_id=:product_id')
                                ->bindValues([
                                    'category_id' => $categoryId,
                                    'product_id' => $id,
                                ]);
                            
                            $update = $queryFactory->newUpdate();
                            $update->table('__products_categories')
                                ->cols(['category_id'])
                                ->where('product_id = :product_id')
                                ->orderBy(['position DESC'])
                                ->limit(1)
                                ->bindValues([
                                    'category_id' => $categoryId,
                                    'product_id' => $id
                                ])
                                ->ignore();
                            
                            $this->db->query($update);
                            if ($this->db->affectedRows() == 0) {
                                $insert = $queryFactory->newInsert();
                                $insert->into('__products_categories')
                                    ->ignore()
                                    ->set('category_id', $categoryId)
                                    ->set('product_id', $id);
                                $this->db->query($insert);
                            }
                        }
                        break;
                    }
                    case 'move_to_brand': {
                        /*Переместить в бренд*/
                        $brandId = $this->request->post('target_brand', 'integer');
                        $brand = $brandsEntity->get($brandId);
                        $filter['page'] = 1;
                        $filter['brand_id'] = $brandId;
                        $productsEntity->update($ids, ['brand_id' => $brandId]);
                        
                        // Заново выберем бренды категории
                        $brandsCount = $brandsEntity->count(['category_id'=>$categoryId]);
                        $brands = $brandsEntity->find(['category_id'=>$categoryId, 'limit'=>$brandsCount]);
                        $this->design->assign('brands', $brands);
                        
                        break;
                    }
                }
            }
        }
        
        // Отображение
        if (isset($brand)) {
            $this->design->assign('brand', $brand);
        }
        if (isset($category)) {
            $this->design->assign('category', $category);
        }
        
        $productsCount = $productsEntity->count($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $productsCount;
        }
        
        if ($filter['limit']>0) {
            $pagesCount = ceil($productsCount/$filter['limit']);
        } else {
            $pagesCount = 0;
        }
        $filter['page'] = min($filter['page'], $pagesCount);
        $this->design->assign('products_count', $productsCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        
        $products = [];
        $imagesIds = [];
        foreach ($productsEntity->find($filter) as $p) {
            $products[$p->id] = $p;
            $imagesIds[] = $p->main_image_id;
        }
        
        
        if (!empty($products)) {
            // Товары
            $productsIds = array_keys($products);
            foreach($products as $product) {
                $product->variants = array();
                $product->properties = array();
            }
            
            $variants = $variantsEntity->find(['product_id'=>$productsIds]);
            foreach ($variants as $variant) {
                $products[$variant->product_id]->variants[] = $variant;
            }

            if (!empty($imagesIds)) {
                foreach ($imagesEntity->find(['id'=>$imagesIds]) as $image) {
                    if (isset($products[$image->product_id])) {
                        $products[$image->product_id]->image = $image;
                    }
                }
            }
        }
        
        $this->design->assign('currencies', $moneyEntity->mappedBy('id')->find());
        $this->design->assign('products', $products);

        $this->response->setContent($this->design->fetch('products.tpl'));
    }

}
