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
        return trim($category->path_url, '/');
    }

    public function generateRouteParams($url)
    {
        $prefix = $this->settings->get('category_routes_template__prefix_and_path') . '/';
        $allCategories = $this->categoriesEntity->find();

        $matchedRoute = null;
        foreach($allCategories as $category) {
            $urlPath = trim($category->path_url, '/');
            if ($this->compareUrlStartsNoSuccess($prefix.$urlPath, $url)) {
                continue;
            }
            
            if ($this->matchHasHigherPriority($matchedRoute, $prefix.$urlPath)) {

                $urlParts = explode('/', $urlPath);
                $lastPart = array_pop($urlParts);
                $pathPrefix = '';
                if (!empty($urlParts)) {
                    $pathPrefix = implode('/', $urlParts) . '/';
                }
                $filter = trim($this->matchFiltersUrl($prefix.$urlPath, $url), '/');
                $matchedRoute = [
                    $prefix.'{$url}{$filtersUrl}',
                    [
                        '{$url}' => "{$pathPrefix}({$lastPart})",
                        '{$filtersUrl}' => "/?(" . $filter . ")",
                    ],
                    []
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