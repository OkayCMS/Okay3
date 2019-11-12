<?php 


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendValidateHelper;
use \stdClass;
use Okay\Entities\BrandsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Admin\Requests\BackendProductsRequest;
use Okay\Admin\Helpers\BackendProductsHelper;
use Okay\Admin\Helpers\BackendVariantsHelper;
use Okay\Admin\Helpers\BackendFeaturesHelper;
use Okay\Admin\Helpers\BackendSpecialImagesHelper;
use Okay\Core\Entity\UrlUniqueValidator;

class ProductAdmin extends IndexAdmin
{

    public function fetch(
        ProductsEntity             $productsEntity,
        CategoriesEntity           $categoriesEntity,
        BrandsEntity               $brandsEntity,
        CurrenciesEntity           $currenciesEntity,
        BackendProductsRequest     $productRequest,
        BackendProductsHelper      $backendProductsHelper,
        BackendVariantsHelper      $backendVariantsHelper,
        BackendFeaturesHelper      $backendFeaturesHelper,
        BackendSpecialImagesHelper $backendSpecialImagesHelper,
        BackendValidateHelper      $backendValidateHelper
    ) {

        if ($this->request->method('post') && !empty($_POST)) {
            $product           = $productRequest->postProduct();
            $productVariants   = $productRequest->postVariants();
            $productCategories = $productRequest->postCategories();
            $relatedProducts   = $productRequest->postRelatedProducts();

            if ($error = $backendValidateHelper->getProductValidateError($product, $productCategories)) {
                $this->design->assign('message_error', $error);
            } else {
                // Товар
                if (empty($product->id)) {
                    $preparedProduct = $backendProductsHelper->prepareAdd($product);
                    $addedProductId  = $backendProductsHelper->add($preparedProduct);
                    $product = $productsEntity->get($addedProductId);

                    $this->design->assign('message_success', 'added');
                } else {
                    $preparedProduct = $backendProductsHelper->prepareUpdate($product);
                    $backendProductsHelper->update($preparedProduct);
                    $product = $productsEntity->get($product->id);

                    $this->design->assign('message_success', 'updated');
                }

                // Категории
                $productCategories = $backendProductsHelper->prepareUpdateProductsCategories($product, $productCategories);
                $backendProductsHelper->updateProductsCategories($product, $productCategories);

                // Варианты
                $productVariants = $backendVariantsHelper->prepareUpdateVariants($productVariants);
                $backendVariantsHelper->updateVariants($product, $productVariants);

                // Картинки
                $images        = $productRequest->postImages();
                $droppedImages = $productRequest->fileDroppedImages();
                $backendProductsHelper->updateImages($product, $images, $droppedImages);
                
                // Промо-изображения
                $specImages        = $productRequest->postSpecialImages();
                $specDroppedImages = $productRequest->fileDroppedSpecialImages();
                $backendProductsHelper->updateSpecialImages($product, $specImages, $specDroppedImages);

                // Характеристики
                $featuresValues     = $productRequest->postFeaturesValues();
                $featuresValuesText = $productRequest->postFeaturesValuesText();
                $newFeaturesNames   = $productRequest->postNewFeaturesNames();
                $newFeaturesValues  = $productRequest->postNewFeaturesValues();
                $backendFeaturesHelper->updateProductFeatures(
                    $product,
                    $featuresValues,
                    $featuresValuesText,
                    $newFeaturesNames,
                    $newFeaturesValues,
                    $productCategories
                );

                // Связанные товары
                $relatedProducts = $backendProductsHelper->prepareUpdateRelatedProducts($product, $relatedProducts);
                $backendProductsHelper->updateRelatedProducts($product, $relatedProducts);
            }
        } else {
            $id = $this->request->get('id', 'integer');
            $product = $productsEntity->get(intval($id));
            if (empty($product->id)) {
                // Сразу активен
                $product = new stdClass();
                $product->visible = 1;
            }
        }

        $categoriesTree = $categoriesEntity->getCategoriesTree();
        
        $productVariants   = $backendVariantsHelper->findProductVariants($product);
        $productImages     = $backendProductsHelper->findProductImages($product);
        $productCategories = $backendProductsHelper->findProductCategories($product);
        $relatedProducts   = $backendProductsHelper->findRelatedProducts($product);
        $features          = $backendFeaturesHelper->findCategoryFeatures($productCategories, $categoriesTree);
        $featuresValues    = $backendFeaturesHelper->findProductFeaturesValues($product);
        $specialImages     = $backendSpecialImagesHelper->findSpecialImages();

        if (empty($product->brand_id) && $brand_id = $this->request->get('brand_id')) {
            $product->brand_id = $brand_id;
        }

        $this->design->assign('product',            $product);
        $this->design->assign('special_images',     $specialImages);
        $this->design->assign('product_categories', $productCategories);
        $this->design->assign('product_variants',   $productVariants);
        $this->design->assign('product_images',     $productImages);
        $this->design->assign('features',           $features);
        $this->design->assign('features_values',    $featuresValues);
        $this->design->assign('related_products',   $relatedProducts);

        $brandsCount = $brandsEntity->count();
        $brands = $brandsEntity->find(['limit' => $brandsCount]);

        $this->design->assign('brands',     $brands);
        $this->design->assign('categories', $categoriesTree);
        $this->design->assign('currencies', $currenciesEntity->find());

        $this->response->setContent($this->design->fetch('product.tpl'));
    }

    private function validateProduct($product, ProductsEntity $productsEntity, $productCategories, UrlUniqueValidator $urlUniqueValidator)
    {
        if (empty($product->name)) {
            $this->design->assign('message_error', 'empty_name');
            return false;
        }
        elseif (empty($product->url)) {
            $this->design->assign('message_error', 'empty_url');
            return false;
        }
        elseif (($p = $productsEntity->get($product->url)) && $p->id != $product->id) {
            $this->design->assign('message_error', 'url_exists');
            return false;
        }
        elseif ($this->settings->get('global_unique_url') && !$urlUniqueValidator->validateGlobal($product->url, ProductsEntity::class, $product->id)) {
            $this->design->assign('message_error', 'global_url_exists');
            return false;
        }
        elseif (substr($product->url, -1) == '-' || substr($product->url, 0, 1) == '-') {
            $this->design->assign('message_error', 'url_wrong');
            return false;
        }
        elseif (empty($productCategories)) {
            $this->design->assign('message_error', 'empty_categories');
            return false;
        }

        return true;
    }
}
