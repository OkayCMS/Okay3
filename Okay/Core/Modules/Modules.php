<?php


namespace Okay\Core\Modules;


use Okay\Core\Design;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\Config;
use Okay\Entities\ManagersEntity;
use Okay\Entities\ModulesEntity;
use Okay\Core\EntityFactory;
use OkayLicense\License;
use Okay\Core\ServiceLocator;
use Okay\Core\OkayContainer\OkayContainer;

class Modules // todo подумать, мож сюда переедит CRUD Entity/Modules
{
    /**
     * @var Module
     */
    private $module;
    /**
     * @var License
     */
    private $license;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var Database
     */
    private $db;

    /**
     * @var Config
     */
    private $config;
    
    /** @var array список контроллеров бекенда */
    private $backendControllersList = [];
    
    /** @var array список запущенных модулей */
    private $runningModules = [];

    public function __construct(
        EntityFactory $entityFactory,
        License $license,
        Module $module,
        QueryFactory $queryFactory,
        Database $database,
        Config $config
    ) {
        $this->entityFactory = $entityFactory;
        $this->module        = $module;
        $this->license       = $license;
        $this->queryFactory  = $queryFactory;
        $this->db            = $database;
        $this->config        = $config;
    }
    
    /**
     * Метод возвращает список зарегистрированных контроллеров для бекенда
     * @return array
     */
    public function getBackendControllers()
    {
        return $this->backendControllersList;
    }

    public function startAllModules()
    {
        $this->startModules(false);
    }
    
    /**
     * Процедура запуска включенных подулей. Включает в себя загрузку конфигураций,
     * маршрутов и сервисов обявленных в рамках модулей
     *
     * @throws \Exception
     * @return void
     */
    public function startEnabledModules()
    {
        $this->startModules(true);
    }

    private function startModules($activeOnly = true)
    {

        $select = $this->queryFactory->newSelect()
            ->from(ModulesEntity::getTable())
            ->cols(['id', 'vendor', 'module_name', 'enabled'])
            ->orderBy(['position ASC']);

        $this->db->query($select);
        $modules = $this->db->results();

        $SL = new ServiceLocator();
        /** @var Design $design */
        $design = $SL->getService(Design::class);

        foreach ($modules as $module) {
            if ($this->module->moduleDirectoryNotExists($module->vendor, $module->module_name)) {
                continue;
            }

            // TODO подумать над тем, чтобы перенести этот код отсюда
            if ($activeOnly === true && (int) $module->enabled !== 1) {
                $plugins = $this->module->getSmartyPlugins($module->vendor, $module->module_name);
                foreach ($plugins as $plugin) {
                    $reflector = new \ReflectionClass($plugin['class']);
                    $props     = (object) $reflector->getDefaultProperties();
                    $parentClass = $reflector->getParentClass();

                    if (!empty($props->tag)) {
                        $tag = $props->tag;
                    } else {
                        $tag = strtolower($reflector->getShortName());
                    }

                    $mock = function() {
                        return '';
                    };

                    if ($parentClass->name === \Okay\Core\SmartyPlugins\Func::class) {
                        $design->registerPlugin('function', $tag, $mock);
                    }
                    elseif ($parentClass->name === \Okay\Core\SmartyPlugins\Modifier::class) {
                        $design->registerPlugin('modifier', $tag, $mock);
                    }
                }

                continue;
            }

            // Запоминаем какие модули мы запустили, они понадобятся чтобы активировать их js и css
            $this->runningModules[] = [
                'vendor' => $module->vendor,
                'module_name' => $module->module_name,
            ];

            $moduleConfigFile = __DIR__ . '/../../Modules/' . $module->vendor . '/' . $module->module_name . '/config/config.php';
            if (is_file($moduleConfigFile)) {
                $this->config->loadConfigsFrom($moduleConfigFile);
            }
            
            $this->backendControllersList = array_merge($this->backendControllersList, $this->license->startModule($module->id, $module->vendor, $module->module_name));
        }
    }

    /**
     * Возвращаем массив запущенных модулей в формате указанном ниже
     *
     *  [
            'vendor' => $module->vendor,
            'module_name' => $module->module_name,
        ];
     */
    public function getRunningModules()
    {
        return $this->runningModules;
    }
    
    /**
     * Метод проверяет активен ли модуль
     * @param $vendor
     * @param $moduleName
     * @return bool
     * @throws \Exception
     */
    public function isActiveModule($vendor, $moduleName)
    {
        $this->db->query(
            $this->queryFactory->newSelect()
                ->from(ModulesEntity::getTable())
                ->cols(['enabled'])
                ->where('vendor = ?', (string)$vendor)
                ->where('module_name = ?', (string)$moduleName)
        );
        
        return (bool)$this->db->result('enabled');
    }
    
    public function getPaymentModules($langLabel)
    {
        $modules = [];

        /** @var ModulesEntity $modulesEntity */
        $modulesEntity = $this->entityFactory->get(ModulesEntity::class);
        foreach ($modulesEntity->find(['enabled' => 1, 'type' => MODULE_TYPE_PAYMENT]) as $module) {
            $module->settings = $this->initModuleSettings($module->vendor, $module->module_name, $langLabel);
            $modules[$module->vendor . '/' . $module->module_name] = $module;
        }
        return $modules;
    }
    
