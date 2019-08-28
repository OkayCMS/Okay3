<?php

/**
 * значения можно использовать как {$var} - заменится на соответствующую директиву из конфига
 * так можно и использовать {%var%} заменится на директиву из класса (Settings)
 * ВАЖНО! директивы вида {%var%} нужно передавать через методы конфигураторы.
 * Например, если передать параметр через конструктор (в блоке arguments), то такие параметры не будут заменены
 * на settings. Такие директивы нужно передавать через дополнительный метод, указанный в блоке calls
 * 
 *  Money::class => [
        'class' => Money::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
        'calls' => [
            [
                'method' => 'configure',
                'arguments' => [
                    new PR('money.thousands_separator'),
                ]
            ],
        ]
    ],
 */

return [
    'root_dir' => '{$root_dir}',
    'logger' => [
        'file' => __DIR__ . '/../../log/app.log',
    ],
    'db' => [
        'driver'   => '{$db_driver}',
        'dsn'      => '{$db_driver}:host={$db_server};dbname={$db_name};charset={$db_charset}',
        'user'     => '{$db_user}',
        'password' => '{$db_password}',
        'prefix'   => '{$db_prefix}',
        'db_sql_mode' => '{$db_sql_mode}',
        'db_timezone' => '{$db_timezone}',

    ],
    'adapters' => [
        'resize' => [
            'default_adapter' => '{$resize_adapter}',
            'watermark' => '{$watermark_file}',
            'watermark_offset_x' => '{%watermark_offset_x%}',
            'watermark_offset_y' => '{%watermark_offset_y%}',
            'image_quality' => '{%image_quality%}',
        ],
        'response' => [
            'default_adapter' => 'Html',
        ],
    ],
    'money' => [
        'decimals_point' => '{%decimals_point%}',
        'thousands_separator' => '{%thousands_separator%}',
    ],
    'theme' => [
        'name' => '{%theme%}',
        'admin_theme_name' => '{%admin_theme%}',
        'admin_theme_managers' => '{%admin_theme_managers%}',
    ],
    'plugins' => [
        'date' => [
            'date_format' => '{%date_format%}',
        ],
    ],
];
