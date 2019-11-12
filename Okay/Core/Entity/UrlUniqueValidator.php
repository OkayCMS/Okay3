<?php


namespace Okay\Core\Entity;


use Okay\Core\EntityFactory;
use Okay\Entities\ProductsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\BlogEntity;
use Okay\Entities\BrandsEntity;

class UrlUniqueValidator
{
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function validateGlobal($url, $entityName, $id)
    {
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        $product = $productsEntity->get((string) $url);
        if (!empty($product) && $entityName !== ProductsEntity::class && $product->id != $id) {
            return false;
        }

        /** @var CategoriesEntity $categoriesEntity */
        $categoriesEntity = $this->entityFactory->get(CategoriesEntity::class);
        $category = $categoriesEntity->get((string) $url);
        if (!empty($category) && $entityName !== CategoriesEntity::class && $category->id != $id) {
            return false;
        }

        /** @var BlogEntity $blogEntity */
        $blogEntity = $this->entityFactory->get(BlogEntity::class);
        $post = $blogEntity->get((string) $url);
        if (!empty($post) && $entityName !== BlogEntity::class && $post->id != $id) {
            return false;
        }

        /** @var BrandsEntity $brandsEntity */
        $brandsEntity = $this->entityFactory->get(BrandsEntity::class);
        $brand = $brandsEntity->get((string) $url);
        if (!empty($brand) && $entityName !== BrandsEntity::class && $brand->id != $id) {
            return false;
        }

        return true;
    }
}