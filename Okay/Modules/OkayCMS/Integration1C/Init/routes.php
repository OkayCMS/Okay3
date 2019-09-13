<?php

namespace Okay\Modules\OkayCMS\LiqPay;

return [
    'integration_1c' => [
        'slug' => 'cml/1c_exchange.php',
        'params' => [
            'controller' => '\Okay\Modules\OkayCMS\Integration1C\Controllers\Integration1cController',
            'method' => 'runIntegration',
        ],
    ],
];