<?php


namespace Okay\Controllers;


use Okay\Entities\PagesEntity;

class PageController extends AbstractController
{

    /*Отображение страниц сайта*/
    public function render(PagesEntity $pagesEntity, $url)
    {
        $page = $pagesEntity->get($url);
        
        // Отображать скрытые страницы только админу
        if ((empty($page) || (!$page->visible && empty($_SESSION['admin']))) && $url != '404') {
            return false;
        }
        
        //lastModify
        if ($page->url != '404') {
            $this->response->setHeaderLastModify($page->last_modify);
        }
        
        $this->design->assign('page', $page);
        $this->design->assign('meta_title', $page->meta_title);
        $this->design->assign('meta_keywords', $page->meta_keywords);
        $this->design->assign('meta_description', $page->meta_description);
        
        $this->response->setContent($this->design->fetch('page.tpl'));
    }
    
}
