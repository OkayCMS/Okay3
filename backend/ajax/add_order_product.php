<?php

use Okay\Core\Image;
use Okay\Entities\ImagesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\ProductsEntity;

require_once 'configure.php';

if (!$managers->access('orders',  $manager)) {
    exit();
}

/*Поиск товара*/
$keyword = $request->get('query', 'string');

/** @var CurrenciesEntity $currenciesEntity */
$currenciesEntity = $entityFactory->get(CurrenciesEntity::class);

/** @var ProductsEntity $productsEntity */
$productsEntity = $entityFactory->get(ProductsEntity::class);

/** @var VariantsEntity $variantsEntity */
$variantsEntity = $entityFactory->get(VariantsEntity::class);

/** @var ImagesEntity $imagesEntity */
$imagesEntity = $entityFactory->get(ImagesEntity::class);

/** @var Image $imagesCore */
$imagesCore = $DI->get(Image::class);

$productFields = [
    'id',
    'name',
    'main_image_id',
];

$productsFilter = [
    'keyword' => $keyword,
    'limit' => 10,
    'in_stock' => !$settings->is_preorder,
];

$imagesIds = [];
$products = [];
foreach ($productsEntity->cols($productFields)->find($productsFilter) as $product) {
    $products[$product->id] = $product;
    $imagesIds[] = $product->main_image_id;
}

if (!empty($products)) {
    foreach ($imagesEntity->find(['id' => $imagesIds]) as $image) {
        if (isset($products[$image->product_id])) {
            $products[$image->product_id]->image = $image->filename;
        }
    }

    $variants = $variantsEntity->find([
        'product_id' => array_keys($products),
        'in_stock' => !$settings->is_preorder,
        'has_price' => true,
    ]);

    foreach ($variants as $variant) {
        if (isset($products[$variant->product_id])) {
            $variant->units = $variant->units ? $variant->units : $settings->units;
            $products[$variant->product_id]->variants[] = $variant;
            if ($variant->currency_id && ($currency = $currenciesEntity->get(intval($variant->currency_id)))) {
                if ($currency->rate_from != $currency->rate_to) {
                    $variant->price = round($variant->price*$currency->rate_to/$currency->rate_from,2);
                    $variant->compare_price = round($variant->compare_price*$currency->rate_to/$currency->rate_from,2);
                }
            }
        }
    }
}

$suggestions = [];
foreach($products as $product) {
    if(!empty($product->variants)) {
        $suggestion = new \stdClass;
        if(!empty($product->image)) {
            $product->image = $imagesCore->getResizeModifier($product->image, 35, 35);
        }
        $suggestion->value = $product->name;
        $suggestion->data = $product;
        $suggestions[] = $suggestion;
    }
}

$result = new \stdClass;
$result->query = $keyword;
$result->suggestions = $suggestions;
$response->setContent(json_encode($result), RESPONSE_JSON);
$response->sendContent();
