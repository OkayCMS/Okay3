<?php


namespace Okay\Modules\OkayCMS\GoogleMerchant;


use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Modules\OkayCMS\GoogleMerchant\Extenders\BackendExtender;

return [
    BackendExtender::class => [
        'class' => BackendExtender::class,
        'arguments' => [
        ],
    ],
];