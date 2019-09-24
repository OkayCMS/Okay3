<?php


namespace Okay\Core\Modules;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\Database;
use Okay\Core\Entity\Entity;
use Okay\Core\EntityFactory;
use Okay\Core\Managers;
use Okay\Core\QueryFactory;
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

    /** @var QueryFactory $queryFactory */
    private $queryFactory;
    
    /** @var ModulesEntitiesFilters $entitiesFilters */
    private $entitiesFilters;

    /** @var EntityMigrator $entityMigrator */
    private $entityMigrator;

    /** @var UpdateObject $updateObject */
    private $updateObject;

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
        
        $serviceLocator        = new ServiceLocator();
        $this->entityFactory   = $serviceLocator->getService(EntityFactory::class);
        $this->queryFactory    = $serviceLocator->getService(QueryFactory::class);
        $this->entityMigrator  = $serviceLocator->getService(EntityMigrator::class);
        $this->module          = $serviceLocator->getService(Module::class);
        $this->modules         = $serviceLocator->getService(Modules::class);
        $this->managers        = $serviceLocator->getService(Managers::class);
        $this->db              = $serviceLocator->getService(Database::class);
        $this->entitiesFilters = $serviceLocator->getService(ModulesEntitiesFilters::class);
        $this->updateObject    = $serviceLocator->getService(UpdateObject::class);
        $this->moduleId        = $moduleId;
        $this->vendor          = $vendor;
        $this->moduleName      = $moduleName;
    }

    /**
     * Метод, который вызывается во время утавноки модуля
     */
    abstract public function install();

    /**
     * Метод, который вызывается для каждого модуля во время каждого запуска системы
     */
    abstract public function init();

    /**
     * Метод расширяет коллекцию объектов доступную для использования в файле ajax/update_object.php,
     * который обновляет определенноую по алиасу сущность повредством AJAX запроса из админ панели сайта
     *
     * @param $alias - уникальный псевдоним, который идентифицирует сущность (указывается в атрибуте data-controller="алиас" тега в админ панели)
     * @param $permission - права доступа к псевдониму для менеджера
     * @param $entityClassName - полное имя сущности, которая будет обновляться
     * @throws \Exception
     */
    protected function registerUpdateObject($alias, $permission, $entityClassName)
    {
        $this->updateObject->register($alias, $permission, $entityClassName);
    }

    protected function registerEntityFilter($entityClassName, $filterName, $filterClassName, $filterMethod)
    {
        $this->entitiesFilters->registerFilter($entityClassName, $filterName, $filterClassName, $filterMethod);
    }

    protected function migrateEntityTable($entityClassName, $field)
    {
        $this->entityMigrator->migrateTable($entityClassName, $field);
    }

    protected function registerEntityField($entityClassName, EntityField $field)
    {
        $this->entityMigrator->migrateField($entityClassName, $field);

        /** @var Entity $entityClassName */
        if ($field->isLangField()) {
            $entityClassName::addLangField($field->getName());
            return;
        }

        $entityClassName::addField($field->getName());
    }

    protected function registerEntityFields($entityClassName, $fields)
    {
        /** @var Entity $entityClassName */
        $this->entityMigrator->migrateFieldSet($entityClassName, (array) $fields);

        /** @var EntityField $field */
        foreach($fields as $field) {
            if ($field->isLangField()) {
                $entityClassName::addLangField($field->getName());
            } else {
                $entityClassName::addField($field->getName());
            }
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