<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Notify;
use Okay\Entities\FeedbacksEntity;

class FeedbacksAdmin extends IndexAdmin
{
    
    public function fetch(FeedbacksEntity $feedbacksEntity, Notify $notify)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (!empty($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        /*Удалить заявку с формы обратной связи*/
                        $feedbacksEntity->delete($ids);
                        break;
                    }
                }
            }
        }

        /*Ответ админисратора на заявку с формы обратной связи*/
        if ($this->request->method('post')) {
            if ($this->request->post('feedback_answer', 'boolean') && ($feedbackId = $feedbacksEntity->get($this->request->post('feedback_id', 'integer')))) {
                $answerText = $this->request->post('text');
                if (!empty($feedbackId)) {
                    $newFeedback = new \stdClass();
                    $newFeedback->is_admin = 1;
                    $newFeedback->message = $answerText;
                    $newFeedback->email = $this->settings->notify_from_email;
                    $newFeedback->name = $this->settings->notify_from_name;
                    $newFeedback->processed = 1;
                    $newFeedback->ip = $_SERVER['REMOTE_ADDR'];
                    $newFeedback->lang_id = $_SESSION['admin_lang_id'];
                    $newFeedback->parent_id = $feedbackId->id;
                    $res = $feedbacksEntity->add($newFeedback);
                    
                    if ($res) {
                        $notify->emailFeedbackAnswerFoUser($feedbackId->id, $answerText);
                    }
                }
            }
        }
        
        // Отображение
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        
        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['feedback_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['feedback_num_admin'])) {
            $filter['limit'] = $_SESSION['feedback_num_admin'];
        } else {
            $filter['limit'] = 25;
        }
        $this->design->assign('current_limit', $filter['limit']);

        // Выбираем главные сообщения
        $filter['has_parent'] = false;
        
        // Сортировка по статусу
        $status = $this->request->get('status', 'string');
        if ($status == 'processed') {
            $filter['processed'] = 1;
        } elseif ($status == 'unprocessed') {
            $filter['processed'] = 0;
        }
        $this->design->assign('status', $status);
        
        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        $feedbacksCount = $feedbacksEntity->count($filter);
        // Показать все страницы сразу
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $feedbacksCount;
        }

        $filter['sort'] = 'new_first';
        $feedbacks = $feedbacksEntity->find($filter);

        // Сохраняем id сообщений для выборки ответов
        $feedbackIds = [];
        foreach ($feedbacks as $feedback) {
            $feedbackIds[] = $feedback->id;
        }

        // Выбераем ответы на сообщения
        if (!empty($feedbackIds)) {
            $adminAnswer = [];
            foreach ($feedbacksEntity->find(['parent_id' => $feedbackIds]) as $f) {
                $adminAnswer[$f->parent_id][] = $f;
            }
            $this->design->assign('admin_answer', $adminAnswer);
        }
        
        $this->design->assign('pages_count', ceil($feedbacksCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        
        $this->design->assign('feedbacks', $feedbacks);
        $this->design->assign('feedbacks_count', $feedbacksCount);

        $this->response->setContent($this->design->fetch('feedbacks.tpl'));
    }
    
}
