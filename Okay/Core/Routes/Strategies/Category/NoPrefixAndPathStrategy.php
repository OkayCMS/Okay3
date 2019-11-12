<?php


namespace Okay\Core\Routes\Strategies\Category;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\CategoriesEntity;

class NoPrefixAndPathStrategy extends AbstractRouteStrategy
{
    /**
     * @var CategoriesEntity
     */
    private $categoriesEntity;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = new ServiceLocator();
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
    }

    public function generateSlugUrl($url)
    {
        $category = $this->categoriesEntity->get((string) $url);
        return $category->path_url;
    }

    public function generateRouteParams($url)
    {
        $allCategories = $this->categoriesEntity->find();

        $matchedRoute = null;
        foreach($allCategories as $category) {
            if ($this->compareUrlStartsNoSuccess($category->path_url, $url)) {
                continue;
            }

            if ($this->matchHasHigherPriority($matchedRoute, $category->path_url)) {
                $matchedRoute = [
                    '{$url}{$filtersUrl}',
                    [
                        '{$url}' => $category->path_url,
                        '{$filtersUrl}' => '/'.$this->matchFiltersUrl($category->path_url, $url),
                    ],
                    [
                        '{$url}' => $category->url,
                        '{$filtersUrl}' => $this->matchFiltersUrl($category->path_url, $url),
                    ]
                ];
            }
        }

        if (empty($matchedRoute)) {
            return $this->mockRouteParams;
        }

        return $matchedRoute;
    }

    private function compareUrlStartsNoSuccess($categoryPathUrl, $url)
    {
        $categoryPathUrl = substr($categoryPathUrl, 1);
        $compareAccessUri = substr($url, 0, strlen($categoryPathUrl));
        return $categoryPathUrl !== $compareAccessUri;
    }

    private function matchHasHigherPriority($prevMatch, $currentMatch)
    {
        return strlen($prevMatch[0]) < strlen($currentMatch.'{$filtersUrl}');
    }

    private function matchFiltersUrl($categoryPathUrl, $url)
    {
        return substr($url, strlen($categoryPathUrl));
    }
}