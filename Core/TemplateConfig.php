<?php


namespace Okay\Core;


use MatthiasMullie\Minify\JS;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\OutputFormat;

class TemplateConfig
{
    /** @var Config */
    private $config;
    
    private $template_css = [];
    private $individual_css = [];
    private $template_js = [];
    private $individual_js = [];
    private $defer_js_files = [];
    private $cssVariables = [];
    private $theme_settings_file = 'theme-settings.css';
    private $theme;
    private $adminTheme;
    private $adminThemeManagers;
    private $compile_css_dir = 'cache/css/';
    private $compile_js_dir = 'cache/js/';
    private $settings_file;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param $theme
     * @param $adminTheme
     * @param $adminThemeManagers
     * 
     * Метод конфигуратор, нужен, чтобы не передавать в зависимости целый класс Settings
     */
    public function configure($theme, $adminTheme, $adminThemeManagers)
    {
        $this->theme = $theme;
        $this->adminTheme = $adminTheme;
        $this->adminThemeManagers = $adminThemeManagers;
        $this->settings_file = 'design/' . $this->getTheme() . '/css/' . $this->theme_settings_file;
    }
    
    public function __destruct() {
        // Инвалидация компилированных js и css файлов
        $css = glob($this->config->root_dir . $this->compile_css_dir . $this->getTheme() . ".*.css");
        $js = glob($this->config->root_dir . $this->compile_js_dir . $this->getTheme() . ".*.js");
        
        $cache_files = array_merge($css, $js);
        if (is_array($cache_files)) {
            foreach ($cache_files as $f) {
                $file_time = filemtime($f);
                // Если файл редактировался более недели назад, удалим его, вероятнее всего он уже не нужен
                if ($file_time !== false && time() - $file_time > 604800) {
                    @unlink($f);
                }
            }
        }
    }

    public function register_css($filename, $position = 'head', $dir = null, $individual = false) {
        
        if ($position != 'head' && $position != 'footer') {
            $position = 'head';
        }
        
        // Файл настроек шаблона регистрировать не нужно
        if ($filename != $this->theme_settings_file && $this->check_file($filename, 'css', $dir) === true) {
            $full_path = $this->get_full_path($filename, 'css', $dir);
            $file_id = md5($full_path);
            if ($individual === true) {
                $this->individual_css[$position][$file_id] = $full_path;
            } else {
                $this->template_css[$position][$file_id] = $full_path;
            }
        }
    }

    public function register_js($filename, $position = 'head', $dir = null, $individual = false, $defer = false) {

        if ($position != 'head' && $position != 'footer') {
            $position = 'head';
        }

        if ($this->check_file($filename, 'js', $dir) === true) {
            $full_path = $this->get_full_path($filename, 'js', $dir);
            $file_id = md5($full_path);
            if ($individual === true) {
                if ($defer === true) {
                    $this->defer_js_files[$full_path] = $full_path;
                }
                $this->individual_js[$position][$file_id] = $full_path;
            } else {
                $this->template_js[$position][$file_id] = $full_path;
            }
        }
    }

    /**
     * Метод возвращает теги на подключение всех зарегестрированных js и css для блока head
     * @return string
     */
    public function head() {
        return $this->get_include_html('head');
    }

    /**
     * Метод возвращает теги на подключение всех зарегестрированных js и css для футера
     * @return string
     */
    public function footer() {
        $footer = $this->get_include_html('footer');

        /*// todo Подключаем тултип для админа
        if ($manager = $this->managers->get_manager()) {
            
            $templates_dir = $this->design->get_templates_dir();
            $compiled_dir = $this->design->get_compiled_dir();
            
            $this->design->set_templates_dir('backend/design/html');
            $this->design->set_compiled_dir('backend/design/compiled');

            // Перевод админки
            $backend_translations = $this->backend_translations;
            $file = "backend/lang/" . $manager->lang . ".php";
            if (!file_exists($file)) {
                foreach (glob("backend/lang/??.php") as $f) {
                    $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                    break;
                }
            }
            require_once($file);
            $this->design->assign('btr', $backend_translations);
            $footer .= $this->design->fetch('admintooltip.tpl');
            
            // Возвращаем настройки компилирования файлов smarty
            $this->design->set_templates_dir($templates_dir);
            $this->design->set_compiled_dir($compiled_dir);
        }*/

        //$footer .= $this->adminToolTip->getToolTip();
        
        // Подключаем динамический JS (scripts.tpl)
        $dynamic_js_file = "design/" . $this->getTheme() . "/html/scripts.tpl";
        if (is_file($dynamic_js_file)) {
            $filename = md5_file($dynamic_js_file) . json_encode($_GET);
            if (isset($_SESSION['dynamic_js'])) {
                $filename .= json_encode($_SESSION['dynamic_js']);
            }
            
            $filename = md5($filename);
            
            $getParams = (!empty($_GET) ? "?" . http_build_query($_GET) : '');
            $footer .= "<script src=\"" . Router::generateUrl('dynamic_js', ['fileId' => $filename]) . $getParams . "\"" . ($this->config->scripts_defer == true ? " defer" : '') . "></script>" . PHP_EOL;
        }
        
        return $footer;
    }
    
