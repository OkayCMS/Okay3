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

        $this->response->setContent('page.tpl');
    }
    
    public function siteDisabled()
    {
        $this->response->setStatusCode(503);
        $this->response->setContent('tech.tpl');
    }
    
}
