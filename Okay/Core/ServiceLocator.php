<?php


namespace Okay\Core;


use Okay\Core\OkayContainer\OkayContainer;

class ServiceLocator
{
    /**
     * @var OkayContainer
     */
    private $DI;
    
    public function __construct()
    {
        $this->DI = include 'Okay/Core/config/container.php';
    }

    /**
     * @param $service
     * @return object
     */
    public function getService($service) // todo добавить type hint
    {
        return $this->DI->get($service);
    }
}
