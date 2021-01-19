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

            $moduleParams = $this->module->getModuleParams($vendor, $moduleName);
            
            if (!empty($moduleParams->version)) {
                $module->version = $moduleParams->version;
            } else {
                $module->version = '1.0.0';
            }
            
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
    
    public function update($moduleId)
    {
        if (!$module = $this->modulesEntity->findOne(['id' => $moduleId])) {
            return;
        }
        
        if (!($moduleParams = $this->module->getModuleParams($module->vendor, $module->module_name)) || empty($moduleParams->version)) {
            return;
        }

        if ($initClassName = $this->module->getInitClassName($module->vendor, $module->module_name)) {
            $reflection = new \ReflectionClass($initClassName);
            $updateMethods = [];
            
            //  Собираем список методов обновления, которые нужно вызвать
            foreach ($reflection->getMethods() as $method) {
                $matches = [];
                
                if (preg_match('~^update_([0-9]+_[0-9]+_[0-9]+)~', $method->name, $matches)) {
                    $version = str_replace('_', '.', $matches[1]);
                    
                    if ($version <= $moduleParams->version && $version > $module->version) {
                        $updateMethods[$version] = $method->name;
                    }
                }
            }
            
            ksort($updateMethods, SORT_NATURAL);
            
            // Вызываем поочередно методы для обновления модуля
            if (!empty($updateMethods)) {
                $initObject = new $initClassName($moduleId, $module->vendor, $module->module_name);
                foreach ($updateMethods as $method) {
                    $initObject->$method();
                }
            }

            // Обновляем версию модуля в системе
            $this->modulesEntity->update($moduleId, ['version' => $moduleParams->version]);
        }
    }
    
}