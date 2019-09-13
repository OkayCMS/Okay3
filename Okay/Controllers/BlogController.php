<?php


namespace Okay\Controllers;


use Okay\Core\Notify;
use Okay\Core\Validator;
use Okay\Entities\BlogEntity;
use Okay\Entities\CommentsEntity;
use Okay\Logic\ProductsLogic;

class BlogController extends AbstractController
{
    
    public function fetchPost(
        BlogEntity $blogEntity,
        CommentsEntity $commentsEntity,
        Validator $validator,
        ProductsLogic $productsLogic,
        Notify $notify,
        $url,
        $typePost
    ) {
        $post = $blogEntity->get((string)$url);
        
        // Если не найден - ошибка
        if (empty($post) || $post->type_post != $typePost || (!$post->visible && empty($_SESSION['admin']))) {
            return false;
        }

        $this->response->setHeaderLastModify($post->last_modify);
        
        // Автозаполнение имени для формы комментария
        if (!empty($this->user)) {
            $this->design->assign('comment_name', $this->user->name);
            $this->design->assign('comment_email', $this->user->email);
        }
        
        /*Принимаем комментарий*/
        if ($this->request->method('post') && $this->request->post('comment')) {
            $comment = new \stdClass;
            $comment->name  = $this->request->post('name');
            $comment->email = $this->request->post('email');
            $comment->text  = $this->request->post('text');
            $captchaCode   =  $this->request->post('captcha_code', 'string');
            
            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            $this->design->assign('comment_text', $comment->text);
            $this->design->assign('comment_name', $comment->name);
            $this->design->assign('comment_email', $comment->email);
            
            // Проверяем капчу и заполнение формы
            if ($this->settings->captcha_post && !$validator->verifyCaptcha('captcha_post', $captchaCode)) {
                $this->design->assign('error', 'captcha');
            } elseif (!$validator->isName($comment->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$validator->isComment($comment->text, true)) {
                $this->design->assign('error', 'empty_comment');
            } elseif (!$validator->isEmail($comment->email)) {
                $this->design->assign('error', 'empty_email');
            } else {
                // Создаем комментарий
                $comment->object_id = $post->id;
                $comment->type      = $post->type_post;
                $comment->ip        = $_SERVER['REMOTE_ADDR'];
                $comment->lang_id   = $_SESSION['lang_id'];
                // Добавляем комментарий в базу
                $commentId = $commentsEntity->add($comment);
                // Отправляем email
                $notify->emailCommentAdmin($commentId);

                $this->response->redirectTo($_SERVER['REQUEST_URI'].'#comment_'.$commentId);
            }
        }

        // Связанные товары
        $relatedIds = [];
        $relatedProducts = [];
        foreach ($blogEntity->getRelatedProducts(['post_id' => $post->id]) as $p) {
            $relatedIds[] = $p->related_id;
            $relatedProducts[$p->related_id] = null;
        }

        if (!empty($relatedIds)) {
            $relatedFilter = [
                'id' => $relatedIds,
                'limit' => count($relatedIds),
                'visible' => 1,
                'in_stock' => 1,
            ];
            foreach ($productsLogic->getProductList($relatedFilter) as $p) {
                $relatedProducts[$p->id] = $p;
            }
            foreach ($relatedProducts as $id=>$r) {
                if ($r === null) {
                    unset($relatedProducts[$id]);
                }
            }
            $this->design->assign('related_products', $relatedProducts);
        }
        
        // Комментарии к посту
        $comments = $commentsEntity->find([
            'has_parent' => false,
            'type' => $post->type_post,
            'object_id' => $post->id,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);
        $children = [];
        $childrenFilter = [
            'has_parent' => true,
            'type' => $post->type_post,
            'object_id' => $post->id,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];
        foreach ($commentsEntity->find($childrenFilter) as $c) {
            $children[$c->parent_id][] = $c;
        }
        
        $this->design->assign('comments', $comments);
        $this->design->assign('children', $children);
        $this->design->assign('post', $post);
        
        // Соседние записи
        $this->design->assign('next_post', $blogEntity->getNextPost($post->id));
        $this->design->assign('prev_post', $blogEntity->getPrevPost($post->id));
        
        // Мета-теги
        $this->design->assign('meta_title', $post->meta_title);
        $this->design->assign('meta_keywords', $post->meta_keywords);
        $this->design->assign('meta_description', $post->meta_description);

        $this->response->setContent($this->design->fetch('post.tpl'));
    }
    
    public function fetchBlog(BlogEntity $blogEntity, $typePost)
    {

        if (!in_array($typePost, ['blog', 'news'])) {
            return false;
        }
        
        $this->design->assign('typePost', $typePost);

        //lastModify
        $lastModify = $blogEntity->cols(['last_modify'])->find(['type_post' => $typePost]);
        $lastModify[] = $typePost == "news" ? $this->settings->lastModifyNews : $this->settings->lastModifyPosts;
        if ($this->page) {
            $lastModify[] = $this->page->last_modify;
        }
        $this->response->setHeaderLastModify(max($lastModify));
        
        // Количество постов на одной странице
        $itemsPerPage = max(1, intval($this->settings->posts_num));
        
        $filter = [];
        
        // Выбираем только видимые посты
        $filter['visible'] = 1;
        $filter['type_post'] = $typePost;
        
        // Текущая страница в постраничном выводе
        $currentPage = $this->request->get('page', 'integer');
        
        // Если не задана, то равна 1
        $currentPage = max(1, $currentPage);
        $this->design->assign('current_page_num', $currentPage);
        
        // Вычисляем количество страниц
        $postsCount = $blogEntity->count($filter);
        
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $itemsPerPage = $postsCount;
        }
        
        $pages_num = ceil($postsCount/$itemsPerPage);
        $this->design->assign('total_pages_num', $pages_num);
        
        $filter['page'] = $currentPage;
        $filter['limit'] = $itemsPerPage;
        
        // Выбираем статьи из базы
        $posts = $blogEntity->cols([
            'id',
            'url',
            'date',
            'image',
            'type_post',
            'name',
            'annotation',
        ])->find($filter);
        
        // Передаем в шаблон
        $this->design->assign('posts', $posts);
        
        // Метатеги
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        }
        
        $this->response->setContent($this->design->fetch('blog.tpl'));
    }
    
}
