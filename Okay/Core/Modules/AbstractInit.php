<?php


namespace Okay\Core\Modules;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\Database;
use Okay\Core\Entity\Entity;
use Okay\Core\EntityFactory;
use Okay\Core\Managers;
use Okay\Core\ServiceLocator;
use Okay\Entities\ModulesEntity;

abstract class AbstractInit
{
    private $allowedTypes = [
        MODULE_TYPE_PAYMENT,
        MODULE_TYPE_XML,
    ];

    /** @var EntityFactory $entityFactory */
    private $entityFactory;

    /** @var Module $module */
    private $module;
    
    /** @var Modules $modules */
    private $modules;
    
    /** @var Managers $managers */
    private $managers;
    
    /** @var Database $db */
    private $db;
    
    /** @var ModulesEntitiesFilters $entitiesFilters */
    private $entitiesFilters;

    /**
     * @var int id модуля в базе
     */
    private $moduleId;
    private $vendor;
    private $moduleName;
    
    /** @var array Список зарегестрированных контроллеров админки */
    private $backendControllers = [];
    
    public function __construct($moduleId, $vendor, $moduleName)
    {
        if (!is_int($moduleId)) {
            throw new \Exception('"$moduleId" must be integer');
        }
        
        $serviceLocator      = new ServiceLocator();
        $this->entityFactory = $serviceLocator->getService(EntityFactory::class);
        $this->module        = $serviceLocator->getService(Module::class);
        $this->modules       = $serviceLocator->getService(Modules::class);
        $this->managers      = $serviceLocator->getService(Managers::class);
        $this->db            = $serviceLocator->getService(Database::class);
        $this->entitiesFilters = $serviceLocator->getService(ModulesEntitiesFilters::class);
        $this->moduleId      = $moduleId;
        $this->vendor        = $vendor;
        $this->moduleName    = $moduleName;
        
    }

    /**
     * Метод, который вызывается во время утавноки модуля
     */
    abstract public function install();

    /**
     * Метод, который вызывается для каждого модуля во время каждого запуска системы
     */
    abstract public function init();
    
    protected function registerEntityFilter($entityClassName, $filterName, $filterClassName, $filterMethod)
    {
        $this->entitiesFilters->registerFilter($entityClassName, $filterName, $filterClassName, $filterMethod);
    }
    
    protected function registerEntityField(EntityField $field)
    {
        $field->changeDatabase();
        
        /** @var Entity $entityClass */
        $entityClass = $field->getEntityClass();
        if ($field->getIsLang() === true) {
            $entityClass::addLangField($field->getName());
        } else {
            $entityClass::addField($field->getName());
        }
        
    }
    
    /**
     * Имя контроллера, который будет в админке обрабатываться как основной.
     * Когда со списка модулей переход внутрь модуля, попадаем на этот контроллер
     * @param $className 
     * @throws \Exception
     */
    protected function setBackendMainController($className)
    {
        if ($this->validateBackendController($className)) {

            /** @var ModulesEntity $modulesEntity */
            $modulesEntity = $this->entityFactory->get(ModulesEntity::class);
            $modulesEntity->update($this->moduleId, ['backend_main_controller' => $className]);
        }
    }
    
    public function getBackendControllers()
    {
        return $this->backendControllers;
    }
    
    // todo documentation
    protected function addPermission($permission)
    {
        $this->managers->addModulePermission((string)$permission, $this->vendor . '/' . $this->moduleName);
    }
    
    // TODO хорошая валидация
    protected function addBackendControllerPermission($controllerClass, $permission)
    {
        if ($this->validateBackendController($controllerClass)) {
            $this->addPermission($permission);
            $controllerClass = $this->module->getBackendControllerName($this->vendor, $this->moduleName, $controllerClass);
            $this->managers->addModuleControllerPermission($controllerClass, (string)$permission);
        }
    }

    protected function registerBackendController($controllerClass)
    {
        if (is_dir($this->module->getBackendControllersDirectory($this->vendor, $this->moduleName))) {

            // Вырезаем namespace из названия контроллера
            $controllerClass = str_replace(
                $this->module->getBackendControllersNamespace($this->vendor, $this->moduleName) . '\\',
                '',
                $controllerClass
            );

            if ($this->validateBackendController($controllerClass)) {
                $this->backendControllers[] = $controllerClass;
            }
        }
    }
    
    protected function setModuleType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new \Exception("Type \"$type\" not supported");
        }

        /** @var ModulesEntity $modulesEntity */
        $modulesEntity = $this->entityFactory->get(ModulesEntity::class);
        
        $modulesEntity->update($this->moduleId, ['type' => $type]);
    }

    private function validateBackendController($className)
    {
        $fullControllerName = $this->module->getBackendControllersDirectory($this->vendor, $this->moduleName) . $className . '.php';
        if (!is_file($fullControllerName)) {
            throw new \Exception("Controller \"$fullControllerName\" not exists");
        }

        $backendControllersNamespace = $this->module->getBackendControllersNamespace($this->vendor, $this->moduleName);
        if (!is_subclass_of($backendControllersNamespace . '\\' . $className, IndexAdmin::class)) {
            throw new \Exception("Controller \"$fullControllerName\" must be a subclass of \"". IndexAdmin::class . "\"");
        }
        
        if (!method_exists($backendControllersNamespace . '\\' . $className, 'fetch')) {
            throw new \Exception("Controller \"$fullControllerName\" must have a method \"fetch()\"");
        }
        
        return true;
    }
    
}