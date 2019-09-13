<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Func;
use Okay\Core\TemplateConfig;

class JsFile extends Func
{
    protected $tag = 'js';

    private $templateConfig;
    
    public function __construct(TemplateConfig $templateConfig)
    {
        $this->templateConfig = $templateConfig;
    }

    public function run($params) {
        $filename = '';
        $dir = null;
        $defer = false;
        
        if (!empty($params['filename'])) {
            $filename = $params['filename'];
        } elseif (!empty($params['file'])) {
            $filename = $params['file'];
        }

        if (!empty($params['dir'])) {
            $dir = $params['dir'];
        }

        if (!empty($params['defer'])) {
            $defer = $params['defer'];
        }
        
        return $this->templateConfig->compileIndividualJs($filename, $dir, $defer);
    }
}