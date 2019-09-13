<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Entities\BlogEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\ProductsEntity;

class PostAdmin extends IndexAdmin
{
    
    public function fetch(BlogEntity $blogEntity, ProductsEntity $productsEntity, ImagesEntity $imagesEntity, Image $imageCore)
    {
        $relatedProducts = [];
        
        /*Прием информации о записи*/
        if ($this->request->method('post')) {
            $post = new \stdClass;
            $post->id   = $this->request->post('id', 'integer');
            $post->name = $this->request->post('name');
            $post->date = date('Y-m-d H:i:s', strtotime($this->request->post('date')));
            $post->url  = trim($this->request->post('url', 'string'));
            $post->visible      = $this->request->post('visible', 'integer');
            $post->type_post    = $this->request->post('type_post');
            $post->meta_title       = $this->request->post('meta_title');
            $post->meta_keywords    = $this->request->post('meta_keywords');
            $post->meta_description = $this->request->post('meta_description');
            
            $post->annotation  = $this->request->post('annotation');
            $post->description = $this->request->post('description');

            // Связанные товары
            if (is_array($this->request->post('related_products'))) {
                $rp = [];
                foreach ($this->request->post('related_products') as $p) {
                    $rp[$p] = new \stdClass;
                    $rp[$p]->post_id = $post->id;
                    $rp[$p]->related_id = $p;
                }
                $relatedProducts = $rp;
            }
            
            // Не допустить одинаковые URL записей.
            if (($a = $blogEntity->get((int)$post->url)) && $a->id!=$post->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif (empty($post->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (empty($post->url)) {
                $this->design->assign('message_error', 'empty_url');
            } elseif (substr($post->url, -1) == '-' || substr($post->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/Обновление записи*/
                if (empty($post->id)) {
                    $post->id = $blogEntity->add($post);
                    $this->design->assign('message_success', 'added');
                } else {
                    $blogEntity->update($post->id, $post);
                    $this->design->assign('message_success', 'updated');
                }
                
                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $imageCore->deleteImage(
                        $post->id,
                        'image',
                        BlogEntity::class,
                        $this->config->original_blog_dir,
                        $this->config->resized_blog_dir
                    );
                }
                
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->original_blog_dir))) {
                    $imageCore->deleteImage(
                        $post->id,
                        'image',
                        BlogEntity::class,
                        $this->config->original_blog_dir,
                        $this->config->resized_blog_dir
                    );
                    $blogEntity->update($post->id, ['image'=>$filename]);
                }
                // Связанные товары
                $blogEntity->deleteRelatedProduct($post->id);
                if (is_array($relatedProducts)) {
                    $pos = 0;
                    foreach ($relatedProducts  as $i=>$relatedProduct) {
                        $blogEntity->addRelatedProduct($post->id, $relatedProduct->related_id, $pos++);
                    }
                }
                $post = $blogEntity->get($post->id);
            }
        } else {
            $postId = $this->request->get('id', 'integer');
            $post = $blogEntity->get($postId);
            // Связанные товары
            if (!empty($post->id)) {
                $relatedProducts = $blogEntity->getRelatedProducts(['post_id' => $post->id]);
            }
        }
        
        if (empty($post)) {
            $post = new \stdClass;
            $post->date = date($this->settings->date_format, time());
            $post->visible = 1;
        }

        /*Связанные товары записи*/
        if (!empty($relatedProducts)) {
            $imagesIds = [];
            $rProducts = [];
            foreach ($relatedProducts as &$r_p) {
                $rProducts[$r_p->related_id] = &$r_p;
            }
            $tempProducts = $productsEntity->find(['id'=>array_keys($rProducts)]);
            foreach ($tempProducts as $tempProduct) {
                $rProducts[$tempProduct->id] = $tempProduct;
                $imagesIds[] = $tempProduct->main_image_id;
            }

            if (!empty($imagesIds)) {
                $images = $imagesEntity->find(['id'=>$imagesIds]);
                foreach ($images as $image) {
                    if (isset($rProducts[$image->product_id])) {
                        $rProducts[$image->product_id]->image = $image;
                    }
                }
            }
        }

        $this->design->assign('related_products', $relatedProducts);
        $this->design->assign('post', $post);
        $this->response->setContent($this->design->fetch('post.tpl'));
    }
    
}
