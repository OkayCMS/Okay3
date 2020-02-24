<?php


namespace Okay\Modules\OkayCMS\GoogleMerchant\Controllers;


use Okay\Controllers\AbstractController;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Helpers\ProductsHelper;
use Okay\Modules\OkayCMS\GoogleMerchant\Init\Init;

class GoogleMerchantController extends AbstractController
{
    
    public function render(
        ProductsEntity $productsEntity,
        ProductsHelper $productsHelper,
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
        $categoriesToGoogle = $db->results();
        $uploadCategories = [];

        $uploadCategories = $this->addAllChildrenToList($categoriesEntity, $categoriesToGoogle, $uploadCategories);

        $filter['visible'] = 1;
        $filter['okaycms__google_merchant__only'] = $uploadCategories;
        
        if ($this->settings->get('okaycms__google_merchant__upload_only_available_to_google')) {
            $filter['in_stock'] = 1;
        }

        $productsIds = $productsEntity->cols(['id'])->find($filter);

        if (!empty($productsIds)) {
            $allBrands = $brandsEntity->mappedBy('id')->find(['product_id' => $productsIds]);
            $this->design->assign('all_brands', $allBrands);
        }

        $this->design->assign('all_categories', $categoriesEntity->find());

        $this->response->setContentType(RESPONSE_XML);

        $this->response->sendHeaders();
        $this->response->sendStream(pack('CCC', 0xef, 0xbb, 0xbf));
        $this->response->sendStream($this->design->fetch('feed_head.xml.tpl'));

        // Выдаём товары пачками
        $itemsPerPage = $this->settings->get('okaycms__google_merchant__products_per_page');
        $itemsPerPage = !empty($itemsPerPage) ? $itemsPerPage : 1000;
        $productsCount = $productsEntity->count($filter);

        $pages = ceil($productsCount/$itemsPerPage);
        $pages = max(1, $pages);

        $variantsFilter = $filter;
        
        // Проходимся пагинацией, выводим товары пачками
        for ($page = 1; $page <= $pages; $page++) {
            $filter['limit'] = $itemsPerPage;
            $filter['page'] = $page;

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
            
            $products = $productsHelper->attachVariants($products, $variantsFilter);
            $products = $productsHelper->attachImages($products);
            $products = $this->sliceImagesByProduct($products, 10);
            $products = $productsHelper->attachFeatures($products);

            $allCategoriesMappedById = $categoriesEntity->mappedBy('id')->find();
            $products = $this->attachProductType($products, $allCategoriesMappedById);
            $products = $this->attachColorToProductsByFeatureId($products, $this->settings->get('okaycms__google_merchant__color'));

            $this->design->assign('products', $products);
            $this->response->sendStream($this->design->fetch('feed_offers.xml.tpl'));
        }

        $this->response->sendStream($this->design->fetch('feed_footer.xml.tpl'));
        
    }

    private function attachProductType($products, $allCategoriesMappedById)
    {
        foreach($products as $id => $product) {
            $mainCategoryId = $product->main_category_id;

            if (empty($allCategoriesMappedById[$mainCategoryId])) {
                continue;
            }

            $categoryPath = $allCategoriesMappedById[$mainCategoryId]->path;
            $products[$id]->product_type = $this->buildProductType($categoryPath);
        }

        return $products;
    }

    private function buildProductType($categoryPath)
    {
        $productType = '';

        foreach($categoryPath as $category) {
            $productType .= $category->name.' > ';
        }

        return substr($productType, 0, -3);
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
                $products[$id]->images = array_splice($product->images, 0, $amountPhotosByProduct);
            }
        }

        return $products;
    }

    private function attachColorToProductsByFeatureId($products, $colorFeatureId)
    {
        foreach($products as $id => $product) {
            if (empty($product->features)) {
                continue;
            }

            foreach($product->features as $feature) {
                if ($feature->id != $colorFeatureId) {
                     continue;
                }

                $products[$id]->color = reset($feature->values)->value;
            }
        }

        return $products;
    }
}
