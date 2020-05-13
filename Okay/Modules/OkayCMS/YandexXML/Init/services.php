<?php


namespace Okay\Modules\OkayCMS\YandexXML;


use Okay\Core\EntityFactory;
use Okay\Core\Image;
use Okay\Core\Languages;
use Okay\Core\Money;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\QueryFactory;
use Okay\Core\Settings;
use Okay\Helpers\XmlFeedHelper;
use Okay\Modules\OkayCMS\YandexXML\Extenders\BackendExtender;
use Okay\Modules\OkayCMS\YandexXML\Helpers\YandexXMLHelper;

return [
    BackendExtender::class => [
        'class' => BackendExtender::class,
        'arguments' => [
        ],
    ],
    YandexXMLHelper::class => [
        'class' => YandexXMLHelper::class,
        'arguments' => [
            new SR(Image::class),
            new SR(Money::class),
            new SR(Settings::class),
            new SR(QueryFactory::class),
            new SR(Languages::class),
            new SR(EntityFactory::class),
            new SR(XmlFeedHelper::class),
        ],
    ],
];