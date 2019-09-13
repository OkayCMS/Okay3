<?php

namespace Okay\Modules\OkayCMS\YandexXML;

return [
    'OkayCMS_YandexXML_feed' => [
        'slug' => 'yandex.xml',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\YandexXMLController',
            'method' => 'render',
        ],
    ],
];