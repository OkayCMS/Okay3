<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\BannersEntity;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\PagesEntity;

class BannersAdmin extends IndexAdmin
{
    public function fetch(
        BannersEntity $bannersEntity,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        PagesEntity $pagesEntity
    ) {
        /*Принимаем выбранные группы баннеров*/
        if ($this->request->method('post')) {
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        /*Выключаем группы баннеров*/
                        $bannersEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включаем группы банннеров*/
                        $bannersEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удаляем группы баннеров*/
                        $bannersEntity->delete($ids);
                        break;
                    }
                }
            }
            
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $bannersEntity->update($ids[$i], ['position'=>$position]);
            }
        }
        
        $banners = $bannersEntity->find();
        
        if ($banners) {
            $categories = $categoriesEntity->find();
            $brands     = $brandsEntity->find();
            $pages      = $pagesEntity->find();
            foreach ($banners as $banner){
                $banner->category_selected  = explode(",",$banner->categories);//Создаем массив категорий
                $banner->brand_selected     = explode(",",$banner->brands);//Создаем массив брендов
                $banner->page_selected      = explode(",",$banner->pages);//Создаем массив страниц
                foreach ($brands as $b){
                    if (in_array($b->id, $banner->brand_selected)){
                        $banner->brands_show[] = $b;
                    }
                }
                foreach ($categories as $c){
                    if (in_array($c->id, $banner->category_selected)){
                        $banner->category_show[] = $c;
                    }
                }
                foreach ($pages as $p){
                    if (in_array($p->id, $banner->page_selected)){
                        $banner->page_show[] = $p;
                    }
                }
            }
        }
        $this->design->assign('banners', $banners);

        $this->response->setContent($this->design->fetch('banners.tpl'));
    }
    
}
