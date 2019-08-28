<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\UserGroupsEntity;

class UserGroupAdmin extends IndexAdmin
{
    
    public function fetch(UserGroupsEntity $userGroups)
    {
        
        /*Прием данных о группе пользователей*/
        if ($this->request->method('post')) {
            $group = new \stdClass;
            $group->id = $this->request->post('id', 'integer');
            $group->name = $this->request->post('name');
            $group->discount = $this->request->post('discount', 'float');
            
            if (empty($group->name)) {
                $this->design->assign('message_error', 'empty_name');
            } else {
                /*Добавление/Обновление групы пользователей*/
                if (empty($group->id)) {
                    $group->id = $userGroups->add($group);
                    $this->design->assign('message_success', 'added');
                } else {
                    $group->id = $userGroups->update($group->id, $group);
                    $this->design->assign('message_success', 'updated');
                }
                $group = $userGroups->get(intval($group->id));
            }
        } else {
            $id = $this->request->get('id', 'integer');
            if (!empty($id)) {
                $group = $userGroups->get(intval($id));
            }
        }
        
        if (!empty($group)) {
            $this->design->assign('group', $group);
        }

        $this->response->setContent($this->design->fetch('user_group.tpl'));
    }
    
}
