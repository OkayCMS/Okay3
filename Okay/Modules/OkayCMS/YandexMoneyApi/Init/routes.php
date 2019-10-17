<?php

use Okay\Modules\OkayCMS\YandexMoneyApi\Controllers\CallbackController;
use Okay\Modules\OkayCMS\YandexMoneyApi\Controllers\RequestController;

return [
    'OkayCMS.YandexMoneyApi.Callback' => [
        'slug' => 'payment/OkayCMS/YandexMoneyApi/callback',
        'params' => [
            'controller' => CallbackController::class,
            'method' => 'payOrder',
        ],
    ],
    'OkayCMS.YandexMoneyApi.SendPaymentRequest' => [
        'slug' => 'payment/OkayCMS/YandexMoneyApi/sendPaymentRequest',
        'params' => [
            'controller' => RequestController::class,
            'method' => 'sendPaymentRequest',
        ],
    ],
];