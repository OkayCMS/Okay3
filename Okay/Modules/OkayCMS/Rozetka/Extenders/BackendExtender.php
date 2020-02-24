<?php


namespace Okay\Modules\OkayCMS\Rozetka\Extenders;


use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Modules\OkayCMS\Rozetka\Init\Init;

class BackendExtender implements ExtensionInterface
{
    public function parseProductData($product, $itemFromCsv)
    {
        if (isset($itemFromCsv[Init::TO_FEED_FIELD])) {
            $product[Init::TO_FEED_FIELD] = trim($itemFromCsv[Init::TO_FEED_FIELD]);
        }
        return $product;
    }

    public function extendExportColumnsNames($product)
    {
        $product[Init::TO_FEED_FIELD] = Init::TO_FEED_FIELD;
        return $product;
    }
}