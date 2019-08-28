<?php

use Okay\Entities\LanguagesEntity;
use Okay\Entities\ManagersEntity;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\ServiceLocator;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Managers;
use Okay\Core\ManagerMenu;

ini_set('display_errors', 'on');
error_reporting(E_ALL & ~E_DEPRECATED);

chdir('..');

require_once('vendor/autoload.php');

$DI = include 'Core/config/container.php';

// Засекаем время
$time_start = microtime(true);
if(!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
$_SESSION['id'] = session_id();

@ini_set('session.gc_maxlifetime', 86400); // 86400 = 24 часа
@ini_set('session.cookie_lifetime', 0); // 0 - пока браузер не закрыт

$smartyPlugins = include_once 'Core/SmartyPlugins/SmartyPlugins.php';

/** @var Request $request */
$request = $DI->get(Request::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var ManagerMenu $managerMenu */
$managerMenu = $DI->get(ManagerMenu::class);

/** @var EntityFactory $entityFactory */
$entityFactory = $DI->get(EntityFactory::class);

// SL будем использовать только для получения сервисов, которые запросили для контроллера
$serviceLocator = new ServiceLocator();

/** @var ManagersEntity $managersEntity */
$managersEntity = $entityFactory->get(ManagersEntity::class);

$response->addHeader('Cache-Control: no-cache, must-revalidate');
$response->addHeader('Expires: -1');
$response->addHeader('Pragma: no-cache');

// Берем название модуля из get-запроса
$module = $request->get('module', 'string');
$module = preg_replace("/[^A-Za-z0-9]+/", "", $module);

$manager = null;
if (!empty($_SESSION['admin'])) {
    $manager = $managersEntity->get($_SESSION['admin']);
}

if (!$manager && $module != 'AuthAdmin') {
    $_SESSION['before_auth_url'] = $request->getBasePathWithDomain();
    $response->redirectTo($request->getRootUrl() . '/backend/index.php?module=AuthAdmin');
}

if ($manager && $module == 'AuthAdmin') {
    $response->redirectTo($request->getRootUrl() . '/backend/index.php');
}

if (empty($module)) {
    if ($managers->access($managers->getPermissionByModule('ProductsAdmin'), $manager)) {
        $module = 'ProductsAdmin';
    } else {
        $module = array_search(reset($manager->permissions), $managers->getModulesPermissions());
    }
}

$controllerName = '\\Okay\\Admin\\Controllers\\' . $module;
$backend = new $controllerName($manager, $module);

$access = call_user_func_array([$backend, 'onInit'], getMethodParams($backend, 'onInit'));
if ($access) {
    call_user_func_array([$backend, 'fetch'], getMethodParams($backend, 'fetch'));
}

function getMethodParams($controllerName, $methodName)
{
    global $serviceLocator, $entityFactory;
    $methodParams = [];

    // Проходимся рефлексией по параметрам метода, подеделяем их тип, и пытаемся через DI передать нужный объект
    $reflectionMethod = new \ReflectionMethod($controllerName, $methodName);
    foreach ($reflectionMethod->getParameters() as $parameter) {

        if ($parameter->getClass() !== null) {
            $class = new \ReflectionClass($parameter->getClass()->name);
            $namespace = trim($class->getNamespaceName(), '\\');

            // Определяем namespace запрашиваемого типа, это Entity или сервис из DI
            if ($namespace == 'Okay\Entities') {
                $methodParams[] = $entityFactory->get($parameter->getClass()->name);
            } else {
                $methodParams[] = $serviceLocator->getService($parameter->getClass()->name);
            }
        }
    }

    return $methodParams;
}

// Проверка сессии для защиты от xss
if (!$request->checkSession()) {
    unset($_POST);
    trigger_error('Session expired', E_USER_WARNING);
}

$response->sendContent();
