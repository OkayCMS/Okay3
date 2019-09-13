<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\BannersEntity;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\PagesEntity;

class BannerAdmin extends IndexAdmin
{
    
    public function fetch(
        BannersEntity $bannersEntity,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        PagesEntity $pagesEntity
    ) {
        $categories = $categoriesEntity->getCategoriesTree();
        $brands     = $brandsEntity->find();
        $pages      = $pagesEntity->find();
        
        $banner = new \stdClass;
        /*Принимаем данные о группе баннеров*/
        if ($this->request->method('POST')) {
            $banner->id = $this->request->post('id', 'integer');
            $banner->group_id = trim($this->request->post('group_id', 'string'));
            $banner->name = $this->request->post('name');
            $banner->visible = $this->request->post('visible', 'boolean');
            $banner->show_all_pages = (int)$this->request->post('show_all_pages');
            $banner->categories = implode(",",$this->request->post('categories'));
            $banner->brands = implode(",",$this->request->post('brands'));
            $banner->pages = implode(",",$this->request->post('pages'));

            $banner->group_id = preg_replace("/[\s]+/ui", '', $banner->group_id);
            $banner->group_id = strtolower(preg_replace("/[^0-9a-z_]+/ui", '', $banner->group_id));

            if (($b = $bannersEntity->get((string)$banner->group_id)) && $b->id!=$banner->id) {
                $this->design->assign('message_error', 'group_id_exists');
            } elseif (empty($banner->group_id)) {
                $this->design->assign('message_error', 'empty_group_id');
            } else {
                /*Добавляем/обновляем группу баннеров*/
                if (empty($banner->id)) {
                    $banner->id = $bannersEntity->add($banner);
                    $banner = $bannersEntity->get($banner->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    $bannersEntity->update($banner->id, $banner);
                    $banner = $bannersEntity->get($banner->id);
                    $this->design->assign('message_success', 'updated');
                }
            }
            $banner->category_selected  = $this->request->post('categories');
            $banner->brand_selected     = $this->request->post('brands');
            $banner->page_selected      = $this->request->post('pages');
        } else {
            /*Отображение группы баннеров*/
            $id = $this->request->get('id', 'integer');
            if(!empty($id)) {
                $banner = $bannersEntity->get(intval($id));
                $banner->category_selected  = explode(",",$banner->categories);//Создаем массив категорий
                $banner->brand_selected     = explode(",",$banner->brands);//Создаем массив брендов
                $banner->page_selected      = explode(",",$banner->pages);//Создаем массив страниц
            } else {
                $banner->visible = 1;
            }
        }
        
        $this->design->assign('banner', $banner);
        $this->design->assign('categories', $categories);
        $this->design->assign('brands',     $brands);
        $this->design->assign('pages',      $pages);

        $this->response->setContent($this->design->fetch('banner.tpl'));
    }
    
}
