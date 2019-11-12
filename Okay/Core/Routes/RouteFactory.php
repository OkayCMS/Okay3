<?php


namespace Okay\Core\Routes;


class RouteFactory
{
    public function create($routeName)
    {
        if ($routeName === 'product') {
            return new ProductRoute();
        }

        if ($routeName === 'category') {
            return new CategoryRoute();
        }

        if ($routeName === 'brand') {
            return new BrandRoute();
        }

        if ($routeName === 'blog_item') {
            return new BlogItemRoute();
        }

        if ($routeName === 'news_item') {
            return new NewsItemRoute();
        }

        return false;
    }
}