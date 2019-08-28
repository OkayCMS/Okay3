<?php


namespace Okay\Controllers;


use Okay\Core\Database;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Logic\ProductsLogic;

class FeedController extends AbstractController
{
    
    public function render(
        ProductsEntity $productsEntity,
        ProductsLogic $productsLogic,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        Database $db
    ) {

        if (!empty($this->currencies)) {
            $this->design->assign('main_currency', reset($this->currencies));
        }
        
        $this->design->set_templates_dir('xml');
        $this->design->set_compiled_dir('xml/compiled');
        
        $this->response->addHeader('Content-type: text/xml; charset=UTF-8');
        print (pack('CCC', 0xef, 0xbb, 0xbf));

        $db->customQuery('SET SQL_BIG_SELECTS=1');
        $filter['visible'] = 1;
        $filter['feed'] = 1;
        
        if (!$this->settings->yandex_export_not_in_stock) {
            $filter['in_stock'] = 1;
        }
        
        if ($this->settings->yandex_no_export_without_price) {
            $filter['has_price'] = 1;
        }
        
        $products = $productsEntity->cols([
            'description',
            'name',
            'id',
            'brand_id',
            'url',
            'annotation',
            'main_category_id',
        ])
            ->mappedBy('id')
            ->find($filter);

        if (!empty($products)) {
            $products = $productsLogic->attachVariants($products, $filter);
            $products = $productsLogic->attachImages($products);
            $products = $productsLogic->attachFeatures($products, ['feed'=>1]);
            
            $this->design->assign('products', $products);
            
            $allBrands = $brandsEntity->mappedBy('id')->find(['product_id' => array_keys($products)]);
            $this->design->assign('all_brands', $allBrands);
        }
        
        $this->design->assign('all_categories', $categoriesEntity->find());
        
        $this->response->setContent($this->design->fetch('feed.xml.tpl'), RESPONSE_XML);
        
    }
    
}
