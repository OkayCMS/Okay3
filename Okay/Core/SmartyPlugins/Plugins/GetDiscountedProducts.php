<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\EntityFactory;
use Okay\Entities\ProductsEntity;
use Okay\Logic\ProductsLogic;
use Okay\Core\SmartyPlugins\Func;

class GetDiscountedProducts extends Func
{

    protected $tag = 'get_discounted_products';
    
    /**
     * @var ProductsEntity
     */
    private $productsEntity;
    
    /**
     * @var ProductsLogic
     */
    private $productsLogic;

    
    public function __construct(EntityFactory $entityFactory, ProductsLogic $productsLogic)
    {
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->productsLogic = $productsLogic;
    }

    public function run($params, \Smarty_Internal_Template $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        $params['in_stock'] = 1;
        $params['discounted'] = 1;
        if (!empty($params['var'])) {
            $products = $this->productsLogic->getProductList($params);
            $smarty->assign($params['var'], $products);
        }
    }
}