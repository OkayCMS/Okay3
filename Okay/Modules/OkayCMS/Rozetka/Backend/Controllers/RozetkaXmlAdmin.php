<?php


namespace Okay\Modules\OkayCMS\Rozetka\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Logic\ProductsLogic;

class RozetkaXmlAdmin extends IndexAdmin
{

    public function fetch(
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        ProductsEntity $productsEntity,
        ProductsLogic $productsLogic,
        QueryFactory $queryFactory,
        Database $database
    ) {
        if ($this->request->post('add_all_categories')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set('to_rozetka', 1)
            );
        } elseif($this->request->post('remove_all_categories')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set('to_rozetka', 0)
            );
        } elseif ($this->request->post('add_all_brands')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set('to_rozetka', 1)
            );
        } elseif($this->request->post('remove_all_brands')) {
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set('to_rozetka', 0)
            );
        } elseif ($this->request->method('post')) {
            $categoriesToXml   = $this->request->post('categories');
            $brandsToXml       = $this->request->post('brands');
            $productsToXml     = $this->request->post('related_products');
            $productsNotToXml  = $this->request->post('not_related_products');

            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(CategoriesEntity::getTable())->set('to_rozetka', 0)
            );
            if (!empty($categoriesToXml)) {
                $categoriesEntity->update($categoriesToXml, ['to_rozetka' => 1]);
            }
            
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(BrandsEntity::getTable())->set('to_rozetka', 0)
            );
            if (!empty($brandsToXml)) {
                $brandsEntity->update($brandsToXml, ['to_rozetka' => 1]);
            }
            
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(ProductsEntity::getTable())->set('to_rozetka', 0)
            );
            if (!empty($productsToXml)) {
                $productsEntity->update($productsToXml, ['to_rozetka' => 1]);
            }
            
            $update = $queryFactory->newUpdate();
            $database->query(
                $update->table(ProductsEntity::getTable())->set('not_to_rozetka', 0)
            );
            if (!empty($productsNotToXml)) {
                $productsEntity->update($productsNotToXml, ['not_to_rozetka' => 1]);
            }
            
            $this->updateCheckboxes();
        }

        $allCategories       = $categoriesEntity->getCategoriesTree();
        $allBrands           = $brandsEntity->find(['limit' => $brandsEntity->count()]);
        $relatedProducts     = $productsLogic->getProductList(['to_rozetka' => 1]);
        $notRelatedProducts  = $productsLogic->getProductList(['not_to_rozetka' => 1]);
        
        $this->design->assign('categories', $allCategories);
        $this->design->assign('brands', $allBrands);
        $this->design->assign('related_products', $relatedProducts);
        $this->design->assign('not_related_products', $notRelatedProducts);

        $this->response->setContent($this->design->fetch('rozetka_xml.tpl'));
    }

    private function updateCheckboxes()
    {
        if ($this->request->post('upload_non_available', 'integer')) {
            $this->settings->set('upload_only_available_to_rozetka', 1);
        } else {
            $this->settings->set('upload_only_available_to_rozetka', 0);
        }

        if ($this->request->post('full_description', 'integer')) {
            $this->settings->set('use_full_description_in_upload_rozetka', 1);
        } else {
            $this->settings->set('use_full_description_in_upload_rozetka', 0);
        }
    }
    
}
