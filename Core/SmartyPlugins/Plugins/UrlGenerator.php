<?php 


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\Router;
use Okay\Core\SmartyPlugins\Func;

class UrlGenerator extends Func
{
    protected $tag = 'url_generator';
    
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function run($params)
    {
        $routeName = '';
        $isAbsolute = false;
        if (isset($params['route'])) {
            $routeName = $params['route'];
        }
        if (isset($params['absolute'])) {
            $isAbsolute = (bool)$params['absolute'];
            unset($params['absolute']);
        }
        unset($params['route']);
       
        return $this->router->generateUrl($routeName, $params, $isAbsolute);
    }
}