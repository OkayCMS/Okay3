<?php


namespace Okay\Core\Modules;


class Module
{
    const COMMON_MODULE_NAMESPACE = 'Okay\\Modules';
    const COMMON_MODULE_DIRECTORY = 'Okay/Modules/';

    /**
     * Получить базовую область видимости для указанного модуля
     * @param string $vendor
     * @param string $moduleName
     * @return string
     */
    public function getBaseNamespace($vendor, $moduleName)
    {
        return self::COMMON_MODULE_NAMESPACE.'\\'.$vendor.'\\'.$moduleName;
    }
    
    /**
     * Получить область видимости контроллеров админки для указанного модуля
     * @param string $vendor
     * @param string $moduleName
     * @return string
     */
    public function getBackendControllersNamespace($vendor, $moduleName)
    {
        return self::COMMON_MODULE_NAMESPACE.'\\'.$vendor.'\\'.$moduleName.'\\Backend\\Controllers';
    }
    
    /**
     * Получить область видимости контроллеров админки для указанного модуля
     * @param string $vendor
     * @param string $moduleName
     * @return string
     * @throws \Exception
     */
    public function getBackendControllersDirectory($vendor, $moduleName)
    {
        return $this->getModuleDirectory($vendor, $moduleName) . 'Backend/Controllers/';
    }

    /**
     * Получить экземпляр конфигурационного класса указанного модуля
     * @param string $vendor
     * @param string $moduleName
     * @return string
     */
    public function getInitClassName($vendor, $moduleName)
    {
        $initClassName = $this->getBaseNamespace($vendor, $moduleName).'\\Init\\Init';
        if (class_exists($initClassName)) {
            return $initClassName;
        }

        return '';
    }

    /**
     * Получить базовую директорию для указанного модуля
     * @param string $vendor
     * @param string $moduleName
     * @throws \Exception
     * @return string
     */
    public function getModuleDirectory($vendor, $moduleName)
    {
        if (!preg_match('~^[\w]+$~', $vendor)) {
            throw new \Exception('"'.$vendor.'" is wrong name of vendor');
        }
        
        if (!preg_match('~^[\w]+$~', $moduleName)) {
            throw new \Exception('"'.$moduleName.'" is wrong name of module');
        }
        
        $dir = self::COMMON_MODULE_DIRECTORY.'/'.$vendor.'/'.$moduleName;

        if (!is_dir($dir)) {
            throw new \Exception('Module "'.$vendor.'/'.$moduleName.'" not exists');
        }

        return rtrim($dir, '/') . '/';
    }

    /**
     * Получить список роутов модуля
     * @param string $vendor
     * @param string $moduleName
     * @throws \Exception
     * @return array
     */
    public function getRoutes($vendor, $moduleName)
    {
        $file = $this->getModuleDirectory($vendor, $moduleName).'/Init/routes.php';

        if (!file_exists($file)) {
            return [];
        }

        return include($file);
    }

    /**
     * Получить список сервисов модуля
     * @param string $vendor
     * @param string $moduleName
     * @throws \Exception
     * @return array
     */
    public function getServices($vendor, $moduleName)
    {
        $file = $this->getModuleDirectory($vendor, $moduleName).'/Init/services.php';

        if (!file_exists($file)) {
            return [];
        }

        return include($file);
    }

    public function isModuleClass($className)
    {
        return preg_match('~Okay\\\\Modules\\\\([a-zA-Z0-9]+)\\\\([a-zA-Z0-9]+)\\\\?.*~', $className);
    }

    public function getVendorName($className)
    {
        if (!$this->isModuleClass($className)) {
            throw new \Exception('Wrong module name');
        }
        return preg_replace('~Okay\\\\Modules\\\\([a-zA-Z0-9]+)\\\\([a-zA-Z0-9]+)\\\\?.*~', '$1', $className);
    }

    public function getModuleName($className)
    {
        if (!$this->isModuleClass($className)) {
            throw new \Exception('Wrong module name');
        }

        return preg_replace('~Okay\\\\Modules\\\\([a-zA-Z0-9]+)\\\\([a-zA-Z0-9]+)\\\\?.*~', '$2', $className);
    }

    public function isModuleController($controllerName)
    {
        return preg_match('~Okay\\\\Modules\\\\([a-zA-Z0-9]+)\\\\([a-zA-Z0-9]+)\\\\Controllers\\\\?.*~', $controllerName);
    }

    /**
     * Получить параметры контроллера админки. Имя контроллера имеет структуру Vendor.Module.Controller
     * В случае если имя контроллера соответствует контрорллеру админки,
     * в ответ получим массив 
     * [
     *      'vendor' => 'Vendor',
     *      'module' => 'Module',
     *      'controller' => 'Controller',
     * ]
     * @param $vendorModuleController
     * @return bool|array
     * @throws \Exception
     */
    public function getBackendControllerParams($vendorModuleController)
    {
        if (preg_match('~([a-zA-Z0-9]+)\.([a-zA-Z0-9]+)\.([a-zA-Z0-9]+)+~', $vendorModuleController, $matches)) {
            $vendor = $matches[1];
            $moduleName = $matches[2];
            $controllerName = $matches[3];

            if (is_file($this->getBackendControllersDirectory($vendor, $moduleName) . $controllerName . '.php')) {
                return [
                    'vendor' => $vendor,
                    'module' => $moduleName,
                    'controller' => $controllerName,
                ];
            }
        }
        
        return false;
    }
    
    public function getBackendControllerName($vendor, $module, $controllerClass)
    {
        return $vendor . '.' . $module . '.' . $controllerClass;
    }

    public function generateModuleTemplateDir($vendorName, $moduleName)
    {
        return realpath(__DIR__.'/../../Modules/'.$vendorName.'/'.$moduleName.'/design/html/');
    }
}