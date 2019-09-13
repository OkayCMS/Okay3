<?php


namespace Okay\Controllers;


use Okay\Entities\BrandsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\FeaturesAliasesValuesEntity;
use Okay\Entities\FeaturesValuesAliasesValuesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\SEOFilterPatternsEntity;
use Okay\Logic\CatalogLogic;
use Okay\Logic\FilterLogic;
use Okay\Logic\ProductsLogic;

class CategoryController extends AbstractController
{

    private $catalogType = 'category';
    private $isFilterPage = false;
    private $categoryFeatures = [];
    private $categoryBrands = [];

    /*Отображение каталога*/
    public function render(
        BrandsEntity $brandsEntity,
        CategoriesEntity $categoriesEntity,
        CatalogLogic $catalogLogic,
        ProductsLogic $productsLogic,
        FilterLogic $filterLogic,
        ProductsEntity $productsEntity,
        FeaturesValuesEntity $featuresValuesEntity,
        $url,
        $filtersUrl = ''
    ) {
        $filter['visible'] = 1;
        $sortProducts = null;

        $filterLogic->setFiltersUrl($filtersUrl);
        
        $category = $categoriesEntity->get((string)$url);
        if (empty($category) || (!$category->visible && empty($_SESSION['admin']))) {
            return false;
        }
        $this->design->assign('category', $category);
        $filter['category_id'] = $category->children;

        $filterLogic->setCategory($category);
        $this->categoryFeatures = $filterLogic->getCategoryFeatures();
        
        if (($currentBrandsIds = $filterLogic->getCurrentBrands($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentOtherFilters = $filterLogic->getCurrentOtherFilters($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentPage = $filterLogic->getCurrentPage($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentFeatures = $filterLogic->getCurrentCategoryFeatures($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentSort = $filterLogic->getCurrentSort($filtersUrl)) === false) {
            return false;
        }

        $filterLogic->changeLangUrls($filtersUrl);

        // Если задан бренд, выберем его из базы
        if (!empty($currentBrandsIds)) {
            $filter['brand_id'] = $currentBrandsIds;
            $this->design->assign('selected_brands_ids', $currentBrandsIds);
        }

        if (!empty($currentOtherFilters)) {
            $filter['other_filter'] = $currentOtherFilters;
            $this->design->assign('selected_other_filters', $currentOtherFilters);
        }

        $filter['price'] = $catalogLogic->getPriceFilter($this->catalogType, $category->id);

        // Сортировка товаров, сохраняем в сесси, чтобы текущая сортировка оставалась для всего сайта
        if (!empty($currentSort)) {
            $_SESSION['sort'] = $currentSort;
        }
        if (!empty($_SESSION['sort'])) {
            $sortProducts = $_SESSION['sort'];
        } else {
            $sortProducts = 'position';
        }
        $this->design->assign('sort', $currentSort);

        // Свойства товаров
        if (!empty($this->categoryFeatures)) {
            foreach ($this->categoryFeatures as $feature) {
                if (isset($currentFeatures[$feature->id])) {
                    $filter['features'][$feature->id] = $currentFeatures[$feature->id];
                }
            }
        }

        // Выбираем бренды, они нужны нам в шаблоне
        $brandsFilter = [
            'category_id' => $category->children,
            'visible' => 1,
            'product_visible' => 1,
        ];
        foreach ($brandsEntity->find($brandsFilter) as $b) {
            $this->categoryBrands[$b->id] = $b;
        }
         
        $metaArray = $filterLogic->getMetaArray($filtersUrl);
        // Если в строке есть параметры которые не должны быть в фильтре, либо параметры с другой категории, бросаем 404
        if (!empty($metaArray['features_values']) && array_intersect_key($metaArray['features_values'], $this->categoryFeatures) !== $metaArray['features_values'] ||
            !empty($metaArray['brand']) && array_intersect_key($metaArray['brand'], $this->categoryBrands) !== $metaArray['brand']) {
            return false;
        }

        if ((!empty($filter['price']) && $filter['price']['min'] !== '' && $filter['price']['max'] !== '' && $filter['price']['min'] !== null)
            || !empty($filter['features'])
            || !empty($filter['other_filter'])
            || !empty($filter['brand_id'])
        ) {
            $this->isFilterPage = true;
            $this->design->assign('is_filter_page', $this->isFilterPage);
        }
        
        $brandsFilter = [
            'category_id'   => $category->children,
            'visible'       => 1,
            'product_visible' => 1
        ];

        if (!empty($filter['features'])) {
            $brandsFilter['features'] = $filter['features'];
        }

        if (!empty($filter['other_filter'])) {
            $brandsFilter['other_filter'] = $filter['other_filter'];
        }

        if (!empty($filter['price']) && $filter['price']['min'] != '' && $filter['price']['max'] != '') {
            $brandsFilter['price'] = $filter['price'];
        }

        // В выборку указываем выбранные бренды, чтобы достать еще и все выбранные бренды, чтобы их можно было отменить
        if (!empty($currentBrandsIds)) {
            $brandsFilter['selected_brands'] = $currentBrandsIds;// todo проверить, корректно ли работает
        }
        
        $category->brands = $brandsEntity->find($brandsFilter);
        // Если в фильтре только один бренд и он не выбран, тогда вообще не выводим фильтр по бренду
        if (($firstBrand = reset($category->brands)) && count($category->brands) <= 1 && !in_array($firstBrand->id, $currentBrandsIds)) {
            unset($category->brands);
        }
        
        $featuresValuesFilter['visible'] = 1;
        if (!empty($this->categoryFeatures)) {
            $features_ids = array_keys($this->categoryFeatures);
            if (!empty($features_ids)) {
                $featuresValuesFilter['feature_id'] = $features_ids;
            }
        }
        $featuresValuesFilter['category_id'] = $category->children;

        /**
         * Получаем значения свойств для категории, чтобы на страницах фильтров убрать фильтры
         * у которых изначально был только один вариант выбора
         */
        $baseFeaturesValues = [];
        if ($this->isFilterPage === true) {
            foreach ($featuresValuesEntity->find($featuresValuesFilter) as $fv) {
                $baseFeaturesValues[$fv->feature_id][$fv->id] = $fv;
            }
        }
        
        if (isset($filter['features'])) {
            $featuresValuesFilter['features'] = $filter['features'];
            $featuresValuesFilter['selected_features'] = $filter['features']; // todo
        }
        if (!empty($brand)) {
            $featuresValuesFilter['brand_id'] = $brand->id;
        } elseif (isset($filter['brand_id'])) {
            $featuresValuesFilter['brand_id'] = $filter['brand_id'];
        }

        if (!empty($filter['other_filter'])) {
            $featuresValuesFilter['other_filter'] = $filter['other_filter'];
        }

        if (!empty($filter['price']) && $filter['price']['min'] != '' && $filter['price']['max'] != '') {
            $featuresValuesFilter['price'] = $filter['price'];
        }

        $featuresValuesEntity->addHighPriority('category_id');
        $featuresValues = $featuresValuesEntity->find($featuresValuesFilter);
        foreach ($featuresValues as $featureValue) {
            if(isset($this->categoryFeatures[$featureValue->feature_id])) {
                $filterLogic->setCategoryFeatureValue($featureValue);
                $this->categoryFeatures[$featureValue->feature_id]->features_values[$featureValue->id] = $featureValue;
            }
        }
        
        $metaArray = $filterLogic->getMetaArray($filtersUrl);
        $selectedFeaturesValues = [];
        if (!empty($metaArray['features_values'])) {
            $selectedFeaturesValues = $metaArray['features_values'];
        }
        if (!empty($this->categoryFeatures)) {
            foreach ($this->categoryFeatures as $i => $feature) {
                // Если хоть одно значение свойства выбранно, его убирать нельзя
                if (empty($selectedFeaturesValues[$feature->id])) {
                    // На странице фильтра убираем свойства у корорых вообще нет значений (отфильтровались)
                    // или они изначально имели только один вариант выбора
                    if ($this->isFilterPage === true) {
                        if (!isset($baseFeaturesValues[$feature->id])
                            || (count($baseFeaturesValues[$feature->id]) <= 1
                            || !isset($feature->features_values)
                            || count($feature->features_values) == 0)) {
                            
                            unset($this->categoryFeatures[$i]);
                        }
                        // Иначе убираем свойства у которых только один вариант выбора
                    } elseif (!isset($feature->features_values) || count($feature->features_values) <= 1) {
                        unset($this->categoryFeatures[$i]);
                    }
                }
            }
        }
        $this->design->assign('features', $this->categoryFeatures);
        $this->design->assign('selected_filters', $selectedFeaturesValues);

        $this->design->assign('other_filters', $catalogLogic->getOtherFilters($filter));
        
        $prices = $catalogLogic->getPrices($filter, $this->catalogType, $category->id);
        $this->design->assign('prices', $prices);

        
        $paginate = $catalogLogic->paginate(
            $this->settings->get('products_num'),
            $currentPage,
            $filter,
            $this->design
        );
        $this->design->assign('current_page', $currentPage);
        
        if (!$paginate) {
            return false;
        }

        // Товары
        $products = $productsLogic->getProductList($filter, $sortProducts);
        $this->design->assign('products', $products);
        
        if ($this->request->get('ajax','boolean')) {
            $this->design->assign('ajax', 1);
            $result = new \stdClass;
            $result->products_content = $this->design->fetch('products_content.tpl');
            $result->products_pagination = $this->design->fetch('chpu_pagination.tpl');
            $result->products_sort = $this->design->fetch('products_sort.tpl');
            $result->features = $this->design->fetch('features.tpl');
            $result->selected_features = $this->design->fetch('selected_features.tpl');
            $this->response->setContent(json_encode($result), RESPONSE_JSON);
            return;
        }

        //lastModify
        $lastModify = $productsEntity->cols(['last_modify'])
            ->order('last_modify_desc')
            ->find([
                'category_id' => $filter['category_id'],
                'limit' => 1,
            ]);
        $lastModify[] = $category->last_modify;
        if ($this->page) {
            $lastModify[] = $this->page->last_modify;
        }
        $this->response->setHeaderLastModify(max($lastModify));
        //lastModify END
        
        /** @var FeaturesAliasesValuesEntity $featuresAliasesValuesEntity */
        $featuresAliasesValuesEntity = $this->entityFactory->get(FeaturesAliasesValuesEntity::class);
        
        /** @var FeaturesValuesAliasesValuesEntity $featuresValuesAliasesValuesEntity */
        $featuresValuesAliasesValuesEntity = $this->entityFactory->get(FeaturesValuesAliasesValuesEntity::class);
        
        /** @var SEOFilterPatternsEntity $SEOFilterPatternsEntity */
        $SEOFilterPatternsEntity = $this->entityFactory->get(SEOFilterPatternsEntity::class);
        
        /** @var FeaturesEntity $featuresEntity */
        $featuresEntity = $this->entityFactory->get(FeaturesEntity::class);
        
        $parts = array(
            '{$category}' => ($category->name ? $category->name : ''),
            '{$category_h1}' => ($category->name_h1 ? $category->name_h1 : ''),
            '{$sitename}' => ($this->settings->get('site_name') ? $this->settings->get('site_name') : '')
        );

        if (!empty($filter['features'])) {
            foreach ($featuresAliasesValuesEntity->find(array('feature_id'=>array_keys($filter['features']))) as $fv) {
                $parts['{$f_alias_'.$fv->variable.'}'] = $fv->value;
            }
            
            $aliasesValuesFilter['feature_id'] = array_keys($filter['features']);
            // Если только одно значение одного свойства, получим для него все алиасы значения
            if (count($filter['features']) == 1 && (count($translits = reset($filter['features']))) == 1) {
                $aliasesValuesFilter['translit'] = reset($translits);
            }
            foreach ($featuresValuesAliasesValuesEntity->find($aliasesValuesFilter) as $ov) {
                $parts['{$o_alias_'.$ov->variable.'}'] = $ov->value;
            }
        }

        $seoFilterPattern = null;
        $seoFilterPatterns = [];
        $metaArray = $filterLogic->getMetaArray($filtersUrl);
        
        if (!empty($metaArray['brand']) && count($metaArray['brand']) == 1 && empty($metaArray['features_values'])) {
            $parts['{$brand}'] = reset($metaArray['brand']);
            $seoFilterPatterns = $SEOFilterPatternsEntity->find(['category_id'=>$category->id, 'type'=>'brand']);
            $seoFilterPattern = reset($seoFilterPatterns);

        } elseif (!empty($metaArray['features_values']) && count($metaArray['features_values']) == 1 && empty($metaArray['brand'])) {

            foreach($SEOFilterPatternsEntity->find(['category_id'=>$category->id, 'type'=>'feature']) as $p) {
                $key = 'feature'.(!empty($p->feature_id) ? '_'.$p->feature_id : '');
                $seoFilterPatterns[$key] = $p;
            }
            
            reset($metaArray['features_values']);
            $featureId = key($metaArray['features_values']);
            $feature = $featuresEntity->get((int)$featureId);
            
            // Определяем какой шаблон брать, для категории + определенное свойство, или категории и любое свойство
            if (isset($seoFilterPatterns['feature_'.$feature->id])) {
                $seoFilterPattern = $seoFilterPatterns['feature_'.$feature->id];
            } elseif(isset($seoFilterPatterns['feature'])) {
                $seoFilterPattern = $seoFilterPatterns['feature'];
            }

            $parts['{$feature_name}'] = $feature->name;
            $parts['{$feature_val}'] = implode(', ', reset($metaArray['features_values']));
        }
        
        if (!empty($seoFilterPattern)) {
            $seoFilterPattern->h1 = strtr($seoFilterPattern->h1, $parts);
            $seoFilterPattern->title = strtr($seoFilterPattern->title, $parts);
            $seoFilterPattern->keywords = strtr($seoFilterPattern->keywords, $parts);
            $seoFilterPattern->meta_description = strtr($seoFilterPattern->meta_description, $parts);
            $seoFilterPattern->description = strtr($seoFilterPattern->description, $parts);

            $seoFilterPattern->h1 = preg_replace('/{\$[^$]*}/', '', $seoFilterPattern->h1);
            $seoFilterPattern->title = preg_replace('/{\$[^$]*}/', '', $seoFilterPattern->title);
            $seoFilterPattern->keywords = preg_replace('/{\$[^$]*}/', '', $seoFilterPattern->keywords);
            $seoFilterPattern->meta_description = preg_replace('/{\$[^$]*}/', '', $seoFilterPattern->meta_description);
            $seoFilterPattern->description = preg_replace('/{\$[^$]*}/', '', $seoFilterPattern->description);
        }

        $this->design->assign('seo_filter_pattern', $seoFilterPattern);
        
        $filterAutoMeta = $filterLogic->getFilterAutoMeta($filtersUrl);
        $this->design->assign('filter_meta', $filterAutoMeta);

        $this->design->assign('set_canonical', $filterLogic->isSetCanonical($filtersUrl));

        // Устанавливаем мета-теги в зависимости от запроса
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        } elseif (isset($category)) {
            $this->design->assign('meta_title', $category->meta_title);
            $this->design->assign('meta_keywords', $category->meta_keywords);
            $this->design->assign('meta_description', $category->meta_description);
        }

        $relPrevNext = $this->design->fetch('products_rel_prev_next.tpl');
        $this->design->assign('rel_prev_next', $relPrevNext);
        $this->design->assign('sort_canonical', $filterLogic->getSortCanonical());

        $this->response->setContent($this->design->fetch('products.tpl'));
    }
}
