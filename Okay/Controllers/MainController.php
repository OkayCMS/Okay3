<?php


namespace Okay\Controllers;


class MainController extends AbstractController
{

    /*Отображение контента главной страницы*/
    public function render(\Okay\Core\QueryFactory $queryFactory, \Okay\Core\Database $db)
    {
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        }

        $this->response->setContent($this->design->fetch('main.tpl'));
    }
    
}
