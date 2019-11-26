<?php


namespace Okay\Core\Routes\Strategies\Brand;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\BrandsEntity;

class NoPrefixStrategy extends AbstractRouteStrategy
{
    /**
     * @var BrandsEntity
     */
    private $brandsEntity;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], ['{$url}' => '']];

    public function __construct()
    {
        $serviceLocator = ServiceLocator::getInstance();
        $entityFactory  = $serviceLocator->getService(EntityFactory::class);

        $this->brandsEntity = $entityFactory->get(BrandsEntity::class);
    }

    public function generateRouteParams($url)
    {
        $categoryUrl = $this->matchCategoryUrl($url);
        $category    = $this->brandsEntity->get((string) $categoryUrl);

        if (empty($category)) {
            return $this->mockRouteParams;
        }

        $matchedRoute = [
            '{$url}{$filtersUrl}',
            [
                '{$url}' => $categoryUrl,
                '{$filtersUrl}' => '/'.$this->matchFiltersUrl($categoryUrl, $url)
            ],
            [
                '{$url}' => $categoryUrl,
                '{$filtersUrl}' => $this->matchFiltersUrl($categoryUrl, $url)
            ]
        ];

        return $matchedRoute;
    }

    private function matchCategoryUrl($url)
    {
        preg_match("/([^\/]+)/ui", $url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }

    private function matchFiltersUrl($categoryUrl, $url)
    {
        return substr($url, strlen($categoryUrl) + 1);
    }
}