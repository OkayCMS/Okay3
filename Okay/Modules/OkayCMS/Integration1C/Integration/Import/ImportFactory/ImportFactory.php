<?php


namespace Okay\Modules\OkayCMS\Integration1C\Integration\Import\ImportFactory;


use Okay\Modules\OkayCMS\Integration1C\Integration\AbstractFactory;
use Okay\Modules\OkayCMS\Integration1C\Integration\Import;

class ImportFactory extends AbstractFactory
{
    
    public function create($importType)
    {
        $importType = strtolower($importType);
        switch ($importType) {
            case 'orders':
                return new Import\ImportOrders($this->integration1C);
                break;
            case 'products':
                return new Import\ImportProducts($this->integration1C);
                break;
            case 'offers':
                return new Import\ImportOffers($this->integration1C);
                break;
            default:
                throw new \Exception('Unknown import type: "' . $importType . '"');
        }
    }
}