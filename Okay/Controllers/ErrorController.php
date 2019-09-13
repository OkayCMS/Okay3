<?php


namespace Okay\Controllers;


use Okay\Entities\PagesEntity;

class ErrorController extends AbstractController
{
    
    public function pageNotFound(PagesEntity $pagesEntity)
    {
        $this->response->setStatusCode(404);
        
        $page = $pagesEntity->get('404');
        $this->design->assign('page', $page);
        $this->design->assign('meta_title', $page->meta_title);
        $this->design->assign('meta_keywords', $page->meta_keywords);
        $this->design->assign('meta_description', $page->meta_description);

        $this->response->setContent($this->design->fetch('page.tpl'));
    }
    
}
