<?php


namespace Okay\Modules\OkayCMS\GoogleMerchant\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Logic\ProductsLogic;
use Okay\Modules\OkayCMS\GoogleMerchant\Init\Init;

class GoogleMerchantAdmin extends IndexAdmin
{

    public function fetch(
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        ProductsEntity $productsEntity,
        ProductsLogic $productsLogic,
        QueryFactory $queryFactory,
        Database $database,
        FeaturesEntity $featuresEntity
    ) {
        if ($this->request->post('add_all_categories')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set(Init::TO_FEED_FIELD, 1)
            );
        } elseif($this->request->post('remove_all_categories')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set(Init::TO_FEED_FIELD, 0)
            );
        } elseif ($this->request->post('add_all_brands')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set(Init::TO_FEED_FIELD, 1)
            );
        } elseif($this->request->post('remove_all_brands')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set(Init::TO_FEED_FIELD, 0)
            );
        } elseif ($this->request->method('post')) {
            $categoriesToXml   = $this->request->post('categories');
            $brandsToXml       = $this->request->post('brands');
            $productsToXml     = $this->request->post('related_products');
            $productsNotToXml  = $this->request->post('not_related_products');

            $this->settings->set('okaycms__google_merchant__company', $this->request->post('okaycms__google_merchant__company'));
            $this->settings->set('okaycms__google_merchant__color', $this->request->post('okaycms__google_merchant__color'));

            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set(Init::TO_FEED_FIELD, 0)
            );
            if (!empty($categoriesToXml)) {
                $categoriesEntity->update($categoriesToXml, [Init::TO_FEED_FIELD => 1]);
            }

            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set(Init::TO_FEED_FIELD, 0)
            );
            if (!empty($brandsToXml)) {
                $brandsEntity->update($brandsToXml, [Init::TO_FEED_FIELD => 1]);
            }

            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(ProductsEntity::getTable())->set(Init::TO_FEED_FIELD, 0)
            );
            if (!empty($productsToXml)) {
                $productsEntity->update($productsToXml, [Init::TO_FEED_FIELD => 1]);
            }

            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(ProductsEntity::getTable())->set(Init::NOT_TO_FEED_FIELD, 0)
            );
            if (!empty($productsNotToXml)) {
                $productsEntity->update($productsNotToXml, [Init::NOT_TO_FEED_FIELD => 1]);
            }

            $this->updateCheckboxes();
        }

        $allCategories       = $categoriesEntity->getCategoriesTree();
        $allBrands           = $brandsEntity->find(['limit' => $brandsEntity->count()]);
        $relatedProducts     = $productsLogic->getProductList([Init::TO_FEED_FIELD => 1]);
        $notRelatedProducts  = $productsLogic->getProductList([Init::NOT_TO_FEED_FIELD => 1]);
        $allFeatures         = $featuresEntity->find();

        $this->design->assign('categories', $allCategories);
        $this->design->assign('brands', $allBrands);
        $this->design->assign('related_products', $relatedProducts);
        $this->design->assign('not_related_products', $notRelatedProducts);
        $this->design->assign('features', $allFeatures);

        $this->response->setContent($this->design->fetch('google_merchant.tpl'));
    }

    private function updateCheckboxes()
    {
        $this->updateSingleCheckbox('okaycms__google_merchant__upload_non_exists_products_to_google');
        $this->updateSingleCheckbox('okaycms__google_merchant__use_full_description_to_google');
        $this->updateSingleCheckbox('okaycms__google_merchant__has_manufacturer_warranty');
        $this->updateSingleCheckbox('okaycms__google_merchant__no_export_without_price');
        $this->updateSingleCheckbox('okaycms__google_merchant__pickup');
        $this->updateSingleCheckbox('okaycms__google_merchant__store');
        $this->updateSingleCheckbox('okaycms__google_merchant__delivery_disallow');
        $this->updateSingleCheckbox('okaycms__google_merchant__adult');
        $this->updateSingleCheckbox('okaycms__google_merchant__use_variant_name_like_size');
    }

    private function updateSingleCheckbox($name)
    {
        if ($this->request->post($name, 'integer')) {
            $this->settings->set($name, 1);
        } else {
            $this->settings->set($name, 0);
        }
    }
}
