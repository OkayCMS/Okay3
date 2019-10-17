<?php


namespace Okay\Core;


use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Core\Modules\Module;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Admin\Helpers\BackendProductsHelper;
use Okay\Admin\Helpers\BackendVariantsHelper;
use Okay\Admin\Helpers\BackendFeaturesHelper;
use Okay\Admin\Helpers\BackendBrandsHelper;
use Okay\Admin\Helpers\BackendCategoriesHelper;
use Okay\Admin\Helpers\BackendSpecialImagesHelper;
use Okay\Admin\Helpers\BackendCurrenciesHelper;
use Okay\Helpers\CartHelper;
use Okay\Helpers\DeliveriesHelper;
use Okay\Helpers\MainHelper;
use Okay\Helpers\PaymentsHelper;
use Okay\Helpers\ValidateHelper;
use Psr\Log\LoggerInterface;
use Okay\Helpers\FeaturesHelper;
use Okay\Helpers\ProductsHelper;
use Okay\Helpers\CatalogHelper;
use Okay\Helpers\OrdersHelper;
use Okay\Helpers\FilterHelper;
use Okay\Helpers\MoneyHelper;

$helpers = [
    BackendProductsHelper::class => [
        'class' => BackendProductsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(QueryFactory::class),
            new SR(Database::class),
            new SR(Image::class),
            new SR(Config::class),
            new SR(Request::class),
        ]
    ],
    BackendVariantsHelper::class => [
        'class' => BackendVariantsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendFeaturesHelper::class => [
        'class' => BackendFeaturesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(QueryFactory::class),
            new SR(Translit::class),
            new SR(Database::class),
        ]
    ],
    BackendSpecialImagesHelper::class => [
        'class' => BackendSpecialImagesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendOrdersHelper::class => [
        'class' => BackendOrdersHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(MoneyHelper::class),
        ]
    ],
    BackendCategoriesHelper::class => [
        'class' => BackendCategoriesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Image::class),
            new SR(Config::class),
        ]
    ],
    BackendBrandsHelper::class => [
        'class' => BackendBrandsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendCurrenciesHelper::class => [
        'class' => BackendCurrenciesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    MainHelper::class => [
        'class' => MainHelper::class,
    ],
    DeliveriesHelper::class => [
        'class' => DeliveriesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Module::class),
            new SR(LoggerInterface::class),
        ]
    ],
    PaymentsHelper::class => [
        'class' => PaymentsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Module::class),
            new SR(LoggerInterface::class),
        ]
    ],
    ValidateHelper::class => [
        'class' => ValidateHelper::class,
        'arguments' => [
            new SR(Validator::class),
            new SR(Settings::class),
            new SR(Request::class),
        ]
    ],
    CartHelper::class => [
        'class' => CartHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    ProductsHelper::class => [
        'class' => ProductsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(MoneyHelper::class),
            new SR(Settings::class),
        ],
    ],
    CatalogHelper::class => [
        'class' => CatalogHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Money::class)
        ],
    ],
    OrdersHelper::class => [
        'class' => OrdersHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(ProductsHelper::class),
            new SR(MoneyHelper::class),
        ],
    ],
    FilterHelper::class => [
        'class' => FilterHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(Languages::class),
            new SR(Request::class),
            new SR(Router::class),
            new SR(Design::class),
        ],
    ],
    MoneyHelper::class => [
        'class' => MoneyHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    FeaturesHelper::class => [
        'class' => FeaturesHelper::class,
        'arguments' => [
            new SR(Database::class),
            new SR(Import::class),
            new SR(Translit::class),
            new SR(EntityFactory::class),
            new SR(QueryFactory::class),
        ]
    ],
];

return $helpers;
