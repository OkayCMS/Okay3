<?php


namespace Okay\Helpers;


use Okay\Core\Modules\Extender\ExtenderFacade;

class NotifyHelper
{
    public function finalEmailOrderAdmin($order)
    {
        ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }
}