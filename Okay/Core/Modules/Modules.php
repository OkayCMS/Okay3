<?php


namespace Okay\Core\Modules;


use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\TemplateConfig;
use Okay\Core\TemplateConfig\Css as TemplateCss;
use Okay\Core\TemplateConfig\Js as TemplateJs;
use Okay\Entities\ManagersEntity;
use Okay\Entities\ModulesEntity;
use Okay\Core\EntityFactory;
use OkayLicense\License;

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
    
    /** @var array список контроллеров бекенда */
    private $backendControllersList = [];
    
    /** @var array список запущенных модулей */
    private $runningModules = [];

    public function __construct(
        EntityFactory $entityFactory,
        License $license,
        Module $module,
        QueryFactory $queryFactory,
        Database $database
    ) {
        $this->entityFactory = $entityFactory;
        $this->module = $module;
        $this->license = $license;
        $this->queryFactory = $queryFactory;
        $this->db = $database;
        
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
            ->cols(['id', 'vendor', 'module_name']);
        
        if ($activeOnly === true) {
            $select->where('enabled = 1');
        }
        
        $this->db->query($select);
        $modules = $this->db->results();

        foreach ($modules as $module) {
            
            // Запоминаем какие модули мы запустили, они понадобятся чтобы активировать их js и css
            $this->runningModules[] = [
                'vendor' => $module->vendor,
                'module_name' => $module->module_name,
            ];
            
            $this->backendControllersList = array_merge($this->backendControllersList, $this->license->startModule($module->id, $module->vendor, $module->module_name));
        }
    }
    
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
    
    public function getPaymentModules()
    {

        $modules = [];

        /** @var ModulesEntity $modulesEntity */
        $modulesEntity = $this->entityFactory->get(ModulesEntity::class);
        foreach ($modulesEntity->find(['enabled' => 1, 'type' => MODULE_TYPE_PAYMENT]) as $module) {
            $langLabel = $this->getLangLabel($module->vendor, $module->module_name);
            $moduleDir = $this->module->getModuleDirectory($module->vendor, $module->module_name);

            $lang = [];
            $moduleTranslations = [];
            if (include $moduleDir . '/lang/' . $langLabel . '.php') {
                foreach ($lang as $var => $translation) {
                    $moduleTranslations["{\$lang->{$var}}"] = $translation;
                }
            }
            
            if (is_readable($moduleDir . '/settings.xml') && $xml = simplexml_load_file($moduleDir . '/settings.xml')) {
                $module->settings = [];

                foreach ($xml->settings as $setting) {
                    $settingName = (string)$setting->name;
                    $settingName = isset($moduleTranslations[$settingName]) ? $moduleTranslations[$settingName] : $settingName;
                    $module->settings[(string)$setting->variable] = new \stdClass;
                    $module->settings[(string)$setting->variable]->name = $settingName;
                    $module->settings[(string)$setting->variable]->variable = (string)$setting->variable;
                    $module->settings[(string)$setting->variable]->variable_options = [];
                    foreach ($setting->options as $option) {
                        $module->settings[(string)$setting->variable]->options[(string)$option->value] = new \stdClass;
                        $module->settings[(string)$setting->variable]->options[(string)$option->value]->name = (string)$option->name;
                        $module->settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
                    }
                }
            }
            
            $modules[$module->vendor . '/' . $module->module_name] = $module;
        }
        return $modules;
    }

    /**
     * Метод возвращает массив переводов
     * @param string $vendor
     * @param string $moduleName
     * @return array
     * @throws \Exception
     */
    public function getModuleTranslations($vendor, $moduleName)
    {
        $langLabel = $this->getLangLabel($vendor, $moduleName);
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);

        $lang = [];
        include $moduleDir . '/lang/' . $langLabel . '.php';
        return $lang;
    }
    
    /**
     * @param string $vendor
     * @param string $moduleName
     * @return string
     * @throws \Exception
     */
    private function getLangLabel($vendor, $moduleName)
    {
        $langLabel = '';
        /** @var ManagersEntity $managersEntity */
        $managersEntity = $this->entityFactory->get(ManagersEntity::class);

        $manager = $managersEntity->get($_SESSION['admin']);
        $moduleDir = $this->module->getModuleDirectory($vendor, $moduleName);
        
        if (is_file($moduleDir . '/lang/' . $manager->lang . '.php')) {
            $langLabel = $manager->lang;
        } elseif (is_file($moduleDir . '/lang/en.php')) {
            $langLabel = 'en';
        } elseif (($langs = array_slice(scandir($moduleDir . '/lang/'), 2)) && count($langs) > 0) {
            $langLabel = str_replace('.php', '', reset($langs));
        }
        
        return $langLabel;
    }
}