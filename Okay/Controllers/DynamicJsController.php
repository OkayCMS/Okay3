<?php


namespace Okay\Controllers;


use Okay\Core\TemplateConfig;

class DynamicJsController extends AbstractController
{
    
    public function getJs(
        TemplateConfig $templateConfig,
        $fileId
    ) {
        $dynamicJsFile = "design/" . $templateConfig->getTheme() . "/html/scripts.tpl";

        $dynamicJs = '';
        
        if (is_file($dynamicJsFile)) {
            if (!empty($_SESSION['dynamic_js']['controller'])) {
                $this->design->assign('controller', $_SESSION['dynamic_js']['controller']);
            }
    
            if (isset($_SESSION['dynamic_js']['vars'])) {
                // Передаем глобальные переменные в шаблон
                foreach ($_SESSION['dynamic_js']['vars'] as $var => $value) {
                    $this->design->assign($var, $value);
                }
            }
    
            $dynamicJs = $this->design->fetch('scripts.tpl');
            $dynamicJs = preg_replace('~<script(.*?)>(.*?)</script>~is', '$2', $dynamicJs);
    
            $dynamicJs = $templateConfig->minifyJs($dynamicJs);
        }

        $this->response->addHeader('Cache-Control: no-cache, must-revalidate');
        $this->response->addHeader('Expires: -1');
        $this->response->addHeader('Pragma: no-cache');
        $this->response->addHeader('Content-Type: application/javascript');
        
        $this->response->setContent($dynamicJs, RESPONSE_JAVASCRIPT);
    }
}
