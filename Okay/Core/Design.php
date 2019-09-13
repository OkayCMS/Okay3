<?php


namespace Okay\Core;


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

    /** @var array */
    private $smartyFunctions = [];
    
    /** @var array */
    private $smartyModifiers = [];

    /** @var string */
    private $moduleTemplateDir;

    /** @var string */
    private $defaultTemplateDir;

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
    ];


    public function __construct(
        Smarty $smarty,
        Mobile_Detect $mobileDetect,
        TemplateConfig $templateConfig,
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
    
    /*Подключение переменной в шаблон*/
    public function assign($var, $value, $dynamicJs = false)
    {
        
        if ($dynamicJs === true) {
            $_SESSION['dynamic_js']['vars'][$var] = $value;
        }
        
        return $this->smarty->assign($var, $value);
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
    public function get_var($name) {
        return $this->smarty->getTemplateVars($name);
    }

    /*Очитска кэша Smarty*/
    public function clearCache()
    {
        $this->smarty->clearAllCache();
    }

    /*Определение мобильного устройства*/
    public function isMobile(){
        $res = $this->detect->isMobile();
        return $res;
    }

    /*Определение планшетного устройства*/
    public function isTablet(){
        $res = $this->detect->isTablet();
        return $res;
    }

    public function setSmartyTemplatesDir($dir)
    {
        $this->smarty->setTemplateDir($dir);
    }

    public function getModuleVendor()
    {
        return preg_replace('~.*/?Okay/Modules/([a-zA-Z0-9]+)/([a-zA-Z0-9]+)/?.*~', '$1', $this->getModuleTemplatesDir());
    }

    public function getModuleName()
    {
        return preg_replace('~.*/?Okay/Modules/([a-zA-Z0-9]+)/([a-zA-Z0-9]+)/?.*~', '$2', $this->getModuleTemplatesDir());
    }


}
