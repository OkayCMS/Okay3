<?php 


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Core\Image;
use Okay\Core\Translit;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\SpecialImagesEntity;
use Okay\Entities\VariantsEntity;
use \stdClass;

class ProductAdmin extends IndexAdmin
{
    private function prepareVariantsFromPost($postFields)
    {
        if (empty($postFields)) {
            return false;
        }

        $productVariants = [];
        foreach ($postFields as $n=>$va) {
            foreach ($va as $i=>$v) {
                if (empty($productVariants[$i])) {
                    $productVariants[$i] = new stdClass();
                }
                if (empty($v) && in_array($n, ['id', 'weight'])) {
                    $v = null;
                }
                $productVariants[$i]->$n = $v;
            }
        }

        return $productVariants;
    }

    public function fetch(
        ProductsEntity $productsEntity,
        VariantsEntity $variantsEntity,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        ImagesEntity $imagesEntity,
        Image $imageCore,
        CurrenciesEntity $currenciesEntity,
        SpecialImagesEntity $specialImagesEntity,
        FeaturesValuesEntity $featuresValuesEntity,
        FeaturesEntity $featuresEntity,
        QueryFactory $queryFactory,
        Translit $translit
    ) {
        $productCategories = [];
        $relatedProducts = [];
        $productImages = [];

        /*Прием данных о товаре*/
        if ($this->request->method('post') && !empty($_POST)) {
            
            $product = new stdClass();
            $product->id       = $this->request->post('id', 'integer');
            $product->name     = $this->request->post('name');
            $product->visible  = $this->request->post('visible', 'integer');
            $product->featured = $this->request->post('featured', 'integer');
            $product->brand_id = $this->request->post('brand_id', 'integer');
            
            $product->url              = trim($this->request->post('url', 'string'));
            $product->meta_title       = $this->request->post('meta_title');
            $product->meta_keywords    = $this->request->post('meta_keywords');
            $product->meta_description = $this->request->post('meta_description');
            
            $product->annotation  = $this->request->post('annotation');
            $product->description = $this->request->post('description');
            $product->rating      = $this->request->post('rating', 'float');
            $product->votes       = $this->request->post('votes', 'integer');
            $product->special     = $this->request->post('special','string');

            $productVariants = $this->prepareVariantsFromPost($this->request->post('variants'));
            
            // Категории товара
            $productCategories = $this->request->post('categories');
            if (is_array($productCategories)) {
                $pc = [];
                foreach ($productCategories as $c) {
                    $x = new stdClass();
                    $x->id = $c;
                    $pc[$x->id] = $x;
                }
                $productCategories = $pc;
            }

            // Связанные товары
            if (is_array($this->request->post('related_products'))) {
                $rp = [];
                foreach($this->request->post('related_products') as $p) {
                    $rp[$p] = new stdClass();
                    $rp[$p]->product_id = $product->id;
                    $rp[$p]->related_id = $p;
                }
                $relatedProducts = $rp;
            }
            
            // Не допустить пустое название товара.
            if (empty($product->name)) {
                $this->design->assign('message_error', 'empty_name');
                if(!empty($product->id)) {
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);
                }
            }
            // Не допустить пустую ссылку.
            elseif (empty($product->url)) {
                $this->design->assign('message_error', 'empty_url');
                if (!empty($product->id)) {
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);
                }
            }
            // Не допустить одинаковые URL разделов.
            elseif (($p = $productsEntity->get($product->url)) && $p->id!=$product->id) {
                $this->design->assign('message_error', 'url_exists');
                if (!empty($product->id)) {
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);
                }
            }
            // Не допусть URL с '-' в начале или конце
            elseif (substr($product->url, -1) == '-' || substr($product->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
                if (!empty($product->id)) {
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);
                }
            }
            elseif (empty($productCategories)) {
                $this->design->assign('message_error', 'empty_categories');
                if (!empty($product->id)) {
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);
                }
            } else {
                
                if (empty($product->id)) {
                    //lastModify
                    if (!empty($product->brand_id)) {
                        $brandsEntity->update($product->brand_id, ['last_modify'=>'now()']);
                    }
                    
                    $product->id = $productsEntity->add($product);
                    $product = $productsEntity->get($product->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    //lastModify                    
                    $oldBrandId = $productsEntity->cols(['brand_id'])->get((int)$product->id)->brand_id;
                    if (!empty($product->brand_id) && $oldBrandId != $product->brand_id) {
                        $brandsEntity->update($oldBrandId, ['last_modify'=>'now()']);
                        $brandsEntity->update($product->brand_id, ['last_modify'=>'now()']);
                    }
                    
                    $productsEntity->update($product->id, $product);
                    $product = $productsEntity->get($product->id);
                    $this->design->assign('message_success', 'updated');
                }
                
                if (!empty($product->id)) {
                    //lastModify
                    $select = $queryFactory->newSelect();
                    $select->cols(['category_id'])
                        ->from('__products_categories')
                        ->where('product_id=:product_id')
                        ->bindValue('product_id', $product->id);
                    
                    $this->db->query($select);
                    $cIds = $this->db->results('category_id');
                    if (!empty($cIds)) {
                        $categoriesEntity->update($cIds, ['last_modify' => 'now()']);
                    }
                    
                    // Категории товара
                    $delete = $queryFactory->newDelete();
                    $delete->from('__products_categories')
                        ->where('product_id=:product_id')
                        ->bindValue('product_id', $product->id);
                    
                    $this->db->query($delete);
                    if (is_array($productCategories)) {
                        $i = 0;
                        foreach($productCategories as $category) {
                            $categoriesEntity->addProductCategory($product->id, $category->id, $i);
                            $i++;
                        }
                        unset($i);
                    }
                    
                    /*Работы с вариантами товара*/
                    if (is_array($productVariants)) {
                        $variantsIds = [];
                        
                        foreach ($productVariants as $index=>&$variant) {
                            if ($variant->stock == '∞' || $variant->stock == '') {
                                $variant->stock = null;
                            }
                            $variant->price = $variant->price > 0 ? str_replace(',', '.', $variant->price) : 0;
                            $variant->compare_price = $variant->compare_price > 0 ? str_replace(',', '.', $variant->compare_price) : 0;
                            
                            if (!empty($variant->id)) {
                                $variantsEntity->update($variant->id, $variant);
                            } else {
                                $variant->product_id = $product->id;
                                $variant->id = $variantsEntity->add($variant);
                            }
                            $variant = $variantsEntity->get((int)$variant->id);
                            if (!empty($variant->id)) {
                                $variantsIds[] = $variant->id;
                            }
                        }
                        
                        // Удалить непереданные варианты
                        $current_variants = $variantsEntity->find(['product_id'=>$product->id]);
                        foreach ($current_variants as $current_variant) {
                            if (!in_array($current_variant->id, $variantsIds)) {
                                $variantsEntity->delete($current_variant->id);
                            }
                        }
                        
                        // Отсортировать варианты
                        asort($variantsIds);
                        $i = 0;
                        foreach ($variantsIds as $variant_id) {
                            $variantsEntity->update($variantsIds[$i], ['position'=>$variant_id]);
                            $i++;
                        }
                    }
                    
                    // Удаление изображений
                    $images = (array)$this->request->post('images');
                    $currentImages = $imagesEntity->find(['product_id'=>$product->id]);
                    foreach ($currentImages as $image) {
                        if (!in_array($image->id, $images)) {
                            $imagesEntity->delete($image->id);
                        }
                    }
                    
                    // Порядок изображений
                    if ($images = $this->request->post('images')) {
                        $i=0;
                        foreach ($images as $id) {
                            $imagesEntity->update($id, ['position'=>$i]);
                            $i++;
                        }
                    }
                    
                    // Загрузка изображений drag-n-drop файлов
                    $images = $this->request->post('images_urls');
                    $droppedImages = $this->request->files('dropped_images');
                    if (!empty($images) && !empty($droppedImages)) {
                        foreach ($images as $url) {
                            $key = array_search($url, $droppedImages['name']);
                            if ($key!==false && $filename = $imageCore->uploadImage($droppedImages['tmp_name'][$key], $droppedImages['name'][$key])) {
                                $image = new stdClass();
                                $image->product_id = $product->id;
                                $image->filename = $filename;
                                $imagesEntity->add($image);
                            }
                        }
                    }
                    $productImages = $imagesEntity->find(['product_id'=>$product->id]);

                    $main_category = reset($productCategories);
                    $main_image = reset($productImages);
                    $main_image_id = $main_image ? $main_image->id : null;
                    $productsEntity->update($product->id, ['main_category_id'=>$main_category->id, 'main_image_id'=>$main_image_id]);
                    
                    //Загрузка и удаление промо-изображений
                    // Удаление изображений
                    $specialImages = (array)$this->request->post('spec_images');
                    $currentSpecialImages = $specialImagesEntity->find();
                    if (!empty($currentSpecialImages)) {
                        foreach ($currentSpecialImages as $image) {
                            if (!in_array($image->id, $specialImages)) {
                                $specialImagesEntity->delete($image->id);
                            }
                        }
                    }
                    
                    // Загрузка изображений из интернета и drag-n-drop файлов
                    if (($specialImages = $this->request->post('spec_images_urls')) && ($specDroppedImages = $this->request->files('spec_dropped_images'))) {
                        foreach ($specialImages as $url) {
                            $key = array_search($url, $specDroppedImages['name']);
                            if ($key !== false && ($specialImagesFilename = $imageCore->uploadImage($specDroppedImages['tmp_name'][$key], $specDroppedImages['name'][$key], $this->config->special_images_dir))) {
                                $specialImage = new stdClass();
                                $specialImage->filename = $specialImagesFilename;
                                $specialImagesEntity->add($specialImage);
                            }
                        }
                    }
                    // Порядок промо изображений
                    if ($specialImages = $this->request->post('spec_images')) {
                        $i=0;
                        foreach ($specialImages as $id) {
                            $specialImagesEntity->update($id, ['position'=>$i]);
                            $i++;
                        }
                    }
                    
                    // Характеристики товара
                    // Удалим все значения свойств товара
                    $featuresValuesEntity->deleteProductValue($product->id,1,3);
                    if ($featuresValues = $this->request->post('features_values')) {
                        $featuresValuesText = $this->request->post('features_values_text');
                        foreach ($featuresValues as $featureId=>$feature_values) {
                            foreach ($feature_values as $k=>$valueId) {

                                $value = trim($featuresValuesText[$featureId][$k]);
                                if (!empty($value)) {
                                    if (!empty($valueId)) {
                                        $featuresValuesEntity->update($valueId, ['value' => $value]);
                                    } else {
                                        /**
                                         * Проверим может есть занчение с таким транслитом,
                                         * дабы исключить дублирование значений "ТВ приставка" и "TV приставка" и подобных
                                         */
                                        $valueTranslit = $translit->translitAlpha($value);
                                        
                                        // Ищем значение по транслиту в основной таблице, если мы создаем значение не на основном языке
                                        $select = $queryFactory->newSelect();
                                        $select->from('__features_values')
                                            ->cols(['id'])
                                            ->where('feature_id=:feature_id')
                                            ->where('translit=:translit')
                                            ->limit(1)
                                            ->bindValues([
                                                'feature_id' => $featureId,
                                                'translit' => $valueTranslit,
                                            ]);
                                        $this->db->query($select);
                                        $valueId = $this->db->result('id');
                                        
                                        if (empty($valueId) && ($fv = $featuresValuesEntity->find(['feature_id' => $featureId, 'translit' => $valueTranslit]))) {
                                            $fv = reset($fv);
                                            $valueId = $fv->id;
                                        }
                                        
                                        // Если такого значения еще нет, но его запостили тогда добавим
                                        if (!$valueId) {
                                            $toIndex = $featuresEntity->cols(['to_index_new_value'])->get((int)$featureId)->to_index_new_value;
                                            $featureValue = new stdClass();
                                            $featureValue->value = $value;
                                            $featureValue->feature_id = $featureId;
                                            $featureValue->to_index = $toIndex;
                                            $valueId = $featuresValuesEntity->add($featureValue);
                                        }
                                    }
                                }

                                if (!empty($valueId)) {
                                    $featuresValuesEntity->addProductValue($product->id, $valueId);
                                }
                            }
                        }
                    }
                    
                    // Новые характеристики
                    $newFeaturesNames = $this->request->post('new_features_names');
                    $newFeaturesValues = $this->request->post('new_features_values');
                    if (is_array($newFeaturesNames) && is_array($newFeaturesValues)) {
                        foreach ($newFeaturesNames as $i=>$name) {
                            $value = trim($newFeaturesValues[$i]);
                            if (!empty($name) && !empty($value)) {
                                $featuresIds = $featuresEntity->cols(['id'])->find([
                                    'name' => trim($name),
                                    'limit' => 1,
                                ]);

                                $featureId = reset($featuresIds);
                                
                                if (empty($featureId)) {
                                    $featureId = $featuresEntity->add(['name'=>trim($name)]);
                                }
                                $featuresEntity->addFeatureCategory($featureId, reset($productCategories)->id);

                                // Добавляем вариант значения свойства
                                $featureValue = new stdClass();
                                $featureValue->feature_id = $featureId;
                                $featureValue->value = $value;
                                $valueId = $featuresValuesEntity->add($featureValue);

                                // Добавляем значения к товару
                                $featuresValuesEntity->addProductValue($product->id, $valueId);
                            }
                        }
                    }

                    // Связанные товары
                    $productsEntity->deleteRelatedProduct($product->id);
                    if (is_array($relatedProducts)) {
                        $pos = 0;
                        foreach($relatedProducts  as $i=>$relatedProduct) {
                            $productsEntity->addRelatedProduct($product->id, $relatedProduct->related_id, $pos++);
                        }
                    }
                }
            }
        } else {
            $id = $this->request->get('id', 'integer');
            $product = $productsEntity->get(intval($id));
            if (!empty($product->id)) {
                $productVariants = $variantsEntity->find(['product_id'=>$product->id]);
                $relatedProducts = $productsEntity->getRelatedProducts(array('product_id'=>$product->id));
                $productImages = $imagesEntity->find(['product_id'=>$product->id]);
            } else {
                // Сразу активен
                $product = new stdClass();
                $product->visible = 1;
            }
        }

        // Категории товара
        if (!empty($productCategories)) {
            $productCategories = $categoriesEntity->find(['id'=>array_keys($productCategories)]);
        } elseif (!empty($product->id)) {
            $productCategories = $categoriesEntity->find(['product_id'=>$product->id]);
        }
        
        if (empty($productVariants)) {
            $productVariants = [1];
        }
        
        if (empty($productCategories)) {
            if ($category_id = $this->request->get('category_id')) {
                $productCategories[$category_id] = new stdClass();
                $productCategories[$category_id]->id = $category_id;
            } else {
                $productCategories = [];
            }
        }
        if (empty($product->brand_id) && $brand_id = $this->request->get('brand_id')) {
            $product->brand_id = $brand_id;
        }
        
        if (!empty($relatedProducts)) {
            $r_products = [];
            foreach($relatedProducts as &$r_p) {
                $r_products[$r_p->related_id] = &$r_p;
            }
            $tempProducts = $productsEntity->find(['id' => array_keys($r_products), 'limit' => count(array_keys($r_products))]);
            foreach($tempProducts as $tempProduct) {
                $r_products[$tempProduct->id] = $tempProduct;
            }

            $relatedProductsImages = $imagesEntity->find(['product_id' => array_keys($r_products)]);
            foreach($relatedProductsImages as $image) {
                $r_products[$image->product_id]->images[] = $image;
            }
        }

        // Свойства товара
        $featuresValues = array();
        if (!empty($product->id)) {
            foreach ($featuresValuesEntity->find(['product_id' => $product->id]) as $fv) {
                $featuresValues[$fv->feature_id][] = $fv;
            }
        }

        $specialImages = $specialImagesEntity->find();
        $this->design->assign('special_images', $specialImages);
        $this->design->assign('product', $product);

        $this->design->assign('product_categories', $productCategories);
        $this->design->assign('product_variants', $productVariants);
        $this->design->assign('product_images', $productImages);
        $this->design->assign('features_values', $featuresValues);
        $this->design->assign('related_products', $relatedProducts);
        
        // Все бренды
        $brandsCount = $brandsEntity->count();
        $brands = $brandsEntity->find(['limit' => $brandsCount]);
        $this->design->assign('brands', $brands);
        
        // Все категории
        $categories = $categoriesEntity->getCategoriesTree();
        $this->design->assign('categories', $categories);
        $this->design->assign('currencies', $currenciesEntity->find());
        
        // Все свойства товара
        $category = reset($productCategories);
        if (!is_object($category)) {
            $category = reset($categories);
        }
        if (is_object($category)) {
            $features = $featuresEntity->find(['category_id'=>$category->id]);
            $this->design->assign('features', $features);
        }

        $this->response->setContent($this->design->fetch('product.tpl'));
        //return $this->smarty_func();// todo license
    }

    private function smarty_func(){
        if (file_exists('backend/core/LicenseAdmin.php')) {
            $module = $this->request->get('module', 'string');
            $module = preg_replace("/[^A-Za-z0-9]+/", "", $module);
            $p=13; $g=3; $x=5; $r = ''; $s = $x;
            $bs = explode(' ', $this->config->license);
            foreach($bs as $bl){
                for($i=0, $m=''; $i<strlen($bl)&&isset($bl[$i+1]); $i+=2){
                    $a = base_convert($bl[$i], 36, 10)-($i/2+$s)%27;
                    $b = base_convert($bl[$i+1], 36, 10)-($i/2+$s)%24;
                    $m .= ($b * (pow($a,$p-$x-5) )) % $p;}
                $m = base_convert($m, 10, 16); $s+=$x;
                for ($a=0; $a<strlen($m); $a+=2) $r .= @chr(hexdec($m{$a}.$m{($a+1)}));}

            @list($l->domains, $l->expiration, $l->comment) = explode('#', $r, 3);

            $l->domains = explode(',', $l->domains);
            $h = getenv("HTTP_HOST");
            if(substr($h, 0, 4) == 'www.') $h = substr($h, 4);
            $sv = false;$da = explode('.', $h);$it = count($da);
            for ($i=1;$i<=$it;$i++) {
                unset($da[0]);$da = array_values($da);$d = '*.'.implode('.', $da);
                if (in_array($d, $l->domains) || in_array('*.'.$h, $l->domains)) {
                    $sv = true;break;
                }
            }
            if(((!in_array($h, $l->domains) && !$sv) || (strtotime($l->expiration)<time() && $l->expiration!='*')) && $module!='LicenseAdmin') {
                $this->design->fеtсh('рrоduсt.tрl');
            } else {
                $l->valid = true;
                $this->design->assign('license', $l);
                $this->design->assign('license', $l);
                $license_result =  $this->design->fetch('product.tpl');
                return $license_result;
            }
        }
        else{
            die('<a href="http://okay-cms.com">OkayCMS</a>');
        }
    }
    
}
