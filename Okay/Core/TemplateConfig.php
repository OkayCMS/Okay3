<?php


namespace Okay\Core;


use MatthiasMullie\Minify\JS;
use Okay\Core\Modules\Module;
use Okay\Core\Modules\Modules;
use Okay\Core\TemplateConfig\Css as TemplateCss;
use Okay\Core\TemplateConfig\Js as TemplateJs;
use Okay\Entities\ManagersEntity;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\OutputFormat;

class TemplateConfig
{
    private $rootDir;
    private $scriptsDefer;
    
    private $templateCss = [];
    private $individualCss = [];
    private $templateJs = [];
    private $individualJs = [];
    private $deferJsFiles = [];
    private $cssVariables = [];
    private $themeSettingsFileName;
    private $theme;
    private $adminTheme;
    private $adminThemeManagers;
    private $compileCssDir;
    private $compileJsDir;
    private $settingsFile;
    
    /** @var Modules */
    private $modules;
    
    /** @var Module */
    private $module;
    
    public function __construct(
        Modules $modules,
        Module $module,
        $rootDir,
        $scriptsDefer,
        $themeSettingsFileName,
        $compileCssDir,
        $compileJsDir
    ) {
        $this->modules = $modules;
        $this->module = $module;
        $this->rootDir = $rootDir;
        $this->scriptsDefer = $scriptsDefer;
        $this->themeSettingsFileName = $themeSettingsFileName;
        $this->compileCssDir = $compileCssDir;
        $this->compileJsDir = $compileJsDir;
    }

    /**
     * @param $theme
     * @param $adminTheme
     * @param $adminThemeManagers
     * @throws \Exception
     * 
     * Метод конфигуратор, нужен, чтобы не передавать в зависимости целый класс Settings
     */
    public function configure($theme, $adminTheme, $adminThemeManagers)
    {
        $this->theme = $theme;
        $this->adminTheme = $adminTheme;
        $this->adminThemeManagers = $adminThemeManagers;
        $this->settingsFile = 'design/' . $this->getTheme() . '/css/' . $this->themeSettingsFileName;
        
        if (($themeJs = include 'design/' . $this->getTheme() . '/js.php') && is_array($themeJs)) {
            foreach ($themeJs as $jsItem) {
                $this->registerJs($jsItem);
            }
        }
        
        if (($themeCss = include 'design/' . $this->getTheme() . '/css.php') && is_array($themeCss)) {
            foreach ($themeCss as $cssItem) {
                $this->registerCss($cssItem);
            }
        }
        
        $runningModules = $this->modules->getRunningModules();
        foreach ($runningModules as $runningModule) {

            $moduleThemesDir = $this->module->getModuleDirectory($runningModule['vendor'], $runningModule['module_name']) . 'design/';
            
            if (file_exists($moduleThemesDir . 'css.php') && ($moduleCss = include $moduleThemesDir . 'css.php') && is_array($moduleCss)) {
                /** @var TemplateCss $cssItem */
                foreach ($moduleCss as $cssItem) {
                    if ($cssItem->getDir() === null) {
                        $cssItem->setDir($moduleThemesDir . 'css/');
                    }
                    $this->registerCss($cssItem);
                }
            }
            if (file_exists($moduleThemesDir . 'js.php') && ($moduleJs = include $moduleThemesDir . 'js.php') && is_array($moduleJs)) {

                /** @var TemplateJs $jsItem */
                foreach ($moduleJs as $jsItem) {
                    if ($jsItem->getDir() === null) {
                        $jsItem->setDir($moduleThemesDir . 'js/');
                    }
                    $this->registerJs($jsItem);
                }
            }
        }
    }
    
    public function __destruct()
    {
        // Инвалидация компилированных js и css файлов
        $css = glob($this->rootDir . $this->compileCssDir . $this->getTheme() . ".*.css");
        $js = glob($this->rootDir . $this->compileJsDir . $this->getTheme() . ".*.js");
        
        $cacheFiles = array_merge($css, $js);
        if (is_array($cacheFiles)) {
            foreach ($cacheFiles as $f) {
                $fileTime = filemtime($f);
                // Если файл редактировался более недели назад, удалим его, вероятнее всего он уже не нужен
                if ($fileTime !== false && time() - $fileTime > 604800) {
                    @unlink($f);
                }
            }
        }
    }

