<?php


namespace Okay\Modules\OkayCMS\Banners\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\OkayCMS\Banners\Entities\BannersEntity;
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
            $banner->name = $this->request->post('name');
            $banner->visible = $this->request->post('visible', 'boolean');
            $banner->show_all_pages = (int)$this->request->post('show_all_pages');
            $banner->categories = implode(",",$this->request->post('categories'));
            $banner->brands = implode(",",$this->request->post('brands'));
            $banner->pages = implode(",",$this->request->post('pages'));
            $banner->settings = serialize($this->request->post('settings'));

            if ($this->request->post('use_individual_shortcode', 'boolean') && ($individualShortCode = $this->request->post('individual_shortcode'))) {
                $individualShortCode = preg_replace('~^{*\$*([\w]+?)}*$~', '$1', $individualShortCode);
                $banner->individual_shortcode = $individualShortCode;
            }

            if (!empty($banner->individual_shortcode) && ($b = $bannersEntity->get($banner->individual_shortcode)) && $b->id!=$banner->id) {
                $this->design->assign('message_error', 'shortcode_exists');
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

                if ($this->request->post('use_individual_shortcode', 'boolean')) {
                    if (empty($individualShortCode)) {
                        $banner->individual_shortcode = 'banner_shortcode_group_' . $banner->id;
                    }
                } else {
                    $banner->individual_shortcode = '';
                }
                $bannersEntity->update($banner->id, $banner);
            }
            $banner->category_selected = $this->request->post('categories');
            $banner->brand_selected = $this->request->post('brands');
            $banner->page_selected = $this->request->post('pages');
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
        
        $this->design->assign('banner',     $banner);
        $this->design->assign('categories', $categories);
        $this->design->assign('brands',     $brands);
        $this->design->assign('pages',      $pages);

        if (!empty($banner->settings)) {
            $banner->settings = unserialize($banner->settings);
        }
        
        $this->response->setContent($this->design->fetch('banner.tpl'));
    }
    
}
