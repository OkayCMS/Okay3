<?php


namespace Okay\Logic;


use Okay\Core\Money as MoneyCore;// TODO: по какой-то причине вылаеет ошибка если не использовать алиас Fatal error: Cannot use Okay\Core\Money as Money because the name is already in use in C:\OpenServer\OSPanel\domains\okaycms3\Logic\Catalog.php on line 7
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Entities\TranslationsEntity;
use Okay\Entities\ProductsEntity;

class CatalogLogic
{

    private $money;
    private $entityFactory;
    private $otherFilters = [
        'discounted',
        'featured',
    ];

    public function __construct(EntityFactory $entityFactory, MoneyCore $money)
    {
        $this->entityFactory = $entityFactory;
        $this->money = $money;
    }
    
    public function getPriceFilter($catalogType, $objectId = null)
    {
        $resultPrice = [];
        $priceFilter = $this->getPriceFromStorage($catalogType, $objectId);
        
        $currentPrices = [];
        if (isset($_GET['p'])) {
            $currentPrices = $_GET['p']; //todo принимать через Request
            if (isset($currentPrices['min'])) {
                $currentPrices['min'] = $this->money->convert($currentPrices['min'], null, false, true);
            }

            if (isset($currentPrices['max'])) {
                $currentPrices['max'] = $this->money->convert($currentPrices['max'], null, false, true);
            }
        }

        if (isset($currentPrices['min']) && isset($currentPrices['max']) && $currentPrices['max'] !== '' && $currentPrices['min'] !== '' && $currentPrices['min'] !== null) {
            $resultPrice = $currentPrices;
        }

        if (empty($resultPrice) && $priceFilter['price_range']['min'] !== '' && $priceFilter['price_range']['max'] !== '' && $priceFilter['price_range']['min'] !== null) {
            $resultPrice = $priceFilter['price_range'];
        }
        
        return $resultPrice;
    }
    
    public function getPrices(array &$filter, $catalogType, $objectId = null)
    {
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);

        $priceFilter = $this->getPriceFromStorage($catalogType, $objectId);

        $prices = [];
        if (isset($_GET['p'])) {
            $prices['current'] = $_GET['p']; //todo принимать через Request

            if (isset($prices['current']['min'])) {
                $prices['current']['min'] = $this->money->convert($prices['current']['min'], null, false, true);
            }

            if (isset($prices['current']['max'])) {
                $prices['current']['max'] = $this->money->convert($prices['current']['max'], null, false, true);
            }
        }
        if (isset($prices['current']['min']) && isset($prices['current']['max']) && $prices['current']['max'] !== '' && $prices['current']['min'] !== '' && $prices['current']['min'] !== null) {
            $filterPrice = $prices['current'];
        } else {
            unset($prices['current']);
        }
        
        // Если прилетела фильтрация по цене, запомним её
        if (!empty($filterPrice)) {
            $priceFilter['price_range'] = $filterPrice;
            // Если в куках есть сохраненный фильтр по цене, применяем его
        } elseif ($priceFilter['price_range']['min'] !== '' && $priceFilter['price_range']['max'] !== '' && $priceFilter['price_range']['min'] !== null) {
            $prices['current'] = $priceFilter['price_range'];
        }

        if (!empty($filter['price']['min'])) {
            $filter['price']['min'] = round($this->money->convert($filter['price']['min'], null, false));
        }

        if (!empty($filter['price']['max'])) {
            $filter['price']['max'] = round($this->money->convert($filter['price']['max'], null, false));
        }

        if (isset($prices['current'])) {
            $prices['current'] = (object)$prices['current'];
        }
        $prices = (object)$prices;
        
        $rangeFilter = $filter;
        unset($rangeFilter['price']);
        $prices->range = $productsEntity->getPriceRange($rangeFilter);

