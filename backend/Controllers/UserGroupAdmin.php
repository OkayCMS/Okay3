<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendUserGroupsHelper;
use Okay\Admin\Helpers\BackendValidateHelper;
use Okay\Admin\Requests\BackendUserGroupsRequest;
use Okay\Entities\UserGroupsEntity;

class UserGroupAdmin extends IndexAdmin
{
    
    public function fetch(UserGroupsEntity $userGroups, BackendUserGroupsRequest $backendUserGroupsRequest, BackendUserGroupsHelper $backendUserGroupsHelper, BackendValidateHelper $backendValidateHelper)
    {
        
        /*Прием данных о группе пользователей*/
        if ($this->request->method('post')) {
            $group = $backendUserGroupsRequest->postGroup();
            
            if ($error = $backendValidateHelper->getUserGroupsValidateError($group)) {
                $this->design->assign('message_error', $error);
            } else {
                /*Добавление/Обновление групы пользователей*/
                if (empty($group->id)) {
                    $preparedGroup = $backendUserGroupsHelper->prepareAdd($group);
                    $group->id     = $backendUserGroupsHelper->add($preparedGroup);
                    $this->design->assign('message_success', 'added');
                } else {
                    $preparedGroup = $backendUserGroupsHelper->prepareUpdate($group);
                    $backendUserGroupsHelper->update($preparedGroup->id, $preparedGroup);
                    $this->design->assign('message_success', 'updated');
                }
            }
        }

        if (!empty($group)) {
            $groupId = (int)$group->id;
        } else {
            $groupId = $this->request->get('id', 'integer');
        }
        
        $group = $backendUserGroupsHelper->getGroup($groupId);
        $this->design->assign('group', $group);
        $this->response->setContent($this->design->fetch('user_group.tpl'));
    }
    
}
