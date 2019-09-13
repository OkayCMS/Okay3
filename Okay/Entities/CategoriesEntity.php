<?php


namespace Okay\Entities;


use Aura\SqlQuery\Exception;
use Okay\Core\Entity\Entity;
use Okay\Core\Image;

class CategoriesEntity extends Entity
{
    private $allCategories;
    private $categoriesTree;
    private $filteredCategoryIds = [];

    protected static $fields = [
        'id',
        'parent_id',
        'url',
        'image',
        'position',
        'visible',
        'external_id',
        'level_depth',
        'last_modify',
        'created'
    ];

    protected static $langFields = [
        'name',
        'name_h1',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'annotation',
        'description',
        'auto_meta_title',
        'auto_meta_keywords',
        'auto_meta_desc',
        'auto_description'
    ];

    protected static $searchFields = [];

    protected static $defaultOrderFields = [
        'parent_id',
        'position',
    ];

    protected static $table = '__categories';
    protected static $langObject = 'category';
    protected static $langTable = 'categories';
    protected static $tableAlias = 'c';

    public function flush()
    {
        $this->filteredCategoryIds = [];
        parent::flush();
    }

    public function getCategoriesTree() {
        if (empty($this->categoriesTree)) {
            $this->initCategories();
        }
        return $this->categoriesTree;
    }

    public function get($id)
    {
        if (empty($this->categoriesTree)) {
            $this->initCategories();
        }

        if (is_int($id) && array_key_exists(intval($id), $this->allCategories)) {
            return $category = $this->allCategories[intval($id)];
        }

        if(is_string($id)) {
            foreach ($this->allCategories as $category) {
                if ($category->url == $id) {
                    return $this->get((int)$category->id);
                }
            }
        }

        return false;
    }
    
    public function add($category)
    {
        $id = parent::add($category);
        unset($this->categoriesTree);
        unset($this->allCategories);
        return $id;
    }
    
    public function update($id, $category)
    {
        $id = parent::update($id, $category);
        unset($this->categoriesTree);
        unset($this->allCategories);
        return $id;
    }

    public function find(array $filter = [])
    {
        if (empty($this->categoriesTree)) {
            $this->initCategories();
        }

        if (empty($filter)) {
            return $this->allCategories;
        }
        
        $this->buildFilter($filter);
        $matchedCategories = [];
        foreach ($this->filteredCategoryIds as $id) {
            if (isset($this->allCategories[$id])) {
                $matchedCategories[$id] = $this->allCategories[$id];
            }
        }

        return $matchedCategories;
    }

    public function delete($ids)
    {
        /** @var Image $imageCore */
        $imageCore = $this->serviceLocator->getService(Image::class);
        
        $ids = (array)$ids;
        foreach ($ids as $id) {
            $category = $this->get((int)$id);
            if ($category === false && empty($category->children)) {
                continue;
            }

            foreach ($category->children as $cId) {
                $imageCore->deleteImage(
                    $cId,
                    'image',
                    self::class,
                    $this->config->original_categories_dir,
                    $this->config->resized_categories_dir
                );
            }

            $select = $this->queryFactory->newSelect();
            $select->from('__products_categories')
                ->cols(['product_id'])
                ->where('category_id IN (:category_id)')
                ->bindValue('category_id', $category->children);

            $this->db->query($select);
            //Получим товары для которых нужно будет обновить информацию о главных категориях
            $productIds = $this->db->results('product_id');

            $delete = $this->queryFactory->newDelete();
            $delete->from('__products_categories')
                ->where('category_id IN (:category_id)')
                ->bindValue('category_id', $category->children);

            $this->db->query($delete);
            //Обновим информацию о главной категории
            $this->updateMainProductsCategory($productIds);

            $SEOFilterPatternsEntity = $this->entity->get(SEOFilterPatternsEntity::class);

            $patternsIds = $SEOFilterPatternsEntity->cols(['id'])->find(['category_id' => $category->children]);
            $SEOFilterPatternsEntity->delete($patternsIds);

            parent::delete($category->children);
        }
        
        unset($this->categoriesTree);
        unset($this->allCategories);
        return true;
    }

