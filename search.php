<?php
header('Content-Type: application/json; charset=utf-8');

$dbFile = 'icp_records.db';
if (!file_exists($dbFile)) {
    echo json_encode([
        'code' => 1,
        'msg' => '数据库文件不存在',
        'data' => []
    ]);
    exit;
}

$db = new SQLite3($dbFile);
$db->exec("PRAGMA encoding = 'UTF-8'");

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (!$keyword) {
    echo json_encode([
        'code' => 1,
        'msg' => '请输入查询关键词',
        'data' => []
    ]);
    exit;
}

$stmt = $db->prepare("
    SELECT * FROM icp_records 
    WHERE record_number LIKE :keyword 
    OR site_name LIKE :keyword 
    OR owner LIKE :keyword 
    OR domain LIKE :keyword 
    ORDER BY record_number ASC
");
$searchKeyword = "%" . $keyword . "%";
$stmt->bindValue(':keyword', $searchKeyword, SQLITE3_TEXT);

$result = $stmt->execute();
$records = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}

if (!empty($records)) {
    echo json_encode([
        'code' => 0,
        'msg' => '查询成功',
        'data' => $records
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'code' => 1,
        'msg' => '未找到匹配记录',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}

$db->close();
?>