    private function registerCss(TemplateCss $css)
    {
        // Файл настроек шаблона регистрировать не нужно
        if ($css->getFilename() != $this->themeSettingsFileName && $this->checkFile($css->getFilename(), 'css', $css->getDir()) === true) {
            $fullPath = $this->getFullPath($css->getFilename(), 'css', $css->getDir());
            $fileId = md5($fullPath);
            if ($css->getIndividual() === true) {
                $this->individualCss[$css->getPosition()][$fileId] = $fullPath;
            } else {
                $this->templateCss[$css->getPosition()][$fileId] = $fullPath;
            }
        }
    }

    private function registerJs(TemplateJs $js)
    {
        if ($this->checkFile($js->getFilename(), 'js', $js->getDir()) === true) {
            $fullPath = $this->getFullPath($js->getFilename(), 'js', $js->getDir());
            $fileId = md5($fullPath);
            if ($js->getIndividual() === true) {
                if ($js->getDefer() === true) {
                    $this->deferJsFiles[$fullPath] = $fullPath;
                }
                $this->individualJs[$js->getPosition()][$fileId] = $fullPath;
            } else {
                $this->templateJs[$js->getPosition()][$fileId] = $fullPath;
            }
        }
    }

    /**
     * Метод возвращает теги на подключение всех зарегестрированных js и css для блока head
     * @return string
     */
    public function head()
    {
        return $this->getIncludeHtml('head');
    }

    /**
     * Метод возвращает теги на подключение всех зарегестрированных js и css для футера
     * @return string
     * @throws \Exception
     */
    public function footer()
    {
        $SL = new ServiceLocator();
        
        /** @var Design $design */
        $design = $SL->getService(Design::class);
        
        /** @var EntityFactory $entityFactory */
        $entityFactory = $SL->getService(EntityFactory::class);
        
        /** @var ManagersEntity $managersEntity */
        $managersEntity = $entityFactory->get(ManagersEntity::class);

        $footer = $this->getIncludeHtml('footer');
        
        if (!empty($_SESSION['admin']) && ($manager = $managersEntity->get($_SESSION['admin']))) {

            $templatesDir = $design->getTemplatesDir();
            $compiledDir = $design->getCompiledDir();
            
            $design->setTemplatesDir('backend/design/html');
            $design->setCompiledDir('backend/design/compiled');

            // Перевод админки
            $backendTranslations = new \stdClass();
            $file = "backend/lang/" . $manager->lang . ".php";
            if (!file_exists($file)) {
                foreach (glob("backend/lang/??.php") as $f) {
                    $file = "backend/lang/" . pathinfo($f, PATHINFO_FILENAME) . ".php";
                    break;
                }
            }
            include ($file);
            $design->assign('scripts_defer', $this->scriptsDefer);
            $design->assign('btr', $backendTranslations);
            $footer .= $design->fetch('admintooltip.tpl');
            
            // Возвращаем настройки компилирования файлов smarty
            $design->setTemplatesDir($templatesDir);
            $design->setCompiledDir($compiledDir);

        }
        
        // Подключаем динамический JS (scripts.tpl)
        $dynamicJsFile = "design/" . $this->getTheme() . "/html/scripts.tpl";
        if (is_file($dynamicJsFile)) {
            $filename = md5_file($dynamicJsFile) . json_encode($_GET);
            if (isset($_SESSION['dynamic_js'])) {
                $filename .= json_encode($_SESSION['dynamic_js']);
            }
            
            $filename = md5($filename);
            
            $getParams = (!empty($_GET) ? "?" . http_build_query($_GET) : '');
            $footer .= "<script src=\"" . Router::generateUrl('dynamic_js', ['fileId' => $filename]) . $getParams . "\"" . ($this->scriptsDefer == true ? " defer" : '') . "></script>" . PHP_EOL;
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
            $this->compileCssDir,
            $this->compileJsDir,
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
        
        $oCssParser = new Parser(file_get_contents($this->settingsFile));
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
        file_put_contents($this->settingsFile, $result_file);
    }
    
    public function compileIndividualCss($filename, $dir = null)
    {
        if ($filename != $this->themeSettingsFileName && $this->checkFile($filename, 'css', $dir) === true) {
            $file = $this->getFullPath($filename, 'css', $dir);
            
            $hash = md5_file($file) . md5_file($this->settingsFile);
            $compiled_filename = $this->compileCssDir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . $hash . '.css';

            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
            } else {
                $result_file = $this->compileCssFile($file);
                $this->saveCompileFile($result_file, $compiled_filename);
            }
            
            return !empty($compiled_filename) ? "<link href=\"{$compiled_filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL : '';
        }
        return '';
    }
    
