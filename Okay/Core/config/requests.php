<?php


namespace Okay\Core;

use Okay\Admin\Requests\OrdersRequest;
use Okay\Admin\Requests\CategoriesRequest;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Admin\Requests\ProductAdminRequest;
use Okay\Requests\CartRequest;

$requestContainers = [
    ProductAdminRequest::class => [
        'class' => ProductAdminRequest::class,
        'arguments' => [
            new SR(Request::class),
        ]
    ],
    OrdersRequest::class => [
        'class' => OrdersRequest::class,
        'arguments' => [
            new SR(Request::class),
        ]
    ],
    CategoriesRequest::class => [
        'class' => CategoriesRequest::class,
        'arguments' => [
            new SR(Request::class),
        ]
    ],
    CartRequest::class => [
        'class' => CartRequest::class,
        'arguments' => [
            new SR(Request::class),
        ]
    ],
];

return $requestContainers;
