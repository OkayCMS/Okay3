<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\EntityFactory;
use Okay\Entities\ProductsEntity;
use Okay\Helpers\ProductsHelper;
use Okay\Core\SmartyPlugins\Func;

class GetBrowsedProducts extends Func
{

    protected $tag = 'get_browsed_products';
    
    /**
     * @var ProductsEntity
     */
    private $productsEntity;
    
    /**
     * @var ProductsHelper
     */
    private $productsHelper;

    
    public function __construct(EntityFactory $entityFactory, ProductsHelper $productsHelper)
    {
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->productsHelper = $productsHelper;
    }

    public function run($params, \Smarty_Internal_Template $smarty)
    {
        $browsedProductsIds = explode(',', $_COOKIE['browsed_products']);
        $browsedProductsIds = array_reverse($browsedProductsIds);

        if(isset($params['limit'])) {
            $browsedProductsIds = array_slice($browsedProductsIds, 0, $params['limit']);
        }

        $products = $this->productsHelper->getList(['id' => $browsedProductsIds]);

        $browsedProducts = [];
        foreach($browsedProductsIds as  $browsedProductId) {
            if (!empty($products[$browsedProductId])) {
                $browsedProducts[$browsedProductId] = $products[$browsedProductId];
            }
        }

        $smarty->assign($params['var'], $browsedProducts);
    }
}