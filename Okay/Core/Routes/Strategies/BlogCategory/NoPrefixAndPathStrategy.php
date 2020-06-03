<?php


namespace Okay\Core\Routes\Strategies\BlogCategory;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\BlogCategoryRoute;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\BlogCategoriesEntity;
use Okay\Entities\RouterCacheEntity;
use Psr\Log\LoggerInterface;

class NoPrefixAndPathStrategy extends AbstractRouteStrategy
{
    /** @var BlogCategoriesEntity */
    private $categoriesEntity;

    /** @var RouterCacheEntity */
    private $cacheEntity;

    /** @var LoggerInterface */
    private $logger;

    // Сообщаем что данная стратегия может использовать sql для формирования урла
    protected $isUsesSqlToGenerate = true;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->logger           = $serviceLocator->getService(LoggerInterface::class);
        $this->categoriesEntity = $entityFactory->get(BlogCategoriesEntity::class);
        $this->cacheEntity      = $entityFactory->get(RouterCacheEntity::class);
    }

    public function generateSlugUrl($url)
    {
        if (empty($url)) {
            return '';
        } elseif ($route = BlogCategoryRoute::getUrlSlugAlias($url)) {// Может уже указали для этого урла его slug
            return $route;
        } elseif (BlogCategoryRoute::getUseSqlToGenerate() === false) {// Если запретили выполнять запросы для генерации урла
            $this->logger->notice('For generate route to category "'.$url.'" need execute SQL query. Or set url through "Okay\Core\Routes\CategoryRoute::setUrlSlugAlias()"');
            return '';
        }
        
        if ($slug = $this->cacheEntity->cols(['slug_url'])->findOne(['type' => 'blog_category', 'url' => $url])) {
            return $slug;
        }
        
        $category = $this->categoriesEntity->get((string) $url);
        $slug = trim($category->path_url, '/');

        // Запоминаем в оперативке slug для этого урла
        BlogCategoryRoute::setUrlSlugAlias($url, $slug);
        
        $this->cacheEntity->add([
            'url' => $url,
            'slug_url' => $slug,
            'type' => 'blog_category',
        ]);
        
        return $slug;
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
            
            $matchedRoute = [
                '{$url}',
                [
                    '{$url}' => "{$pathPrefix}({$lastPart})",
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
}