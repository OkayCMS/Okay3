<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Entities\CategoriesEntity;

class CategoryAdmin extends IndexAdmin
{
    
    public function fetch(
        CategoriesEntity $categoriesEntity,
        Image $imageCore
    ) {
        $category = new \stdClass;
        /*Принимаем данные о категории*/
        if ($this->request->method('post')) {
            $category->id = $this->request->post('id', 'integer');
            $category->parent_id = $this->request->post('parent_id', 'integer');
            $category->name = $this->request->post('name');
            $category->name_h1 = $this->request->post('name_h1');
            $category->visible = $this->request->post('visible', 'boolean');
            
            $category->url = trim($this->request->post('url', 'string'));
            $category->meta_title = $this->request->post('meta_title');
            $category->meta_keywords = $this->request->post('meta_keywords');
            $category->meta_description = $this->request->post('meta_description');

            $category->annotation = $this->request->post('annotation');
            $category->description = $this->request->post('description');
            
            // Не допустить одинаковые URL разделов.
            if (($c = $categoriesEntity->get($category->url)) && $c->id!=$category->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif (empty($category->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (empty($category->url)) {
                $this->design->assign('message_error', 'empty_url');
            } elseif (substr($category->url, -1) == '-' || substr($category->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/обновление категории*/
                if (empty($category->id)) {
                    $category->id = $categoriesEntity->add($category);
                    $this->design->assign('message_success', 'added');
                } else {
                    $categoriesEntity->update($category->id, $category);
                    $this->design->assign('message_success', 'updated');
                }
                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $imageCore->deleteImage(
                        $category->id,
                        'image',
                        CategoriesEntity::class,
                        $this->config->original_categories_dir,
                        $this->config->resized_categories_dir
                    );
                }
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->original_categories_dir))) {
                    $imageCore->deleteImage(
                        $category->id,
                        'image',
                        CategoriesEntity::class,
                        $this->config->original_categories_dir,
                        $this->config->resized_categories_dir
                    );

                    $categoriesEntity->update($category->id, ['image'=>$filename]);
                }
                $category = $categoriesEntity->get(intval($category->id));
            }
        } else {
            $category->id = $this->request->get('id', 'integer');
            $category = $categoriesEntity->get($category->id);
        }
        /*Выборка дерева категорий*/
        $categories = $categoriesEntity->getCategoriesTree();
        
        $this->design->assign('category', $category);
        $this->design->assign('categories', $categories);
        $this->response->setContent($this->design->fetch('category.tpl'));
    }

}
