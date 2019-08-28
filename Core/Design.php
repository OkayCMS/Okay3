<?php


namespace Okay\Core;


use \Smarty;
use \Mobile_Detect;

class Design
{
    
    public $smarty;
    public $detect;
    private $templateConfig;
    private $config;
    private $allowed_php_functions;
    
    private $smartyModifiers = [];
    private $smartyFunctions = [];

    /*Объявляем основные настройки для дизайна*/
    public function __construct(Config $config, Smarty $smarty, Mobile_Detect $mobileDetect, TemplateConfig $templateConfig) {

        $this->config = $config;
        $this->templateConfig = $templateConfig;
        $this->detect = $mobileDetect;
        // Создаем и настраиваем Смарти
        $this->smarty = $smarty;
        $this->smarty->compile_check = $this->config->smarty_compile_check; // todo убрать конфиги отсюда
        $this->smarty->caching = $this->config->smarty_caching;
        $this->smarty->cache_lifetime = $this->config->smarty_cache_lifetime;
        $this->smarty->debugging = $this->config->smarty_debugging;
        $this->smarty->error_reporting = E_ALL & ~E_NOTICE;
        
        $theme = $this->templateConfig->getTheme();

        $smarty_security = $this->config->smarty_security;
        if ($smarty_security == true) {
            $this->allowed_php_functions = [
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

            // Настраиваем безопасный режим
            $this->smarty->enableSecurity();
            $this->smarty->security_policy->php_modifiers = $this->allowed_php_functions;
            $this->smarty->security_policy->php_functions = $this->allowed_php_functions;

            $this->smarty->security_policy->secure_dir = array(
                $this->config->root_dir . 'design/' . $theme,
                $this->config->root_dir . 'backend/design',
                $this->config->root_dir . 'payment',
            );
        }

        $this->smarty->compile_dir = $this->config->root_dir.'compiled/'.$theme;
        $this->smarty->template_dir = $this->config->root_dir.'design/'.$theme.'/html';
        
        // Создаем папку для скомпилированных шаблонов текущей темы
        if(!is_dir($this->smarty->compile_dir)) {
            mkdir($this->smarty->compile_dir, 0777);
        }
        
        $this->smarty->cache_dir = 'cache';
        
        if($this->config->smarty_html_minify) {
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
    public function fetch($template) {
        
        // Регистрируем плагины в смарти
        $this->registerSmartyPlugins();
        /*// Передаем в дизайн то, что может понадобиться в нем // todo передать настройки и конфиг в дизайн
        $this->assign('config',        $this->config);
        $this->assign('settings',    $this->settings);*/
        
        return $this->smarty->fetch($template);
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
    
    /*Установка директории файлов шаблона(отображения)*/
    public function set_templates_dir($dir) {
        $this->smarty->template_dir = $dir;
    }

    /*Установка директории для готовых файлов для отображения*/
    public function set_compiled_dir($dir) {
        $this->smarty->compile_dir = $dir;
    }

    /*Получение директории файлов шаблона(отображения)*/
    public function get_templates_dir() {
        return $this->smarty->template_dir;
    }

    /*Получение директории для готовых файлов для отображения*/
    public function get_compiled_dir() {
        return $this->smarty->compile_dir;
    }

    /*Выборка переменой*/
    public function get_var($name) {
        return $this->smarty->getTemplateVars($name);
    }

    /*Очитска кэша Smarty*/
    public function clear_cache() {
        $this->smarty->clearAllCache();
    }
    
    /*Плагин возвращает название активной темы*/
    public function get_theme_plugin() {
        return $this->templateConfig->getTheme();
    }

    /*Определение мобильного устройства*/
    public function is_mobile(){
        $res = $this->detect->isMobile();
        return $res;
    }

    /*Определение планшетного устройства*/
    public function is_tablet(){
        $res = $this->detect->isTablet();
        return $res;
    }
    
}
