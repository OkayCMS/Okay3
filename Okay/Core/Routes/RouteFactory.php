<?php


namespace Okay\Core\Routes;


class RouteFactory
{
    public function create($routeName, $params = [])
    {
        if ($routeName === 'product') {
            return new ProductRoute($params);
        }

        if ($routeName === 'category') {
            return new CategoryRoute($params);
        }

        if ($routeName === 'brand') {
            return new BrandRoute($params);
        }

        if ($routeName === 'blog_item') {
            return new BlogItemRoute($params);
        }

        if ($routeName === 'news_item') {
            return new NewsItemRoute($params);
        }

        if ($routeName === 'brands') {
            return new AllBrandsRoute($params);
        }

        if ($routeName === 'news') {
            return new AllNewsRoute($params);
        }

        if ($routeName === 'blog') {
            return new AllBrandsRoute($params);
        }

        return false;
    }
}