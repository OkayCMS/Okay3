<?php


namespace Okay\Controllers;


use Okay\Core\Notify;
use Okay\Core\Validator;
use Okay\Entities\FeedbacksEntity;

class FeedbackController extends AbstractController {
    
    public function render(FeedbacksEntity $feedbacksEntity, Validator $validator, Notify $notify)
    {
        /*Принимаем заявку с формы обратной связи*/
        if ($this->request->method('post') && $this->request->post('feedback')) {
            $feedback = new \stdClass;
            $feedback->name         = $this->request->post('name');
            $feedback->email        = $this->request->post('email');
            $feedback->message      = $this->request->post('message');
            $captcha_code           = $this->request->post('captcha_code');
            
            $this->design->assign('name',  $feedback->name);
            $this->design->assign('email', $feedback->email);
            $this->design->assign('message', $feedback->message);

            /*Валидация данных клиента*/
            if(!$validator->isName($feedback->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif(!$validator->isEmail($feedback->email, true)) {
                $this->design->assign('error', 'empty_email');
            } elseif(!$validator->isComment($feedback->message, true)) {
                $this->design->assign('error', 'empty_text');
            } elseif($this->settings->captcha_feedback && !$validator->verifyCaptcha('captcha_feedback', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } else {
                $this->design->assign('message_sent', true);
                
                $feedback->ip = $_SERVER['REMOTE_ADDR'];
                $feedback->lang_id = $_SESSION['lang_id'];
                $feedback_id = $feedbacksEntity->add($feedback);
                
                // Отправляем email
                $notify->emailFeedbackAdmin($feedback_id);
            }
        }
        
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        }
        
        $this->response->setContent($this->design->fetch('feedback.tpl'));
    }
    
}
