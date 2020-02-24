<?php


namespace Okay\Modules\OkayCMS\YandexXML;


use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Modules\OkayCMS\YandexXML\Extenders\BackendExtender;

return [
    BackendExtender::class => [
        'class' => BackendExtender::class,
        'arguments' => [
        ],
    ],
];