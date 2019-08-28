<?php 


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Func;
use Okay\Core\Router;
use Okay\Logic\ChpuFilterLogic;

class Furl extends Func
{
    private $router;
    private $chpuFilterLogic;

    public function __construct(Router $router, ChpuFilterLogic $chpuFilterLogic)
    {
        $this->router = $router;
        $this->chpuFilterLogic = $chpuFilterLogic;
    }

    public function run($params)
    {

        if (is_array($params) && is_array(reset($params))) {
            $params = reset($params);
        }
        
        $routeName = $this->router->getCurrentRouteName();
        $isAbsolute = false;
        
        if (isset($params['absolute'])) {
            $isAbsolute = (bool)$params['absolute'];
            unset($params['absolute']);
        }
        
        $routeParams = $this->router->getCurrentRouteParams();
        unset($routeParams['filtersUrl']); // todo пока только топорно можно называть переменную фильтра $filtersUrl
        
        $baseUrl = $this->router->generateUrl($routeName, $routeParams, $isAbsolute);
        $chpuUrl = $this->chpuFilterLogic->filterChpuUrl($params);
        
        $baseUrl = trim($baseUrl, '/');
        $chpuUrl = trim($chpuUrl, '/');
        
        return $baseUrl . '/' . $chpuUrl;
        
    }
}