<?php


use Okay\Entities\ReportStatEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Core\QueryFactory;
use Okay\Core\Managers;
use Okay\Core\Database;
use Okay\Core\Response;

require_once 'configure.php';

/*����(�������) ��� ����� ��������*/
$columnsNames = [
    'category_name' => '���������',
    'product_name'  => '�������� ������',
    'sum_price'     => '�����',
    'amount'        => '����������'
];

$columnDelimiter = ';';
$statCount       = 100;
$exportFilesDir = 'backend/files/export/';
$filename         = 'export_stat_products.csv';


/** @var Database $db */
$db = $DI->get(Database::class);

/** @var QueryFactory $queryFactory */
$queryFactory = $DI->get(QueryFactory::class);

/** @var Managers $managers */
$managers = $DI->get(Managers::class);

/** @var Response $response */
$response = $DI->get(Response::class);

/** @var ReportStatEntity $reportStatEntity */
$reportStatEntity     = $entityFactory->get(ReportStatEntity::class);

/** @var CategoriesEntity $categoriesEntity */
$categoriesEntity     = $entityFactory->get(CategoriesEntity::class);


if (!$managers->access('stats', $managersEntity->get($_SESSION['admin']))) {
    exit();
}

// ������ ������ ������ 1251
setlocale(LC_ALL, 'ru_RU.1251');
$sqlQuery = $queryFactory->newSqlQuery()->setStatement('SET NAMES cp1251');
$db->query($sqlQuery);

// ��������, ������� ������������
$page = $request->get('page');
if (empty($page) || $page==1) {
    $page = 1;
    // ���� ������ ������� - ������ ������ ���� ��������
    if (is_writable($exportFilesDir.$filename)) {
        unlink($exportFilesDir.$filename);
    }
}

// ��������� ���� �������� �� ����������
$f = fopen($exportFilesDir.$filename, 'ab');

// ���� ������ ������� - ������� � ������ ������ �������� �������
if ($page == 1) {
    fputcsv($f, $columnsNames, $columnDelimiter);
}

$filter = [];
$dateFilter = $request->get('date_filter');
if (!empty($dateFilter)) {
    $filter['date_filter'] = $dateFilter;
}

if($request->get('date_from') || $request->get('date_to')) {
    $dateFrom = $request->get('date_from');
    $dateTo = $request->get('date_to');
}

if($request->get('category')){
    $filter['category_id'] = $request->get('category');
}

if (!empty($dateFrom)) {
    $filter['date_from'] = date("Y-m-d 00:00:01", strtotime($dateFrom));
}
if (!empty($dateTo)) {
    $filter['date_to'] = date("Y-m-d 23:59:00", strtotime($dateTo));
}
$status = $request->get('status', 'integer');
if (!empty($status)) {
    $filter['status'] = $status;
}

$sortProd = $request->get('sort_prod');
if (!empty($sortProd)) {
    $filter['sort_prod'] = $sortProd;
} else {
    $filter['sort_prod'] = 'price';
}

$filter['page'] = max(1, $request->get('page', 'integer'));
$filter['limit'] = 40;

$temp_filter = $filter;
unset($temp_filter['limit']);
unset($temp_filter['page']);
$totalCount = $reportStatEntity->count($temp_filter);

if($request->get('page') == 'all') {
    $filter['limit'] = $totalCount;
}

$totalSum = 0;
$totalAmount = 0;
$reportStatPurchases = $reportStatEntity->find($filter);
$catFilter = $request->get('category');

if (!empty($filter['category_id'])) {
    $cat = $categoriesEntity->get(intval($filter['category_id']));
}

foreach ($reportStatPurchases as $id=>$r) {
    if (!empty($r->product_id)) {
        $tmpCat = $categoriesEntity->find(['product_id' => $r->product_id]);
        $tmpCat = reset($tmpCat);

        if (!empty($catFilter) && !in_array($catFilter,(array)$tmpCat->path[$cat->level_depth-1]->children)) {
            unset($reportStatPurchases[$id]);
        } else {
            $reportStatPurchases[$id]->category_name = $tmpCat->name;
        }
    }
}

foreach ($reportStatPurchases as $u) {
    $totalSum += $u->sum_price;
    $totalAmount += $u->amount;
    $str = [];
    foreach($columnsNames as $n=>$c) {
        $str[] = $u->$n;
    }
    fputcsv($f, $str, $columnDelimiter);
}

$total = [
    'category_name' => '�����',
    'product_name'  => ' ',
    'price'         => $totalSum,
    'amount'        => $totalAmount
];

fputcsv($f, $total, $columnDelimiter);
fclose($f);

if ($statCount*$page < $totalCount) {
    $data = ['end'=>false, 'page'=>$page, 'totalpages'=>$totalCount/$statCount];
} else {
    $data = ['end'=>true, 'page'=>$page, 'totalpages'=>$totalCount/$statCount];
}

if ($data) {
    $response->setContent(json_encode($data), RESPONSE_JSON)->sendContent();
}
