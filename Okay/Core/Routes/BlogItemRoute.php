<?php


namespace Okay\Core\Routes;


use Okay\Core\Routes\Strategies\BlogItem\NoPrefixStrategy;
use Okay\Core\Routes\Strategies\BlogItem\DefaultStrategy;

class BlogItemRoute extends AbstractRoute
{
    const BLOG_ITEM_ROUTE_TEMPLATE = 'blog_item_routes_template';
    const TYPE_NO_PREFIX           = 'no_prefix';
    const SLASH_END                = 'blog_item_routes_template_slash_end';

    public function hasSlashAtEnd()
    {
        return intval($this->settings->get(static::SLASH_END)) === 1;
    }

    protected function getStrategy()
    {
        if (static::TYPE_NO_PREFIX === $this->settings->get(static::BLOG_ITEM_ROUTE_TEMPLATE)) {
            return new NoPrefixStrategy();
        }

        return new DefaultStrategy();
    }
}