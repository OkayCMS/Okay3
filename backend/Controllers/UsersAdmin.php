<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\OrdersEntity;
use Okay\Entities\UsersEntity;
use Okay\Entities\UserGroupsEntity;

class UsersAdmin extends IndexAdmin
{
    
    public function fetch(UsersEntity $usersEntity, UserGroupsEntity $userGroups, OrdersEntity $ordersEntity)
    {
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        /*Удалить пользователя*/
                        $usersEntity->delete($ids);
                        break;
                    }
                    case 'move_to': {
                        /*Переместить пользователя в группу*/
                        $usersEntity->update($ids, ['group_id' => $this->request->post('move_group', 'integer')]);
                        break;
                    }
                }
            }
        }

        $groups = [];
        foreach($userGroups->find() as $g) {
            $groups[$g->id] = $g;
        }
        
        $group = null;
        $filter = array();
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 20;
        
        $groupId = $this->request->get('group_id', 'integer');
        if (!empty($groupId)) {
            $group = $userGroups->get($groupId);
            $filter['group_id'] = $group->id;
            $this->design->assign('group', $group);
        }
        
        // Поиск
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        // Сортировка пользователей, сохраняем в сессии, чтобы текущая сортировка не сбрасывалась
        if ($sort = $this->request->get('sort', 'string')) {
            $_SESSION['users_admin_sort'] = $sort;
        }
        if (!empty($_SESSION['users_admin_sort'])) {
            $filter['sort'] = $_SESSION['users_admin_sort'];
        } else {
            $filter['sort'] = 'name';
        }
        $this->design->assign('sort', $filter['sort']);
        
        $usersCount = $usersEntity->count($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $usersCount;
        }
        
        $users = $usersEntity->find($filter);
        foreach ($users as $user){
            $user->orders = $ordersEntity->find(array('user_id'=>$user->id));
        }
        $this->design->assign('pages_count', ceil($usersCount/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('groups', $groups);
        $this->design->assign('group', $group);
        $this->design->assign('users', $users);
        $this->design->assign('users_count', $usersCount);

        $this->response->setContent($this->design->fetch('users.tpl'));
    }
    
}