    public function compileIndividualJs($filename, $dir = null, $defer = false)
    {
        if ($this->checkFile($filename, 'js', $dir) === true) {
            $file = $this->getFullPath($filename, 'js', $dir);
            
            $compiled_filename = $this->compileJsDir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . md5_file($file) . '.js';

            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
            } else {
                $result_file = file_get_contents($file) . PHP_EOL . PHP_EOL;

                $minifier = new JS();
                $minifier->add($result_file);
                $result_file = $minifier->minify();

                $this->saveCompileFile($result_file, $compiled_filename);
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
    private function getIncludeHtml($position = 'head')
    {
        $include_html = '';

        // Подключаем основной файл стилей
        if (($css_filename = $this->compileRegisteredCss($position)) !== '') {
            $include_html .= "<link href=\"{$css_filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL;
        }

        // Подключаем дополнительные индивидуальные файлы стилей
        if (($individualCss_filenames = $this->compileRegisteredIndividualCss($position)) !== []) {
            foreach ($individualCss_filenames as $filename) {
                $include_html .= "<link href=\"{$filename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL;
            }
        }

        // Подключаем основной JS файл
        if (($js_filename = $this->compileRegisteredJs($position)) !== '') {
            $include_html .= "<script src=\"{$js_filename}\"" . ($this->scriptsDefer == true ? " defer" : '') . "></script>" . PHP_EOL;
        }

        // Подключаем дополнительные индивидуальные JS файлы
        if (($individualJs_filenames = $this->compileRegisteredIndividualJs($position)) !== []) {
            foreach ($individualJs_filenames as $filename) {
                $include_html .= "<script src=\"{$filename}\"" . (isset($this->deferJsFiles[$filename]) ? " defer" : '') . "></script>" . PHP_EOL;
            }
        }
        
        return $include_html;
    }
    
    private function compileRegisteredIndividualJs($position)
    {
        $result = [];
        if (!empty($this->individualJs[$position])) {

            foreach ($this->individualJs[$position] as $k=>$file) {
                
                $compiled_filename = $this->compileJsDir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . md5_file($file) . '.js';
                $result[] = $compiled_filename;
                
                if (isset($this->deferJsFiles[$file])) {
                    $this->deferJsFiles[$compiled_filename] = $compiled_filename;
                    unset($this->deferJsFiles[$file]);
                }
                
                if (file_exists($compiled_filename)) {
                    // Обновляем дату редактирования файла, чтобы он не инвалидировался
                    touch($compiled_filename);
                } else {
                    $result_file = file_get_contents($file) . PHP_EOL . PHP_EOL;

                    $minifier = new JS();
                    $minifier->add($result_file);
                    $result_file = $minifier->minify();

                    $this->saveCompileFile($result_file, $compiled_filename);
                }
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->individualCss[$position][$k]);
            }
        }
        return $result;
    }
    
    private function compileRegisteredIndividualCss($position)
    {
        $result = [];
        if (!empty($this->individualCss[$position])) {

            foreach ($this->individualCss[$position] as $k=>$file) {
                $hash = md5_file($file) . md5_file($this->settingsFile);
                $compiled_filename = $this->compileCssDir . $this->getTheme() . '.' . pathinfo($file, PATHINFO_BASENAME) . '.' . $hash . '.css';
                $result[] = $compiled_filename;
                
                if (file_exists($compiled_filename)) {
                    // Обновляем дату редактирования файла, чтобы он не инвалидировался
                    touch($compiled_filename);
                } else {
                    $result_file = $this->compileCssFile($file);
                    $this->saveCompileFile($result_file, $compiled_filename);
                }
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->individualJs[$position][$k]);
            }
        }
        return $result;
    }
    
