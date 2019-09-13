<?php


namespace Okay\Core\Modules;


use Okay\Core\EntityFactory;
use Okay\Entities\ModulesEntity;

class Installer
{
    /** @var ModulesEntity */
    private $modulesEntity;

    /** @var Module */
    private $module;
    
    public function __construct(EntityFactory $entityFactory, Module $module)
    {
        $this->modulesEntity = $entityFactory->get(ModulesEntity::class);
        $this->module = $module;
    }

    public function install($fullModuleName)
    {

        $moduleId = false;
        
        list($vendor, $moduleName) = explode('/', $fullModuleName);
        
        // Директорию получаем чтобы провалидировать, что такой модуль существует в ФС
        if ($this->module->getModuleDirectory($vendor, $moduleName)) {
            
            $findModules = $this->modulesEntity->cols(['id', 'type'])->find([
                'vendor' => $vendor,
                'module_name' => $moduleName,
            ]);

            if (count($findModules) > 0) {
                throw new \Exception('Module name "'.$vendor.'/'.$moduleName.'" is already exists');
            }

            $module = new \stdClass();
            $module->vendor  = $vendor;
            $module->module_name = $moduleName;
            $module->enabled = 1;
            if (!$moduleId = $this->modulesEntity->add($module)) {
                // todo ошибка во время утановки
            }
            
            if ($initClassName = $this->module->getInitClassName($vendor, $moduleName)) {
                /** @var AbstractInit $initObject */
                $initObject = new $initClassName($moduleId, $vendor, $moduleName);
                $initObject->install();
            }
        }
        
        return $moduleId;
    }
}