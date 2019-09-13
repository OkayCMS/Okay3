<?php

/**
 *
 * 'имя_роута' => [
 *     'slug' => 'урл_роута{$именованный_параметр}', - если в слаге роута имеется именованный параметр,  то его в последствии можно будет поймать в качестве  аргумента
 *                                                     метода класса контроллера. Имя именованного параметра и переменной, которая используется в аргументе контроллера
 *                                                     должны совпадать. Если же вам необходим необязательный именованный параметр в роуте, то вы можете описать его на
 *                                                     уровне регуляроного выражения в блоке "patterns"  и отлавливать  в  методе  контроллера при помощи переменной со
 *                                                     значением по-умолчанию. Реализацию необязательного параметра вы можете увидеть на примере роута "search" ниже
 *     'patterns' => [
 *         '{$именованный_параметр}' => 'регулярное_выражение_параметра' - указание паттернов не является обязательным. В случае если вы не указываете паттерн для конкретного
 *                                                                         параметра, то подставится регулярное выражение по-умолчанию следующего вида "([^/]+)"
 *     ],
 *     'params' => [
 *         'controller' => 'имя_используемого_класса_контроллера', - если имя класса указано без пространств имен, то будет использоваться пространство имен по-умолчанию
 *                                                                   следующего вида "\Okay\Controllers\"
 *         'method'     => 'имя_вызываемого_метода_контроллера',
 *     ],
 *     'defaults' => [
 *         '{$имя_параметра_по-умолчанию}' => 'значение_параметра_по-умолчанию', - в этом блоке указываются параметры, которые не попадают в слаг роута, но используются внутри метода класса
 *                                                                                 контроллера  для  различных  целей.  Данные  параметры  можно  отловить аналогично  именованному параметру,
 *                                                                                 который   используется   в  слаге,  а именно по  имени  параметра и переменной в  аргументе  метода класса
 *                                                                                 контроллера, которые должны совпадать
 *     ],
 * ],
 *
 */

