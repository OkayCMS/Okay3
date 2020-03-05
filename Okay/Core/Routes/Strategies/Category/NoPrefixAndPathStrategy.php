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

    private $mockRouteParams = ['{$url}{$filtersUrl}', ['{$url}' => '', '{$filtersUrl}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
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
        $allCategories = $this->categoriesEntity->find();

        $matchedRoute = null;
        foreach($allCategories as $category) {
            if ($this->compareUrlStartsNoSuccess($category->path_url, $url)) {
                continue;
            }
            
            $urlPath = trim($category->path_url, '/');

            $urlParts = explode('/', $urlPath);
            $lastPart = array_pop($urlParts);
            $pathPrefix = '';
            if (!empty($urlParts)) {
                $pathPrefix = implode('/', $urlParts) . '/';
            }
            $filter = trim($this->matchFiltersUrl($urlPath, $url), '/');
            $matchedRoute = [
                '{$url}{$filtersUrl}',
                [
                    '{$url}' => "{$pathPrefix}({$lastPart})",
                    '{$filtersUrl}' => "/?(" . $filter . ")",
                ],
                []
            ];
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

    private function matchFiltersUrl($categoryPathUrl, $url)
    {
        return substr($url, strlen($categoryPathUrl));
    }
}