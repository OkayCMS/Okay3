<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Notify;
use Okay\Entities\BlogEntity;
use Okay\Entities\CommentsEntity;
use Okay\Entities\ProductsEntity;

class CommentsAdmin extends IndexAdmin
{
    
    public function fetch(CommentsEntity $commentsEntity, BlogEntity $blogEntity, ProductsEntity $productsEntity, Notify $notify)
    {
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        
        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['comments_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['comments_num_admin'])) {
            $filter['limit'] = $_SESSION['comments_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);
        
        // Выбираем главные сообщения
        $filter['has_parent'] = false;
        
        // Тип
        $type = $this->request->get('type', 'string');
        if ($type) {
            $filter['type'] = $type;
            $this->design->assign('type', $type);
        }

        // Сортировка по статусу
        $status = $this->request->get('status', 'string');
        if ($status == 'approved') {
            $filter['approved'] = 1;
        } elseif ($status == 'unapproved') {
            $filter['approved'] = 0;
        }
        $this->design->assign('status', $status);
        
        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        /*Принимаем ответ администратора на комментарий*/
        if ($this->request->method('post')) {
            if ($this->request->post('comment_answer', 'boolean') && ($parentComment = $commentsEntity->get($this->request->post('parent_id', 'integer')))) {
                $comment = new \stdClass();
                $comment->parent_id = $parentComment->id;
                $comment->type      = $parentComment->type;
                $comment->object_id = $parentComment->object_id;
                $comment->text      = $this->request->post('text');
                $comment->name      = ($this->settings->notify_from_name ? $this->settings->notify_from_name : 'Administrator');
                $comment->approved  = 1;
                $comment->id        = $commentsEntity->add($comment);
                if (!empty($parentComment->email) && $comment->id) {
                    $notify->emailCommentAnswerToUser($comment->id);
                }
            }
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (!empty($ids) && is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'approve': {
                        /*Модерация комментария*/
                        $commentsEntity->update($ids, array('approved'=>1));
                        break;
                    }
                    case 'delete': {
                        /*Удаления комментария*/
                        $commentsEntity->delete($ids);
                        break;
                    }
                }
            }
        }

        // Отображение
        $commentsCount = $commentsEntity->count($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $commentsCount;
        }
        $comments = $commentsEntity->find($filter);

        // Сохраняем id комментариев для выборки ответов
        $commentsIds = [];
        foreach ($comments as $comment) {
            $commentsIds[] = $comment->id;
        }
        
        // Выбераем ответы на комментарии
        if (!empty($commentsIds)) {
            $children = array();
            foreach ($commentsEntity->find(['parent_id' => $commentsIds]) as $c) {
                $children[$c->parent_id][] = $c;
            }
            $this->design->assign('children', $children);
        }
        
        // Выбирает объекты, которые прокомментированы:
        $productsIds = [];
        $postsIds = [];
        foreach ($comments as $comment) {
            if ($comment->type == 'product') {
                $productsIds[] = $comment->object_id;
            }
            if ($comment->type == 'blog') {
                $postsIds[] = $comment->object_id;
            }
            if ($comment->type == 'news') {
                $postsIds[] = $comment->object_id;
            }
        }
        $products = [];
        if (!empty($productsIds)) {
            foreach ($productsEntity->find(['id' => $productsIds, 'limit' => count($productsIds)]) as $p) {
                $products[$p->id] = $p;
            }
        }
        
        $posts = [];
        if (!empty($postsIds)) {
            foreach ($blogEntity->find(['id' => $postsIds]) as $p) {
                $posts[$p->id] = $p;
            }
        }

        /*Определение сущности, к которой был оставлен комментарий*/
        foreach ($comments as $comment) {
            if ($comment->type == 'product' && isset($products[$comment->object_id])) {
                $comment->product = $products[$comment->object_id];
            }
            if ($comment->type == 'blog' && isset($posts[$comment->object_id])) {
                $comment->post = $posts[$comment->object_id];
            }
            if ($comment->type == 'news' && isset($posts[$comment->object_id])) {
                $comment->post = $posts[$comment->object_id];
            }
        }
        
        $this->design->assign('pages_count', ceil($commentsCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        
        $this->design->assign('comments', $comments);
        $this->design->assign('comments_count', $commentsCount);
        $this->response->setContent($this->design->fetch('comments.tpl'));
    }
    
}
