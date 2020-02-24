<?php


namespace Okay\Modules\OkayCMS\YandexXMLVendorModel;


use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Modules\OkayCMS\YandexXMLVendorModel\Extenders\BackendExtender;

return [
    BackendExtender::class => [
        'class' => BackendExtender::class,
        'arguments' => [
        ],
    ],
];