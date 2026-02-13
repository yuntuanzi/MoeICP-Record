<?php
$db = new SQLite3("icp_records.db");
$year = isset($_GET["year"]) ? intval($_GET["year"]) : 2023;
$prefix = isset($_GET["prefix"]) ? intval($_GET["prefix"]) : 0;
$start = $prefix * 1000;
$end = ($prefix + 1) * 1000 - 1;
$stmt = $db->prepare("
    SELECT * FROM icp_records 
    WHERE year = :year AND number BETWEEN :start AND :end
    ORDER BY number ASC
");
$stmt->bindValue(":year", $year);
$stmt->bindValue(":start", $start);
$stmt->bindValue(":end", $end);
$result = $stmt->execute();

$records = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $year; ?>年 <?php echo $prefix; ?>000-<?php echo $prefix; ?>999 - 萌国ICP备案</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .back { margin: 20px 0; }
        .back a { text-decoration: none; color: #5698c3; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f0f0f0; }
        tr:hover { background-color: #f9f9f9; }
        .detail-link { color: #5698c3; text-decoration: none; }
        .empty { text-align: center; padding: 50px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back">
            <a href="index.php">← 返回首页</a>
        </div>
        <h1><?php echo $year; ?>年 备案号 <?php echo $prefix; ?>000-<?php echo $prefix; ?>999</h1>
        
        <?php if (empty($records)): ?>
            <div class="empty">暂无备案数据</div>
        <?php else: ?>
            <table>
                <tr>
                    <th>备案号</th>
                    <th>网站名称</th>
                    <th>域名</th>
                    <th>所有者</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo $record["record_number"]; ?></td>
                    <td><?php echo htmlspecialchars($record["site_name"]); ?></td>
                    <td><?php echo htmlspecialchars($record["domain"]); ?></td>
                    <td><?php echo htmlspecialchars($record["owner"]); ?></td>
                    <td><?php echo htmlspecialchars($record["status"]); ?></td>
                    <td>
                        <a href="record_detail.php?id=<?php echo $record["id"]; ?>" class="detail-link">查看详情</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>