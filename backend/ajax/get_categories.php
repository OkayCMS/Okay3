<?php

use Okay\Core\Design;
use Okay\Core\BackendTranslations;
use Okay\Entities\CategoriesEntity;

require_once 'configure.php';

if (!$managers->access('categories', $manager)) {
    exit();
}

/** @var Design $design */
$design = $DI->get(Design::class);

/** @var BackendTranslations $backendTranslations */
$backendTranslations = $DI->get(BackendTranslations::class);

/** @var CategoriesEntity $categoriesEntity */
$categoriesEntity = $entityFactory->get(CategoriesEntity::class);

$design->assign('config', $config);

$design->setTemplatesDir('backend/design/html');
$design->setCompiledDir('backend/design/compiled');

// Перевод админки
$file = "backend/lang/".$manager->lang.".php";
if (!file_exists($file)) {
    foreach (glob("backend/lang/??.php") as $f) {
        $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
        break;
    }
}
require_once($file);
$design->assign('btr', $backendTranslations);

$result = [];
/*Выборка категории и её деток*/
if ($request->get("category_id")) {
    $categoryId = $request->get("category_id", 'integer');
    $categories = $categoriesEntity->get($categoryId);
    $design->assign('categories_ajax', $categories->subcategories);
    $result['success'] = true;
    $result['cats'] = $design->fetch("categories_ajax.tpl");
} else {
    $result['success ']= false;
}

$response->setContent(json_encode($result), RESPONSE_JSON);
$response->sendContent();
