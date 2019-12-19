<?php

use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Response;
use Okay\Core\Design;
use Okay\Core\BackendTranslations;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\ManagersEntity;
use OkayLicense\License;

chdir('../../../../');

if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}

session_start();
require_once('vendor/autoload.php');

$DI = include 'Okay/Core/config/container.php';

/** @var License $license */
$license = $DI->get(License::class);
$license->check();

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

$design->setTemplatesDir('backend/design/js/admintooltip');
$design->setCompiledDir('backend/design/compiled');

// Перевод админки
$backendTranslations = $DI->get(BackendTranslations::class);
$backendTranslations->initTranslations($manager->lang);
$design->assign('btr', $backendTranslations);
$language = $manager = $DI->get(EntityFactory::class)->get(LanguagesEntity::class)->get((string)$manager->lang);
$design->assign('language', $language);

$response->addHeader('Content-Type: application/javascript');
$response->setContent($design->fetch('tooltip.js'), RESPONSE_JAVASCRIPT);
$response->sendContent();