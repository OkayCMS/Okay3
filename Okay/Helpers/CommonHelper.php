<?php


namespace Okay\Helpers;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Notify;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\SubscribesEntity;
use Okay\Entities\UsersEntity;
use Okay\Requests\CommonRequest;

class CommonHelper
{
    private $validateHelper;
    private $notify;
    private $design;
    private $commonRequest;
    private $entityFactory;

    public function __construct(
        ValidateHelper $validateHelper,
        Notify $notify,
        Design $design,
        CommonRequest $commonRequest,
        EntityFactory $entityFactory
    ) {
        $this->validateHelper = $validateHelper;
        $this->notify = $notify;
        $this->design = $design;
        $this->commonRequest = $commonRequest;
        $this->entityFactory = $entityFactory;
    }
    
    public function rootPostProcedure()
    {
        if (($callback = $this->commonRequest->postCallback()) !== null) {

            /** @var CallbacksEntity $callbacksEntity */
            $callbacksEntity = $this->entityFactory->get(CallbacksEntity::class);

            /*Валидация данных клиента*/
            if ($error = $this->validateHelper->getCallbackValidateError($callback)) {
                $this->design->assign('call_error', $error, true);
            } elseif ($callbackId = $callbacksEntity->add($callback)) {
                $this->design->assign('call_sent', true, true);
                // Отправляем email
                $this->notify->emailCallbackAdmin($callbackId);
            } else {
                $this->design->assign('call_error', 'unknown error', true);
            }

            if (!empty($error)) {
                $this->design->assign('callback_name',    $callback->name);
                $this->design->assign('callback_phone',   $callback->phone);
                $this->design->assign('callback_message', $callback->message);
            }
        }

        // Если прилетел токен, вероятно входят через соц. сеть
        if (($token = $this->commonRequest->uLoginToken()) !== null) {
            /** @var UsersEntity $usersEntity */
            $usersEntity = $this->entityFactory->get(UsersEntity::class);

            $uLoginData = $usersEntity->getULoginUser($token);
            if (!empty($uLoginData)) {
                $user = new \stdClass();
                $user->last_ip = $_SERVER['REMOTE_ADDR'];
                $user->name    = $uLoginData['last_name'] . ' ' . $uLoginData['first_name'];
                $user->email   = $uLoginData['email'];
                
                // Проверим, может такой пользователь уже существует
                if ($tmpUser = $usersEntity->get((string)$user->email)) {
                    $_SESSION['user_id'] = $tmpUser->id;
                    Response::redirectTo(Router::generateUrl('user', [], true));
                } elseif (empty($usersEntity->count(['email' => (string)$user->email]))) {
                    $user->password = $usersEntity->generatePass(6);
                    $userId = $usersEntity->add($user);
                    $_SESSION['user_id'] = $userId;
                    // Перенаправляем пользователя в личный кабинет
                    Response::redirectTo(Router::generateUrl('user', [], true));
                }
            }
        }

        if (($subscribe = $this->commonRequest->postSubscribe()) !== null) {

            /** @var SubscribesEntity $subscribesEntity */
            $subscribesEntity = $this->entityFactory->get(SubscribesEntity::class);

            /*Валидация данных клиента*/
            if ($error = $this->validateHelper->getSubscribeValidateError($subscribe)) {
                $this->design->assign('subscribe_error', $error, true);
            } elseif ($subscribeId = $subscribesEntity->add($subscribe)) {
                $this->design->assign('subscribe_success', true, true);
            }
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
}