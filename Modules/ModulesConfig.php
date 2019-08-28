<?php


namespace Okay\Modules;


use Aura\SqlQuery\QueryFactory;
use Okay\Core\Config;
use Okay\Core\Database;
use Okay\Core\DataCleaner;
use Okay\Core\EntityFactory;
use Okay\Core\Managers;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Core\Translit;

$DI = include 'Core/config/container.php';

$services = [
    Integration1C\Integration\Integration1C::class => [
        'class' => Integration1C\Integration\Integration1C::class,
        'arguments' => [
            new SR(Managers::class),
            new SR(EntityFactory::class),
            new SR(DataCleaner::class),
            new SR(Database::class),
            new SR(QueryFactory::class),
            new SR(Request::class),
            new SR(Settings::class),
            new SR(Config::class),
            new SR(Translit::class),
        ],
    ],
    Integration1C\Integration\Import\ImportFactory\ImportFactory::class => [
        'class' => Integration1C\Integration\Import\ImportFactory\ImportFactory::class,
        'arguments' => [
            new SR(Integration1C\Integration\Integration1C::class),
        ],
    ],
    Integration1C\Integration\Export\ExportFactory\ExportFactory::class => [
        'class' => Integration1C\Integration\Export\ExportFactory\ExportFactory::class,
        'arguments' => [
            new SR(Integration1C\Integration\Integration1C::class),
        ],
    ],
];

$DI->bindServices($services);
