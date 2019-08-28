<?php

use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Response;
use Okay\Core\Design;
use Okay\Core\BackendTranslations;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\ManagersEntity;

chdir('../../../../');

if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}

session_start();
require_once('vendor/autoload.php');

$DI = include 'Core/config/container.php';

// Кеширование нам не нужно
/** @var Response $response */
$response = $DI->get(Response::class);
$response->addHeader('Cache-Control: no-cache, must-revalidate');
$response->addHeader('Expires: -1');
$response->addHeader('Pragma: no-cache');

$manager = $DI->get(EntityFactory::class)->get(ManagersEntity::class)->get($_SESSION['admin']);

/** @var Design $design */
$design = $DI->get(Design::class);

if (empty($manager->id)) {
    print "not admin :(";
    exit;
}

$design->set_templates_dir('backend/design/js/admintooltip');
$design->set_compiled_dir('backend/design/compiled');

// Перевод админки
$backendTranslations = $DI->get(BackendTranslations::class);
$file = "backend/lang/" . $manager->lang . ".php";
if (!file_exists($file)) {
    foreach (glob("backend/lang/??.php") as $f) {
        $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
        break;
    }
}
require_once($file);
$design->assign('btr', $backendTranslations);
$language = $manager = $DI->get(EntityFactory::class)->get(LanguagesEntity::class)->get((string)$manager->lang);
$design->assign('language', $language);

$response->addHeader('Content-Type: application/javascript');
$response->setContent($design->fetch('tooltip.js'), RESPONSE_JAVASCRIPT);
$response->sendContent();