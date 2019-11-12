<?php


namespace Okay\Core\Routes\Strategies\BlogItem;


use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\Settings;
use Okay\Core\ServiceLocator;

class DefaultStrategy extends AbstractRouteStrategy
{
    private $settings;

    public function __construct()
    {
        $serviceLocator = new ServiceLocator();
        $this->settings = $serviceLocator->getService(Settings::class);
    }

    public function generateRouteParams($url)
    {
        $prefix = $this->settings->get('blog_item_routes_template__default');

        if (empty($prefix)) {
            $prefix = 'blog';
        }

        return ['/'.$prefix.'/{$url}', [], ['{$typePost}' => 'blog']];
    }
}