return [
    'blog' => [
        'slug' => 'blog',
        'params' => [
            'controller' => 'BlogController',
            'method' => 'fetchBlog',
        ],
        'defaults' => [
            '{$typePost}' => 'blog',
        ],
    ],
    'blog_item' => [
        'slug' => 'blog/{$url}',
        'params' => [
            'controller' => 'BlogController',
            'method' => 'fetchPost',
        ],
        'defaults' => [
            '{$typePost}' => 'blog',
        ],
    ],
    'news' => [
        'slug' => 'news',
        'params' => [
            'controller' => 'BlogController',
            'method' => 'fetchBlog',
        ],
        'defaults' => [
            '{$typePost}' => 'news',
        ],
    ],
    'news_item' => [
        'slug' => 'news/{$url}',
        'params' => [
            'controller' => 'BlogController',
            'method' => 'fetchPost',
        ],
        'defaults' => [
            '{$typePost}' => 'news',
        ],
    ],
    'contact' => [
        'slug' => '/contact',
        'params' => [
            'controller' => 'FeedbackController',
            'method' => 'render',
        ],
    ],
    'main' => [
        'slug' => '/',
        'params' => [
            'controller' => 'MainController',
            'method' => 'render',
        ],
    ],
    'cart' => [
        'slug' => '/cart',
        'params' => [
            'controller' => 'CartController',
            'method' => 'render',
        ],
    ],
    'cart_remove_item' => [
        'slug' => '/cart/remove/{$variantId}',
        'params' => [
            'controller' => 'CartController',
            'method' => 'removeItem',
        ],
        'patterns' => [
            '{$variantId}' => '([0-9]+)',
        ],
    ],
    'cart_add_item' => [
        'slug' => '/cart/{$variantId}',
        'params' => [
            'controller' => 'CartController',
            'method' => 'addItem',
        ],
        'patterns' => [
            '{$variantId}' => '([0-9]+)',
        ],
    ],
    'cart_ajax' => [
        'slug' => '/ajax/cart_ajax.php',
        'params' => [
            'controller' => 'CartController',
            'method' => 'cartAjax',
        ],
    ],
    'wishlist' => [
        'slug' => '/wishlist',
        'params' => [
            'controller' => 'WishListController',
            'method' => 'render',
        ],
    ],
    'wishlist_ajax' => [
        'slug' => '/ajax/wishlist.php',
        'params' => [
            'controller' => 'WishListController',
            'method' => 'ajaxUpdate',
        ],
    ],
    'comparison' => [
        'slug' => 'comparison',
        'params' => [
            'controller' => 'ComparisonController',
            'method' => 'render',
        ],
    ],
    'comparison_ajax' => [
        'slug' => '/ajax/comparison.php',
        'params' => [
            'controller' => 'ComparisonController',
            'method' => 'ajaxUpdate',
        ],
    ],
    'product' => [
        'slug' => '/products/{$url}',
        'params' => [
            'controller' => 'ProductController',
            'method' => 'render',
        ],
    ],
    'ajax_product_rating' => [
        'slug' => '/ajax/rating',
        'params' => [
            'controller' => 'ProductController',
            'method' => 'rating',
        ],
    ],
    'category' => [
        'slug' => '/catalog/{$url}{$filtersUrl}',
        'patterns' => [
            '{$filtersUrl}' => '/?(.*)',
        ],
        'params' => [
            'controller' => 'CategoryController',
            'method' => 'render',
        ],
    ],
    'brands' => [
        'slug' => 'brands',
        'params' => [
            'controller' => 'BrandsController',
            'method' => 'render',
        ],
    ],
    'brand' => [
        'slug' => '/brand/{$url}{$filtersUrl}',
        'patterns' => [
            '{$url}' => '([^/]*)',
            '{$filtersUrl}' => '/?(.*)',
        ],
        'params' => [
            'controller' => 'BrandController',
            'method' => 'render',
        ],
    ],
    'search' => [
        'slug' => '/all-products{$filtersUrl}',
        'patterns' => [
            '{$filtersUrl}' => '/?(.*)',
        ],
        'params' => [
            'controller' => 'ProductsController',
            'method' => 'render',
        ],
    ],
    'ajax_search' => [
        'slug' => '/ajax/search_products',
        'params' => [
            'controller' => 'ProductsController',
            'method' => 'ajaxSearch',
        ],
    ],
    'discounted' => [
        'slug' => '/discounted{$filtersUrl}',
        'patterns' => [
            '{$filtersUrl}' => '/?(.*)',
        ],
        'params' => [
            'controller' => 'ProductsController',
            'method' => 'render',
        ],
    ],
    'bestsellers' => [
        'slug' => '/bestsellers{$filtersUrl}',
        'patterns' => [
            '{$filtersUrl}' => '/?(.*)',
        ],
        'params' => [
            'controller' => 'ProductsController',
            'method' => 'render',
        ],
    ],
    'order' => [
        'slug' => 'order/{$url}',
        'params' => [
            'controller' => 'OrderController',
            'method' => 'render',
        ],
    ],
    'sitemap' => [
        'slug' => 'sitemap.xml',
        'params' => [
            'controller' => 'SiteMapController',
            'method' => 'renderXml',
        ],
    ],
    'opensearch' => [
        'slug' => 'opensearch.xml',
        'params' => [
            'controller' => 'OpenSearchController',
            'method' => 'renderXml',
        ],
    ],
    'opensearch_ajax' => [
        'slug' => 'ajax/opensearch',
        'params' => [
            'controller' => 'OpenSearchController',
            'method' => 'liveSearch',
        ],
    ],
    'user' => [
        'slug' => 'user',
        'params' => [
            'controller' => 'UserController',
            'method' => 'render',
        ],
    ],
    'login' => [
        'slug' => '/user/login',
        'params' => [
            'controller' => 'UserController',
            'method' => 'login',
        ],
    ],
    'register' => [
        'slug' => 'user/register',
        'params' => [
            'controller' => 'UserController',
            'method' => 'register',
        ],
    ],
    'password_remind' => [
        'slug' => 'user/password_remind{$code}',
        'params' => [
            'controller' => 'UserController',
            'method' => 'passwordRemind',
        ],
        'patterns' => [
            '{$code}' => '([0-9a-z]+)?',
        ],
    ],
    'logout' => [
        'slug' => 'user/logout',
        'params' => [
            'controller' => 'UserController',
            'method' => 'logout',
        ],
    ],
    'resize' => [
        'slug' => 'files/resized/{$object}/{$filename}',
        'patterns' => [
            '{$object}' => '(.+)',
            '{$filename}' => '(.+)',
        ],
        'params' => [
            'controller' => 'ResizeController',
            'method' => 'resize',
        ],
    ],
    'dynamic_js' => [
        'slug' => 'dynamic_js/{$fileId}.js',
        'params' => [
            'controller' => 'DynamicJsController',
            'method' => 'getJs',
        ],
    ],
    'support' => [
        'slug' => '/support.php',
        'params' => [
            'controller' => 'SupportController',
            'method' => 'checkDomain',
        ],
    ],
    'page' => [
        'slug' => '{$url}',
        'patterns' => [
            '{$url}' => '(.*)',
        ],
        'params' => [
            'controller' => 'PageController',
            'method' => 'render',
        ],
    ],
];