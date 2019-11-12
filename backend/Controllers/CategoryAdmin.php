<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendValidateHelper;
use Okay\Core\Entity\UrlUniqueValidator;
use Okay\Entities\CategoriesEntity;
use Okay\Admin\Requests\BackendCategoriesRequest;
use Okay\Admin\Helpers\BackendCategoriesHelper;

class CategoryAdmin extends IndexAdmin
{

    public function fetch(
        CategoriesEntity         $categoriesEntity,
        BackendCategoriesRequest $categoriesRequest,
        BackendCategoriesHelper  $backendCategoriesHelper,
        BackendValidateHelper    $backendValidateHelper
    ) {
        if ($this->request->method('post')) {
            $category = $categoriesRequest->postCategory();

            if ($error = $backendValidateHelper->getCategoryValidateError($category)) {
                $this->design->assign('message_error', $error);
            } else {
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
}
