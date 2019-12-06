<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendBlogHelper;
use Okay\Admin\Helpers\BackendValidateHelper;
use Okay\Admin\Requests\BackendBlogRequest;
use Okay\Entities\BlogEntity;
use Okay\Helpers\RelatedProductsHelper;

class PostAdmin extends IndexAdmin
{
    
    public function fetch(
        BlogEntity            $blogEntity,
        BackendBlogRequest    $backendBlogRequest,
        BackendBlogHelper     $backendBlogHelper,
        BackendValidateHelper $backendValidateHelper,
        RelatedProductsHelper $relatedProductsHelper
    ) {
        
        /*Прием информации о записи*/
        if ($this->request->method('post')) {
            $post = $backendBlogRequest->postArticle();

            $relatedProducts = $backendBlogRequest->postRelatedProducts();

            if ($error = $backendValidateHelper->getBlogValidateError($post)) {
                $this->design->assign('message_error', $error);
            } else {
                /*Добавление/Обновление записи*/
                if (empty($post->id)) {
                    $preparedPost = $backendBlogHelper->prepareAdd($post);
                    $post->id     = $backendBlogHelper->add($preparedPost);

                    $this->postRedirectGet->storeMessageSuccess('added');
                    $this->postRedirectGet->storeNewEntityId($post->id);
                } else {
                    $preparedPost = $backendBlogHelper->prepareUpdate($post);
                    $backendBlogHelper->update($preparedPost->id, $post);

                    $this->postRedirectGet->storeMessageSuccess('updated');
                }

                // Картинка
                if ($backendBlogRequest->postDeleteImage()) {
                    $backendBlogHelper->deleteImage($post);
                }

                if ($image = $backendBlogRequest->fileImage()) {
                    $backendBlogHelper->uploadImage($image, $post);
                }

                // Связанные товары
                $relatedProducts = $backendBlogHelper->prepareUpdateRelatedProducts($post, $relatedProducts);
                $backendBlogHelper->updateRelatedProducts($post, $relatedProducts);

                $this->postRedirectGet->redirect();
            }
        }

        $postId = $this->request->get('id', 'integer');
        $post   = $backendBlogHelper->getPost($postId);

        $relatedProducts = [];
        if (!empty($post->id)) {
            $relatedProducts = $relatedProductsHelper->getRelatedProductsList($blogEntity, ['post_id' => $post->id]);
        }

        $this->design->assign('related_products', $relatedProducts);
        $this->design->assign('post',             $post);
        $this->response->setContent($this->design->fetch('post.tpl'));
    }
    
}
