<?php


namespace Okay\Modules\OkayCMS\FastOrder;


use Okay\Core\FrontTranslations;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

return [
    FormSpecification::class => [
        'class' => FormSpecification::class,
        'arguments' => [
            new SR(FrontTranslations::class),
        ],
    ],
];