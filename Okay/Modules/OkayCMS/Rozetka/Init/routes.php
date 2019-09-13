<?php

namespace Okay\Modules\OkayCMS\Rozetka;

return [
    'OkayCMS_Rozetka_feed' => [
        'slug' => 'rozetka.xml',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\RozetkaController',
            'method' => 'render',
        ],
    ],
];