    /**
     * Метод возвращает название активной темы. Нужно для того,
     * чтобы логика определения темы под админом была в одном месте
     * @return string Название темы
     */
    public function getTheme()
    {
        if (!empty($this->theme)) {
            return $this->theme;
        }
        
        $adminTheme = $this->adminTheme;
        $adminThemeManagers = $this->adminThemeManagers;
        if (!empty($_SESSION['admin']) && !empty($adminTheme) && $this->theme != $this->adminTheme) {
            if (empty($adminThemeManagers) || in_array($_SESSION['admin'], $this->adminThemeManagers)) {
                $this->theme = $this->adminTheme;
            }
        }
        
        return $this->theme;
    }

    public function clearCompiled()
    {
        $cache_directories = [
            $this->compile_css_dir,
            $this->compile_js_dir,
        ];
        
        foreach ($cache_directories as $dir) {
            if (is_dir($dir)) {
                foreach (scandir($dir) as $file) {
                    if (!in_array($file, array(".", ".."))) {
                        @unlink($dir . $file);
                    }
                }
            }
        }
    }

    public function getCssVariables() {

        if (empty($this->cssVariables)) {
            $this->initCssVariables();
        }
        
        return $this->cssVariables;
    }
    
    public function updateCssVariables($variables)
    {

        if (empty($variables)) {
            return false;
        }
        
        if (empty($this->cssVariables)) {
            $this->initCssVariables();
        }
        
        $oCssParser = new Parser(file_get_contents($this->settings_file));
        $oCssDocument = $oCssParser->parse();
        foreach ($oCssDocument->getAllRuleSets() as $oBlock) {
            foreach ($oBlock->getRules() as $r) {
                if (isset($variables[$r->getRule()])) {
                    $r->setValue($variables[$r->getRule()]);
                    $this->cssVariables[$r->getRule()] = $variables[$r->getRule()];
                }
            }
        }

        $result_file = '/**' . PHP_EOL;
        $result_file .= '* Файл стилей для настройки шаблона.' . PHP_EOL;
        $result_file .= '* Регистрировать этот файл для подключения в шаблоне не нужно' . PHP_EOL;
        $result_file .= '*/' . PHP_EOL . PHP_EOL;

        $result_file .= trim($oCssDocument->render(OutputFormat::createPretty())) . PHP_EOL;
        file_put_contents($this->settings_file, $result_file);
    }
    
