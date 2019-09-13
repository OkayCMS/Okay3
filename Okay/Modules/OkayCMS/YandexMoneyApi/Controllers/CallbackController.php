<?php


namespace Okay\Modules\OkayCMS\YandexMoneyApi\Controllers;


use Okay\Modules\OkayCMS\YandexMoneyApi\YandexMoneyCallbackHandler;
use Okay\Controllers\AbstractController;

class CallbackController extends AbstractController
{
    public function payOrder(YandexMoneyCallbackHandler $handler) {
        $action    = $this->request->get('action');

        if ($action == 'notify') {
            $handler->processNotification();
            return;
        }

        $handler->processReturnUrl();
    }
}