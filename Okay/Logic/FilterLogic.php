<?php


namespace Okay\Logic;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Router;
use Okay\Core\Settings;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\BrandsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Entities\TranslationsEntity;

class FilterLogic
{

    private $entityFactory;
    private $request;
    private $router;
    private $design;
    
    private $categoryFeatures = null;
    private $categoryFeaturesByUrl;
    private $featuresUrls;

    private $maxFilterBrands;
    private $maxFilterFilter;
    private $maxFilterFeaturesValues;
    private $maxFilterFeatures;
    private $maxFilterDepth;

    private $category;
    private $language;
    private $filtersUrl;

    private $currentBrands;
    private $otherFilters = [
        'discounted',
        'featured',
    ];

    private $metaDelimiter = ', ';

    private $featureValuesCache = [];

    public function __construct(
        EntityFactory $entityFactory,
        Settings $settings,
        Languages $languages,
        Request $request,
        Router $router,
        Design $design
    ) {
        $this->entityFactory = $entityFactory;
        $this->request = $request;
        $this->router = $router;
        $this->design = $design;

        /** @var LanguagesEntity $languagesEntity */
        $languagesEntity = $entityFactory->get(LanguagesEntity::class);
        $this->language = $languagesEntity->get($languages->getLangId());

        $this->maxFilterBrands = $settings->max_filter_brands;
        $this->maxFilterFilter = $settings->max_filter_filter;
        $this->maxFilterFeaturesValues = $settings->max_filter_features_values;
        $this->maxFilterFeatures = $settings->max_filter_features;
        $this->maxFilterDepth = $settings->max_filter_depth;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function setFiltersUrl($filtersUrl)
    {
        $this->filtersUrl = $filtersUrl;
    }

    public function setCategoryFeatureValue($featureValue)
    {
        if ($this->categoryFeatures === null) {
            $this->getCategoryFeatures();
        }
        
        if (!isset($this->categoryFeatures[$featureValue->feature_id]->values[$featureValue->id])) {
            $this->categoryFeatures[$featureValue->feature_id]->values[$featureValue->id] = $featureValue;
            $this->categoryFeatures[$featureValue->feature_id]->values_ids[$featureValue->translit] = $featureValue->id;
        }
    }
    
    public function getCategoryFeatures()
    {
        if (!empty($this->categoryFeatures)) {
            return $this->categoryFeatures;
        }
        /** @var FeaturesEntity $featuresEntity */
        $featuresEntity = $this->entityFactory->get(FeaturesEntity::class);

        if (!empty($this->category) && empty($this->categoryFeatures)) {
            foreach ($featuresEntity->find(['category_id' => $this->category->id, 'in_filter' => 1]) as $feature) {
                $this->categoryFeatures[$feature->id] = $feature;
                $this->categoryFeaturesByUrl[$feature->url] = $feature;
                $this->featuresUrls[$feature->id] = $feature->url;
            }
        }

        return $this->categoryFeatures;
    }

    public function getCurrentPage($filtersUrl)
    {
        $currentPage = '';
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            if ($paramName == 'page') {
                $currentPage = (string)$paramValues;
                if ($paramValues != 'all' && (!preg_match('~^[0-9]+$~', $paramValues) || strpos($paramValues, '0') === 0)) {
                    return false;
                }

            }
        }
        return $currentPage;
    }

