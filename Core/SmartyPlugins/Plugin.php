<?php


namespace Okay\Core\SmartyPlugins;


use Okay\Core\Design;

abstract class Plugin
{
    
    final public function register(Design $design)
    {
        $reflector = new \ReflectionClass($this);
        
        if (!empty($this->tag)) {
            $tag = $this->tag;
        } else {
            $tag = strtolower($reflector->getShortName());
        }
        
        if (!$reflector->hasMethod('run')) {
            throw new \Exception('smarty plugin not exists!! Okay\Core\Plugins\Plugin');
        }
        
        if ($this instanceof Modifier) {
            $design->registerPlugin('modifier', $tag, array($this, 'run'));
        } elseif ($this instanceof Func) {
            $design->registerPlugin('function', $tag, array($this, 'run'));
        } else {
            throw new \Exception('smarty plugin bad instanceof!! Okay\Core\Plugins\Plugin');
        }
    }
}