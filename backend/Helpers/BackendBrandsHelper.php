<?php


namespace Okay\Admin\Helpers;


use Okay\Core\EntityFactory;
use Okay\Entities\BrandsEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendBrandsHelper
{
    private $brandsEntity;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->brandsEntity = $entityFactory->get(BrandsEntity::class);
    }

    public function prepareFindBrands($filter)
    {

    }

    public function findBrands($filter)
    {
        $brands = $this->brandsEntity->mappedBy('id')->find($filter);
        return ExtenderFacade::execute(__METHOD__, $brands, func_get_args());
    }

    public function findAllBrands()
    {
        $brandsCount = $this->brandsEntity->count();
        $allBrands = $this->brandsEntity->mappedBy('id')->find(['limit' => $brandsCount]);
        return ExtenderFacade::execute(__METHOD__, $allBrands, func_get_args());
    }

    public function prepareFilterForProductsAdmin($categoryId)
    {
        $brandsFilter = [];
        if (!empty($categoryId)) {
            $brandsFilter['category_id'] = ['category_id' => $categoryId];
        }

        $brandsCount = $this->brandsEntity->count($brandsFilter);
        $brandsFilter['limit'] = $brandsCount;

        return ExtenderFacade::execute(__METHOD__, $brandsFilter, func_get_args());
    }
}