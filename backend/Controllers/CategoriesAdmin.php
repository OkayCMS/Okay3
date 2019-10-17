<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Requests\CategoriesRequest;
use Okay\Admin\Helpers\BackendCategoriesHelper;

class CategoriesAdmin extends IndexAdmin
{
    
    public function fetch(
        BackendCategoriesHelper $backendCategoriesHelper,
        CategoriesRequest       $categoriesRequest
    ) {
        if ($this->request->method('post')) {

            // Действия с выбранными
            $ids = $categoriesRequest->postCheckedIds();
            if (is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        $backendCategoriesHelper->disable($ids);
                        break;
                    }
                    case 'enable': {
                        $backendCategoriesHelper->enable($ids);
                        break;
                    }
                    case 'delete': {
                        $backendCategoriesHelper->delete($ids);
                        break;
                    }
                }
            }
            
            // Сортировка
            $positions = $categoriesRequest->postPositions();
            list($ids, $positions) = $backendCategoriesHelper->sortPositions($positions);
            $backendCategoriesHelper->updatePositions($ids, $positions);
        }

        // Категории
        $categories      = $backendCategoriesHelper->getCategoriesTree();
        $categoriesCount = $backendCategoriesHelper->countAllCategories();

        $this->design->assign('categoriesCount', $categoriesCount);
        $this->design->assign('categories',      $categories);
        $this->response->setContent($this->design->fetch('categories.tpl'));
    }
    
}
