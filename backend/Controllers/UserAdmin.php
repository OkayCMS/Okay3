<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\OrdersEntity;
use Okay\Entities\UserGroupsEntity;
use Okay\Entities\UsersEntity;

class UserAdmin extends IndexAdmin
{
    
    public function fetch(UsersEntity $usersEntity, UserGroupsEntity $userGroups, OrdersEntity $ordersEntity)
    {
        
        /*Прием данных о пользователе*/
        if ($this->request->method('post')) {
            $user = new \stdClass;
            $user->id = $this->request->post('id', 'integer');
            $user->name = $this->request->post('name');
            $user->email = $this->request->post('email');
            $user->phone = $this->request->post('phone');
            $user->address = $this->request->post('address');
            $user->group_id = $this->request->post('group_id');
            
            /*Не допустить одинаковые email пользователей*/
            if (empty($user->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (empty($user->email)) {
                $this->design->assign('message_error', 'empty_email');
            } elseif (($u = $usersEntity->get($user->email)) && $u->id!=$user->id) {
                $this->design->assign('message_error', 'login_exists');
            } else {
                /*Обновление пользователя*/
                $user->id = $usersEntity->update($user->id, $user);
                $this->design->assign('message_success', 'updated');
                $user = $usersEntity->get(intval($user->id));
            }
        }
        
        $id = $this->request->get('id', 'integer');
        if (!empty($id)) {
            $user = $usersEntity->get(intval($id));
        }
        
        /*История заказов пользователя*/
        if (!empty($user)) {
            $this->design->assign('user', $user);
            
            $orders = $ordersEntity->find(['user_id'=>$user->id]);
            $this->design->assign('orders', $orders);
        }
        
        $groups = $userGroups->find();
        $this->design->assign('groups', $groups);
        
        $this->response->setContent($this->design->fetch('user.tpl'));
    }
    
}
