<?php

$startTime = microtime(true);

use Okay\Core\Router;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Config;
use OkayLicense\License;
use Okay\Core\Modules\Modules;

try {
    ini_set('display_errors', 'off');

    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        session_name(md5($_SERVER['HTTP_USER_AGENT']));
    }
    session_start();
    
    require_once('vendor/autoload.php');

    $DI = include 'Okay/Core/config/container.php';

    /** @var Router $router */
    $router = $DI->get(Router::class);
    $router->resolveCurrentLanguage();
    
    /** @var Config $config */
    $config = $DI->get(Config::class);

    if ($config->get('debug_mode') == true) {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
    }
    
    /** @var Response $response */
    $response = $DI->get(Response::class);
    
    /** @var Request $request */
    $request = $DI->get(Request::class);
    // Установим время начала выполнения скрипта
    $request->setStartTime($startTime);

    if (isset($_GET['logout'])) {
        unset($_SESSION['admin']);
        $response->redirectTo($request->getRootUrl());
    }

    /** @var License $license */
    $license = $DI->get(License::class);
    $license->check();
    
    /** @var Modules $modules */
    $modules = $DI->get(Modules::class);
    $modules->startEnabledModules();
    
    $router->run();

    if ($response->getContentType() == RESPONSE_HTML) {
        // Отладочная информация
        print "<!--\r\n";
        $timeEnd = microtime(true);
        $execTime = $timeEnd - $startTime;

        if (function_exists('memory_get_peak_usage')) {
            print "memory peak usage: " . memory_get_peak_usage() . " bytes\r\n";
        }
        print "page generation time: " . $execTime . " seconds\r\n";
        print "-->";
    }
    
} catch (\Exception $e) {
    print $e->getMessage() . PHP_EOL;
    print $e->getTraceAsString();
}
