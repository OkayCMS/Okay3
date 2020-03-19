<?php


namespace Okay\Core;


use Okay\Core\Modules\Module;
use OkayLicense\License;
use Smarty;
use Mobile_Detect;

class Design
{
    
    const TEMPLATES_DEFAULT = 'default';
    const TEMPLATES_MODULE = 'module';
    
    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * @var Mobile_Detect
     */
    public $detect;

    /**
     * @var TemplateConfig
     */
    private $templateConfig;

    /**
     * @var Module
     */
    private $module;

    /** @var array */
    private $smartyFunctions = [];
    
    /** @var array */
    private $smartyModifiers = [];

    /** @var string */
    private $moduleTemplateDir;

    /** @var string */
    private $defaultTemplateDir;
    
    private $moduleChangeDir = null;
    
    private $prevModuleDir;
    
    private $isUseModuleDirBeforeChange;
    
    private $rootDir;

    /** @var string */
    private $useTemplateDir = self::TEMPLATES_DEFAULT;
    
    /**
     * @var array
     */
    private $allowedPhpFunctions = [
        'escape',
        'cat',
        'count',
        'in_array',
        'nl2br',
        'str_replace',
        'reset',
        'floor',
        'round',
        'ceil',
        'max',
        'min',
        'number_format',
        'print_r',
        'var_dump',
        'printa',
        'file_exists',
        'stristr',
        'strtotime',
        'empty',
        'urlencode',
        'intval',
        'isset',
        'sizeof',
        'is_array',
        'array_intersect',
        'time',
        'array',
        'base64_encode',
        'implode',
        'explode',
        'preg_replace',
        'preg_match',
        'key',
        'json_encode',
        'json_decode',
        'is_file',
        'date',
        'strip_tags',
        'trim',
        'ltrim',
        'rtrim',
        'array_keys',
    ];


    public function __construct(
        Smarty $smarty,
        Mobile_Detect $mobileDetect,
        TemplateConfig $templateConfig,
        Module $module,
        $smartyCacheLifetime,
        $smartyCompileCheck,
        $smartyHtmlMinify,
        $smartyDebugging,
        $smartySecurity,
        $smartyCaching,
        $rootDir
    ) {
        $this->templateConfig = $templateConfig;
        $this->detect         = $mobileDetect;
        $this->module         = $module;
        $this->rootDir        = $rootDir;

        $this->smarty = $smarty;
        $this->smarty->compile_check   = $smartyCompileCheck;
        $this->smarty->caching         = $smartyCaching;
        $this->smarty->cache_lifetime  = $smartyCacheLifetime;
        $this->smarty->debugging       = $smartyDebugging;
        $this->smarty->error_reporting = E_ALL & ~E_NOTICE;

        $theme = $this->templateConfig->getTheme();

        if ($smartySecurity == true) {
            $this->smarty->enableSecurity();
            $this->smarty->security_policy->php_modifiers = $this->allowedPhpFunctions;
            $this->smarty->security_policy->php_functions = $this->allowedPhpFunctions;
            $this->smarty->security_policy->secure_dir = array(
                $rootDir . 'design/' . $theme,
                $rootDir . 'backend/design',
                $rootDir . 'Okay/Modules',
            );
        }

        $this->defaultTemplateDir = $rootDir.'design/'.$theme.'/html';
        $this->smarty->setCompileDir($rootDir.'compiled/'.$theme);
        $this->smarty->setTemplateDir($this->defaultTemplateDir);

        // Создаем папку для скомпилированных шаблонов текущей темы
        if (!is_dir($this->smarty->getCompileDir())) {
            mkdir($this->smarty->getCompileDir(), 0777);
        }
        
        $this->smarty->setCacheDir('cache');
        
        if ($smartyHtmlMinify) {
            $this->smarty->loadFilter('output', 'trimwhitespace');
        }
    }

    /**
     * Метод нужен для модулей, если в каком-то экстендере или еще где нужно обработать tpl файл
     * нужно предватилельно вызвать этот метод, чтобы переключить директорию tpl файлов.
     * После вызова fetch() нужно обязательно вернуть стандартную директорию методом rollbackTemplatesDir()
     * 
     * @param $moduleClassName
     * @throws \Exception
     */
    public function setModuleDir($moduleClassName)
    {
        
        if ($this->moduleChangeDir !== null) {
            throw new \Exception("Module \"{$this->moduleChangeDir}\" is changed dir and not rollback from Design::rollbackTemplatesDir()");
        }
        
        $vendor = $this->module->getVendorName($moduleClassName);
        $name = $this->module->getModuleName($moduleClassName);

        $moduleTemplateDir = $this->module->generateModuleTemplateDir(
            $vendor,
            $name
        );

        $this->prevModuleDir = $this->getModuleTemplatesDir();
        $this->isUseModuleDirBeforeChange = $this->isUseModuleDir();
        $this->setModuleTemplatesDir($moduleTemplateDir);
        $this->useModuleDir();
        $this->moduleChangeDir = "{$vendor}\{$name}";
    }

    /**
     * Метод возвращает стандартную директорию tpl файлов.
     * Применяется если в модуле сменили директорию tpl файлов посредством метода setModuleDir()
     */
    public function rollbackTemplatesDir()
    {
        if (!empty($this->prevModuleDir)) {
            $this->setModuleTemplatesDir($this->prevModuleDir);
        }
        if (!$this->isUseModuleDirBeforeChange) {
            $this->useDefaultDir();
        }
        $this->moduleChangeDir = null;
    }
    
