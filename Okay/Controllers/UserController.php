<?php


namespace Okay\Controllers;


use Okay\Core\Notify;
use Okay\Core\Validator;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\UsersEntity;
use Okay\Core\Router;

class UserController extends AbstractController
{
    
    public function render(
        UsersEntity $usersEntity,
        Validator $validator,
        OrdersEntity $ordersEntity,
        OrderStatusEntity $orderStatusEntity
    ) {
        if (empty($this->user->id)) {
            $this->response->redirectTo(Router::generateUrl('login', [], true));
        }

        if ($this->request->method('post') && $this->request->post('user_save')) {
            $user = new \stdClass();
            $user->name       = $this->request->post('name');
            $user->email      = $this->request->post('email');
            $user->phone      = $this->request->post('phone');
            $user->address    = $this->request->post('address');
            $password         = $this->request->post('password');

            $this->design->assign('name', $user->name);
            $this->design->assign('email', $user->email);
            $this->design->assign('phone', $user->phone);
            $this->design->assign('address', $user->address);

            /*Валидация данных*/
            if (($u = $usersEntity->get((string)$user->email)) && $u->id != $this->user->id) {
                $this->design->assign('error', 'user_exists');
            } elseif(!$validator->isName($user->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif(!$validator->isEmail($user->email, true)) {
                $this->design->assign('error', 'empty_email');
            } elseif(!$validator->isPhone($user->phone)) {
                $this->design->assign('error', 'empty_phone');
            } elseif(!$validator->isAddress($user->address)) {
                $this->design->assign('error', 'empty_address');
            } elseif($userId = $usersEntity->update($this->user->id, $user)) {
                $this->user = $usersEntity->get(intval($userId));
                $this->design->assign('user', $this->user);
            } else {
                $this->design->assign('error', 'unknown error');
            }

            if (!empty($password)) {
                $usersEntity->update($this->user->id, ['password'=>$password]);
            }
        } else {
            // Передаем в шаблон
            $this->design->assign('name', $this->user->name);
            $this->design->assign('email', $this->user->email);
            $this->design->assign('phone', $this->user->phone);
            $this->design->assign('address', $this->user->address);
        }

        /*Выборка истории заказов клиента*/
        $orders = $ordersEntity->find(['user_id'=>$this->user->id]);
        $allStatuses = $orderStatusEntity->mappedBy('id')->find();

        $this->design->assign('orders_status', $allStatuses);
        $this->design->assign('orders', $orders);

        $this->design->assign('meta_title', $this->user->name);
        $this->response->setContent($this->design->fetch('user.tpl'));
    }
    
    public function login(UsersEntity $usersEntity)
    {
        if (!empty($this->user->id)) {
            $this->response->redirectTo(Router::generateUrl('user', [], true));
        }

        if ($this->request->method('post')) {
            $email    = $this->request->post('email');
            $password = $this->request->post('password');
            $this->design->assign('email', $email);

            if ($userId = $usersEntity->checkPassword($email, $password)) {
                $_SESSION['user_id'] = $userId;
                $usersEntity->update($userId, ['last_ip'=>$_SERVER['REMOTE_ADDR']]);

                // Перенаправляем пользователя в личный кабинет
                $this->response->redirectTo(Router::generateUrl('user', [], true));
            } else {
                $this->design->assign('error', 'login_incorrect');
            }
        }
        
        $this->response->setContent($this->design->fetch('login.tpl'));
    }
    
    public function register(UsersEntity $usersEntity, Validator $validator)
    {
        if (!empty($this->user->id)) {
            $this->response->redirectTo(Router::generateUrl('user', [], true));
        }

        if ($this->request->method('post') && $this->request->post('register')) {
            $user = new \stdClass();
            $user->last_ip  = $_SERVER['REMOTE_ADDR'];
            $user->name     = $this->request->post('name');
            $user->email    = $this->request->post('email');
            $user->phone    = $this->request->post('phone');
            $user->address  = $this->request->post('address');
            $user->password = $this->request->post('password');
            $captcha_code   = $this->request->post('captcha_code');

            $this->design->assign('name', $user->name);
            $this->design->assign('email', $user->email);
            $this->design->assign('phone', $user->phone);
            $this->design->assign('address', $user->address);

            $userExists = $usersEntity->count(['email'=>$user->email]);
            
            /*Валидация данных клиента*/
            if ($userExists) {
                $this->design->assign('error', 'user_exists');
            } elseif (!$validator->isName($user->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$validator->isEmail($user->email, true)) {
                $this->design->assign('error', 'empty_email');
            } elseif (!$validator->isPhone($user->phone)) {
                $this->design->assign('error', 'empty_phone');
            } elseif (!$validator->isAddress($user->address)) {
                $this->design->assign('error', 'empty_address');
            } elseif (empty($user->password)) {
                $this->design->assign('error', 'empty_password');
            } elseif ($this->settings->captcha_register && !$validator->verifyCaptcha('captcha_register', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif ($userId = $usersEntity->add($user)) {
                $_SESSION['user_id'] = $userId;
                $this->response->redirectTo(Router::generateUrl('user', [], true));
            } else {
                $this->design->assign('error', 'unknown error');
            }
        }
        
        $this->response->setContent($this->design->fetch('register.tpl'));
    }
    
    public function logout()
    {
        unset($_SESSION['user_id']);
        $this->response->redirectTo(Router::generateUrl('main', [], true));
        return;
    }
    
    public function passwordRemind(UsersEntity $usersEntity, Notify $notify, $code = '')
    {
        
        if (!empty($code)) {

            // Выбераем пользователя из базы
            $users = $usersEntity->find(['remind_code'=>$code, 'limit'=>1]); // todo переделать когда будут методы getByField()
            if (empty($users)) {
                return false;
            }

            $user = reset($users);

            $usersEntity->update($user->id, ['remind_code'=>null, 'remind_expire'=>null]);
            if (date('Y-m-d H:i:s') > $user->remind_expire) {
                return false;
            }

            // Залогиниваемся под пользователем и переходим в кабинет для изменения пароля
            $_SESSION['user_id'] = $user->id;
            $this->response->redirectTo(Router::generateUrl('user', [], true));
            return;
        }
        
        // Если запостили email
        if ($this->request->method('post') && $this->request->post('email')) {
            $email = $this->request->post('email');
            $this->design->assign('email', $email);
            
            // Выбираем пользователя из базы
            $user = $usersEntity->get($email);
            if (!empty($user->id)) {
                // Генерируем секретный код и запишем в базу с датой до которой он будет активен (+5 минут от текущей)
                $code = md5(uniqid($this->config->salt, true));
                
                $usersEntity->update($user->id, ['remind_code'=>$code, 'remind_expire'=>date('Y-m-d H:i:s', time()+300)]);

                // Отправляем письмо пользователю для восстановления пароля
                $notify->emailPasswordRemind($user->id, $code);
                $this->design->assign('email_sent', true);
            } else {
                $this->design->assign('error', 'user_not_found');
            }
        }
        
        $this->response->setContent($this->design->fetch('password_remind.tpl'));
    }
    
}
