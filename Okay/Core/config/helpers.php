<?php


namespace Okay\Core;


use Okay\Admin\Helpers\BackendBlogHelper;
use Okay\Admin\Helpers\BackendCallbacksHelper;
use Okay\Admin\Helpers\BackendCategoryStatsHelper;
use Okay\Admin\Helpers\BackendCommentsHelper;
use Okay\Admin\Helpers\BackendCouponsHelper;
use Okay\Admin\Helpers\BackendDeliveriesHelper;
use Okay\Admin\Helpers\BackendFeaturesValuesHelper;
use Okay\Admin\Helpers\BackendFeedbacksHelper;
use Okay\Admin\Helpers\BackendMainHelper;
use Okay\Admin\Helpers\BackendManagersHelper;
use Okay\Admin\Helpers\BackendNotifyHelper;
use Okay\Admin\Helpers\BackendOrderSettingsHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Helpers\BackendPagesHelper;
use Okay\Admin\Helpers\BackendPaymentsHelper;
use Okay\Admin\Helpers\BackendSettingsHelper;
use Okay\Admin\Helpers\BackendUserGroupsHelper;
use Okay\Admin\Helpers\BackendUsersHelper;
use Okay\Admin\Helpers\BackendValidateHelper;
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
use Okay\Helpers\BlogHelper;
use Okay\Helpers\BrandsHelper;
use Okay\Helpers\CartHelper;
use Okay\Helpers\CouponHelper;
use Okay\Helpers\CommentsHelper;
use Okay\Helpers\DeliveriesHelper;
use Okay\Helpers\MainHelper;
use Okay\Helpers\MetadataHelpers\AllProductsMetadataHelper;
use Okay\Helpers\MetadataHelpers\BestsellersMetadataHelper;
use Okay\Helpers\MetadataHelpers\BrandMetadataHelper;
use Okay\Helpers\MetadataHelpers\CartMetadataHelper;
use Okay\Helpers\MetadataHelpers\CategoryMetadataHelper;
use Okay\Helpers\MetadataHelpers\CommonMetadataHelper;
use Okay\Helpers\MetadataHelpers\DiscountedMetadataHelper;
use Okay\Helpers\MetadataHelpers\OrderMetadataHelper;
use Okay\Helpers\MetadataHelpers\PostMetadataHelper;
use Okay\Helpers\MetadataHelpers\ProductMetadataHelper;
use Okay\Helpers\PaymentsHelper;
use Okay\Helpers\RelatedProductsHelper;
use Okay\Helpers\CommonHelper;
use Okay\Helpers\ResizeHelper;
use Okay\Helpers\SiteMapHelper;
use Okay\Helpers\UserHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Requests\CommonRequest;
use Psr\Log\LoggerInterface;
use Okay\Helpers\FeaturesHelper;
use Okay\Helpers\ProductsHelper;
use Okay\Helpers\CatalogHelper;
use Okay\Helpers\OrdersHelper;
use Okay\Helpers\FilterHelper;
use Okay\Helpers\MoneyHelper;
use Okay\Core\Entity\UrlUniqueValidator;

