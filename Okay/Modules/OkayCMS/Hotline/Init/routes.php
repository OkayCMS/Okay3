<?php

namespace Okay\Modules\OkayCMS\Hotline;

use Okay\Modules\OkayCMS\Hotline\Controllers\HotlineController;

return [
    'OkayCMS.Hotline.Feed' => [
        'slug' => 'hotline.xml',
        'params' => [
            'controller' => HotlineController::class,
            'method' => 'render',
        ],
    ],
];