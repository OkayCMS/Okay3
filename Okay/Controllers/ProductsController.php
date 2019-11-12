<?php


namespace Okay\Controllers;


use Okay\Core\Image;
use Okay\Core\Money;
use Okay\Core\Request;
use Okay\Core\Router;
use Okay\Entities\ProductsEntity;
use Okay\Helpers\CatalogHelper;
use Okay\Helpers\FilterHelper;
use Okay\Helpers\MetadataHelpers\AllProductsMetadataHelper;
use Okay\Helpers\MetadataHelpers\BestsellersMetadataHelper;
use Okay\Helpers\MetadataHelpers\DiscountedMetadataHelper;
use Okay\Helpers\ProductsHelper;

class ProductsController extends AbstractController
{

    private $catalogType;

    public function render(
        CatalogHelper $catalogHelper,
        ProductsHelper $productsHelper,
        ProductsEntity $productsEntity,
        FilterHelper $filterHelper,
        Router $router,
        DiscountedMetadataHelper $discountedMetadataHelper,
        BestsellersMetadataHelper $bestsellersMetadataHelper,
        AllProductsMetadataHelper $allProductsMetadataHelper,
        $filtersUrl = ''
    ) {
        
        $this->catalogType = $router->getCurrentRouteName();
        
        switch ($this->catalogType) {
            case 'bestsellers':
                $this->setMetadataHelper($bestsellersMetadataHelper);
                break;
            case 'discounted':
                $this->setMetadataHelper($discountedMetadataHelper);
                break;
            case 'search':
                $this->setMetadataHelper($allProductsMetadataHelper);
                break;
        }
        
        $filterHelper->setFiltersUrl($filtersUrl);

        $sortProducts = null;
        $filter['visible'] = 1;

        // Если нашли фильтр по бренду, кидаем 404
        if (($currentBrandsIds = $filterHelper->getCurrentBrands($filtersUrl)) === false || !empty($currentBrandsIds)) {
            return false;
        }

        // Если нашли фильтр по свойствам, кидаем 404
        if (($currentFeatures = $filterHelper->getCurrentCategoryFeatures($filtersUrl)) === false || !empty($currentFeatures)) {
            return false;
        }
        
        // данный фильтр может быть применен только на странице search (all-products)
        if (($currentOtherFilters = $filterHelper->getCurrentOtherFilters($filtersUrl)) === false
            || $this->catalogType != 'search' && !empty($currentOtherFilters)) {
            return false;
        }

        if (($currentPage = $filterHelper->getCurrentPage($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentSort = $filterHelper->getCurrentSort($filtersUrl)) === false) {
            return false;
        }

        if (!empty($currentOtherFilters)) {
            $filter['other_filter'] = $currentOtherFilters;
            $this->design->assign('selected_other_filters', $currentOtherFilters);
        }

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
        
        $filter['price'] = $catalogHelper->getPriceFilter($this->catalogType);
        
        if ($this->catalogType == 'search') {
            $this->design->assign('other_filters', $catalogHelper->getOtherFilters($filter));
        }

        if ((!empty($filter['price']) && $filter['price']['min'] !== '' && $filter['price']['max'] !== '' && $filter['price']['min'] !== null) || !empty($filter['other_filter'])) {
            $this->design->assign('is_filter_page', true);
        }
        
        $prices = $catalogHelper->getPrices($filter, $this->catalogType);
        $this->design->assign('prices', $prices);
        
        switch ($this->catalogType) {
            case 'bestsellers':
                $filter = $filterHelper->getFeaturedProductsFilter($filter);
                break;
            case 'discounted':
                $filter = $filterHelper->getDiscountedProductsFilter($filter);
                break;
            case 'search':
                // Если задано ключевое слово
                $keyword = $this->request->get('keyword');
                $filter = $filterHelper->getSearchProductsFilter($filter, $keyword);
                if (!empty($keyword)) {
                    $this->design->assign('keyword', $keyword);
                }
                break;
        }
        
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
        $lastModifyFilter = ['limit' => 1];
        switch ($this->catalogType) {
            case 'bestsellers':
                $lastModifyFilter['featured'] = true;
                break;
            case 'discounted':
                $lastModifyFilter['discounted'] = true;
                break;
        }
        $lastModify = $productsEntity->cols(['last_modify'])
            ->order('last_modify_desc')
            ->find($lastModifyFilter);
        if ($this->page) {
            $lastModify[] = $this->page->last_modify;
        }
        $this->response->setHeaderLastModify(max($lastModify));
        //lastModify END

        $relPrevNext = $this->design->fetch('products_rel_prev_next.tpl');
        $this->design->assign('rel_prev_next', $relPrevNext);
        $this->design->assign('sort_canonical', $filterHelper->getSortCanonical());
        
        $this->response->setContent('products.tpl');
    }
    
    public function ajaxSearch(ProductsHelper $productsHelper, Image $image, Money $money, Router $router)
    {

        $filter['keyword'] = $this->request->get('query', 'string');
        $filter['visible'] = true;
        $filter['limit'] = 10;

        $products = $productsHelper->getProductList($filter, 'name');

        $suggestions = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $suggestion = new \stdClass();
                if (isset($product->image)) {
                    $product->image = $image->getResizeModifier($product->image->filename, 35, 35);
                }

                $product->url = $router->generateUrl('product', ['url' => $product->url]);

                $suggestion->price = $money->convert($product->variant->price);
                $suggestion->currency = $this->currency->sign;
                $suggestion->value = $product->name;
                $suggestion->data = $product;
                $suggestions[] = $suggestion;
            }
        }

        $res = new \stdClass;
        $res->query = $filter['keyword'];
        $res->suggestions = $suggestions;

        $this->response->setContent(json_encode($res), RESPONSE_JSON);
    }

}
