<?php


namespace Okay\Core;


use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\ImagesEntity;

class Comparison
{
    /** @var ProductsEntity */
    private $productsEntity;

    /** @var VariantsEntity */
    private $variantsEntity;

    /** @var ImagesEntity */
    private $imagesEntity;

    /** @var FeaturesValuesEntity */
    private $featuresValuesEntity;

    /** @var FeaturesEntity */
    private $featuresEntity;
    
    private $settings;

    public function __construct(
        EntityFactory $entityFactory,
        Settings $settings
    ){
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->variantsEntity = $entityFactory->get(VariantsEntity::class);
        $this->imagesEntity   = $entityFactory->get(ImagesEntity::class);
        $this->featuresEntity   = $entityFactory->get(FeaturesEntity::class);
        $this->featuresValuesEntity   = $entityFactory->get(FeaturesValuesEntity::class);
        $this->settings       = $settings;
    }

    public function get()
    {
        $comparison = new \stdClass();
        $comparison->products = [];
        $comparison->features = [];
        $comparison->ids = [];

        $items = !empty($_COOKIE['comparison']) ? json_decode($_COOKIE['comparison']) : [];
        if (!empty($items) && is_array($items)) {
            $products = [];
            $images_ids = [];
            foreach ($this->productsEntity->find(['id'=>$items, 'visible'=>1]) as $p) {
                $products[$p->id] = $p;
                $images_ids[] = $p->main_image_id;
            }
            if (!empty($products)) {
                $products_ids = array_keys($products);
                $comparison->ids = $products_ids;
                foreach($products as $product) {
                    $product->variants = [];
                    $product->features = [];
                }

                $variants = $this->variantsEntity->find(array('product_id'=>$products_ids));

                foreach($variants as $variant) {
                    $products[$variant->product_id]->variants[] = $variant;
                }

                if (!empty($images_ids)) {
                    $images = $this->imagesEntity->find(array('id'=>$images_ids));
                    foreach ($images as $image) {
                        if (isset($products[$image->product_id])) {
                            $products[$image->product_id]->image = $image;
                        }
                    }
                }

                $featuresValues = $this->featuresValuesEntity->mappedBy('id')->find(array('product_id'=>$products_ids));

                $productsValues = [];
                foreach ($this->featuresValuesEntity->getProductValuesIds($products_ids) as $pv) {
                    $productsValues[$pv->product_id][$pv->value_id] = $pv->value_id;
                }

                $featuresIds = [];
                foreach ($featuresValues as $fv) {
                    $featuresIds[] = $fv->feature_id;
                }
                
                $features = $this->featuresEntity->mappedBy('id')->find(['id' => $featuresIds]);
                foreach ($featuresValues as $fv) {
                    if (isset($features[$fv->feature_id])) {
                        $features[$fv->feature_id]->value = $fv->value;
                    }

                    foreach ($products as $p) {
                        if(isset($productsValues[$p->id][$fv->id])){
                            $features[$fv->feature_id]->products[$p->id][] = $fv->value;
                        } else {
                            $features[$fv->feature_id]->products[$p->id] = null;
                        }
                    }
                }

                foreach ($featuresValues as $fv) {
                    foreach ($products as $p) {
                        if (is_array($features[$fv->feature_id]->products[$p->id])){
                            $features[$fv->feature_id]->products[$p->id] = implode(", ", $features[$fv->feature_id]->products[$p->id]);
                        }
                    }
                    $features[$fv->feature_id]->not_unique = (count(array_unique($features[$fv->feature_id]->products)) == 1) ? true : false;
                }

                if (!empty($features)) {
                    $comparison->features = $features;
                }

                foreach($products as $product) {
                    if(isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }

                    $productFeatures = [];
                    if (isset($productsValues[$product->id])) {
                        foreach ($productsValues[$product->id] as $valueId) {
                            if ($featureValue = $featuresValues[$valueId]) {
                                $productFeatures[$featureValue->feature_id][] = $featureValue->value;
                            }
                        }
                    }

                    foreach($features as $f) {
                        if (isset($productFeatures[$f->id])) {
                            $product->features[$f->id] = implode(", ", $productFeatures[$f->id]);
                        } else {
                            $product->features[$f->id] = null;
                        }
                    }
                }
                $comparison->products = $products;
            }
        }
        return $comparison;
    }

    public function addItem($productId)
    {
        $items = !empty($_COOKIE['comparison']) ? json_decode($_COOKIE['comparison']) : array();
        $items = $items && is_array($items) ? $items : array();
        if (!in_array($productId, $items)) {
            $items[] = $productId;
            if ($this->settings->comparison_count && $this->settings->comparison_count < count($items)) {
                array_shift($items);
            }
        }
        $_COOKIE['comparison'] = json_encode($items);
        setcookie('comparison', $_COOKIE['comparison'], time()+30*24*3600, '/');
    }

    /*Удаление товара из корзины*/
    public function deleteItem($productId)
    {
        $items = !empty($_COOKIE['comparison']) ? json_decode($_COOKIE['comparison']) : array();
        if (!is_array($items)) {
            return;
        }
        $i = array_search($productId, $items);
        if ($i !== false) {
            unset($items[$i]);
        }
        $items = array_values($items);
        $_COOKIE['comparison'] = json_encode($items);
        setcookie('comparison', $_COOKIE['comparison'], time()+30*24*3600, '/');
    }
    
    /*Очистка списка сравнения*/
    public function emptyComparison()
    {
        unset($_COOKIE['comparison']);
        setcookie('comparison', '', time()-3600, '/');
    }
}
