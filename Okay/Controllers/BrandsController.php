<?php


namespace Okay\Controllers;


use Okay\Entities\BrandsEntity;

class BrandsController extends AbstractController
{

    /*Отображение страницы всех брендов*/
    public function render(BrandsEntity $brands)
    {
        /*Выбираем все бренды*/
        $brandsList = $brands->find(['visible'=>1]);
        
        $this->design->assign('brands', $brandsList);
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        }

        $this->response->setContent($this->design->fetch('brands.tpl'));
    }
    
}
