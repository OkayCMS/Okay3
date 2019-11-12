<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\UsersEntity;

class UserHelper
{

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }
    
    /**
     * Метод вызывается во время входа в личный кабинет.
     * 
     * @param string $email
     * @param string $password
     * @return int|false id пользователя или false при возникновении ошибки
     * @throws \Exception
     */
    public function login($email, $password)
    {
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);
        if ($userId = $usersEntity->checkPassword($email, $password)) {
            $_SESSION['user_id'] = $userId;
            $usersEntity->update($userId, ['last_ip'=>$_SERVER['REMOTE_ADDR']]);
        }
        return ExtenderFacade::execute(__METHOD__, $userId, func_get_args());
    }
    
    public function logout()
    {
        unset($_SESSION['user_id']);
        return ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
    
    public function register($user)
    {
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        $user->last_ip  = $_SERVER['REMOTE_ADDR'];
        if ($userId = $usersEntity->add($user)) {
            $_SESSION['user_id'] = $userId;
        }
        return ExtenderFacade::execute(__METHOD__, $userId, func_get_args());
    }
}