<?php


use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ManagersEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\BrandsEntity;
use Okay\Core\QueryFactory;
use Okay\Core\Managers;
use Okay\Core\Response;
use Okay\Core\Database;

require_once 'configure.php';

$columnDelimiter      = ';';
$valuesDelimiter      = ',,';
$subcategoryDelimiter = '/';
$productsCount        = 100;
$exportFilesDir      = 'backend/files/export/';
$filename              = 'export.csv';

$columnsNames = [
    'category'         => 'Category',
    'brand'            => 'Brand',
    'name'             => 'Product',
    'variant'          => 'Variant',
    'sku'              => 'SKU',
    'price'            => 'Price',
    'compare_price'    => 'Old price',
    'currency'         => 'Currency ID',
    'weight'           => 'Weight',
    'stock'            => 'Stock',
    'units'            => 'Units',
    'visible'          => 'Visible',
    'featured'         => 'Featured',
    'meta_title'       => 'Meta title',
    'meta_keywords'    => 'Meta keywords',
    'meta_description' => 'Meta description',
    'annotation'       => 'Annotation',
    'description'      => 'Description',
    'images'           => 'Images',
    'url'              => 'URL',
];

/** @var Database $db */
$db = $DI->get(Database::class);

/** @var QueryFactory $queryFactory */
$queryFactory = $DI->get(QueryFactory::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var FeaturesValuesEntity $featuresValuesEntity */
$featuresValuesEntity = $entityFactory->get(FeaturesValuesEntity::class);

/** @var CategoriesEntity $categoriesEntity */
$categoriesEntity     = $entityFactory->get(CategoriesEntity::class);

/** @var ProductsEntity $productsEntity */
$productsEntity       = $entityFactory->get(ProductsEntity::class);

/** @var VariantsEntity $variantsEntity */
$variantsEntity       = $entityFactory->get(VariantsEntity::class);

/** @var FeaturesEntity $featuresEntity */
$featuresEntity       = $entityFactory->get(FeaturesEntity::class);

/** @var ImagesEntity $imagesEntity */
$imagesEntity         = $entityFactory->get(ImagesEntity::class);

/** @var BrandsEntity $brandsEntity */
$brandsEntity         = $entityFactory->get(BrandsEntity::class);

/** @var ManagersEntity $managersEntity */
$managersEntity       = $entityFactory->get(ManagersEntity::class);


if (!$managers->access('export', $managersEntity->get($_SESSION['admin']))) {
    exit();
}

session_write_close();
unset($_SESSION['lang_id']);
unset($_SESSION['admin_lang_id']);

// Страница, которую экспортируем
$page = $request->get('page');
if(empty($page) || $page==1) {
    $page = 1;
    if(is_writable($exportFilesDir.$filename)) {
        unlink($exportFilesDir.$filename);
    }
}

// Открываем файл экспорта на добавление
$f = fopen($exportFilesDir.$filename, 'ab');

$filter = ['page'=>$page, 'limit'=>$productsCount];
$featuresFilter = [];
if (($cid = $request->get('category_id', 'integer')) && ($category = $categoriesEntity->get($cid))) {
    $filter['category_id'] = $featuresFilter['category_id'] = $category->children;
}
if ($brandId = $request->get('brand_id', 'integer')) {
    $filter['brand_id'] = $brandId;
}

// Экспортируем свойства
$featuresFilter['limit'] = $featuresEntity->count($featuresFilter);
$features = $featuresEntity->find($featuresFilter);
foreach($features as $feature) {
    $columnsNames[$feature->name] = $feature->name;
}

// Если начали сначала - добавим в первую строку названия колонок
if($page == 1) {
    fputcsv($f, $columnsNames, $columnDelimiter);
}

$products = array();
foreach($productsEntity->find($filter) as $p) {
    $products[$p->id] = (array)$p;
}

$productsIds = array_keys($products);

$featuresValues = [];
foreach ($featuresValuesEntity->find(['product_id' => $productsIds]) as $fv) {
    $featuresValues[$fv->id] = $fv;
}

$productsValues = [];
foreach ($featuresValuesEntity->getProductValuesIds($productsIds) as $pv) {
    $productsValues[$pv->product_id][$pv->value_id] = $pv->value_id;
}

// Значения свойств товара
foreach($products as $pId=>&$product) {

    if (isset($productsValues[$pId])) {
        $productFeatureValues = [];
        foreach($productsValues[$pId] as $valueId) {
            if(isset($featuresValues[$valueId])) {
                $feature = $featuresValues[$valueId];
                $tempFeature = $featuresEntity->get(intval($feature->feature_id));
                $productFeatureValues[$tempFeature->name][] = str_replace(',', '.', trim($feature->value));
            }
        }

        foreach ($productFeatureValues as $featureName=>$values) {
            $product[$featureName] = implode($valuesDelimiter, $values);
        }
    }

    $categories = [];
    $cats = $categoriesEntity->getProductCategories($pId);
    foreach($cats as $category) {
        $path = [];
        $cat = $categoriesEntity->get((int)$category->category_id);
        if(!empty($cat)) {
            // Вычисляем составляющие категории
            foreach($cat->path as $p) {
                $path[] = str_replace($subcategoryDelimiter, '\\'.$subcategoryDelimiter, $p->name);
            }
            // Добавляем категорию к товару
            $categories[] = implode('/', $path);
        }
    }
    $product['category'] = implode(',, ', $categories);
}

// Изображения товаров
$images = $imagesEntity->find(['product_id'=>array_keys($products)]);
foreach($images as $image) {
    // Добавляем изображения к товару чезер запятую
    if(empty($products[$image->product_id]['images'])) {
        $products[$image->product_id]['images'] = $image->filename;
    } else {
        $products[$image->product_id]['images'] .= ', '.$image->filename;
    }
}

$variants = $variantsEntity->find(['product_id'=>array_keys($products)]);
foreach($variants as $variant) {
    if(isset($products[$variant->product_id])) {
        $v                    = [];
        $v['variant']         = $variant->name;
        $v['price']           = $variant->price;
        $v['compare_price']   = $variant->compare_price;
        $v['sku']             = $variant->sku;
        $v['stock']           = $variant->stock;
        $v['weight']          = $variant->weight;
        $v['units']           = $variant->units;
        $v['currency']        = $variant->currency_id;
        if($variant->infinity) {
            $v['stock']       = '';
        }
        $products[$variant->product_id]['variants'][] = $v;
    }
}

$allBrands = [];
$brandsCount = $brandsEntity->count();
foreach ($brandsEntity->find(['limit'=>$brandsCount]) as $b) {
    $allBrands[$b->id] = $b;
}

foreach($products as &$product) {
    if ($product['brand_id'] && isset($allBrands[$product['brand_id']])) {
        $product['brand'] = $allBrands[$product['brand_id']]->name;
    }

    $variants = $product['variants'];
    unset($product['variants']);

    if(isset($variants)) {
        foreach($variants as $variant) {
            $res = [];
            $result =  $product;
            foreach($variant as $name=>$value) {
                $result[$name]=$value;
            }

            foreach($columnsNames as $internalName=>$columnName) {
                if(isset($result[$internalName])) {
                    $res[$internalName] = str_replace(["\r\n", "\r", "\n"], '', $result[$internalName]);
                } else {
                    $res[$internalName] = '';
                }
            }
            fputcsv($f, $res, $columnDelimiter);
        }
    }
}

$totalProducts = $productsEntity->count($filter);
fclose($f);

if ($productsCount*$page < $totalProducts) {
    $data = ['end'=>false, 'page'=>$page, 'totalpages'=>$totalProducts/$productsCount];
} else {
    $data = ['end'=>true, 'page'=>$page, 'totalpages'=>$totalProducts/$productsCount];
    // Эксель кушает только 1251, поэтому конвертируем файл
    file_put_contents($exportFilesDir.$filename, iconv( "utf-8", "windows-1251//IGNORE", file_get_contents($exportFilesDir.$filename)));
}

if($data) {
    $response->setContent(json_encode($data), RESPONSE_JSON)->sendContent();
}