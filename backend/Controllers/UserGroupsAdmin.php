<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\UserGroupsEntity;
use Okay\Entities\UsersEntity;

class UserGroupsAdmin extends IndexAdmin
{
    
    public function fetch(UserGroupsEntity $userGroups, UsersEntity $usersEntity)
    {
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')){
                    case 'delete': {
                        /*Удаление группы пользователей*/
                        $userGroups->delete($ids);
                        break;
                    }
                }
            }
        }
        
        $groups = $userGroups->find();
        foreach ($groups as $group){
            $group->cnt_users = $usersEntity->count(["group_id"=>$group->id]);
        }
        
        $this->design->assign('groups', $groups);
        $this->response->setContent($this->design->fetch('user_groups.tpl'));
    }
    
}
