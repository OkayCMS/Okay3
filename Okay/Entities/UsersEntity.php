<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class UsersEntity extends Entity
{

    protected static $fields = [
        'id',
        'email',
        'password',
        'name',
        'phone',
        'address',
        'group_id',
        'last_ip',
        'created',
        'remind_code',
        'remind_expire',
        'g.discount',
        'g.name as group_name',
    ];

    protected static $searchFields = [
        'name',
        'email',
        'last_ip',
    ];

    protected static $defaultOrderFields = [
        'name',
    ];

    protected static $table = '__users';
    protected static $tableAlias = 'u';
    protected static $alternativeIdField = 'email';

    // осторожно, при изменении соли испортятся текущие пароли пользователей
    private $salt = '8e86a279d6e182b3c811c559e6b15484';
    
    public function find(array $filter = [])
    {
        $this->select->join('LEFT', '__groups AS g', 'u.group_id=g.id');
        return parent::find($filter);
    }
    
    public function get($id)
    {
        if (empty($id)) {
            return false;
        }

        $this->select->join('LEFT', '__groups AS g', 'u.group_id=g.id');
        
        $user = parent::get($id);
        
        if (empty($user)) {
            return false;
        }
        $user->discount *= 1; // Убираем лишние нули, чтобы было 5 вместо 5.00
        return $user;
    }

    public function add($user)
    {
        $user = (array)$user;
        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }
        
        $count = $this->count(['email'=>$user['email']]);
        
        if ($count > 0) {
            return false;
        }
        
        return parent::add($user);
    }

    public function update($id, $user)
    {
        $user = (array)$user;
        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }
        
        return parent::update($id, $user);
    }

    public function delete($ids)
    {
        if (!empty($ids)) {
            $update = $this->queryFactory->newUpdate();
            $update->table('__orders')
                ->set('user_id', 0)
                ->where('user_id=:user_id')
                ->bindValue('user_id', $ids);
            
            $this->db->query($update);
            
        }
        parent::delete($ids);
        return true;
    }

    public function checkPassword($email, $password) {
        $encPassword = md5($this->salt . $password . md5($password));
        $usersIds = $this->cols(['id'])->find([
            'email' => $email,
            'password' => $encPassword,
            'limit' => 1,
        ]);
        if (!empty($usersIds)) {
            $userId = reset($usersIds);
            return $userId;
        }
        
        return false;
    }

    public function generatePass($passLen = 6) {
        $pass = '';
        for ($i=0; $i< $passLen; $i++) {
            $ranges = [
                rand(48, 57),
                rand(65, 90),
                rand(97, 122),
            ];
            $pass .= chr($ranges[rand(0, 2)]);
        }
        return $pass;
    }

    public function getUloginUser($token) {
        $s = file_get_contents('https://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
        return json_decode($s, true);
    }

    protected function customOrder($order = null)
    {
        $orderFields = [];
        switch ($order) {
            case 'date':
                $orderFields = ['u.created DESC'];
                break;
            case 'cnt_order':
                $orderFields = ["(select count(o.id) as count from __orders o where o.user_id = u.id) DESC"];
                break;
        }

        return $orderFields;
    }
}
