<?php


namespace Okay\Core\Routes\Strategies;


abstract class AbstractRouteStrategy
{
    abstract public function generateRouteParams($url);

    public function generateSlugUrl($url)
    {
        return $url;
    }
}