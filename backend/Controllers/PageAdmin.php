<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\PagesEntity;

class PageAdmin extends IndexAdmin
{
    
    public function fetch(PagesEntity $pagesEntity)
    {
        
        /*Прием информации о страницу*/
        if ($this->request->method('POST')) {
            $page = new \stdClass;
            $page->id       = $this->request->post('id', 'integer');
            $page->name     = $this->request->post('name');
            $page->name_h1  = $this->request->post('name_h1');
            $page->url      = trim($this->request->post('url'));
            $page->visible  = $this->request->post('visible', 'boolean');
            $page->meta_title       = $this->request->post('meta_title');
            $page->meta_keywords    = $this->request->post('meta_keywords');
            $page->meta_description = $this->request->post('meta_description');
            $page->description      = $this->request->post('description');
            
            /*Не допустить одинаковые URL разделов*/
            if (($p = $pagesEntity->get((string)$page->url)) && $p->id!=$page->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif (empty($page->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (substr($page->url, -1) == '-' || substr($page->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/Обновление страницы*/
                if (empty($page->id)) {
                    $page->id = $pagesEntity->add($page);
                    $page = $pagesEntity->get($page->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    // Запретим изменение системных url.
                    $checkPage = $pagesEntity->get(intval($page->id));
                    if (in_array($checkPage->url, $pagesEntity->getSystemPages()) && $page->url != $checkPage->url) {
                        $page->url = $checkPage->url;
                        $this->design->assign('message_error', 'url_system');
                    }
                    $pagesEntity->update($page->id, $page);
                    $page = $pagesEntity->get($page->id);
                    $this->design->assign('message_success', 'updated');
                }
            }
        } else {
            $id = $this->request->get('id', 'integer');
            if (!empty($id)) {
                $page = $pagesEntity->get(intval($id));
            } else {
                $page = new \stdClass;
                $page->visible = 1;
            }
        }
        
        $this->design->assign('page', $page);
        $this->response->setContent($this->design->fetch('page.tpl'));
    }
    
}