        if (isset($prices->current->min)) {
            $prices->current->min = round($this->money->convert($prices->current->min, null, false));
        }
        if (isset($prices->current->max)) {
            $prices->current->max = round($this->money->convert($prices->current->max, null, false));
        }
        
        // Вдруг вылезли за диапазон доступного...
        if (isset($prices->current->min) && $prices->range->min !== '' && $prices->current->min < $prices->range->min) {
            $prices->current->min = $filter['price']['min'] = $prices->range->min;
        }
        if (isset($prices->current->max) && $prices->range->max !== '' && $prices->current->max > $prices->range->max) {
            $prices->current->max = $filter['price']['max'] = $prices->range->max;
        }

        // Сохраняем фильтр в куки
        setcookie("price_filter", json_encode($priceFilter), time()+3600*24*1, "/");
        
        return $prices;
    }
    
    public function getOtherFilters(array $filter)
    {
        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);
        
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        
        $translations = $translationsEntity->find(['lang' => 'ru']); //todo languages

        $otherFilters = [];
        foreach ($this->otherFilters as $f) {
            $label = 'features_filter_'.$f;
            $item = (object)[
                'url' => $f,
                'name' => $translations->{$label},
                'translation' => $label,
            ];
            if (empty($filter['other_filter']) || !in_array($f, $filter['other_filter'])) {
                $tmFilter = $filter;
                $tmFilter['other_filter'] = [$f];
                $cnt = $productsEntity->count($tmFilter);
                if ($cnt > 0) {
                    $otherFilters[] = $item;
                }
            } else {
                $otherFilters[] = $item;
            }
        }
        return $otherFilters;
    }
    
    public function paginate($itemsPerPage, $currentPage, array &$filter, Design $design)
    {

        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        
        // Вычисляем количество страниц
        $productsCount = $productsEntity->count($filter);

        // Показать все страницы сразу
        $allPages = false;
        if ($currentPage == 'all') {
            $allPages = true;
            $itemsPerPage = $productsCount;
        }

        // Если не задана, то равна 1
        $currentPage = max(1, (int)$currentPage);
        $design->assign('current_page_num', $currentPage);
        
        $pagesNum = !empty($itemsPerPage) ? ceil($productsCount/$itemsPerPage) : 0;
        $design->assign('total_pages_num', $pagesNum);
        $design->assign('total_products_num', $productsCount);

        $filter['page'] = $currentPage;
        $filter['limit'] = $itemsPerPage;

        if ($allPages === false && $currentPage > 1 && $currentPage > $pagesNum) {
            return false;
        }
        return true;
    }

    private function getPriceFromStorage($catalogType, $objectId = null)
    {
        $priceFilter = $this->resetPriceFilter();
        if (isset($_COOKIE['price_filter'])) {
            $priceFilter = json_decode($_COOKIE['price_filter'], true);
        }

        // Когда перешли на другой тип каталога, забываем диапазон цен
        if ($priceFilter['catalog_type'] != $catalogType) {
            $priceFilter = $this->resetPriceFilter();
            $priceFilter['catalog_type'] = $catalogType;
        }

        if ($priceFilter['catalog_type'] !== null) {
            switch ($catalogType) {
                case 'category':
                    if ($priceFilter['category_id'] != $objectId) {
                        $priceFilter = $this->resetPriceFilter();
                        $priceFilter['category_id'] = $objectId;
                        $priceFilter['catalog_type'] = $catalogType;
                    }
                    break;
                case 'brand':
                    if ($priceFilter['brand_id'] != $objectId) {
                        $priceFilter = $this->resetPriceFilter();
                        $priceFilter['brand_id'] = $objectId;
                        $priceFilter['catalog_type'] = $catalogType;
                    }
                    break;
            }
        }

        return $priceFilter;
    }
    
    private function resetPriceFilter() {
        return [
            'category_id'   => null,
            'brand_id'      => null,
            'catalog_type'  => null,
            'price_range'   => [
                'min' => null,
                'max' => null,
            ]
        ];
    }
    
}