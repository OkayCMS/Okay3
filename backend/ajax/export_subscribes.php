<?php


use Okay\Core\Database;
use Okay\Core\Managers;
use Okay\Core\Response;
use Okay\Core\QueryFactory;
use Okay\Entities\UsersEntity;
use Okay\Entities\SubscribesEntity;

require_once 'configure.php';

$columnsNames = [
    'email' => 'Email'
];

$totalUsers      = 500;
$columnDelimiter = ';';
$subscribesCount = 5;
$exportFilesDir  = 'backend/files/export_users/';
$filename        = 'subscribes.csv';


/** @var Database $db */
$db = $DI->get(Database::class);

/** @var QueryFactory $queryFactory */
$queryFactory = $DI->get(QueryFactory::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var SubscribesEntity $subscribesEntity */
$subscribesEntity     = $entityFactory->get(SubscribesEntity::class);

/** @var UsersEntity $usersEntity */
$usersEntity          = $entityFactory->get(UsersEntity::class);

if (!$managers->access('users', $managersEntity->get($_SESSION['admin']))) {
    exit();
}

// Excel ��������� ������ ��������� 1251
setlocale(LC_ALL, 'ru_RU.1251');
$sqlQuery = $queryFactory->newSqlQuery()->setStatement('SET NAMES cp1251');
$db->query($sqlQuery);

$page = $request->get('page');
if(empty($page) || $page==1) {
    $page = 1;
    if(is_writable($exportFilesDir.$filename)) {
        unlink($exportFilesDir.$filename);
    }
}

$f = fopen($exportFilesDir.$filename, 'ab');

if($page == 1) {
    fputcsv($f, $columnsNames, $columnDelimiter);
}

$filter = [];
$filter['page']  = $page;
$filter['limit'] = $totalUsers;
$filter['sort']  = $request->get('sort');

$users = [];
foreach($subscribesEntity->find($filter) as $s) {
    $str = [];
    foreach($columnsNames as $n=>$c) {
        $str[] = $s->$n;
    }

    fputcsv($f, $str, $columnDelimiter);
}

$totalSubscribes = $subscribesEntity->count();

if($subscribesCount*$page < $totalSubscribes) {
    $data = ['end'=>false, 'page'=>$page, 'totalpages'=>$totalSubscribes/$subscribesCount];
} else {
    $data = ['end'=>true, 'page'=>$page, 'totalpages'=>$totalSubscribes/$subscribesCount];
}

fclose($f);

if ($data) {
    $response->setContent(json_encode($data), RESPONSE_JSON)->sendContent();
}
