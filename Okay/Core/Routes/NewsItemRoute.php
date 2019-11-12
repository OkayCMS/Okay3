<?php


namespace Okay\Core\Routes;


use Okay\Core\Routes\Strategies\NewsItem\NoPrefixStrategy;
use Okay\Core\Routes\Strategies\NewsItem\DefaultStrategy;

class NewsItemRoute extends AbstractRoute
{
    const NEWS_ITEM_ROUTE_TEMPLATE = 'news_item_routes_template';
    const TYPE_NO_PREFIX           = 'no_prefix';

    protected function getStrategy()
    {
        if (static::TYPE_NO_PREFIX === $this->settings->get(static::NEWS_ITEM_ROUTE_TEMPLATE)) {
            return new NoPrefixStrategy();
        }

        return new DefaultStrategy();
    }
}