    public function getDeliveryModules($langLabel)
    {
        $modules = [];
        /** @var ModulesEntity $modulesEntity */
        $modulesEntity = $this->entityFactory->get(ModulesEntity::class);
        foreach ($modulesEntity->find(['enabled' => 1, 'type' => MODULE_TYPE_DELIVERY]) as $module) {
            $module->settings = $this->initModuleSettings($module->vendor, $module->module_name, $langLabel);
            $modules[$module->vendor . '/' . $module->module_name] = $module;
        }
        return $modules;
    }

    private function initModuleSettings($vendor, $moduleName, $langLabel)
    {
        $settings = [];
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);

        $moduleTranslations = $this->getModuleBackendTranslations($vendor, $moduleName, $langLabel);
        if (is_readable($moduleDir . '/settings.xml') && $xml = simplexml_load_file($moduleDir . '/settings.xml')) {

            foreach ($xml->settings as $setting) {
                $attributes = $setting->attributes();
                $settingName = (string)$setting->name;
                $translationName = preg_replace('~{\$lang->(.+)?}~', '$1', $settingName);
                $settingName = isset($moduleTranslations[$translationName]) ? $moduleTranslations[$translationName] : $settingName;
                $settings[(string)$setting->variable] = new \stdClass;
                $settings[(string)$setting->variable]->name = $settingName;
                $settings[(string)$setting->variable]->variable = (string)$setting->variable;
                
                if (empty((array)$setting->options)) {
                    $settings[(string)$setting->variable]->type = 'text';
                    if (!empty($attributes->type) && in_array(strtolower($attributes->type), ['hidden', 'text', 'date', 'checkbox'])) {
                        $settings[(string)$setting->variable]->type = strtolower($attributes->type);
                    }
                    
                    if (!empty((string)$setting->value) && $settings[(string)$setting->variable]->type == 'checkbox') {
                        $settings[(string)$setting->variable]->value = (string)$setting->value;
                    }
                    
                } else {
                    $settings[(string)$setting->variable]->options = [];
                    foreach ($setting->options as $option) {
                        $optionName = (string)$option->name;
                        $translationName = preg_replace('~{\$lang->(.+)?}~', '$1', $optionName);
                        $optionName = isset($moduleTranslations[$translationName]) ? $moduleTranslations[$translationName] : $optionName;
                        $settings[(string)$setting->variable]->options[(string)$option->value] = new \stdClass;
                        $settings[(string)$setting->variable]->options[(string)$option->value]->name = $optionName;
                        $settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
                    }
                }
            }
        }
        
        return $settings;
    }
    
    /**
     * Метод возвращает массив переводов
     * @param string $vendor
     * @param string $moduleName
     * @param string $langLabel
     * @return array
     * @throws \Exception
     */
    public function getModuleBackendTranslations($vendor, $moduleName, $langLabel)
    {
        $langLabel = $this->getBackendLangLabel($vendor, $moduleName, $langLabel);
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);

        $lang = [];
        if (is_file($moduleDir . '/Backend/lang/' . $langLabel . '.php')) {
            include $moduleDir . 'Backend/lang/' . $langLabel . '.php';
        }
        return $lang;
    }
    
    /**
     * @param string $vendor
     * @param string $moduleName
     * @param string $langLabel
     * @return string
     * @throws \Exception
     */
    private function getBackendLangLabel($vendor, $moduleName, $langLabel)
    {
        $resultLabel = '';
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);
        
        if (is_file($moduleDir . 'Backend/lang/' . $langLabel . '.php')) {
            $resultLabel = $langLabel;
        } elseif (is_file($moduleDir . 'Backend/lang/en.php')) {
            $resultLabel = 'en';
        } elseif (is_dir($moduleDir . 'Backend/lang/') && ($langs = array_slice(scandir($moduleDir . 'Backend/lang/'), 2)) && count($langs) > 0) {
            $resultLabel = str_replace('.php', '', reset($langs));
        }
        
        return $resultLabel;
    }

    public function getModuleFrontTranslations($vendor, $moduleName, $langLabel)
    {
        $langLabel = $this->getFrontLangLabel($vendor, $moduleName, $langLabel);
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);

        $lang = [];
        if (is_file($moduleDir . '/design/lang/' . $langLabel . '.php')) {
            include $moduleDir . 'design/lang/' . $langLabel . '.php';
        }
        return $lang;
    }

    /**
     * @param string $vendor
     * @param string $moduleName
     * @param string $langLabel
     * @return string
     * @throws \Exception
     */
    private function getFrontLangLabel($vendor, $moduleName, $langLabel)
    {
        $resultLabel = '';
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);

        if (is_file($moduleDir . 'design/lang/' . $langLabel . '.php')) {
            $resultLabel = $langLabel;
        } elseif (is_file($moduleDir . 'design/lang/en.php')) {
            $resultLabel = 'en';
        } elseif (is_dir($moduleDir . 'design/lang/') && ($langs = array_slice(scandir($moduleDir . 'design/lang/'), 2)) && count($langs) > 0) {
            $resultLabel = str_replace('.php', '', reset($langs));
        }

        return $resultLabel;
    }
    
}