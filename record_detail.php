
<?php
$db = new SQLite3("icp_records.db");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

$stmt = $db->prepare("SELECT * FROM icp_records WHERE id = :id");
$stmt->bindValue(":id", $id);
$result = $stmt->execute();
$record = $result->fetchArray(SQLITE3_ASSOC);

if (!$record) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($record["site_name"]); ?> - 萌国ICP备案详情</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .back { margin: 20px 0; }
        .back a { text-decoration: none; color: #5698c3; }
        .record-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #fff; }
        .record-title { font-size: 24px; color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #5698c3; }
        .info-item { display: flex; margin: 15px 0; }
        .label { width: 120px; font-weight: bold; color: #666; }
        .value { flex: 1; color: #333; }
        a { color: #5698c3; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back">
            <a href="javascript:history.back()">← 返回上一页</a>
        </div>
        
        <div class="record-card">
            <div class="record-title"><?php echo htmlspecialchars($record["site_name"]); ?></div>
            
            <div class="info-item">
                <div class="label">备案编号：</div>
                <div class="value"><?php echo $record["record_number"]; ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">萌备案号：</div>
                <div class="value"><?php echo htmlspecialchars($record["icp_number"]); ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">网站域名：</div>
                <div class="value"><?php echo htmlspecialchars($record["domain"]); ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">网站首页：</div>
                <div class="value">
                    <a href="https://<?php echo htmlspecialchars($record["homepage"]); ?>" target="_blank">
                        <?php echo htmlspecialchars($record["homepage"]); ?>
                    </a>
                </div>
            </div>
            
            <div class="info-item">
                <div class="label">网站信息：</div>
                <div class="value"><?php echo htmlspecialchars($record["site_info"]); ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">所有者：</div>
                <div class="value"><?php echo htmlspecialchars($record["owner"]); ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">更新时间：</div>
                <div class="value"><?php echo htmlspecialchars($record["update_time"]); ?></div>
            </div>
            
            <div class="info-item">
                <div class="label">状态：</div>
                <div class="value"><?php echo htmlspecialchars($record["status"]); ?></div>
            </div>
        </div>
    </div>
</body>
</html>