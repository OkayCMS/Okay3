<?php


namespace Okay\Controllers;


use Okay\Core\Image;
use Okay\Core\Money;
use Okay\Core\Router;
use Okay\Entities\ProductsEntity;
use Okay\Logic\CatalogLogic;
use Okay\Logic\FilterLogic;
use Okay\Logic\ProductsLogic;

class ProductsController extends AbstractController
{

    private $catalogType;

    public function render(
        CatalogLogic $catalogLogic,
        ProductsLogic $productsLogic,
        ProductsEntity $productsEntity,
        FilterLogic $filterLogic,
        Router $router,
        $filtersUrl = ''
    ) {
        
        $this->catalogType = $router->getCurrentRouteName();
        
        switch ($this->catalogType) {
            case 'bestsellers':
                $filter['featured'] = true;
                break;
            case 'discounted':
                $filter['discounted'] = true;
                break;
            case 'search':
                // Если задано ключевое слово
                $keyword = $this->request->get('keyword');
                if (!empty($keyword)) {
                    $this->design->assign('keyword', $keyword);
                    $filter['keyword'] = $keyword;
                }
                break;
        }
        
        $filterLogic->setFiltersUrl($filtersUrl);

        $sortProducts = null;
        $filter['visible'] = 1;

        // Если нашли фильтр по бренду, кидаем 404
        if (($currentBrandsIds = $filterLogic->getCurrentBrands($filtersUrl)) === false || !empty($currentBrandsIds)) {
            return false;
        }

        // Если нашли фильтр по свойствам, кидаем 404
        if (($currentFeatures = $filterLogic->getCurrentCategoryFeatures($filtersUrl)) === false || !empty($currentFeatures)) {
            return false;
        }
        
        // данный фильтр может быть применен только на странице search (all-products)
        if (($currentOtherFilters = $filterLogic->getCurrentOtherFilters($filtersUrl)) === false
            || $this->catalogType != 'search' && !empty($currentOtherFilters)) {
            return false;
        }

        if (($currentPage = $filterLogic->getCurrentPage($filtersUrl)) === false) {
            return false;
        }
        
        if (($currentSort = $filterLogic->getCurrentSort($filtersUrl)) === false) {
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
        
        $filter['price'] = $catalogLogic->getPriceFilter($this->catalogType);
        
        if ($this->catalogType == 'search') {
            $this->design->assign('other_filters', $catalogLogic->getOtherFilters($filter));
        }

        if ((!empty($filter['price']) && $filter['price']['min'] !== '' && $filter['price']['max'] !== '' && $filter['price']['min'] !== null) || !empty($filter['other_filter'])) {
            $this->design->assign('is_filter_page', true);
        }
        
        $prices = $catalogLogic->getPrices($filter, $this->catalogType);
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
        
        // Устанавливаем мета-теги в зависимости от запроса
        if ($this->page) {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        } elseif (isset($keyword)) {
            $this->design->assign('meta_title', $keyword);
        }

        $relPrevNext = $this->design->fetch('products_rel_prev_next.tpl');
        $this->design->assign('rel_prev_next', $relPrevNext);
        $this->design->assign('sort_canonical', $filterLogic->getSortCanonical());
        
        $this->response->setContent($this->design->fetch('products.tpl'));
    }
    
    public function ajaxSearch(ProductsLogic $productsLogic, Image $image, Money $money, Router $router)
    {

        $filter['keyword'] = $this->request->get('query', 'string');
        $filter['visible'] = true;
        $filter['limit'] = 10;

        $products = $productsLogic->getProductList($filter, 'name');

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
