<?php


namespace Okay\Modules\OkayCMS\YandexXML\Controllers;


use Okay\Controllers\AbstractController;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Logic\ProductsLogic;
use Okay\Modules\OkayCMS\YandexXML\Init\Init;

class YandexXMLController extends AbstractController
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
        $sql->setStatement("SELECT id, parent_id, ".Init::TO_FEED_FIELD." FROM __categories WHERE ".Init::TO_FEED_FIELD."=1");
        $db->query($sql);
        $categoriesToYandex = $db->results();
        $uploadCategories = [];

        $uploadCategories = $this->addAllChildrenToList($categoriesEntity, $categoriesToYandex, $uploadCategories);
        
        $filter['visible'] = 1;
        $filter['okaycms__yandex_xml__only'] = $uploadCategories;
        
        if ($this->settings->get('okaycms__yandex_xml__upload_only_available_to_yandex')) {
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
            $products = $this->sliceImagesByProduct($products, 10);
            $products = $productsLogic->attachFeatures($products);

            $countryOfOrigin = $this->settings->okaycms__yandex_xml__country_of_origin;
            if (!empty($countryOfOrigin)) {
                $products = $this->attachCountryOfOriginParameter($products);
            }

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

    private function sliceImagesByProduct($products, $amountPhotosByProduct = 10)
    {
        foreach($products as $id => $product) {
            if (empty($product->images)) {
                continue;
            }

            if (count($product->images) > 10) {
                $product->images[$id] = array_splice($product->images, 0, $amountPhotosByProduct);
            }
        }

        return $products;
    }

    private function attachCountryOfOriginParameter($products)
    {
        $countryOfOriginParamId = $this->settings->get('okaycms__yandex_xml__country_of_origin');

        foreach($products as $id => $product) {
            if (empty($product->features)) {
                continue;
            }

            foreach($product->features as $feature) {
                if ($feature->id == $countryOfOriginParamId) {
                    $products[$id]->country_of_origin = reset($feature->values)->value;
                }
            }
        }

        return $products;
    }
    
}
