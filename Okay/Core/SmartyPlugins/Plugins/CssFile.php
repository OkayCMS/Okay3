<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Func;
use Okay\Core\TemplateConfig;

class CssFile extends Func
{
    protected $tag = 'css';

    private $templateConfig;
    
    public function __construct(TemplateConfig $templateConfig)
    {
        $this->templateConfig = $templateConfig;
    }

    public function run($params) {
        $filename = '';
        $dir = null;
        
        if (!empty($params['filename'])) {
            $filename = $params['filename'];
        } elseif (!empty($params['file'])) {
            $filename = $params['file'];
        }

        if (!empty($params['dir'])) {
            $dir = $params['dir'];
        }
        
        return $this->templateConfig->compileIndividualCss($filename, $dir);
    }
}