    //Обновление информацию о главной категории товара
    public function updateMainProductsCategory($productsIds) {
        $productsIds = (array)$productsIds;
        if (empty($productsIds)) {
            return false;
        }

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("UPDATE __products p
                          LEFT JOIN __products_categories pc ON p.id = pc.product_id AND pc.position=(SELECT MIN(position) FROM __products_categories WHERE product_id=p.id LIMIT 1)
                          SET p.main_category_id = pc.category_id
                          WHERE p.id IN (:products_ids)");
        $sql->bindValue('products_ids', $productsIds);
        $this->db->query($sql);
    }
    
    public function addProductCategory($productId, $categoryId, $position = 0)
    {
        $this->update($categoryId, ['last_modify' => 'now()']);
        
        $insert = $this->queryFactory->newInsert();
        $insert->into('__products_categories')
            ->cols([
                'product_id',
                'category_id',
                'position',
            ])
            ->bindValues([
                'product_id' => $productId,
                'category_id' => $categoryId,
                'position' => $position,
            ])
            ->ignore();
        
        $this->db->query($insert);
    }

    public function deleteProductCategory($productsIds, $categoriesIds = [])
    {
        if (empty($productsIds)) {
            return false;
        }

        $productsIds = (array) $productsIds;
        $categoriesIds = (array) $categoriesIds;

        $delete = $this->queryFactory->newDelete()
            ->from('__products_categories')
            ->where('product_id IN(:products_ids)')
            ->bindValue('products_ids', $productsIds);

        if (empty($categoriesIds)) {
            $this->db->query($delete);
            return true;
        }

        $delete->where('category_id IN(:categories_ids)')
            ->bindValue('categories_ids', $categoriesIds);

        $this->db->query($delete);
        return true;
    }

    /*Выбираем категории определенного товара*/
    public function getProductCategories($productsIds = []) {
        $select = $this->queryFactory->newSelect();
        $select->from('__products_categories')
            ->cols([
                'product_id',
                'category_id',
                'position',
            ])
            ->orderBy(['position']);
        
        if (!empty($productsIds)) {
            $select->where('product_id IN (:product_id)')
                ->bindValue('product_id', (array)$productsIds);
        }
        
        $this->db->query($select);
        return $this->db->results();
    }

    /*Выборка категорий яндекс маркета*/
    public function getMarket($query = '')
    {
        $query = mb_strtolower($query);
        $marketCats = [];
        $file = 'files/downloads/market_categories.csv';
        if (file_exists($file)) {
            $f = fopen($file, 'r');
            fgetcsv($f, 0, '^');
            while (!feof($f)) {
                $line = fgetcsv($f, 0, '^');
                if (empty($query) || strpos(mb_strtolower($line[0]), $query) !== false) {
                    $marketCats[] = $line[0];
                }
            }
            fclose($f);
        }
        return $marketCats;
    }
    
    protected function filter__id($ids)
    {
        $this->filteredCategoryIds = array_merge($this->filteredCategoryIds, $ids);
        $this->filteredCategoryIds = array_unique($this->filteredCategoryIds);
    }
    
    protected function filter__product_id($ids)
    {
        $ids = (array)$ids;
        $select = $this->queryFactory->newSelect();
        $select->cols(['category_id'])
            ->from('__products_categories')
            ->where('product_id IN (:products_ids)');
        $select->bindValue('products_ids', $ids);
        
        if (!empty($this->filteredCategoryIds)) {
            $select->where('category_id IN (:category_id)')->bindValue('category_id', $this->filteredCategoryIds);
        }
        
        $this->db->query($select);
        
        $categoriesIds = $this->db->results('category_id');
        $this->filteredCategoryIds = array_merge($this->filteredCategoryIds, $categoriesIds);
        $this->filteredCategoryIds = array_unique($this->filteredCategoryIds);
    }
    
    protected function filter__brand_id($brandsIds, &$filter)
    {
        $brandsIds = (array)$brandsIds;
        $select = $this->queryFactory->newSelect();
        $select->cols(['pc.category_id'])
            ->from('__products_categories AS pc')
            ->join('INNER', '__products p', 'p.id=pc.product_id AND p.brand_id in (:brands_ids) AND p.visible=1')
            ->join('LEFT', '__categories c', 'c.id=pc.category_id')
            ->groupBy(['pc.category_id'])
            ->orderBy([
                'c.parent_id',
                'c.position',
            ]);
        $select->bindValue('brands_ids', $brandsIds);

        if (!empty($this->filteredCategoryIds)) {
            $select->where('pc.category_id IN (:category_id)')->bindValue('category_id', $this->filteredCategoryIds);
        }
        
        if (isset($filter['category_visible'])) {
            $select->where('c.visible=:visible')
                ->bindValue('visible', (int)$filter['category_visible']);
            unset($filter['category_visible']);
        }
        
        $this->db->query($select);
        
        $categoriesIds = $this->db->results('category_id');

        $this->filteredCategoryIds = array_merge($this->filteredCategoryIds, $categoriesIds);
        $this->filteredCategoryIds = array_unique($this->filteredCategoryIds);
    }

    private function initCategories()
    {
        $categories = $this->getAllCategoriesFromDb();

        $tree = new \stdClass();
        $tree->subcategories = array();

        // Указатели на узлы дерева
        $pointers = array();
        $pointers[0] = &$tree;
        $pointers[0]->path = array();
        $pointers[0]->level = 0;

        $finish = false;
        // Не кончаем, пока не кончатся категории, или пока ниодну из оставшихся некуда приткнуть
        while(!empty($categories)  && !$finish) {
            $flag = false;
            // Проходим все выбранные категории
            foreach($categories as $k=>$category) {
                if(isset($pointers[$category->parent_id])) {
                    // В дерево категорий (через указатель) добавляем текущую категорию
                    $pointers[$category->id] = $pointers[$category->parent_id]->subcategories[$category->id] = $category;

                    // Путь к текущей категории
                    $curr = $pointers[$category->id];
                    $pointers[$category->id]->path = array_merge((array)$pointers[$category->parent_id]->path, array($curr));

                    // Уровень вложенности категории
                    $pointers[$category->id]->level = 1+$pointers[$category->parent_id]->level;

                    // Убираем использованную категорию из массива категорий
                    unset($categories[$k]);
                    $flag = true;
                }
            }
            if(!$flag) $finish = true;
        }

        // Для каждой категории id всех ее деток узнаем
        $ids = array_reverse(array_keys($pointers));
        foreach($ids as $id) {
            if($id>0) {
                $pointers[$id]->children[] = $id;

                if(isset($pointers[$pointers[$id]->parent_id]->children)) {
                    $pointers[$pointers[$id]->parent_id]->children = array_merge($pointers[$id]->children, $pointers[$pointers[$id]->parent_id]->children);
                } else {
                    $pointers[$pointers[$id]->parent_id]->children = $pointers[$id]->children;
                }
            }
        }
        unset($pointers[0]);
        unset($ids);

        $this->categoriesTree = $tree->subcategories;
        $this->allCategories  = $pointers;
    }

    private function getAllCategoriesFromDb()
    {
        // Подключаем языковую таблицу
        $langQuery = $this->lang->getQuery(
            $this->getTableAlias(),
            $this->getLangTable(),
            $this->getLangObject()
        );

        $this->select->from($this->getTable() . ' AS ' . $this->getTableAlias());
        if (!empty($langQuery['join'])) {
            $this->select->join('LEFT', $langQuery['join'], $langQuery['cond']);
        }

        $this->select->cols($this->getAllFields());
        $this->db->query($this->select);

        $resultFields = $this->getAllFieldsWithoutAlias();
        $field = null;

        if (count($resultFields) == 1) {
            $field = reset($resultFields);
        }

        $categories = $this->db->results($field);
        $this->flush();
        return $categories;
    }
}