<?php

use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\Modules\Module;
use Okay\Core\Modules\Modules;
use Okay\Entities\ManagersEntity;
use Okay\Core\EntityFactory;
use Okay\Core\ServiceLocator;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Managers;
use Okay\Core\ManagerMenu;
use Okay\Core\Config;
use OkayLicense\License;
use Okay\Core\Entity\Entity;
use Okay\Core\Languages;
use Okay\Entities\LanguagesEntity;

ini_set('display_errors', 'off');

//ini_set('display_errors', 'on');
//error_reporting(E_ALL);

chdir('..');

require_once('vendor/autoload.php');

$DI = include 'Okay/Core/config/container.php';

// Засекаем время
$time_start = microtime(true);
if(!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
$_SESSION['id'] = session_id();

@ini_set('session.gc_maxlifetime', 86400); // 86400 = 24 часа
@ini_set('session.cookie_lifetime', 0); // 0 - пока браузер не закрыт

/** @var Config $config */
$config = $DI->get(Config::class);

if ($config->get('debug_mode') == true) {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
}

/** @var Request $request */
$request = $DI->get(Request::class);

/** @var Languages $languages */
$languages = $DI->get(Languages::class);

$postLangId = $request->post('lang_id', 'integer');
$adminLangId = ($postLangId ? $postLangId : $request->get('lang_id', 'integer'));

if ($adminLangId) {
    $_SESSION['admin_lang_id'] = $adminLangId;
}

if (!empty($_SESSION['admin_lang_id'])) {
    $languages->setLangId((int)$_SESSION['admin_lang_id']);
} else {
    $_SESSION['admin_lang_id'] = $languages->getLangId();
}

/** @var BackendTranslations $backendTranslations */
$backendTranslations = $DI->get(BackendTranslations::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var ManagerMenu $managerMenu */
$managerMenu = $DI->get(ManagerMenu::class);

/** @var EntityFactory $entityFactory */
$entityFactory = $DI->get(EntityFactory::class);

/** @var Modules $modules */
$modules = $DI->get(Modules::class);

/** @var Design $design */
$design = $DI->get(Design::class);

/** @var Module $module */
$module = $DI->get(Module::class);

/** @var License $license */
$license = $DI->get(License::class);
$license->check();

// Запускаем все модули
$modules->startAllModules();

$smartyPlugins = include_once 'Okay/Core/SmartyPlugins/SmartyPlugins.php';

// SL будем использовать только для получения сервисов, которые запросили для контроллера
$serviceLocator = ServiceLocator::getInstance();

/** @var ManagersEntity $managersEntity */
$managersEntity = $entityFactory->get(ManagersEntity::class);

$response->addHeader('Cache-Control: no-cache, must-revalidate');
$response->addHeader('Expires: -1');
$response->addHeader('Pragma: no-cache');

// Берем название модуля из get-запроса
$backendControllerName = $request->get('controller');
$backendControllerName = preg_replace("/[^A-Za-z0-9\.]+/", "", $backendControllerName);

$manager = null;
if (!empty($_SESSION['admin'])) {
    $manager = $managersEntity->get($_SESSION['admin']);
}

if (!$manager && $backendControllerName != 'AuthAdmin') {
    $_SESSION['before_auth_url'] = $request->getBasePathWithDomain();
    $response->redirectTo($request->getRootUrl() . '/backend/index.php?controller=AuthAdmin');
}

if ($manager && $backendControllerName == 'AuthAdmin') {
    $response->redirectTo($request->getRootUrl() . '/backend/index.php');
}

$design->setCompiledDir('backend/design/compiled');
$design->setTemplatesDir('backend/design/html');
$modulesBackendControllers = $modules->getBackendControllers();

foreach ($modulesBackendControllers as $backendController) {
    $managerMenu->addCommonModuleController($backendController);
}

if (!empty($manager)) {

    $backendTranslations->initTranslations($manager->lang);
    $design->assign('btr', $backendTranslations);
    
    foreach ($modules->getRunningModules() as $runningModule) {
        foreach ($modules->getModuleBackendTranslations($runningModule['vendor'], $runningModule['module_name'], $manager->lang) as $var => $translation) {
            $backendTranslations->addTranslation($var, $translation);
        }
    }
}

if (($controllerParams = $module->getBackendControllerParams($backendControllerName)) && in_array($backendControllerName, $modulesBackendControllers)) {

    $vendor = $controllerParams['vendor'];
    $moduleName = $controllerParams['module'];
    $controllerName = $controllerParams['controller'];
    
    $design->useModuleDir();
    $design->setModuleTemplatesDir($module->getModuleDirectory($vendor, $moduleName) . 'Backend/design/html');
    $controllerName = $module->getBackendControllersNamespace($vendor, $moduleName) . '\\' . $controllerName;
} else {
    $design->setTemplatesDir('backend/design/html');
    $backendControllerName = preg_replace("/[^A-Za-z0-9]+/", "", $backendControllerName);
    $controllerName = '\\Okay\\Admin\\Controllers\\' . $backendControllerName;

    if (!class_exists($controllerName)) {
        $backendControllerName = array_search(reset($manager->permissions), $managers->getControllersPermissions());
    }
    $controllerName = '\\Okay\\Admin\\Controllers\\' . $backendControllerName;
}

$backend = new $controllerName($manager, $backendControllerName);

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
            
            // Определяем это Entity или сервис из DI
            if (is_subclass_of($parameter->getClass()->name, Entity::class)) {
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
