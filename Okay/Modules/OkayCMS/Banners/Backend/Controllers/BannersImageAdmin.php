<?php


namespace Okay\Modules\OkayCMS\Banners\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\OkayCMS\Banners\Entities\BannersEntity;
use Okay\Modules\OkayCMS\Banners\Helpers\BannersImagesHelper;
use Okay\Modules\OkayCMS\Banners\Requests\BannersImagesRequest;

class BannersImageAdmin extends IndexAdmin
{
    
    public function fetch(
        BannersEntity $bannersEntity,
        BannersImagesRequest $bannersImagesRequest,
        BannersImagesHelper $bannersImagesHelper
    ) {
        /*Принимаем данные о слайде*/
        if ($this->request->method('post')) {
            $bannersImage = $bannersImagesRequest->postBannerImage();
            
            /*Добавляем/удаляем слайд*/
            if (empty($bannersImage->id)) {
                $preparedBannersImage = $bannersImagesHelper->prepareAdd($bannersImage);
                $bannersImage->id     = $bannersImagesHelper->add($preparedBannersImage);
                $this->design->assign('message_success', 'added');
            } else {
                $preparedBannersImage = $bannersImagesHelper->prepareUpdate($bannersImage);
                $bannersImagesHelper->update($preparedBannersImage->id, $preparedBannersImage);
                $this->design->assign('message_success', 'updated');
            }

            // Картинка
            if ($bannersImagesRequest->postDeleteImage()) {
                $bannersImagesHelper->deleteImage($bannersImage);
            }

            if ($image = $bannersImagesRequest->fileImage()) {
                $bannersImagesHelper->uploadImage($image, $bannersImage);
            }

            $bannersImage = $bannersImagesHelper->getBannerImage((int)$bannersImage->id);
        } else {
            $bannersImageId = $this->request->get('id', 'integer');
            $bannersImage   = $bannersImagesHelper->getBannerImage($bannersImageId);
        }
        
        $banners = $bannersEntity->find();//todo

        $this->design->assign('banners_image', $bannersImage);
        $this->design->assign('banners', $banners);

        $this->response->setContent($this->design->fetch('banners_image.tpl'));
    }
    
}
