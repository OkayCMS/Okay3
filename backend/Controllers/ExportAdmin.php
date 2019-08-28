<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;

class ExportAdmin extends IndexAdmin
{
    
    private $exportFilesDir = 'backend/files/export/';

    /*Экспорт товаров*/
    public function fetch(BrandsEntity $brandsEntity, CategoriesEntity $categoriesEntity){
        $this->design->assign('export_files_dir', $this->exportFilesDir);
        if (!is_writable($this->exportFilesDir)) {
            $this->design->assign('message_error', 'no_permission');
        }
        
        $brandsCount = $brandsEntity->count();
        $this->design->assign('brands', $brandsEntity->find(['limit'=>$brandsCount]));
        $this->design->assign('categories', $categoriesEntity->getCategoriesTree());

        $this->response->setContent($this->design->fetch('export.tpl'));
    }
    
}
