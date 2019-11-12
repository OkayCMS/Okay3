<?php


namespace Okay\Core;


use axy\sourcemap\PosMap;
use axy\sourcemap\SourceMap;
use MatthiasMullie\Minify\JS;
use Okay\Core\Modules\Module;
use Okay\Core\Modules\Modules;
use Okay\Core\TemplateConfig\Css as TemplateCss;
use Okay\Core\TemplateConfig\Js as TemplateJs;
use Okay\Entities\ManagersEntity;
use Psr\Log\LoggerInterface;
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
    private $registeredTemplateFiles = false;
    
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

    private function registerTemplateFiles()
    {
        if ($this->registeredTemplateFiles === true) {
            return;
        }
        
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
        $this->registeredTemplateFiles = true;
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
     * @throws \Exception
     */
    public function head()
    {

        $SL = new ServiceLocator();
        
        /** @var LoggerInterface $logger */
        $logger = $SL->getService(LoggerInterface::class);
        
        /** @var Config $config */
        $config = $SL->getService(Config::class);
        
        $head = '';
        // Подключаем динамический JS (scripts.tpl)
        $commonJsFile = "design/" . $this->getTheme() . "/html/common_js.tpl";
        if (is_file($commonJsFile)) {
            $filename = md5_file($commonJsFile) . json_encode($_GET);
            if (isset($_SESSION['common_js'])) {
                $filename .= json_encode($_SESSION['common_js']);
            }

            $filename = md5($filename);

            $getParams = (!empty($_GET) ? "?" . http_build_query($_GET) : '');
            $head .= "<script src=\"" . Router::generateUrl('common_js', ['fileId' => $filename]) . $getParams . "\"" . ($this->scriptsDefer == true ? " defer" : '') . "></script>" . PHP_EOL;
        } else {
            $logger->error("File \"$commonJsFile\" not found");
        }

        $head .= $this->getIncludeHtml('head');
        
        if ($config->get('dev_mode') == true) {
            $head .= '<style>
                .design_block_parent_element {
                    position: relative;
                    border: 1px solid transparent;
                    min-height: 15px;
                }
                .design_block_parent_element.focus {
                    border: 1px solid red;
                }
                .fn_design_block_name {
                    position: absolute;
                    top: -9px;
                    left: 15px;
                    background-color: #fff;
                    padding: 0 10px;
                    box-sizing: border-box;
                    font-size: 14px;
                    line-height: 14px;
                    font-weight: 700;
                    color: red;
                    cursor: pointer;
                    z-index: 1000;
                }
                .fn_design_block_name:hover {
                    z-index: 1100;
                }
            </style>';
        }
        
        return $head;
        
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
        
        /** @var LoggerInterface $logger */
        $logger = $SL->getService(LoggerInterface::class);
        
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
        } else {
            $logger->error("File \"$dynamicJsFile\" not found");
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

    /**
     * метод компилирует индивидуальный CSS файл, который подключили через смарти плагин
     * @param $filename
     * @param null $dir
     * @return string
     */
    public function compileIndividualCss($filename, $dir = null)
    {
        if ($filename != $this->themeSettingsFileName && $this->checkFile($filename, 'css', $dir) === true) {
            $fullFilePath = $this->getFullPath($filename, 'css', $dir);
            
            $hash = md5(md5_file($fullFilePath) . md5_file($this->settingsFile));
            $compiledFilename = $this->compileCssDir . $this->getTheme() . '.' . pathinfo($fullFilePath, PATHINFO_BASENAME) . '.' . $hash . '.css';

            if (file_exists($compiledFilename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiledFilename);
            } else {
                $this->compileFile($fullFilePath, $compiledFilename);
            }
            
            return !empty($compiledFilename) ? "<link href=\"{$compiledFilename}\" type=\"text/css\" rel=\"stylesheet\">" . PHP_EOL : '';
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
        $this->registerTemplateFiles();
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

            foreach ($this->individualCss[$position] as $k=>$fullFilePath) {
                $hash = md5(md5_file($fullFilePath) . md5_file($this->settingsFile));
                $compiledFilename = $this->compileCssDir . $this->getTheme() . '.' . pathinfo($fullFilePath, PATHINFO_BASENAME) . '.' . $hash . '.css';
                $result[] = $compiledFilename;
                
                if (file_exists($compiledFilename)) {
                    // Обновляем дату редактирования файла, чтобы он не инвалидировался
                    touch($compiledFilename);
                } else {
                    $this->compileFile($fullFilePath, $compiledFilename);
                }
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->individualJs[$position][$k]);
            }
        }
        return $result;
    }
    
    /**
     * @param string $position head|footer указание куда файл генерится
     * Метод компилирует все зарегистрированные, через метод registerCss(), CSS файлы
     * Собитаются они в одном общем выходном файле, в кеше
     * Также здесь подставляются значения переменных CSS.
     * @return string|null
     */
    private function compileRegisteredCss($position)
    {
        
        $resultFile = [];
        $compiledFilename = '';
        if (!empty($this->templateCss[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->templateCss[$position] as $file) {
                $compiledFilename .= md5_file($file) . md5_file($this->settingsFile);
            }

            $mapFile = $this->getTheme() . '.' . $position . '.' . md5($compiledFilename) . '.css.map';
            
            $compiledFilename = $this->compileCssDir . $this->getTheme() . '.' . $position . '.' . md5($compiledFilename) . '.css';
            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiledFilename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiledFilename);
                return $compiledFilename;
            }

            $map = new SourceMap();
            $lineNum = 0;
            foreach ($this->templateCss[$position] as $k=>$fullFilePath) {
                $inputFileName = pathinfo($fullFilePath, PATHINFO_BASENAME);
                $tmpMapFile = $inputFileName . '.map';
                
                $tmpCompiledFilename = $this->compileCssDir . $inputFileName;
                $this->compileFile($fullFilePath, $tmpCompiledFilename);
                
                $content = file_get_contents($tmpCompiledFilename);
                $content = preg_replace('~/\*# sourceMappingURL.*\*/$~s', '', $content);
                $content = rtrim($content);
                $resultFile[] = $content;

                $tmpMap = SourceMap::loadFromFile($this->compileCssDir . $tmpMapFile);
                $map->concat($tmpMap, $lineNum);
                unset($tmpMap);
                $lineNum += 1;
                $resultFile[] = PHP_EOL;
                unlink($this->compileCssDir . $inputFileName);
                unlink($this->compileCssDir . $tmpMapFile);
            }
            $resultFile[] = "\n/*# sourceMappingURL=".$mapFile." */\n";
            $map->save($this->compileCssDir . $mapFile);
        }
        
        $this->saveCompileFile(implode("", $resultFile), $compiledFilename);
        
        return $compiledFilename;
    }

    private function compileFile($fullFilePath, $compiledFilename)
    {
        $map = new SourceMap();
        $position = new PosMap(null);
        $mapFile = $compiledFilename . '.map';
        $map->file = $compiledFilename;
        $generated = $position->generated;
        $source = $position->source;

        $generated->line = 0;
        $generated->column = 0;
        
        $source->fileName = Request::getRootUrl() . '/' . str_replace($this->rootDir, '', $fullFilePath);
        $sourceLine = 0;
        $generatedLine = 0;
        $blockComment = false;

        foreach (file($fullFilePath) as $line) {
            if ($line === '') {
                continue;
            }

            // Проверяем что мы не в блоке комментариев
            $clearLine = $line;
            if (($posComment = strpos($line, '/*')) !== false) {
                $blockComment = true;
                $clearLine = substr($line, 0, $posComment);
            }
            
            if ($blockComment === true && ($posComment = strpos($line, '*/')) !== false) {
                $blockComment = false;
                $clearLine = substr($line, $posComment+2);
            }
            
            $line = $clearLine;
            $line = rtrim($line);
            
            if (strtolower(pathinfo($fullFilePath, PATHINFO_EXTENSION)) == 'css') {
                $line = $this->setCssVariables($line, $fullFilePath);
            }

            if ($blockComment === false) {
                
                if (strtolower(pathinfo($fullFilePath, PATHINFO_EXTENSION)) == 'js') {
                    // todo доделать JS
                    $generatedStrLen = 0;
                    $sourceLenLine = 0;
                } else {
                    $lenPre = strlen($line);
                    $line = ltrim($line);
                    $generatedStrLen = strlen($line);
                    $sourceLenLine = $lenPre - $generatedStrLen;
                }
                
                $source->line = $sourceLine;
                $source->column = $sourceLenLine;
                
                if (strtolower(pathinfo($fullFilePath, PATHINFO_EXTENSION)) == 'css') {
                    $resultFile[] = $line;
                }
                
                $map->addPosition(clone $position);
                $generated->column += $generatedStrLen;
                $generated->line = $generatedLine;
                
            }
            $sourceLine++;
        }
        
        $resultFile[] = "\n/*# sourceMappingURL=".pathinfo($mapFile, PATHINFO_BASENAME)." */\n";
        $map->save($mapFile);
        $this->saveCompileFile(implode("", $resultFile), $compiledFilename);
    }
    
    private function setCssVariables($cssLine, $file)
    {

        if (empty($this->cssVariables)) {
            $this->initCssVariables();
        }

        // Вычисляем директорию, для подключения ресурсов из css файла (background-image: url() etc.)
        $subDir = trim(substr(pathinfo($file, PATHINFO_DIRNAME), strlen($this->rootDir)), "/\\");
        $subDir = dirname($subDir);

        // Переназначаем переменные из файла настроек шаблона
        $var = trim(preg_replace('~^.+?\s*:\s*var\((.+)?\).*$~', '$1', $cssLine));

        if (isset($this->cssVariables[trim($var)])) {
            $cssLine = str_replace("var({$var})", $this->cssVariables[trim($var)], $cssLine);
        }

        // Перебиваем в файле все относительные пути
        if (strpos($cssLine, 'url') !== false && strpos($cssLine, '..') !== false) {
            $cssLine = strtr($cssLine, ['../' => '../../' . $subDir . '/']);
        }
        
        return $cssLine;
    }

    /**
     * Метод компилирует все зарегистрированные JS файлы
     * @param $position (head|footer)
     * @return string compiled filename
     */
    private function compileRegisteredJs($position)
    {

        $resultFile = '';
        $compiledFilename = '';
        if (!empty($this->templateJs[$position])) {

            // Определяем название выходного файла, на основании хешей всех входящих файлов
            foreach ($this->templateJs[$position] as $file) {
                $compiledFilename .= md5_file($file);
            }

            $compiledFilename = $this->compileJsDir . $this->getTheme() . '.' . $position . '.' . md5($compiledFilename) . '.js';

            // Если файл уже скомпилирован, отдаем его.
            if (file_exists($compiledFilename)) {
                // Обновляем дату редактирования файла, чтобы он не инвалидировался
                touch($compiledFilename);
                return $compiledFilename;
            }
            
            foreach ($this->templateJs[$position] as $k=>$file) {
                $filename = pathinfo($file, PATHINFO_BASENAME);

                $resultFile .= '/*! #File ' . $filename . ' */' . PHP_EOL;
                $resultFile .= file_get_contents($file) . PHP_EOL . PHP_EOL;
                
                // Удаляем скомпилированный файл из зарегистрированных, чтобы он повторно не компилировался
                unset($this->templateJs[$position][$k]);
            }
        }

        $minifier = new JS();
        $minifier->add($resultFile);
        $resultFile = $minifier->minify();
        
        $this->saveCompileFile($resultFile, $compiledFilename);
        
        return $compiledFilename;
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
