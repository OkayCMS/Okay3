<?php

namespace Okay\Modules\OkayCMS\YandexMoneyApi;

return [
    'OkayCMS_YandexMoneyApi_callback' => [
        'slug' => 'payment/OkayCMS/YandexMoneyApi/callback',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\CallbackController',
            'method' => 'payOrder',
        ],
    ],
];