    public function getCurrentSort($filtersUrl)
    {
        $currentSort = '';
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            if ($paramName == 'sort') {
                $currentSort = (string)$paramValues;
                if (!in_array($currentSort, ['position', 'price', 'price_desc', 'name', 'name_desc', 'rating', 'rating_desc'])) {
                    return false;
                }
            }
        }
        return $currentSort;
    }

    public function getCurrentOtherFilters($filtersUrl)
    {
        $otherFilter = [];
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            if ($paramName == 'filter') {
                foreach (explode('_', $paramValues) as $f) {
                    if (!in_array($f, $otherFilter) && in_array($f, $this->otherFilters)) {
                        $otherFilter[] = $f;
                    } else {
                        return false;
                    }
                }
            }
        }
        return $otherFilter;
    }

    public function getCurrentBrands($filtersUrl)
    {
        $currentBrands = [];
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            if ($paramName == 'brand') {
                foreach (explode('_', $paramValues) as $bv) {
                    if (($brand = $this->getBrand((string)$bv)) && !in_array($brand->id, $currentBrands)) {
                        $currentBrands[] = $brand->id;
                    } else {
                        return false;
                    }
                }
            }
        }
        return $currentBrands;
    }

    public function getCurrentCategoryFeatures($filtersUrl)
    {
        if ($this->categoryFeatures === null) {
            $this->getCategoryFeatures();
        }
        
        $currentFeatures = [];
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            if (!in_array($paramName, ['brand', 'filter', 'page', 'sort'])) {
                if (isset($this->categoryFeaturesByUrl[$paramName])
                    && ($feature = $this->categoryFeaturesByUrl[$paramName])
                    && !isset($selectedFeatures[$feature->id])) {
                    $selectedFeatures[$feature->id] = explode('_', $paramValues);
                } else {
                    return false;
                }
            }
        }

        if (!empty($selectedFeatures)) {
            $valuesIds = [];
            if (!empty($this->categoryFeatures)) {
                // Выше мы определили какие значения каких свойств выбраны, теперь достаем эти значения из базы, чтобы за один раз
                foreach ($this->getFeaturesValues(['selected_features' => $selectedFeatures, 'category_id' => $this->category->children]) as $fv) {
                    $valuesIds[$fv->feature_id][$fv->translit] = $fv->id;
                }
            }

            foreach ($selectedFeatures as $featureId => $values) {
                foreach ($values as $value) {
                    if (isset($valuesIds[$featureId][$value])) {
                        $valueId = $valuesIds[$featureId][$value];
                        $currentFeatures[$featureId][$valueId] = $value;
                    }
                }
                // если нет повторяющихся значений свойства - ок, иначе 404
                if (isset($currentFeatures[$featureId]) && count($currentFeatures[$featureId]) == count(array_unique($currentFeatures[$featureId]))) {
                    foreach ($currentFeatures[$featureId] as $paramValue) {
                        if (!in_array($paramValue, array_keys($valuesIds[$featureId]))) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
        }
        return $currentFeatures;
    }

    public function getMetaArray($filtersUrl)
    {
        /** @var Translations $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);
        $translations = $translationsEntity->find(['lang' => $this->language->label]);
        
        if ($this->categoryFeatures === null) {
            $this->getCategoryFeatures();
        }
        
        $metaArray = [];
        //определение текущего положения и выставленных параметров
        $uriArray = $this->parseFilterUrl($filtersUrl);
        foreach ($uriArray as $k => $v) {
            if (empty($v)) {
                continue;
            }
            @list($paramName, $paramValues) = explode('-', $v);

            switch ($paramName) {
                case 'brand':
                {
                    foreach (explode('_', $paramValues) as $bv) {
                        if (($brand = $this->getBrand($bv)) && empty($metaArray['brand'][$brand->id])) {
                            $metaArray['brand'][$brand->id] = $brand->name;
                        }
                    }
                    break;
                }
                case 'filter':
                {
                    foreach (explode('_', $paramValues) as $f) {
                        if (empty($metaArray['filter'][$f])) {
                            $metaArray['filter'][$f] = $translations->{"features_filter_" . $f};
                        }
                    }
                    break;
                }
                case 'page': // no break
                case 'sort':
                    break;
                default:
                {
                    if (isset($this->categoryFeaturesByUrl[$paramName])
                        && ($feature = $this->categoryFeaturesByUrl[$paramName])
                        && !isset($selectedFeatures[$feature->id])) {

                        $selectedFeatures[$feature->id] = explode('_', $paramValues);
                    }
                }
            }
        }

        if (!empty($selectedFeatures)) {
            $selectedFeaturesValues = [];
            if (!empty($this->categoryFeatures)) {
                // Выше мы определили какие значения каких свойств выбраны, теперь достаем эти значения из базы, чтобы за один раз
                foreach ($this->getFeaturesValues(['selected_features' => $selectedFeatures, 'category_id' => $this->category->children]) as $fv) {
                    $selectedFeaturesValues[$fv->feature_id][$fv->id] = $fv;
                }
            }

            foreach ($selectedFeatures as $featureId => $values) {
                if (isset($selectedFeaturesValues[$featureId])) {
                    foreach ($selectedFeaturesValues[$featureId] as $fv) {
                        if (in_array($fv->translit, $values, true)) {
                            $metaArray['features_values'][$featureId][$fv->id] = $fv->value;
                        }
                    }
                }
            }
        }

        return $metaArray;
    }
    
    public function isSetCanonical($filtersUrl)
    {
        $setCanonical = false;
        $metaArray = $this->getMetaArray($filtersUrl);
        $categoryFeatures = $this->getCategoryFeatures();
        foreach ($metaArray as $type => $_metaArray) {
            switch ($type) {
                case 'brand':
                {
                    if (count($_metaArray) > $this->maxFilterBrands) {
                        $setCanonical = true;
                    }
                    break;
                }
                case 'filter':
                {
                    if (count($_metaArray) > $this->maxFilterFilter) {
                        $setCanonical = true;
                    }
                    break;
                }
                case 'features_values':
                {
                    foreach ($_metaArray as $fId => $fValues) {
                        if (count($fValues) > $this->maxFilterFeaturesValues || count($_metaArray) > $this->maxFilterFeatures) {
                            $setCanonical = true;
                        }
                        
                        // Если хоть одно значение в фильтре отмечено как "не индексировать" 
                        foreach ($fValues as $valueId => $fValue) {
                            if (isset($categoryFeatures[$fId]->values[$valueId]) && !$categoryFeatures[$fId]->values[$valueId]->to_index) {
                                $setCanonical = true;
                            }
                        }
                    }
                    break;
                }
            }
        }

        if (count($metaArray) > $this->maxFilterDepth) {
            $setCanonical = true;
        }
        return $setCanonical;
    }
    
    public function getSortCanonical()
    {
        $routeParams = $this->router->getCurrentRouteRequiredParams();
        $baseUrl = $this->router->generateUrl($this->router->getCurrentRouteName(), $routeParams, true);
        $chpuUrl = $this->filterChpuUrl(['sort'=>null]);
        $baseUrl = trim($baseUrl, '/');
        $chpuUrl = trim($chpuUrl, '/');
        return $baseUrl . (!empty($chpuUrl) ? '/' . $chpuUrl : '');
    }
    
    public function getFilterAutoMeta($filtersUrl)
    {
        $autoMeta = [
            'h1' => '',
            'title' => '',
            'keywords' => '',
            'description' => '',
        ];
        if ($this->isSetCanonical($filtersUrl) === true) {
            return $autoMeta;
        }
        
        $metaArray = $this->getMetaArray($filtersUrl);
        if (!empty($metaArray)) {
            foreach ($metaArray as $type=>$_meta_array) {
                switch($type) {
                    case 'brand': // no break
                    case 'filter': {
                        $autoMeta['h1'] = $autoMeta['title'] = $autoMeta['keywords'] = $autoMeta['description'] = implode($this->metaDelimiter,$_meta_array);
                        break;
                    }
                    case 'features_values': {
                        foreach($_meta_array as $f_id=>$f_array) {
                            $autoMeta['h1']           .= (!empty($autoMeta['h1'])           ? $this->metaDelimiter : '') . implode($this->metaDelimiter,$f_array);
                            $autoMeta['title']        .= (!empty($autoMeta['title'])        ? $this->metaDelimiter : '') . implode($this->metaDelimiter,$f_array);
                            $autoMeta['keywords']     .= (!empty($autoMeta['keywords'])     ? $this->metaDelimiter : '') . implode($this->metaDelimiter,$f_array);
                            $autoMeta['description']  .= (!empty($autoMeta['description'])  ? $this->metaDelimiter : '') . implode($this->metaDelimiter,$f_array);
                        }
                        break;
                    }
                }
            }
        }
        if (!empty($autoMeta['h1'])) {
            $autoMeta['h1'] = ' ' . $autoMeta['h1'];
        }
        if (!empty($autoMeta['title'])) {
            $autoMeta['title'] = ' ' . $autoMeta['title'];
        }
        if (!empty($autoMeta['keywords'])) {
            $autoMeta['keywords'] = ' ' . $autoMeta['keywords'];
        }

        return (object)$autoMeta;
    }
    
    public function changeLangUrls($filtersUrl)
    {

        if ($languages = (array)$this->design->get_var('languages')) {
            /** @var FeaturesValuesEntity $featuresValuesEntity */
            $featuresValuesEntity = $this->entityFactory->get(FeaturesValuesEntity::class);
            
            $routeParams = $this->router->getCurrentRouteRequiredParams();
    
            $currentCategoryFeatures = $this->getCurrentCategoryFeatures($filtersUrl);
            // Достаем выбранные значения свойств для других языков
            $langValuesFilter = [];
            foreach ($currentCategoryFeatures as $featureId=>$values) {
                $langValuesFilter[$featureId] = array_keys($values);
            }
            $langValues = $featuresValuesEntity->getFeaturesValuesAllLang($langValuesFilter);
            
            //  Заменяем url языка с учетом ЧПУ
            foreach ($languages as $l) {
                $furl = ['sort'=>null];
                $featuresAltLang = [];
                // Для каждого значения, выбираем все его варианты на других языках
                foreach ($currentCategoryFeatures as $featureId=>$values) {
                    if (isset($this->featuresUrls[$featureId])) {
                        foreach (array_keys($values) as $fvId) {
                            if (isset($langValues[$l->id][$featureId][$fvId])) {
                                $translit = $langValues[$l->id][$featureId][$fvId]->translit;
                                $featureUrl = $this->featuresUrls[$featureId];
                                $furl[$featureUrl][$fvId] = $translit;
                                $featuresAltLang[$featureId][$fvId] = $translit;
                            }
                        }
                    }
                }

                $baseUrl = $this->router->generateUrl($this->router->getCurrentRouteName(), $routeParams, true, $l->id);
                $baseUrl = trim($baseUrl, '/');
                $chpuUrl = $this->filterChpuUrl($furl, $featuresAltLang);
                $chpuUrl = trim($chpuUrl, '/');
                $l->url = $baseUrl . (!empty($chpuUrl) ? '/' . $chpuUrl : '');
            }
        }
    }
    
    public function filterChpuUrl($params, $featuresAltLang = [])
    {
        if (is_array($params) && is_array(reset($params))) {
            $params = reset($params);
        }

        $resultArray = ['brand'=>[],'features'=>[], 'filter'=>[], 'sort'=>null,'page'=>null];

        $currentFeaturesValues = $this->getCurrentCategoryFeatures($this->filtersUrl);
        $categoryFeatures = $this->getCategoryFeatures();
        $uriArray = $this->parseFilterUrl($this->filtersUrl);
        //Определяем, что у нас уже есть в строке
        if (!empty($this->filtersUrl)) {
            foreach ($uriArray as $k => $v) {
                list($paramName, $paramValues) = explode('-', $v);
                switch ($paramName) {
                    case 'brand':
                    {
                        $resultArray['brand'] = explode('_', $paramValues);
                        break;
                    }
                    case 'filter':
                    {
                        $resultArray['filter'] = explode('_', $paramValues);
                        break;
                    }
                    case 'sort':
                    {
                        $resultArray['sort'] = strval($paramValues);
                        break;
                    }
                    case 'page':
                    {
                        $resultArray['page'] = $paramValues;
                        break;
                    }
                    default:
                    {
                        // Ключем массива должно быть id значения
                        if (!empty($this->featuresUrls)) {
                            $paramValuesArray = [];
                            $featureId = array_search($paramName, $this->featuresUrls);
                            foreach (explode('_', $paramValues) as $valueTranslit) {
                                if ($valueId = array_search($valueTranslit, $currentFeaturesValues[$featureId])) {
                                    if (isset($featuresAltLang[$featureId][$valueId])) {
                                        $valueTranslit = $featuresAltLang[$featureId][$valueId];
                                    }
                                    $paramValuesArray[$valueId] = $valueTranslit;
                                }
                            }
                            $resultArray['features'][$paramName] = $paramValuesArray;
                        }
                    }
                }
            }
        }

        //Определяем переданные параметры для ссылки
        foreach($params as $paramName=>$paramValues) {
            switch($paramName) {
                case 'brand': {
                    if(is_null($paramValues)) {
                        unset($resultArray['brand']);
                    } elseif(in_array($paramValues,$resultArray['brand'])) {
                        unset($resultArray['brand'][array_search($paramValues,$resultArray['brand'])]);
                    } else {
                        $resultArray['brand'][] = $paramValues;
                    }
                    break;
                }
                case 'filter': {
                    if (is_null($paramValues)) {
                        unset($resultArray['filter']);
                    } elseif (in_array($paramValues, $resultArray['filter'])) {
                        unset($resultArray['filter'][array_search($paramValues, $resultArray['filter'])]);
                    } else {
                        $resultArray['filter'][] = $paramValues;
                    }
                    if (empty($resultArray['filter'])) {
                        unset($resultArray['filter']);
                    }
                    break;
                }
                case 'sort':
                    $resultArray['sort'] = strval($paramValues);
                    break;
                case 'page':
                    $resultArray['page'] = $paramValues;
                    break;
                default:
                    if(is_null($paramValues)) {
                        unset($resultArray['features'][$paramName]);
                    } elseif(!empty($resultArray['features']) && in_array($paramName,array_keys($resultArray['features']), true) && in_array($paramValues,$resultArray['features'][$paramName], true)) {
                        unset($resultArray['features'][$paramName][array_search($paramValues,$resultArray['features'][$paramName])]);
                    } else {
                        if (!empty($this->featuresUrls)) {
                            $featureId = array_search($paramName, $this->featuresUrls);
                            
                            if (!empty($categoryFeatures[$featureId]->values)) {
                                $paramValues = (array)$paramValues;
                                foreach ($paramValues as $valueTranslit) {
                                    if (!empty($valueId = $categoryFeatures[$featureId]->values_ids[$valueTranslit])) {
                                        $resultArray['features'][$paramName][$valueId] = $valueTranslit;
                                    }
                                }
                            }
                        }
                    }
                    if(empty($resultArray['features'][$paramName])) {
                        unset($resultArray['features'][$paramName]);
                    }
                    break;
            }
        }

        $resultString = '';

        $filter_params_count = 0;
        $seoHideFilter = false;
        if (!empty($resultArray['brand'])) {
            if (count($resultArray['brand']) > $this->maxFilterBrands) {
                $seoHideFilter = true;
            }
            $filter_params_count ++;
            $brandsString = $this->sortBrands($resultArray['brand']); // - это с сортировкой по брендам
            if (!empty($brandsString)) {
                $resultString .= '/brand-' . implode("_", $brandsString);
            }
        }
        foreach ($resultArray['features'] as $k=>$v) {
            if (count($resultArray['features'][$k]) > $this->maxFilterFeaturesValues || count($resultArray['features']) > $this->maxFilterFeatures) {
                $seoHideFilter = true;
            }
        }
        if (!empty($resultArray['filter'])) {
            if(count($resultArray['filter']) > $this->maxFilterFilter) {
                $seoHideFilter = true;
            }
            $filter_params_count ++;
            $resultString .= '/filter-' . implode("_", $resultArray['filter']);
        }

        if (!empty($resultArray['features'])) {
            $filter_params_count ++;
            $resultString .= $this->sortFeatures($resultArray['features']);
        }

        if ($filter_params_count > $this->maxFilterDepth) {
            $seoHideFilter = true;
        }

        if (!empty($resultArray['sort'])) {
            $resultString .= '/sort-' . $resultArray['sort'];
        }
        if ($resultArray['page'] > 1 || $resultArray['page'] == 'all') {
            $resultString .= '/page-' . $resultArray['page'];
        }
        $keyword = $this->request->get('keyword');
        if (!empty($keyword)) {
            $resultString .= '?keyword='.$keyword;
        }
        $this->design->assign('seo_hide_filter', $seoHideFilter);
        //отдаем сформированную ссылку
        return $resultString;
    }

    private function parseFilterUrl($filtersUrl)
    {
        return explode('/', $filtersUrl);
    }

    private function getBrand($url)
    {
        /** @var Brands $brandsEntity */
        $brandsEntity = $this->entityFactory->get(BrandsEntity::class);

        $url = (string)$url;

        if (isset($this->currentBrands[$url])) {
            return $this->currentBrands[$url];
        }
        $brand = $brandsEntity->get($url);
        return $this->currentBrands[$url] = $brand;
    }

    private function getFeaturesValues(array $filter)
    {
        array_multisort($filter);
        $cacheKey = serialize($filter);

        if (!empty($this->featureValuesCache[$cacheKey])) {
            return $this->featureValuesCache[$cacheKey];
        }

        /** @var FeaturesValuesEntity $featuresValuesEntity */
        $featuresValuesEntity = $this->entityFactory->get(FeaturesValuesEntity::class);
        $featuresValues = $featuresValuesEntity->find($filter);

        $this->featureValuesCache[$cacheKey] = $featuresValues;
        return $featuresValues;
    }
    
    private function sortBrands($brandsUrls = []) // todo проверить
    {
        if (empty($brandsUrls)) {
            return false;
        }
        
        $brandsEntity = $this->entityFactory->get(BrandsEntity::class);
        $sortedBrandsUrls = $brandsEntity->cols(['url'])
            ->order('position')
            ->find(['url' => $brandsUrls]);

        if (empty($sortedBrandsUrls)) {
            return false;
        }

        return $sortedBrandsUrls;
    }

    private function sortFeatures($features = [])
    {
        if (empty($features)) {
            return false;
        }
        $resultString = '';
        foreach ($this->featuresUrls as $furl) {
            if (in_array($furl, array_keys($features), true)) {
                $resultString .= '/'.$furl.'-'.implode('_', $features[$furl]);
            }
        }
        return $resultString;
    }

}