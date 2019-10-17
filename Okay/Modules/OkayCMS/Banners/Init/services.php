<?php


namespace Okay\Modules\OkayCMS\Banners;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Module;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Modules\OkayCMS\Banners\Extenders\FrontExtender;

return [
    FrontExtender::class => [
        'class' => FrontExtender::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Design::class),
            new SR(Module::class),
        ],
    ],
];