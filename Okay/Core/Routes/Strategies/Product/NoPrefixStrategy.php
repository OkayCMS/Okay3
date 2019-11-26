<?php


namespace Okay\Core\Routes\Strategies\Product;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\ProductsEntity;

class NoPrefixStrategy extends AbstractRouteStrategy
{
    /**
     * @var ProductsEntity
     */
    private $productsEntity;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], ['{$url}' => '']];

    public function __construct()
    {
        $serviceLocator = ServiceLocator::getInstance();
        $entityFactory  = $serviceLocator->getService(EntityFactory::class);

        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
    }

    public function generateRouteParams($url)
    {
        $productUrl = $this->matchProductUrl($url);
        $product    = $this->productsEntity->get((string) $productUrl);

        if (empty($product)) {
            return $this->mockRouteParams;
        }

        return ['{$url}', ['{$url}' => $productUrl], ['{$url}' => $productUrl]];
    }

    private function matchProductUrl($url)
    {
        preg_match("/([^\/]+)/ui", $url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }
}