    /**
     * Проверка существует ли данный файл шаблона
     * 
     * @param $tplFile
     * @return bool
     * @throws \SmartyException
     */
    public function templateExists($tplFile)
    {
        if ($this->isUseModuleDir() === false) {
            $this->setSmartyTemplatesDir($this->getDefaultTemplatesDir());
        } else {
            
            $namespace = str_replace($this->rootDir, '', $this->getModuleTemplatesDir());
            $namespace = str_replace('/', '\\', $namespace);
            
            $vendor = $this->module->getVendorName($namespace);
            $moduleName = $this->module->getModuleName($namespace);
            /**
             * Устанавливаем директории поиска файлов шаблона как:
             * Директория модуля в дизайне (если модуль кастомизируют)
             * Директория модуля
             * Стандартная директория дизайна
             */
            $this->setSmartyTemplatesDir([
                dirname($this->getDefaultTemplatesDir()) . "/modules/{$vendor}/{$moduleName}/html",
                $this->getModuleTemplatesDir(),
                $this->getDefaultTemplatesDir(),
            ]);
        }
        
        return $this->smarty->templateExists(trim(preg_replace('~[\n\r]*~', '', $tplFile)));
    }
    
    public function registerPlugin($type, $tag, $callback)
    {
        switch ($type) {
            case 'modifier':
                $this->smartyModifiers[$tag] = $callback;
                break;
            case 'function':
                $this->smartyFunctions[$tag] = $callback;
                break;
        }
    }

    /**
     * @param string $var
     * @param mixed $value
     * @param bool $dynamicJs Если установить в true, переменная будет доступна в файле scripts.tpl клиентского шаблона,
     * как обычная Smarty переменная
     * @return \Smarty_Internal_Data
     */
    public function assign($var, $value, $dynamicJs = false)
    {
        
        if ($dynamicJs === true) {
            $_SESSION['dynamic_js']['vars'][$var] = $value;
        }
        
        return $this->smarty->assign($var, $value);
    }

    /**
     * @param $var
     * @param $value
     * 
     * Метод позволяет передать переменную с PHP непосредственно в JS код
     * Считать переменную можно будет как okay.var_name
     */
    public function assignJsVar($var, $value)
    {
        $_SESSION['common_js']['vars'][$var] = $value;
    }

    /*Отображение конкретного шаблона*/
    public function fetch($template)
    {
        $this->registerSmartyPlugins();
        return License::getHtml($this, $template);
    }

    public function useDefaultDir()
    {
        $this->useTemplateDir = self::TEMPLATES_DEFAULT;
    }

    public function useModuleDir()
    {
        $this->useTemplateDir = self::TEMPLATES_MODULE;
    }

    public function isUseModuleDir()
    {
        if ($this->useTemplateDir === self::TEMPLATES_MODULE) {
            return true;
        }
        return false;
    }
    
    private function registerSmartyPlugins()
    {
        foreach ($this->smartyModifiers as $tag => $callback) {
            $this->smarty->registerPlugin('modifier', $tag, $callback);
            unset($this->smartyModifiers[$tag]);
        }
        
        foreach ($this->smartyFunctions as $tag => $callback) {
            $this->smarty->registerPlugin('function', $tag, $callback);
            unset($this->smartyFunctions[$tag]);
        }
    }

    public function getDefaultTemplatesDir()
    {
        return rtrim($this->defaultTemplateDir , '/');
    }

    public function setModuleTemplatesDir($moduleTemplateDir)
    {
        $this->moduleTemplateDir = $moduleTemplateDir;
    }

    public function getModuleTemplatesDir()
    {
        return rtrim($this->moduleTemplateDir , '/');
    }

    /*Установка директории файлов шаблона(отображения)*/
    public function setTemplatesDir($dir)
    {
        $dir = rtrim($dir, '/') . '/';
        if (!is_string($dir)) {
            throw new \Exception("Param \$dir must be string");
        }
        
        $this->defaultTemplateDir = $dir;
        $this->setSmartyTemplatesDir($dir);
    }

    /*Установка директории для готовых файлов для отображения*/
    public function setCompiledDir($dir)
    {
        $this->smarty->setCompileDir($dir);
    }

    /*Получение директории файлов шаблона(отображения)*/
    public function getTemplatesDir()
    {
        $dirs = $this->smarty->getTemplateDir();
        return reset($dirs);
    }

    /*Получение директории для готовых файлов для отображения*/
    public function getCompiledDir()
    {
        return $this->smarty->getCompileDir();
    }

    /*Выборка переменой*/
    public function getVar($name)
    {
        return $this->smarty->getTemplateVars($name);
    }
    
    public function get_var($name)
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated. Please use getVar', E_USER_DEPRECATED);
        return $this->getVar($name);
    }

    /*Очитска кэша Smarty*/
    public function clearCache()
    {
        $this->smarty->clearAllCache();
    }

    /*Определение мобильного устройства*/
    public function isMobile()
    {
        return $this->detect->isMobile();
    }

    /*Определение планшетного устройства*/
    public function isTablet()
    {
        return $this->detect->isTablet();
    }

    public function setSmartyTemplatesDir($dir)
    {
        $this->smarty->setTemplateDir($dir);
    }

}