    /**
     * @param $position //head|footer указание куда файл генерится
     * Метод компилирует все зарегистрированные, через метод registerCss(), CSS файлы
     * Собитаются они в одном общем выходном файле, в кеше
     * Также здесь подставляются значения переменных CSS.
     * @return string|null
     */
    private function compileRegisteredCss($position)
    {
        
        $result_file = '';
        $compiled_filename = '';
        
        if (!empty($this->templateCss[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->templateCss[$position] as $file) {
                $compiled_filename .= md5_file($file) . md5_file($this->settingsFile);
            }
            
            $compiled_filename = $this->compileCssDir . $this->getTheme() . '.' . $position . '.' . md5($compiled_filename) . '.css';

            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
                return $compiled_filename;
            }
            
            foreach ($this->templateCss[$position] as $k=>$file) {
                $result_file .= $this->compileCssFile($file);
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->templateCss[$position][$k]);
            }
        }
        
        $this->saveCompileFile($result_file, $compiled_filename);
        
        return $compiled_filename;
    }

    /**
     * Метод компилирует все зарегистрированные JS файлы
     * @param $position (head|footer)
     * @return string compiled filename
     */
    private function compileRegisteredJs($position)
    {

        $result_file = '';
        $compiled_filename = '';
        if (!empty($this->templateJs[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->templateJs[$position] as $file) {
                $compiled_filename .= md5_file($file);
            }
            
            $compiled_filename = $this->compileJsDir . $this->getTheme() . '.' . $position . '.' . md5($compiled_filename) . '.js';

            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiled_filename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiled_filename);
                return $compiled_filename;
            }
            
            foreach ($this->templateJs[$position] as $k=>$file) {
                $filename = pathinfo($file, PATHINFO_BASENAME);

                $result_file .= '/*! #File ' . $filename . ' */' . PHP_EOL;
                $result_file .= file_get_contents($file) . PHP_EOL . PHP_EOL;
                
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->templateJs[$position][$k]);
            }
        }

        $minifier = new JS();
        $minifier->add($result_file);
        $result_file = $minifier->minify();
        
        $this->saveCompileFile($result_file, $compiled_filename);
        
        return $compiled_filename;
    }
    
    /**
     * @param $content
     * @param $file
     * Метод сохраняет скомпилированный css в кеш
     */
    private function saveCompileFile($content, $file) {
        
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
        if (empty($this->cssVariables) && file_exists($this->settingsFile)) {
            $oCssParser = new Parser(file_get_contents($this->settingsFile));
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
    
    private function compileCssFile($file)
    {
        
        if (empty($this->cssVariables)) {
            $this->initCssVariables();
        }
        
        // Вычисляем директорию, для подключения ресурсов из css файла (background-image: url() etc.)
        $sub_dir = trim(substr(pathinfo($file, PATHINFO_DIRNAME), strlen($this->rootDir)), "/\\");
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
    
    private function checkFile($filename, $type, $dir = null)
    {
        // файлы по http регистрировать нельзя
        if (preg_match('~^(https?:)?//~', $filename)) {
            return false;
        }
        
        $file = $this->getFullPath($filename, $type, $dir);
        return (bool)file_exists($file);
    }
    
    private function getFullPath($filename, $type, $dir = null)
    {
        $directory =  $this->rootDir;
        if ($dir !== null) {
            $directory .= trim($dir, ' \t\n\r\0\x0B/') . '/';
        } else {
            $directory .= 'design/' . $this->getTheme() . '/' . $type . '/';
        }
        return $directory . $filename;
    }
    
}
