<?php

namespace Okay\Modules\OkayCMS\YandexXMLVendorModel;

return [
    'OkayCMS_YandexXMLVendorModel_feed' => [
        'slug' => 'yandex-vendor-model.xml',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\YandexXMLController',
            'method' => 'render',
        ],
    ],
];