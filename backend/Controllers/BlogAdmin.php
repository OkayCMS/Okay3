<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\BlogEntity;

class BlogAdmin extends IndexAdmin
{
    
    public function fetch(BlogEntity $blogEntity)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        /*Выключение записей*/
                        $blogEntity->update($ids, ['visible'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включение записей*/
                        $blogEntity->update($ids, ['visible'=>1]);
                        break;
                    }
                    case 'delete': {
                        /*Удаление записей*/
                        $blogEntity->delete($ids);
                        break;
                    }
                }
            }
        }
        
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 20;
        
        // Поиск
        $keyword = $this->request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }

        $typePost = $this->request->get('type_post', 'string');
        if (!empty($typePost)) {
            $filter['type_post'] = $typePost;
            $this->design->assign('type_post', $typePost);
        }
        
        $postsCount = $blogEntity->count($filter);
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $postsCount;
        }
        /*Выбираем записи*/
        $posts = $blogEntity->find($filter);
        $this->design->assign('posts_count', $postsCount);
        
        $this->design->assign('pages_count', ceil($postsCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        
        $this->design->assign('posts', $posts);
        $this->response->setContent($this->design->fetch('blog.tpl'));
    }
    
}
