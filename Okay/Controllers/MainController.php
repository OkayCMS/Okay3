<?php


namespace Okay\Controllers;


class MainController extends AbstractController
{

    /*Отображение контента главной страницы*/
    public function render()
    {
        $this->response->setContent('main.tpl');
    }
    
}
