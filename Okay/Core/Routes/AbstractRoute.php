<?php


namespace Okay\Core\Routes;


use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;

abstract class AbstractRoute
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var AbstractRouteStrategy
     */
    protected $strategy;

    public function __construct()
    {
        $serviceLocator = new ServiceLocator();
        $this->settings = $serviceLocator->getService(Settings::class);
        $this->strategy = $this->getStrategy();
    }

    public function generateRouteParams()
    {
        $url = $this->prepareUrl($_SERVER['REQUEST_URI']);
        list($slug, $patterns, $defaults) = $this->strategy->generateRouteParams($url);
        return new RouteParams($slug, $patterns, $defaults);
    }

    public function generateSlugUrl($url)
    {
        return $this->strategy->generateSlugUrl($url);
    }

    private function prepareUrl($uri)
    {
        if ($uri[0] == '/') {
            $uri = substr($uri, 1);
        }

        return explode('?', $uri)[0];
    }

    abstract protected function getStrategy();
}