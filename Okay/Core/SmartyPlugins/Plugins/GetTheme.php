<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Func;
use Okay\Core\TemplateConfig;

class GetTheme extends Func
{
    protected $tag = 'get_theme';

    private $templateConfig;
    
    public function __construct(TemplateConfig $templateConfig)
    {
        $this->templateConfig = $templateConfig;
    }

    public function run()
    {
        return $this->templateConfig->getTheme();
    }
}