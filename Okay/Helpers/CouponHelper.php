<?php


namespace Okay\Helpers;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\CouponsEntity;

class CouponHelper
{
    /**
     * @var EntityFactory
     */
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function registerUseIfExists($coupon)
    {
        if (!empty($coupon)) {
            $couponsEntity = $this->entityFactory->get(CouponsEntity::class);

            $couponsEntity->update($coupon->id, [
                'usages' => $coupon->usages+1
            ]);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
}