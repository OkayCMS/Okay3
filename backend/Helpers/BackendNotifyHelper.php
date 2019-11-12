<?php


namespace Okay\Admin\Helpers;


use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Notify;

class BackendNotifyHelper
{
    /**
     * @var Notify
     */
    private $notify;

    public function __construct(Notify $notify)
    {
        $this->notify = $notify;
    }

    public function feedbackAnswerNotify($answerFeedback)
    {
        $this->notify->emailFeedbackAnswerFoUser($answerFeedback->parent_id, $answerFeedback->message);
        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
}