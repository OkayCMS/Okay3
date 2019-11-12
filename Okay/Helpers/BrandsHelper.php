<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\BrandsEntity;

class BrandsHelper
{
    
    private $entityFactory;
    
    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function getBrandsFilter(array $filter = [])
    {
        return ExtenderFacade::execute(__METHOD__, $filter, func_get_args());
    }
    
    public function getCurrentSort()
    {
        return ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
    
    public function getBrandsList($filter = [], $sort = null)
    {
        /** @var BrandsEntity $brandsEntity */
        $brandsEntity = $this->entityFactory->get(BrandsEntity::class);

        if ($sort !== null) {
            $brandsEntity->order($sort, $this->getOrderBrandsAdditionalData());
        }
        $brands = $brandsEntity->find($filter);
        
        return ExtenderFacade::execute(__METHOD__, $brands, func_get_args());
    }

    private function getOrderBrandsAdditionalData()
    {
        $orderAdditionalData = [];
        return ExtenderFacade::execute(__METHOD__, $orderAdditionalData, func_get_args());
    }
}