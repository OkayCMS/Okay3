<?php


namespace Okay\Core\Routes\Strategies\Product;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;

class NoPrefixAndCategoryStrategy extends AbstractRouteStrategy
{
    /**
     * @var ProductsEntity
     */
    private $productsEntity;

    /**
     * @var CategoriesEntity
     */
    private $categoriesEntity;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], []];

    public function __construct()
    {
        $serviceLocator         = ServiceLocator::getInstance();
        $entityFactory          = $serviceLocator->getService(EntityFactory::class);
        $this->productsEntity   = $entityFactory->get(ProductsEntity::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
    }

    public function generateSlugUrl($url)
    {
        $product  = $this->productsEntity->get((string) $url);
        $category = $this->categoriesEntity->get((int) $product->main_category_id);
        return $category->url.'/'.$product->url;
    }

    public function generateRouteParams($url)
    {
        $parts = explode('/', $url);

        if (count($parts) != 2) {
            return $this->mockRouteParams;
        }

        list($categoryUrl, $productUrl) = $parts;

        $category = $this->categoriesEntity->get((string) $categoryUrl);
        if (empty($category)) {
            return $this->mockRouteParams;
        }

        $product  = $this->productsEntity->get((string) $productUrl);
        if (empty($product) || $category->id !== $product->main_category_id) {
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
}