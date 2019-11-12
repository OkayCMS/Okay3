<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendBrandsHelper;
use Okay\Admin\Requests\BackendBrandsRequest;

class BrandsAdmin extends IndexAdmin
{
    
    public function fetch(
        BackendBrandsHelper  $backendBrandsHelper,
        BackendBrandsRequest $brandsRequest
    ){
        $filter = $backendBrandsHelper->buildFilter();
        $this->design->assign('current_limit', $filter['limit']);

        // Обработка действий
        if ($this->request->method('post')) {
            // Сортировка
            $positions = $brandsRequest->postPositions();
            $backendBrandsHelper->sortPositions($positions);

            // Действия с выбранными
            $ids = $brandsRequest->postCheck();
            switch ($brandsRequest->postAction()) {
                case 'delete': {
                    $backendBrandsHelper->delete($ids);
                    break;
                }
                case 'move_to_page': {
                    $targetPage = $this->request->post('target_page', 'integer');
                    $backendBrandsHelper->moveToPage($ids, $targetPage, $filter);
                    break;
                }
            }
        }

        $brandsCount               = $backendBrandsHelper->countBrands($filter);
        list($filter, $pagesCount) = $backendBrandsHelper->makePagination($brandsCount, $filter);
        $brands                    = $backendBrandsHelper->findBrands($filter);

        $this->design->assign('brands_count', $brandsCount);
        $this->design->assign('pages_count',  $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('brands',       $brands);
        $this->response->setContent($this->design->fetch('brands.tpl'));
    }
}
