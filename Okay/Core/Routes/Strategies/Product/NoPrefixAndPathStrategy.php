<?php


namespace Okay\Core\Routes\Strategies\Product;

use Okay\Core\Database;
use Okay\Core\EntityFactory;
use Okay\Core\QueryFactory;
use Okay\Core\Routes\ProductRoute;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\RouterCacheEntity;
use Psr\Log\LoggerInterface;

class NoPrefixAndPathStrategy extends AbstractRouteStrategy
{
    /** @var Database */
    private $db;

    /** @var ProductsEntity */
    private $productsEntity;

    /** @var CategoriesEntity */
    private $categoriesEntity;

    /** @var Settings */
    private $settings;

    /** @var QueryFactory */
    private $queryFactory;

    /** @var LoggerInterface */
    private $logger;

    /** @var RouterCacheEntity */
    private $cacheEntity;

    // Сообщаем что данная стратегия может использовать sql для формирования урла
    protected $isUsesSqlToGenerate = true;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->db               = $serviceLocator->getService(Database::class);
        $this->queryFactory     = $serviceLocator->getService(QueryFactory::class);
        $this->settings         = $serviceLocator->getService(Settings::class);
        $this->logger           = $serviceLocator->getService(LoggerInterface::class);
        $this->productsEntity   = $entityFactory->get(ProductsEntity::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
        $this->cacheEntity      = $entityFactory->get(RouterCacheEntity::class);
    }

    public function generateSlugUrl($url)
    {
        if (empty($url)) {
            return '';
        } elseif ($route = ProductRoute::getUrlSlugAlias($url)) {// Может уже указали для этого урла его slug
            return $route;
        } elseif (ProductRoute::getUseSqlToGenerate() === false) {// Если запретили выполнять запросы для генерации урла
            $this->logger->notice('For generate route to product "'.$url.'" need execute SQL query. Or set url through "Okay\Core\Routes\ProductRoute::setUrlSlugAlias()"');
            return '';
        }

        if ($slug = $this->cacheEntity->cols(['slug_url'])->findOne(['type' => 'product', 'url' => $url])) {
            return $slug;
        }

        $product = $this->productsEntity->get((string) $url);
        $slug = $product->url;
        if (empty($product->main_category_id)) {
            $this->logger->warning('Missing "main_category_id" for product "'.$url.'"');
        } else {
            $category = $this->categoriesEntity->get((int) $product->main_category_id);
            $slug = substr($category->path_url, 1).'/'.$product->url;
        }

        // Запоминаем в оперативке slug для этого урла
        ProductRoute::setUrlSlugAlias($url, $slug);

        // Сохраняем в базу slug, чтобы его больше не генерить
        $this->cacheEntity->add([
            'url' => $url,
            'slug_url' => $slug,
            'type' => 'product',
        ]);

        return $slug;
    }

    public function generateRouteParams($url)
    {
        $matchedCategories      = $this->matchCategories($url);
        $mappedParentCategories = $this->mapCategoriesByParents($matchedCategories);

        if (empty($mappedParentCategories)) {
            return $this->mockRouteParams;
        }

        $mainCategoryId = $this->findMostNestedCategoryId($mappedParentCategories);
        $category   = $this->categoriesEntity->get((int) $mainCategoryId);

        if ($this->urlNoContainsValidCategoryPathUrl($url, $category->path_url)) {
            return $this->mockRouteParams;
        }

        $productUrl = $this->matchProductUrlFromUri($url, $category->path_url);
        $product    = $this->productsEntity->findOne([
            'url'              => $productUrl,
            'main_category_id' => $mainCategoryId
        ]);

        if (empty($product)) {
            return $this->mockRouteParams;
        }

        return [
            '{$url}',
            [
                '{$url}' => $url
            ],
            [
                '{$url}' => $productUrl
            ]
        ];
    }

    private function matchProductUrlFromUri($url, $categoryPathUrl)
    {
        $noCategoryPathUri = substr($url, strlen($categoryPathUrl));

        if ($noCategoryPathUri === '/') {
            $noCategoryPathUri = substr($noCategoryPathUri, 1);
        }

        return explode('/', $noCategoryPathUri)[0];
    }

    private function urlNoContainsValidCategoryPathUrl($url, $categoryPathUrl)
    {
        if ($url[0] !== '/') {
            $url = '/'.$url;
        }

        $comparePartUri = substr($url, 0, strlen($categoryPathUrl));

        return $comparePartUri !== $categoryPathUrl;
    }

    private function matchCategories($noPrefixUri)
    {
        $parts = explode('/', $noPrefixUri);

        $select = $this->queryFactory->newSelect();
        $select->cols(['id', 'parent_id', 'url'])
            ->from(CategoriesEntity::getTable())
            ->where('url IN(:urls)')
            ->bindValue('urls', $parts);
        $this->db->query($select);
        return $this->db->results(null, 'id');
    }

    private function findMostNestedCategoryId($mappedByParentCategories)
    {
        $sortCategories = function($category) use (&$sortCategories, $mappedByParentCategories) {
            $nestedSortCategories[] = $category;

            if (empty($mappedByParentCategories[$category->id])) {
                return $category;
            }

            return $sortCategories($mappedByParentCategories[$category->id]);
        };
        $mostNestedCategory = $sortCategories(reset($mappedByParentCategories));
        return $mostNestedCategory->id;
    }

    private function mapCategoriesByParents($categories)
    {
        $categoriesMappedByParent = [];
        foreach($categories as $category) {
            if (isset($categoriesMappedByParent[$category->parent_id])) {
                return false;
            }
            $categoriesMappedByParent[$category->parent_id] = $category;
        }

        return $categoriesMappedByParent;
    }
}