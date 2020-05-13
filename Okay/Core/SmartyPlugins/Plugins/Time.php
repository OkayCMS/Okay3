<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\SmartyPlugins\Modifier;

class Time extends Modifier
{
    public function run($date, $format = null)
    {
        if (!$time = strtotime($date)) {
            $time = $date;
        }
        
        return date(empty($format)?'H:i':$format, $time);
    }
}