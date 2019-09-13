<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\EntityFactory;
use Okay\Entities\BrandsEntity;
use Okay\Core\SmartyPlugins\Func;

class GetBrands extends Func
{

    protected $tag = 'get_brands';
    
    /**
     * @var BrandsEntity
     */
    private $brands;

    
    public function __construct(EntityFactory $entityFactory)
    {
        $this->brands = $entityFactory->get(BrandsEntity::class);
    }

    public function run($params, \Smarty_Internal_Template $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        
        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->brands->find($params));
        }
    }
}