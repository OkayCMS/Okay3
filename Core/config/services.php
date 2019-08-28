<?php


namespace Okay\Core;


use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Bramus\Router\Router as BRouter;
use Smarty;
use Mobile_Detect;
use Aura\SqlQuery\QueryFactory;
use Aura\Sql\ExtendedPdo;
use Okay\Core\Import as ImportCore;
use Okay\Logic\FeaturesLogic;
use PHPMailer\PHPMailer\PHPMailer;
use Okay\Logic\ProductsLogic;
use Okay\Logic\CatalogLogic;
use Okay\Logic\OrdersLogic;
use Okay\Logic\ChpuFilterLogic;
use Okay\Logic\MoneyLogic;

$services = [
    BRouter::class => [
        'class' => BRouter::class,
    ],
    PHPMailer::class => [
        'class' => PHPMailer::class,
    ],
    Smarty::class => [
        'class' => Smarty::class,
    ],
    Mobile_Detect::class => [
        'class' => Mobile_Detect::class,
    ],
    Router::class => [
        'class' => Router::class,
        'arguments' => [
            new SR(BRouter::class),
            new SR(Request::class),
            new SR(Response::class),
            new SR(EntityFactory::class),
            new SR(Languages::class),
        ],
    ],
    Config::class => [
        'class' => Config::class,
    ],
    Database::class => [
        'class' => Database::class,
        'arguments' => [
            new SR(ExtendedPdo::class),
            new SR(LoggerInterface::class),
            new PR('db'),
        ],
    ],
    QueryFactory::class => [
        'class' => QueryFactory::class,
        'arguments' => [
            new PR('db.driver'),
        ],
    ],
    ExtendedPdo::class => [
        'class' => ExtendedPdo::class,
        'arguments' => [
            new PR('db.dsn'),
            new PR('db.user'),
            new PR('db.password'),
        ],
    ],
    EntityFactory::class => [
        'class' => EntityFactory::class,
        'arguments' => [
            new SR(LoggerInterface::class),
        ],
    ],
    Request::class => [
        'class' => Request::class,
    ],
    Response::class => [
        'class' => Response::class,
        'arguments' => [
            new SR(Adapters\Response\AdapterManager::class),
        ],
    ],
    Languages::class => [
        'class' => Languages::class,
        'arguments' => [
            new SR(Database::class),
            new SR(Request::class),
            new SR(QueryFactory::class),
        ],
    ],
    Validator::class => [
        'class' => Validator::class,
        'arguments' => [
            new SR(Settings::class),
            new SR(Recaptcha::class),
        ],
    ],
    Settings::class => [
        'class' => Settings::class,
        'arguments' => [
            new SR(Database::class),
            new SR(Languages::class),
            new SR(QueryFactory::class),
        ],
    ],
    TemplateConfig::class => [
        'class' => TemplateConfig::class,
        'arguments' => [
            new SR(Config::class),
        ],
        'calls' => [
            [
                'method' => 'configure',
                'arguments' => [
                    new PR('theme.name'),
                    new PR('theme.admin_theme_name'),
                    new PR('theme.admin_theme_managers'),
                ]
            ],
        ]
    ],
    Design::class => [
        'class' => Design::class,
        'arguments' => [
            new SR(Config::class),
            new SR(Smarty::class),
            new SR(Mobile_Detect::class),
            new SR(TemplateConfig::class),
        ],
    ],
    Image::class => [
        'class' => Image::class,
        'arguments' => [
            new SR(Settings::class),
            new SR(Config::class),
            new SR(Adapters\Resize\AdapterManager::class),
            new SR(Request::class),
            new PR('root_dir'),
            new SR(Response::class),
            new SR(QueryFactory::class),
            new SR(Database::class),
            new SR(EntityFactory::class),
        ],
    ],
    Notify::class => [
        'class' => Notify::class,
        'arguments' => [
            new SR(Settings::class),
            new SR(Languages::class),
            new SR(EntityFactory::class),
            new SR(Design::class),
            new SR(TemplateConfig::class),
            new SR(\Okay\Logic\OrdersLogic::class),
            new SR(BackendTranslations::class),
            new SR(PHPMailer::class),
            new PR('root_dir'),
        ],
    ],
    Money::class => [
        'class' => Money::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
        'calls' => [
            [
                'method' => 'configure',
                'arguments' => [
                    new PR('money.decimals_point'),
                    new PR('money.thousands_separator'),
                ]
            ],
        ]
    ],
    StreamHandler::class => [
        'class' => StreamHandler::class,
        'arguments' => [
            new PR('logger.file'),
            Logger::DEBUG,
        ],
    ],
    LoggerInterface::class => [
        'class' => Logger::class,
        'arguments' => [ 'channel-name' ],
        'calls' => [
            [
                'method' => 'pushHandler',
                'arguments' => [
                    new SR(StreamHandler::class),
                ]
            ],
        ]
    ],
    Recaptcha::class => [
        'class' => Recaptcha::class,
        'arguments' => [
            new SR(Settings::class),
        ],
    ],
    Managers::class => [
        'class' => Managers::class,
    ],
    Translit::class => [
        'class' => Translit::class,
    ],
    ManagerMenu::class => [
        'class' => ManagerMenu::class,
        'arguments' => [
            new SR(Managers::class),
        ],
    ],
    BackendTranslations::class => [
        'class' => BackendTranslations::class,
    ],
    JsSocial::class => [
        'class' => JsSocial::class,
    ],
    DataCleaner::class => [
        'class' => DataCleaner::class,
        'arguments' => [
            new SR(Database::class),
            new SR(Config::class),
        ],
    ],
    ImportCore::class => [
        'class' => ImportCore::class
    ],
    Cart::class => [
        'class' => Cart::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(ProductsLogic::class),
            new SR(\Okay\Logic\MoneyLogic::class),
        ],
    ],
    Comparison::class => [
        'class' => Comparison::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
        ],
    ],
    WishList::class => [
        'class' => WishList::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
        ],
    ],

    //> Logic classes
    ProductsLogic::class => [
        'class' => ProductsLogic::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(MoneyLogic::class),
        ],
    ],
    CatalogLogic::class => [
        'class' => CatalogLogic::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Money::class)
        ],
    ],
    OrdersLogic::class => [
        'class' => OrdersLogic::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(ProductsLogic::class),
            new SR(\Okay\Logic\MoneyLogic::class),
        ],
    ],
    ChpuFilterLogic::class => [
        'class' => ChpuFilterLogic::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(Languages::class),
            new SR(Request::class),
            new SR(Router::class),
            new SR(Design::class),
        ],
    ],
    MoneyLogic::class => [
        'class' => MoneyLogic::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    FeaturesLogic::class => [
        'class' => FeaturesLogic::class,
        'arguments' => [
            new SR(Database::class),
            new SR(Import::class),
            new SR(Translit::class),
            new SR(EntityFactory::class),
        ]
    ],
    //> Logic
];

$adapters = include __DIR__ . '/../Adapters/adapters.php';

return array_merge($services, $adapters);
