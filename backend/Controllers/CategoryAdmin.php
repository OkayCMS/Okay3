<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\CategoriesEntity;
use Okay\Admin\Requests\CategoriesRequest;
use Okay\Admin\Helpers\BackendCategoriesHelper;

class CategoryAdmin extends IndexAdmin
{

    public function fetch(
        CategoriesEntity        $categoriesEntity,
        CategoriesRequest       $categoriesRequest,
        BackendCategoriesHelper $backendCategoriesHelper
    ) {
        if ($this->request->method('post')) {
            $category = $categoriesRequest->postCategory();

            if ($this->validateCategory($category, $categoriesEntity)) {
                if (empty($category->id)) {
                    // Добавление категории
                    $category     = $backendCategoriesHelper->prepareAdd($category);
                    $category->id = $backendCategoriesHelper->add($category);
                    $this->design->assign('message_success', 'added');
                } else {
                    // Обновление категории
                    $category     = $backendCategoriesHelper->prepareUpdate($category->id, $category);
                    $backendCategoriesHelper->update($category->id, $category);
                    $this->design->assign('message_success', 'updated');
                }

                // Удаление изображения
                $deleteImage = $categoriesRequest->postDeleteImage();
                if (!empty($deleteImage)) {
                    $backendCategoriesHelper->deleteCategoryImage($category);
                }

                // Загрузка изображения
                $image = $categoriesRequest->fileImage();
                $image = $backendCategoriesHelper->prepareUploadCategoryImage($category, $image);
                $backendCategoriesHelper->uploadCategoryImage($category, $image);
                $category = $categoriesEntity->get(intval($category->id));
            }
        } else {
            $categoryId = $this->request->get('id', 'integer');
            $category   = $backendCategoriesHelper->getCategory($categoryId);
        }

        $categories = $backendCategoriesHelper->getCategoriesTree();

        $this->design->assign('category',   $category);
        $this->design->assign('categories', $categories);
        $this->response->setContent($this->design->fetch('category.tpl'));
    }

    private function validateCategory($category, CategoriesEntity $categoriesEntity)
    {
        if (($c = $categoriesEntity->get($category->url)) && $c->id != $category->id) {
            $this->design->assign('message_error', 'url_exists');
            return false;
        }
        elseif (empty($category->name)) {
            $this->design->assign('message_error', 'empty_name');
            return false;
        }
        elseif (empty($category->url)) {
            $this->design->assign('message_error', 'empty_url');
            return false;
        }
        elseif (substr($category->url, -1) == '-' || substr($category->url, 0, 1) == '-') {
            $this->design->assign('message_error', 'url_wrong');
            return false;
        }

        return true;
    }

}
