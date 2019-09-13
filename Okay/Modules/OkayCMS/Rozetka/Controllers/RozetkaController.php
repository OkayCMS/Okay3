<?php


namespace Okay\Modules\OkayCMS\Rozetka\Controllers;


use Okay\Controllers\AbstractController;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Logic\ProductsLogic;

class RozetkaController extends AbstractController
{
    
    public function render(
        ProductsEntity $productsEntity,
        ProductsLogic $productsLogic,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        Database $db,
        QueryFactory $queryFactory
    ) {

        if (!empty($this->currencies)) {
            $this->design->assign('main_currency', reset($this->currencies));
        }

        $sql = $queryFactory->newSqlQuery();
        $sql->setStatement('SET SQL_BIG_SELECTS=1');
        $db->query($sql);
        
        $sql = $queryFactory->newSqlQuery();
        $sql->setStatement("SELECT id, parent_id, to_rozetka FROM __categories WHERE to_rozetka=1");
        $db->query($sql);
        $categoriesToRozetka = $db->results();
        $uploadCategories = [];

        $uploadCategories = $this->addAllChildrenToList($categoriesEntity, $categoriesToRozetka, $uploadCategories);
        
        $filter['visible'] = 1;
        $filter['rozetka_only'] = $uploadCategories;
        
        if ($this->settings->get('upload_only_available_to_rozetka')) {
            $filter['in_stock'] = 1;
        }
        
        $products = $productsEntity->cols([
            'description',
            'name',
            'id',
            'brand_id',
            'url',
            'annotation',
            'main_category_id',
        ])
            ->mappedBy('id')
            ->find($filter);

        if (!empty($products)) {
            $products = $productsLogic->attachVariants($products, $filter);
            $products = $productsLogic->attachImages($products);
            $products = $productsLogic->attachFeatures($products, ['feed'=>1]);
            
            $this->design->assign('products', $products);
            
            $allBrands = $brandsEntity->mappedBy('id')->find(['product_id' => array_keys($products)]);
            $this->design->assign('all_brands', $allBrands);
        }
        
        $this->design->assign('all_categories', $categoriesEntity->find());
        
        $this->response->setContent(pack('CCC', 0xef, 0xbb, 0xbf));
        $this->response->setContent($this->design->fetch('feed.xml.tpl'), RESPONSE_XML);
        
    }
    
    private function addAllChildrenToList(CategoriesEntity $categoriesEntity, $categories, $uploadCategories)
    {
        foreach ($categories as $c) {
            $category = $categoriesEntity->get((int)$c->id);
            $uploadCategories[] = $category->id;
            if (!empty($category->subcategories)) {
                $uploadCategories = $this->addAllChildrenToList($categoriesEntity, $category->subcategories, $uploadCategories);
            }
        }
        return $uploadCategories;
    }
    
}
