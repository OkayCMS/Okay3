<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Entities\BannersEntity;
use Okay\Entities\BannersImagesEntity;
use Okay\Entities\ImagesEntity;

class BannersImageAdmin extends IndexAdmin
{
    
    public function fetch(
        BannersImagesEntity $bannersImagesEntity,
        BannersEntity $bannersEntity,
        ImagesEntity $imagesEntity,
        Image $imageCore
    ) {
        $bannersImage = new \stdClass;
        /*Принимаем данные о слайде*/
        if ($this->request->method('post')) {
            $bannersImage->id = $this->request->post('id', 'integer');
            $bannersImage->name = $this->request->post('name');
            $bannersImage->visible = $this->request->post('visible', 'boolean');
            $bannersImage->banner_id = $this->request->post('banner_id', 'integer');
            
            $bannersImage->url = $this->request->post('url');
            $bannersImage->title = $this->request->post('title');
            $bannersImage->alt = $this->request->post('alt');
            $bannersImage->description = $this->request->post('description');
            
            /*Добавляем/удаляем слайд*/
            if (empty($bannersImage->id)) {
                $bannersImage->id = $bannersImagesEntity->add($bannersImage);
                $this->design->assign('message_success', 'added');
            } else {
                $bannersImagesEntity->update($bannersImage->id, $bannersImage);
                $this->design->assign('message_success', 'updated');
            }
            
            // Удаление изображения
            if ($this->request->post('delete_image')) {
                $imageCore->deleteImage(
                    $bannersImage->id,
                    'image',
                    BannersImagesEntity::class,
                    $this->config->banners_images_dir,
                    $this->config->resized_banners_images_dir
                );
            }

            // Загрузка изображения
            $image = $this->request->files('image');
            if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->banners_images_dir))) {
                $imageCore->deleteImage(
                    $bannersImage->id,
                    'image',
                    BannersImagesEntity::class,
                    $this->config->banners_images_dir,
                    $this->config->resized_banners_images_dir
                );
                $bannersImagesEntity->update($bannersImage->id, ['image'=>$filename]);
            }
            $bannersImage = $bannersImagesEntity->get((int)$bannersImage->id);
        } else {
            $bannersImage->id = $this->request->get('id', 'integer');
            $bannersImage = $bannersImagesEntity->get($bannersImage->id);
        }
        
        $banners = $bannersEntity->find();
        
        $this->design->assign('banners_image', $bannersImage);
        $this->design->assign('banners', $banners);

        $this->response->setContent($this->design->fetch('banners_image.tpl'));
    }
    
}
