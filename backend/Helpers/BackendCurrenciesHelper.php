<?php


namespace Okay\Admin\Helpers;


use Okay\Core\EntityFactory;
use Okay\Entities\CurrenciesEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendCurrenciesHelper
{
    private $currenciesEntity;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->currenciesEntity = $entityFactory->get(CurrenciesEntity::class);
    }

    public function findAllCurrencies()
    {
        $currencies = $this->currenciesEntity->mappedBy('id')->find();
        return ExtenderFacade::execute(__METHOD__, $currencies, func_get_args());
    }
}