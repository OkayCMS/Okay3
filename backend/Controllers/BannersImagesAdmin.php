<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\BannersEntity;
use Okay\Entities\BannersImagesEntity;

class BannersImagesAdmin extends IndexAdmin
{
    
    public function fetch(BannersImagesEntity $bannersImagesEntity, BannersEntity $bannersEntity)
    {
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        
        $filter['limit'] = 20;
        
        // Баннера
        $banners = $bannersEntity->find();
        $this->design->assign('banners', $banners);
        
        // Текущий баннер
        $bannerId = $this->request->get('banner_id', 'integer');
        if ($bannerId && ($banner = $bannersEntity->get($bannerId))) {
            $filter['banner_id'] = $banner->id;
        }
        
        // Текущий фильтр
        if ($f = $this->request->get('filter', 'string'))
        {
            if ($f == 'visible') {
                $filter['visible'] = 1;
            } elseif ($f == 'hidden') {
                $filter['visible'] = 0;
            }
            $this->design->assign('filter', $f);
        }
        
        // Обработка действий
        if ($this->request->method('post')) {
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            $positions = array_reverse($positions);
            foreach($positions as $i=>$position) {
                $bannersImagesEntity->update($ids[$i], array('position'=>$position));
            }
            
            // Смена группы
            $imageBanners = $this->request->post('image_banners');
            foreach($imageBanners as $i=>$imageBanner) {
                $bannersImagesEntity->update($i, array('banner_id'=>$imageBanner));
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (!empty($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        $bannersImagesEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        $bannersImagesEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        $bannersImagesEntity->delete($ids);
                        break;
                    }
                    case 'move_to_banner': {
                        $bannerId = $this->request->post('target_banner', 'integer');
                        $filter['page'] = 1;
                        $banner = $bannersEntity->get($bannerId);
                        $filter['banner_id'] = $banner->id;
                        
                        $bannersImagesEntity->update($ids, ['banner_id'=>$banner->id]);
                        break;
                    }
                }
            }
        }
        
        // Отображение
        if (isset($banner)) {
            $this->design->assign('banner', $banner);
        }
        
        $bannersImagesCount = $bannersImagesEntity->count($filter);
        
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $bannersImagesCount;
        }
        
        if ($filter['limit']>0) {
            $pagesCount = ceil($bannersImagesCount/$filter['limit']);
        } else {
            $pagesCount = 0;
        }
        
        $filter['page'] = min($filter['page'], $pagesCount);
        $this->design->assign('banners_images_count', $bannersImagesCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        
        $bannersImages = [];
        foreach($bannersImagesEntity->find($filter) as $p) {
            $bannersImages[$p->id] = $p;
        }
        
        $this->design->assign('banners_images', $bannersImages);
        
        $this->response->setContent($this->design->fetch('banners_images.tpl'));
    }
    
}
