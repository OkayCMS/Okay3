<?php


namespace Okay\Controllers;


use Okay\Core\Notify;
use Okay\Entities\FeedbacksEntity;
use Okay\Helpers\ValidateHelper;
use Okay\Requests\CommonRequest;

class FeedbackController extends AbstractController {
    
    public function render(
        FeedbacksEntity $feedbacksEntity,
        Notify $notify,
        CommonRequest $commonRequest,
        ValidateHelper $validateHelper
    ) {

        if (($feedback = $commonRequest->postFeedback()) !== null) {
            if ($error = $validateHelper->getFeedbackValidateError($feedback)) {
                $this->design->assign('error', $error);
            } else {

                $this->design->assign('message_sent', true);

                $feedback->ip = $_SERVER['REMOTE_ADDR'];
                $feedback->lang_id = $_SESSION['lang_id'];
                $feedbackId = $feedbacksEntity->add($feedback);

                // Отправляем email
                $notify->emailFeedbackAdmin($feedbackId);
            }
        }
        
        $this->response->setContent('feedback.tpl');
    }
}
