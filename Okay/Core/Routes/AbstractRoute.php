<?php


namespace Okay\Core\Routes;


use Okay\Core\Request;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;

abstract class AbstractRoute
{
    /**
     * Данная константа переопределяется в наследниках и в ней указывается
     * название свойста Okay\Core\Setting::class, которое отвечает за определение
     * слеша в конце в конкретной группе роутов
     */
    const SLASH_END = '';

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var AbstractRouteStrategy
     */
    protected $strategy;

    /**
     * Параметры которые были пойманы роутером при помощи регулярных выражения
     */
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;

        $serviceLocator = ServiceLocator::getInstance();
        $this->settings = $serviceLocator->getService(Settings::class);
        $this->strategy = $this->getStrategy();
    }

    public function generateRouteParams()
    {
        $url = $this->prepareUrl(Request::getRequestUri());
        list($slug, $patterns, $defaults) = $this->strategy->generateRouteParams($url);
        return new RouteParams($slug, $patterns, $defaults);
    }

    public function generateSlugUrl($url)
    {
        return $this->strategy->generateSlugUrl($url);
    }

    private function prepareUrl($uri)
    {

        if ($this->hasLangPrefix($uri)) {
            $uri = $this->removeLangPrefix($uri);
        }

        return explode('?', $uri)[0];
    }

    private function hasLangPrefix($uri)
    {
        return strlen(explode('/', $uri)[0]) == 2;
    }

    private function removeLangPrefix($uri)
    {
        if ($uri[0] === '/') {
            return substr($uri, 4);
        }

        return substr($uri, 3);
    }

    abstract public function hasSlashAtEnd();

    abstract protected function getStrategy();
}