<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Core\Translit;
use Okay\Entities\BrandsEntity;
use Okay\Entities\ImagesEntity;

class BrandAdmin extends IndexAdmin
{
    
    public function fetch(
        BrandsEntity $brandsEntity,
        ImagesEntity $imagesEntity,
        Image $imageCore,
        Translit $translit
    ) {
        $brand = new \stdClass;
        /*Принимаем инофмрацию о бренде*/
        if ($this->request->method('post')) {
            $brand->id = $this->request->post('id', 'integer');
            $brand->name = $this->request->post('name');
            $brand->annotation = $this->request->post('annotation');
            $brand->description = $this->request->post('description');
            $brand->visible = $this->request->post('visible', 'boolean');
            
            $brand->url = trim($this->request->post('url', 'string'));
            $brand->meta_title = $this->request->post('meta_title');
            $brand->meta_keywords = $this->request->post('meta_keywords');
            $brand->meta_description = $this->request->post('meta_description');
            
            $brand->url = preg_replace("/[\s]+/ui", '', $brand->url);
            $brand->url = strtolower(preg_replace("/[^0-9a-z]+/ui", '', $brand->url));
            if (empty($brand->url)) {
                $brand->url = $translit->translitAlpha($brand->name);
            }
            
            // Не допустить одинаковые URL разделов.
            if (($b = $brandsEntity->get($brand->url)) && $b->id!=$brand->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif(empty($brand->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(empty($brand->url)) {
                $this->design->assign('message_error', 'empty_url');
            } else {
                /*Добавляем/обновляем бренд*/
                if (empty($brand->id)) {
                    $brand->id = $brandsEntity->add($brand);
                    $this->design->assign('message_success', 'added');
                } else {
                    $brandsEntity->update($brand->id, $brand);
                    $this->design->assign('message_success', 'updated');
                }
                
                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $imageCore->deleteImage(
                        $brand->id,
                        'image',
                        BrandsEntity::class,
                        $this->config->original_brands_dir,
                        $this->config->resized_brands_dir
                    );
                }
                
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->original_brands_dir))) {
                    $imageCore->deleteImage(
                        $brand->id,
                        'image',
                        BrandsEntity::class,
                        $this->config->original_brands_dir,
                        $this->config->resized_brands_dir
                    );
                    $brandsEntity->update($brand->id, ['image'=>$filename]);
                }
                $brand = $brandsEntity->get((int)$brand->id);
            }
        } else {
            $brand->id = $this->request->get('id', 'integer');
            $brand = $brandsEntity->get($brand->id);
        }
        
        $this->design->assign('brand', $brand);
        $this->response->setContent($this->design->fetch('brand.tpl'));
    }
    
}
