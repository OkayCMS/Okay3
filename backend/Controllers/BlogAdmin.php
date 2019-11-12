<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendBlogHelper;
use Okay\Admin\Requests\BackendBlogRequest;

class BlogAdmin extends IndexAdmin
{
    public function fetch(
        BackendBlogRequest $blogRequest,
        BackendBlogHelper  $backendBlogHelper
    ) {
        if ($this->request->method('post')) {
            $ids = $blogRequest->postCheck();
            switch ($blogRequest->postAction()) {
                case 'disable': {
                    $backendBlogHelper->disable($ids);
                    break;
                }
                case 'enable': {
                    $backendBlogHelper->enable($ids);
                    break;
                }
                case 'delete': {
                    $backendBlogHelper->delete($ids);
                    break;
                }
            }
        }

        $filter     = $backendBlogHelper->buildPostsFilter();
        $posts      = $backendBlogHelper->findPosts($filter);
        $postsCount = $backendBlogHelper->getPostsCount($filter);

        $keyword  = isset($filter['keyword'])   ? $filter['keyword']   : '';
        $typePost = isset($filter['type_post']) ? $filter['type_post'] : '';

        $this->design->assign('keyword',      $keyword);
        $this->design->assign('type_post',    $typePost);
        $this->design->assign('posts_count',  $postsCount);
        $this->design->assign('pages_count',  ceil($postsCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('posts',        $posts);

        $this->response->setContent($this->design->fetch('blog.tpl'));
    }
    
}
