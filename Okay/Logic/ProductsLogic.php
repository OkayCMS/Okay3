<?php


namespace Okay\Logic;


use Okay\Core\EntityFactory;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\FeaturesEntity;

class ProductsLogic
{
    private $entityFactory;
    private $moneyLogic;

    public function __construct(EntityFactory $entityFactory, MoneyLogic $moneyLogic)
    {
        $this->entityFactory = $entityFactory;
        $this->moneyLogic = $moneyLogic;
    }

    public function attachProductData($product)
    {
        if (empty($product->id)) {
            return false;
        }
        $products[$product->id] = $product;

        $products = $this->attachVariants($products);
        $products = $this->attachImages($products);
        $products = $this->attachFeatures($products);

        return reset($products);
    }
    
    public function getProductList($filter = [], $sortProducts = null)
    {
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        if (!empty($sortProducts)) {
            $productsEntity->order($sortProducts);
        }
        $products = $productsEntity->mappedBy('id')->find($filter);

        if (empty($products)) {
            return [];
        }

        $products = $this->attachVariants($products);
        $products = $this->attachMainImages($products);

        return $products;
    }

    public function setBrowsedProduct($productId)
    {
        // Добавление в историю просмотров товаров
        $maxVisitedProducts = 100; // Максимальное число хранимых товаров в истории
        $expire = time()+60*60*24*30; // Время жизни - 30 дней
        if (!empty($_COOKIE['browsed_products'])) {
            $browsedProducts = explode(',', $_COOKIE['browsed_products']);
            // Удалим текущий товар, если он был
            if (($exists = array_search($productId, $browsedProducts)) !== false) {
                unset($browsedProducts[$exists]);
            }
        }
        // Добавим текущий товар
        $browsedProducts[] = $productId;
        $cookieVal = implode(',', array_slice($browsedProducts, -$maxVisitedProducts, $maxVisitedProducts));
        setcookie("browsed_products", $cookieVal, $expire, "/");
    }
    
    public function attachVariants(array $products, array $variantsFilter = [])
    {
        $productsIds = array_keys($products);

        $variantsFilter['product_id'] = $productsIds;
        $variantsEntity = $this->entityFactory->get(VariantsEntity::class);
        $variants = $variantsEntity->find($variantsFilter);

        $variants = $this->moneyLogic->convertVariantsPriceToMainCurrency($variants);
        foreach ($variants as $variant) {
            $products[$variant->product_id]->variants[$variant->id] = $variant;
        }

        foreach ($products as $product) {
            if (!empty($product->variants) && count($product->variants) > 0) {
                $product->variant = reset($product->variants);
            }
        }

        return $products;
    }
    
    public function attachFeatures(array $products, array $featuresFilter = [])
    {
        /** @var FeaturesValuesEntity $featuresValuesEntity */
        $featuresValuesEntity = $this->entityFactory->get(FeaturesValuesEntity::class);
        
        /** @var FeaturesEntity $featuresEntity */
        $featuresEntity = $this->entityFactory->get(FeaturesEntity::class);
        
        $productsIds = array_keys($products);

        $featuresFilter['product_id'] = $productsIds;
        $featuresValues = [];
        $features = [];
        foreach ($featuresValuesEntity->find($featuresFilter) as $fv) {
            $featuresValues[$fv->feature_id][$fv->id] = $fv;
        }
        
        foreach ($featuresEntity->find($featuresFilter) as $f) {
            $features[$f->id] = $f;
        }

        $productsValuesIds = [];
        foreach ($featuresValuesEntity->getProductValuesIds($productsIds) as $productValueId) {
            $productsValuesIds[$productValueId->value_id][] = $productValueId->product_id;
        }
        
        foreach ($features as $feature) {
            if (isset($featuresValues[$feature->id])) {
                foreach ($featuresValues[$feature->id] as $featureValue) {
                    if (isset($productsValuesIds[$featureValue->id])) {
                        foreach ($productsValuesIds[$featureValue->id] as $productId) {
                            if (!isset($products[$productId]->features[$featureValue->feature_id])) {
                                $products[$productId]->features[$featureValue->feature_id] = clone $features[$featureValue->feature_id];
                            }
                            $products[$productId]->features[$featureValue->feature_id]->values[] = $featureValue;
                        }
                    }
                }
            }
        }

        foreach ($products as $p) {
            if (!empty($p->features)) {
                foreach ($p->features as $feature) {
                    $values = [];
                    foreach ($feature->values as $featureValue) {
                        $values[] = $featureValue->value;
                    }

                    $feature->stingify_values = implode(',', $values);
                }
            }
        }

        return $products;
    }
    
    public function attachMainImages(array $products)
    {
        $imagesEntity = $this->entityFactory->get(ImagesEntity::class);

        $imagesIds = [];
        foreach ($products as $product) {
            $imagesIds[] = $product->main_image_id;
        }

        if (empty($imagesIds)) {
            return $products;
        }

        $images = $imagesEntity->find(['id' => $imagesIds]);
        foreach ($images as $image) {
            $products[$image->product_id]->image = $image;
        }

        return $products;
    }
    
    public function attachImages(array $products)
    {
        $imagesEntity = $this->entityFactory->get(ImagesEntity::class);

        $productsIds = array_keys($products);
        
        if (empty($productsIds)) {
            return $products;
        }

        $images = $imagesEntity->find(['product_id' => $productsIds]);
        foreach ($images as $image) {
            $products[$image->product_id]->images[] = $image;
        }

        foreach ($products as $product) {
            if (!empty($product->images) && count($product->images) > 0) {
                $product->image = reset($product->images);
            }
        }
        return $products;
    }

}