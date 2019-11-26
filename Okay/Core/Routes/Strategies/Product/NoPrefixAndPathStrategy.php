<?php


namespace Okay\Core\Routes\Strategies\Product;

use Okay\Core\Database;
use Okay\Core\EntityFactory;
use Okay\Core\QueryFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;

class NoPrefixAndPathStrategy extends AbstractRouteStrategy
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var ProductsEntity
     */
    private $productsEntity;

    /**
     * @var CategoriesEntity
     */
    private $categoriesEntity;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->db               = $serviceLocator->getService(Database::class);
        $this->queryFactory     = $serviceLocator->getService(QueryFactory::class);
        $this->settings         = $serviceLocator->getService(Settings::class);
        $this->productsEntity   = $entityFactory->get(ProductsEntity::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
    }

    public function generateSlugUrl($url)
    {
        $product = $this->productsEntity->get((string) $url);
        $category = $this->categoriesEntity->get((int) $product->main_category_id);
        return substr($category->path_url, 1).'/'.$product->url;
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