$helpers = [
    BackendMainHelper::class => [
        'class' => BackendMainHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(ManagerMenu::class),
            new SR(Design::class),
        ]
    ],
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
            new SR(Request::class),
        ]
    ],
    BackendFeaturesValuesHelper::class => [
        'class' => BackendFeaturesValuesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(QueryFactory::class),
            new SR(Translit::class),
            new SR(Database::class),
            new SR(Request::class),
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
            new SR(Request::class),
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
            new SR(Config::class),
            new SR(Image::class),
            new SR(QueryFactory::class),
            new SR(Database::class),
            new SR(Request::class),
        ]
    ],
    BackendCategoryStatsHelper::class => [
        'class' => BackendCategoryStatsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ]
    ],
    BackendFeedbacksHelper::class => [
        'class' => BackendFeedbacksHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(Settings::class),
        ]
    ],
    BackendNotifyHelper::class => [
        'class' => BackendNotifyHelper::class,
        'arguments' => [
            new SR(Notify::class),
        ]
    ],
    BackendPagesHelper::class => [
        'class' => BackendPagesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendOrderSettingsHelper::class => [
        'class' => BackendOrderSettingsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendCurrenciesHelper::class => [
        'class' => BackendCurrenciesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(QueryFactory::class),
            new SR(Database::class),
            new SR(Request::class),
        ]
    ],
    BackendManagersHelper::class => [
        'class' => BackendManagersHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BackendSettingsHelper::class => [
        'class' => BackendSettingsHelper::class,
        'arguments' => [
            new SR(Settings::class),
            new SR(Request::class),
            new SR(Config::class),
            new SR(EntityFactory::class),
            new SR(DataCleaner::class),
            new SR(Managers::class),
            new SR(TemplateConfig::class),
            new SR(QueryFactory::class),
            new SR(Languages::class),
            new SR(JsSocial::class),
            new SR(Image::class),
        ]
    ],
    BackendValidateHelper::class => [
        'class' => BackendValidateHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(Request::class),
            new SR(UrlUniqueValidator::class),
            new SR(Managers::class),
        ]
    ],
    BackendBlogHelper::class => [
        'class' => BackendBlogHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(Config::class),
            new SR(Image::class),
            new SR(Settings::class),
        ]
    ],
    BackendCallbacksHelper::class => [
        'class' => BackendCallbacksHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ]
    ],
    BackendCommentsHelper::class => [
        'class' => BackendCommentsHelper::class,
        'arguments' => [
            new SR(Request::class),
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(Notify::class),
        ]
    ],
    BackendCouponsHelper::class => [
        'class' => BackendCouponsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ]
    ],
    BackendDeliveriesHelper::class => [
        'class' => BackendDeliveriesHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(Config::class),
            new SR(Image::class),
        ]
    ],
    BackendPaymentsHelper::class => [
        'class' => BackendPaymentsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(Config::class),
            new SR(Image::class),
        ]
    ],
    BackendUsersHelper::class => [
        'class' => BackendUsersHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ]
    ],
    BackendUserGroupsHelper::class => [
        'class' => BackendUserGroupsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
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
            new SR(Money::class),
            new SR(Settings::class),
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
    CouponHelper::class => [
        'class' => CouponHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    CommonHelper::class => [
        'class' => CommonHelper::class,
        'arguments' => [
            new SR(ValidateHelper::class),
            new SR(Notify::class),
            new SR(Design::class),
            new SR(CommonRequest::class),
            new SR(EntityFactory::class),
        ]
    ],
    CommentsHelper::class => [
        'class' => CommentsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(CommonRequest::class),
            new SR(ValidateHelper::class),
            new SR(Design::class),
            new SR(Notify::class),
            new SR(MainHelper::class),
        ]
    ],
    RelatedProductsHelper::class => [
        'class' => RelatedProductsHelper::class,
        'arguments' => [
            new SR(ProductsHelper::class),
        ]
    ],
    BlogHelper::class => [
        'class' => BlogHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    BrandsHelper::class => [
        'class' => BrandsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    ResizeHelper::class => [
        'class' => ResizeHelper::class,
        'arguments' => [
            new SR(Image::class),
            new SR(Config::class),
        ]
    ],
    UserHelper::class => [
        'class' => UserHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ]
    ],
    SiteMapHelper::class => [
        'class' => SiteMapHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Response::class),
            new SR(MainHelper::class),
            new SR(Settings::class),
        ]
    ],
    ProductMetadataHelper::class => [
        'class' => ProductMetadataHelper::class,
    ],
    CommonMetadataHelper::class => [
        'class' => CommonMetadataHelper::class,
    ],
    CartMetadataHelper::class => [
        'class' => CartMetadataHelper::class,
    ],
    OrderMetadataHelper::class => [
        'class' => OrderMetadataHelper::class,
    ],
    CategoryMetadataHelper::class => [
        'class' => CategoryMetadataHelper::class,
    ],
    BrandMetadataHelper::class => [
        'class' => BrandMetadataHelper::class,
    ],
    PostMetadataHelper::class => [
        'class' => PostMetadataHelper::class,
    ],
    DiscountedMetadataHelper::class => [
        'class' => DiscountedMetadataHelper::class,
    ],
    BestsellersMetadataHelper::class => [
        'class' => BestsellersMetadataHelper::class,
    ],
    AllProductsMetadataHelper::class => [
        'class' => AllProductsMetadataHelper::class,
    ],
];

return $helpers;
