<?php


namespace Okay\Controllers;


use Okay\Entities\BrandsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Helpers\CatalogHelper;
use Okay\Helpers\FilterHelper;
use Okay\Helpers\MetadataHelpers\CategoryMetadataHelper;
use Okay\Helpers\ProductsHelper;

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
        CatalogHelper $catalogHelper,
        ProductsHelper $productsHelper,
        FilterHelper $filterHelper,
        ProductsEntity $productsEntity,
        CategoryMetadataHelper $categoryMetadataHelper,
        $url,
        $filtersUrl = ''
    ) {
        $filter['visible'] = 1;
        $sortProducts = null;

        $filterHelper->setFiltersUrl($filtersUrl);
        
        $this->setMetadataHelper($categoryMetadataHelper);
        
        $category = $categoriesEntity->get((string)$url);
        if (empty($category) || (!$category->visible && empty($_SESSION['admin']))) {
            return false;
        }
        $this->design->assign('category', $category);
        $filter['category_id'] = $category->children;

        $filterHelper->setCategory($category);
        $this->categoryFeatures = $filterHelper->getCategoryFeatures();
        
        if (($currentBrandsIds = $filterHelper->getCurrentBrands($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentOtherFilters = $filterHelper->getCurrentOtherFilters($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentPage = $filterHelper->getCurrentPage($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentFeatures = $filterHelper->getCurrentCategoryFeatures($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentSort = $filterHelper->getCurrentSort($filtersUrl)) === false) {
            return false;
        }

        $filterHelper->changeLangUrls($filtersUrl);

        // Если задан бренд, выберем его из базы
        if (!empty($currentBrandsIds)) {
            $filter['brand_id'] = $currentBrandsIds;
            $this->design->assign('selected_brands_ids', $currentBrandsIds);
        }

        if (!empty($currentOtherFilters)) {
            $filter['other_filter'] = $currentOtherFilters;
            $this->design->assign('selected_other_filters', $currentOtherFilters);
        }

        $filter['price'] = $catalogHelper->getPriceFilter($this->catalogType, $category->id);

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
        $this->categoryBrands = $brandsEntity->mappedBy('id')->find($brandsFilter);
         
        $metaArray = $filterHelper->getMetaArray();
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
        }
        $this->design->assign('is_filter_page', $this->isFilterPage);

        $brandsFilter = $filterHelper->prepareFilterGetCategoryBrands($category, $filter);
        if ($brands = $filterHelper->getCategoryBrands($brandsFilter, $currentBrandsIds)) {
            $category->brands = $brands;
        }

        // Дополняем список брендов, теми, которые выбраны в данный момент, но их фильтрация отсекла
        if ($this->isFilterPage === true && !empty($this->categoryBrands) && !empty($currentBrandsIds)) {
            foreach ($currentBrandsIds as $brandId) {
                if (isset($this->categoryBrands[$brandId]) && !isset($category->brands[$brandId])) {
                    $category->brands[$brandId] = $this->categoryBrands[$brandId];
                }
            }
        }
        
        /**
         * Получаем значения свойств для категории, чтобы на страницах фильтров убрать фильтры
         * у которых изначально был только один вариант выбора
         */
        $baseFeaturesValues = [];
        if ($this->isFilterPage === true) {
            $baseFeaturesValues = $filterHelper->getCategoryBaseFeaturesValues($category, $this->settings->get('missing_products'));

            // Дополняем массив categoryFeatures значениями, которые в данный момент выбраны
            // и были изначально, но их фильтрация (по бренду или цене) отсекла.
            if (!empty($baseFeaturesValues)) {
                foreach ($baseFeaturesValues as $values) {
                    foreach ($values as $value) {
                        if (isset($currentFeatures[$value->feature_id][$value->id]) && isset($this->categoryFeatures[$value->feature_id])) {
                            $this->categoryFeatures[$value->feature_id]->features_values[$value->id] = $value;
                        }
                    }
                }
            }
        }
        
        // Достаём значения свойств текущей категории
        $featuresValuesFilter = $filterHelper->prepareFilterGetFeaturesValues($category, $this->settings->get('missing_products'), $filter);
        foreach ($filterHelper->getCategoryFeaturesValues($featuresValuesFilter) as $featureValue) {
            if (isset($this->categoryFeatures[$featureValue->feature_id])) {
                $filterHelper->setCategoryFeatureValue($featureValue);
                $this->categoryFeatures[$featureValue->feature_id]->features_values[$featureValue->id] = $featureValue;
            }
        }
        
        if (!empty($this->categoryFeatures)) {
            foreach ($this->categoryFeatures as $i => $feature) {
                // Если хоть одно значение свойства выбранно, его убирать нельзя
                if (empty($currentFeatures[$feature->id])) {
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
        $this->design->assign('selected_filters', $currentFeatures);

        $this->design->assign('other_filters', $catalogHelper->getOtherFilters($filter));
        
        $prices = $catalogHelper->getPrices($filter, $this->catalogType, $category->id);
        $this->design->assign('prices', $prices);

        $filter = $filterHelper->getCategoryProductsFilter($filter);
        
        $paginate = $catalogHelper->paginate(
            $this->settings->get('products_num'),
            $currentPage,
            $filter,
            $this->design
        );
        
        if (!$paginate) {
            return false;
        }

        // Товары
        $products = $productsHelper->getProductList($filter, $sortProducts);
        $this->design->assign('products', $products);
        
        if ($this->request->get('ajax','boolean')) {
            $this->design->assign('ajax', 1);
            $result = $catalogHelper->getAjaxFilterData($this->design);
            $this->response->setContent(json_encode($result), RESPONSE_JSON);
            return true;
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
        
        $this->design->assign('set_canonical', true);
        
        $relPrevNext = $this->design->fetch('products_rel_prev_next.tpl');
        $this->design->assign('rel_prev_next', $relPrevNext);
        $this->design->assign('sort_canonical', $filterHelper->getSortCanonical());

        $this->response->setContent('products.tpl');
    }
}
