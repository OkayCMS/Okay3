<?php


use Okay\Entities\UsersEntity;
use Okay\Core\QueryFactory;
use Okay\Core\Managers;
use Okay\Core\Database;
use Okay\Core\Response;

require_once 'configure.php';

$columnDelimiter = ';';
$usersCount      = 100;
$exportFilesDir  = 'backend/files/export_users/';
$filename        = 'users.csv';

$columnsNames = [
    'name'       => 'Имя',
    'email'      => 'Email',
    'phone'      => 'Телефон',
    'address'    => 'Адрес',
    'group_name' => 'Группа',
    'discount'   => 'Скидка',
    'created'    => 'Дата',
    'last_ip'    => 'Последний IP'
];

/** @var Database $db */
$db = $DI->get(Database::class);

/** @var QueryFactory $queryFactory */
$queryFactory = $DI->get(QueryFactory::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var UsersEntity $usersEntity */
$usersEntity         = $entityFactory->get(UsersEntity::class);

if (!$managers->access('users', $managersEntity->get($_SESSION['admin']))) {
    exit();
}

// Эксель кушает только 1251
setlocale(LC_ALL, 'ru_RU.1251');
$sqlQuery = $queryFactory->newSqlQuery()->setStatement('SET NAMES cp1251');
$db->query($sqlQuery);

// Страница, которую экспортируем
$page = $request->get('page');
if(empty($page) || $page==1) {
    $page = 1;
    if(is_writable($exportFilesDir.$filename)) {
        unlink($exportFilesDir.$filename);
    }
}

// Открываем файл экспорта на добавление
$f = fopen($exportFilesDir.$filename, 'ab');

// Если начали сначала - добавим в первую строку названия колонок
if($page == 1) {
    fputcsv($f, $columnsNames, $columnDelimiter);
}

$filter = [];
$filter['page'] = $page;
$filter['limit'] = $usersCount;
if($request->get('group_id')) {
    $filter['group_id'] = intval($request->get('group_id'));
}
$filter['sort'] = $request->get('sort');

// Выбираем пользователей
$users = array();
foreach($usersEntity->find($filter) as $u) {
    $str = array();
    foreach($columnsNames as $n=>$c) {
        $str[] = $u->$n;
    }
    fputcsv($f, $str, $columnDelimiter);
}

$totalUsers = $usersEntity->count($filter);

if($usersCount*$page < $totalUsers) {
    $data = ['end'=>false, 'page'=>$page, 'totalpages'=>$totalUsers/$usersCount];
} else {
    $data = ['end'=>true, 'page'=>$page, 'totalpages'=>$totalUsers/$usersCount];
}

fclose($f);

if ($data) {
    $response->setContent(json_encode($data), RESPONSE_JSON)->sendContent();
}
