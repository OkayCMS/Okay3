<?php 


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Func;
use Okay\Core\Request;

class Url extends Func
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run($params)
    {
        if(is_array(reset($params))) {
            return $this->request->url(reset($params));
        }

        return $this->request->url($params);    
    }
}