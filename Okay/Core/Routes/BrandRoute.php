<?php


namespace Okay\Core\Routes;


use Okay\Core\Routes\Strategies\Brand\NoPrefixStrategy;
use Okay\Core\Routes\Strategies\Brand\DefaultStrategy;

class BrandRoute extends AbstractRoute
{
    const BRAND_ROUTE_TEMPLATE = 'brand_routes_template';
    const TYPE_NO_PREFIX       = 'no_prefix';

    protected function getStrategy()
    {
        if (static::TYPE_NO_PREFIX === $this->settings->get(static::BRAND_ROUTE_TEMPLATE)) {
            return new NoPrefixStrategy();
        }

        return new DefaultStrategy();
    }
}