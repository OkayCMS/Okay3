<?php


namespace Okay\Modules\OkayCMS\YandexXMLVendorModel\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Logic\ProductsLogic;
use Okay\Modules\OkayCMS\YandexXML\Init\Init;

class YandexXmlAdmin extends IndexAdmin
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

            $this->settings->set('okaycms__yandex_xml_vendor_model__company', $this->request->post('okaycms__yandex_xml_vendor_model__company'));
            $this->settings->set('okaycms__yandex_xml_vendor_model__country_of_origin', $this->request->post('okaycms__yandex_xml_vendor_model__country_of_origin'));
            $salesNotes = $this->request->post('okaycms__yandex_xml_vendor_model__sales_notes');
            $this->settings->set('okaycms__yandex_xml_vendor_model__sales_notes', substr($salesNotes, 0, 50));

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

        $this->response->setContent($this->design->fetch('yandex_xml.tpl'));
    }

    private function updateCheckboxes()
    {
        if ($this->request->post('okaycms__yandex_xml_vendor_model__upload_only_available_to_yandex', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__upload_only_available_to_yandex', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__upload_only_available_to_yandex', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__use_full_description_to_yandex', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__use_full_description_to_yandex', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__use_full_description_to_yandex', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__has_manufacturer_warranty', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__no_export_without_price', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__no_export_without_price', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__no_export_without_price', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__pickup', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__pickup', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__pickup', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__store', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__store', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__store', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__delivery_disallow', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__delivery_disallow', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__delivery_disallow', 0);
        }

        if ($this->request->post('okaycms__yandex_xml_vendor_model__adult', 'integer')) {
            $this->settings->set('okaycms__yandex_xml_vendor_model__adult', 1);
        } else {
            $this->settings->set('okaycms__yandex_xml_vendor_model__adult', 0);
        }
    }
}
