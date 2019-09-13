<?php

$startTime = microtime(true);

use Okay\Core\Router;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Modules\Modules;
use OkayLicense\License;

try {
    //ini_set('display_errors', 'on');
    //error_reporting(E_ALL);

    $time_start = microtime(true);
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        session_name(md5($_SERVER['HTTP_USER_AGENT']));
    }
    session_start();
    
    require_once('vendor/autoload.php');

    $DI = include 'Okay/Core/config/container.php';

    /** @var Response $response */
    $response = $DI->get(Response::class);

    // Установим время начала выполнения скрипта
    /** @var Request $request */
    $request = $DI->get(Request::class);
    $request->setStartTime($startTime);
    
    if (isset($_GET['logout'])) {
        unset($_SESSION['admin']);
        $response->redirectTo($request->getRootUrl());
        exit();
    }

    /** @var License $license */
    $license = $DI->get(License::class);
    $license->check();
    
    /** @var Modules $modules */
    $modules = $DI->get(Modules::class);
    $modules->startEnabledModules();

    /** @var Router $router */
    $router = $DI->get(Router::class);
    $router->run();

} catch (\Exception $e) {
    print $e->getMessage() . PHP_EOL;
    print $e->getTraceAsString();
}
