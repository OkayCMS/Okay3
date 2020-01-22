<?php


namespace Okay\Controllers;


use Okay\Entities\BlogEntity;
use Okay\Helpers\BlogHelper;
use Okay\Helpers\CommentsHelper;
use Okay\Helpers\MetadataHelpers\PostMetadataHelper;
use Okay\Helpers\RelatedProductsHelper;

class BlogController extends AbstractController
{
    
    public function fetchPost(
        BlogEntity $blogEntity,
        RelatedProductsHelper $relatedProductsHelper,
        CommentsHelper $commentsHelper,
        PostMetadataHelper $postMetadataHelper,
        $url,
        $typePost
    ) {
        $post = $blogEntity->get((string)$url);
        
        // Если не найден - ошибка
        if (empty($post) || $post->type_post != $typePost || (!$post->visible && empty($_SESSION['admin']))) {
            return false;
        }

        $this->setMetadataHelper($postMetadataHelper);
        
        $this->response->setHeaderLastModify($post->last_modify);
        
        // Автозаполнение имени для формы комментария
        if (!empty($this->user)) {
            $this->design->assign('comment_name', $this->user->name);
            $this->design->assign('comment_email', $this->user->email);
        }

        // Комментарии к посту
        $commentsHelper->addCommentProcedure($post->type_post, $post->id);
        $comments = $commentsHelper->getCommentsList($post->type_post, $post->id);
        $this->design->assign('comments', $comments);

        // Связанные товары
        $relatedProducts = $relatedProductsHelper->getRelatedProductsList($blogEntity, ['post_id' => $post->id]);
        $this->design->assign('related_products', $relatedProducts);
        
        $this->design->assign('post', $post);
        
        // Соседние записи
        $this->design->assign('next_post', $blogEntity->getNextPost($post->id));
        $this->design->assign('prev_post', $blogEntity->getPrevPost($post->id));

        $this->response->setContent('post.tpl');
    }
    
    public function fetchBlog(BlogEntity $blogEntity, BlogHelper $blogHelper, $typePost)
    {

        if (!in_array($typePost, ['blog', 'news'])) {
            return false;
        }
        
        $this->design->assign('typePost', $typePost);

        //lastModify
        $lastModify = $blogEntity->cols(['last_modify'])->find(['type_post' => $typePost]);
        $lastModify[] = $typePost == "news" ? $this->settings->get('lastModifyNews') : $this->settings->get('lastModifyPosts');
        if ($this->page) {
            $lastModify[] = $this->page->last_modify;
        }
        $this->response->setHeaderLastModify(max($lastModify));
        
        // Количество постов на одной странице
        //$itemsPerPage = max(1, intval($this->settings->get('posts_num')));
        
        $filter = [];
        // Выбираем только видимые посты
        $filter['visible'] = 1;
        $filter['type_post'] = $typePost;

        $filter = $blogHelper->getPostsFilter($filter);
        
        $paginate = $blogHelper->paginate(
            $this->settings->get('posts_num'),
            $this->request->get('page'),
            $filter,
            $this->design
        );

        if (!$paginate) {
            return false;
        }

        // Посты
        $currentSort = $blogHelper->getCurrentSort();
        
        $posts = $blogHelper->getList($filter, $currentSort);
        
        // Передаем в шаблон
        $this->design->assign('posts', $posts);
        
        $this->response->setContent('blog.tpl');
    }
    
}
