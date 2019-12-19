<?php


namespace Okay\Core\Routes\Strategies\Category;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Entities\CategoriesEntity;

class PrefixAndPathStrategy extends AbstractRouteStrategy
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var CategoriesEntity
     */
    private $categoriesEntity;

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
        $this->settings         = $serviceLocator->getService(Settings::class);
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
    }

    public function generateSlugUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        $category = $this->categoriesEntity->get((string) $url);
        return $category->path_url;
    }

    public function generateRouteParams($url)
    {
        $prefix = $this->settings->get('category_routes_template__prefix_and_path');

        $allCategories = $this->categoriesEntity->find();

        $matchedRoute = null;
        foreach($allCategories as $category) {
            if ($this->compareUrlStartsNoSuccess($prefix.$category->path_url, $url)) {
                continue;
            }

            if ($this->matchHasHigherPriority($matchedRoute, $prefix.$category->path_url)) {
                $matchedRoute = [
                    $prefix.'{$url}{$filtersUrl}',
                    [
                        '{$url}' => $category->path_url,
                        '{$filtersUrl}' => '/'.$this->matchFiltersUrl($prefix.$category->path_url, $url),
                    ],
                    [
                        '{$url}' => $category->url,
                        '{$filtersUrl}' => $this->matchFiltersUrl($prefix.$category->path_url, $url),
                    ]
                ];
            }
        }

        if (empty($matchedRoute)) {
            return $this->getMockRouteParams($prefix);
        }

        return $matchedRoute;
    }

    private function getMockRouteParams($prefix)
    {
        return [$prefix.'{$url}{$filtersUrl}', ['{$url}' => '', '{$filtersUrl}' => ''], []];
    }

    private function compareUrlStartsNoSuccess($categoryPathUrl, $url)
    {
        $compareAccessUri = substr($url, 0, strlen($categoryPathUrl));
        return $categoryPathUrl !== $compareAccessUri;
    }

    private function matchHasHigherPriority($prevMatch, $currentMatch)
    {
        return strlen($prevMatch[0]) < strlen($currentMatch.'{$filtersUrl}');
    }

    private function matchFiltersUrl($categoryPathUrl, $url)
    {
        return substr($url, strlen($categoryPathUrl) + 1);
    }
}