    public function compile_individual_css($filename, $dir = null) {
        if ($filename != $this->theme_settings_file && $this->check_file($filename, 'css', $dir) === true) {
            $file = $this->get_full_path($filename, 'css', $dir);
            
            $hash = md5_file($file) . md5_file($this->settings_file);
            $compiled_filename = $this->compile_css_dir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . $hash . '.css';

            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
            } else {
                $result_file = $this->compile_css_file($file);
                $this->save_compile_file($result_file, $compiled_filename);
            }
            
            return !empty($compiled_filename) ? "<link href=\"{$compiled_filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL : '';
        }
        return '';
    }
    
    public function compile_individual_js($filename, $dir = null, $defer = false) {
        if ($this->check_file($filename, 'js', $dir) === true) {
            $file = $this->get_full_path($filename, 'js', $dir);
            
            $compiled_filename = $this->compile_js_dir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . md5_file($file) . '.js';

            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
            } else {
                $result_file = file_get_contents($file) . PHP_EOL . PHP_EOL;

                $minifier = new JS();
                $minifier->add($result_file);
                $result_file = $minifier->minify();

                $this->save_compile_file($result_file, $compiled_filename);
            }
            
            return !empty($compiled_filename) ? "<script src=\"{$compiled_filename}\"" . ($defer === true ? " defer" : '') . "></script>" . PHP_EOL : '';
        }
        return '';
    }
    
    public function minifyJs($jsString)
    {
        $minifier = new JS();
        $minifier->add($jsString);
        $dynamicJs = $minifier->minify();

        return $dynamicJs;
    }
    
    /**
     * @param string $position
     * @return string html для подключения js и css шаблона
     */
    private function get_include_html($position = 'head') {
        $include_html = '';

        // Подключаем основной файл стилей
        if (($css_filename = $this->compile_registered_css($position)) !== '') {
            $include_html .= "<link href=\"{$css_filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL;
        }

        // Подключаем дополнительные индивидуальные файлы стилей
        if (($individual_css_filenames = $this->compile_registered_individual_css($position)) !== []) {
            foreach ($individual_css_filenames as $filename) {
                $include_html .= "<link href=\"{$filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL;
            }
        }

        // Подключаем основной JS файл
        if (($js_filename = $this->compile_registered_js($position)) !== '') {
            $include_html .= "<script src=\"{$js_filename}\"" . ($this->config->scripts_defer == true ? " defer" : '') . "></script>" . PHP_EOL;
        }

        // Подключаем дополнительные индивидуальные JS файлы
        if (($individual_js_filenames = $this->compile_registered_individual_js($position)) !== []) {
            foreach ($individual_js_filenames as $filename) {
                $include_html .= "<script src=\"{$filename}\"" . (isset($this->defer_js_files[$filename]) ? " defer" : '') . "></script>" . PHP_EOL;
            }
        }
        
        return $include_html;
    }
    
    private function compile_registered_individual_js($position) {
        $result = [];
        if (!empty($this->individual_js[$position])) {

            foreach ($this->individual_js[$position] as $k=>$file) {
                
                $compiled_filename = $this->compile_js_dir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . md5_file($file) . '.js';
                $result[] = $compiled_filename;
                
                if (isset($this->defer_js_files[$file])) {
                    $this->defer_js_files[$compiled_filename] = $compiled_filename;
                    unset($this->defer_js_files[$file]);
                }
                
                if (file_exists($compiled_filename)) {
                    // Обновляем дату редактирования файла, чтобы он не инвалидировался
                    touch($compiled_filename);
                } else {
                    $result_file = file_get_contents($file) . PHP_EOL . PHP_EOL;

                    $minifier = new JS();
                    $minifier->add($result_file);
                    $result_file = $minifier->minify();

                    $this->save_compile_file($result_file, $compiled_filename);
                }
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->individual_css[$position][$k]);
            }
        }
        return $result;
    }
    
    private function compile_registered_individual_css($position) {
        $result = [];
        if (!empty($this->individual_css[$position])) {

            foreach ($this->individual_css[$position] as $k=>$file) {
                $hash = md5_file($file) . md5_file($this->settings_file);
                $compiled_filename = $this->compile_css_dir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . $hash . '.css';
                $result[] = $compiled_filename;
                
                if (file_exists($compiled_filename)) {
                    // Обновляем дату редактирования файла, чтобы он не инвалидировался
                    touch($compiled_filename);
                } else {
                    $result_file = $this->compile_css_file($file);
                    $this->save_compile_file($result_file, $compiled_filename);
                }
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->individual_js[$position][$k]);
            }
        }
        return $result;
    }
    
    /**
     * @param $position //head|footer указание куда файл генерится
     * Метод компилирует все зарегистрированные, через метод register_css(), CSS файлы
     * Собитаются они в одном общем выходном файле, в кеше
     * Также здесь подставляются значения переменных CSS.
     * @return string|null
     */
    private function compile_registered_css($position) {
        
        $result_file = '';
        $compiled_filename = '';
        
        if (!empty($this->template_css[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->template_css[$position] as $file) {
                $compiled_filename .= md5_file($file) . md5_file($this->settings_file);
            }
            
            $compiled_filename = $this->compile_css_dir . $this->getTheme() . '.' . $position . '.' . md5($compiled_filename) . '.css';

            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
                return $compiled_filename;
            }
            
            foreach ($this->template_css[$position] as $k=>$file) {
                $result_file .= $this->compile_css_file($file);
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->template_css[$position][$k]);
            }
        }
        
        $this->save_compile_file($result_file, $compiled_filename);
        
        return $compiled_filename;
    }
    
    private function compile_registered_js($position) {

        $result_file = '';
        $compiled_filename = '';
        if (!empty($this->template_js[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->template_js[$position] as $file) {
                $compiled_filename .= md5_file($file);
            }
            
            $compiled_filename = $this->compile_js_dir . $this->getTheme() . '.' . $position . '.' . md5($compiled_filename) . '.js';

            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
                return $compiled_filename;
            }
            
            foreach ($this->template_js[$position] as $k=>$file) {
                $filename = pathinfo($file, PATHINFO_BASENAME);

                $result_file .= '/*! #File ' . $filename . ' */' . PHP_EOL;
                $result_file .= file_get_contents($file) . PHP_EOL . PHP_EOL;
                
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->template_js[$position][$k]);
            }
        }

        $minifier = new JS();
        $minifier->add($result_file);
        $result_file = $minifier->minify();
        
        $this->save_compile_file($result_file, $compiled_filename);
        
        return $compiled_filename;
    }
    
    /**
     * @param $content
     * @param $file
     * Метод сохраняет скомпилированный css в кеш
     */
    private function save_compile_file($content, $file) {
        
        $dir = pathinfo($file, PATHINFO_DIRNAME);
        if(!empty($dir) && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!empty($content)) {
            // Сохраняем скомпилированный CSS
            file_put_contents($file, $content);
        }
    }
    
    private function initCssVariables() {
        if (empty($this->cssVariables) && file_exists($this->settings_file)) {
            $oCssParser = new Parser(file_get_contents($this->settings_file));
            $oCssDocument = $oCssParser->parse();
            foreach ($oCssDocument->getAllRuleSets() as $oBlock) {
                foreach ($oBlock->getRules() as $r) {
                    $css_value = (string)$r->getValue();
                    if (strpos($r->getRule(), '--') === 0) {
                        $this->cssVariables[$r->getRule()] = $css_value;
                    }
                }
            }
        }
    }
    
    private function compile_css_file($file) {
        
        if (empty($this->cssVariables)) {
            $this->initCssVariables();
        }
        
        // Вычисляем директорию, для подключения ресурсов из css файла (background-image: url() etc.)
        $sub_dir = trim(substr(pathinfo($file, PATHINFO_DIRNAME), strlen($this->config->root_dir)), "/\\");
        $sub_dir = dirname($sub_dir);
        
        $oCssParser = new Parser(file_get_contents($file));
        $oCssDocument = $oCssParser->parse();
        foreach ($oCssDocument->getAllRuleSets() as $oBlock) {
            foreach ($oBlock->getRules() as $r) {
                $css_value = (string)$r->getValue();

                // Переназначаем переменные из файла настроек шаблона
                $var = preg_replace('~^var\((.+)?\)$~', '$1', $css_value);
                
                if (isset($this->cssVariables[$var])) {
                    $r->setValue($this->cssVariables[$var]);
                }

                // Перебиваем в файле все относительные пути
                if (strpos($css_value, 'url') !== false && strpos($css_value, '..') !== false) {
                    $css_value = strtr($css_value, ['../' => '../../' . $sub_dir . '/']);
                    $r->setValue($css_value);
                }
            }
        }

        $filename = pathinfo($file, PATHINFO_BASENAME);
        $result_file = '/***** #File ' . $filename . ' *****/' . PHP_EOL;
        
        $result_file .= trim($oCssDocument->render(OutputFormat::createCompact())) . PHP_EOL;
        unset($oCssParser);
        return $result_file;
    }
    
    private function check_file($filename, $type, $dir = null) {
        // файлы по http регистрировать нельзя
        if (preg_match('~^(https?:)?//~', $filename)) {
            return false;
        }
        
        $file = $this->get_full_path($filename, $type, $dir);
        return (bool)file_exists($file);
    }
    
    private function get_full_path($filename, $type, $dir = null) {
        $directory =  $this->config->root_dir;
        if ($dir !== null) {
            $directory .= trim($dir, ' \t\n\r\0\x0B/') . '/';
        } else {
            $directory .= 'design/' . $this->getTheme() . '/' . $type . '/';
        }
        return $directory . $filename;
    }
    
}
