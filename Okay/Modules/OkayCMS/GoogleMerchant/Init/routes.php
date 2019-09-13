<?php

namespace Okay\Modules\OkayCMS\GoogleMerchant;

return [
    'OkayCMS_GoogleMerchant_feed' => [
        'slug' => 'google.xml',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\GoogleMerchantController',
            'method' => 'render',
        ],
    ],
];