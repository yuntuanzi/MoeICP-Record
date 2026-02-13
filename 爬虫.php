<?php
set_time_limit(0);
ignore_user_abort(1);
error_reporting(E_ALL & ~E_NOTICE);

// 核心配置
$dbFile = 'icp_records.db';     // SQLite数据库文件
$jsonFile = 'icp_records.json'; // JSON备份文件
$startYear = 2019;              // 起始年份
$endYear = 2026;                // 结束年份
$batchSize = 100;               // 并发数（提速关键）

// ========== 初始化数据库（仅保留核心表结构） ==========
$db = new SQLite3($dbFile);
// 优化SQLite性能
$db->exec('PRAGMA journal_mode = WAL; PRAGMA synchronous = NORMAL; PRAGMA cache_size = -102400;');

// 创建核心数据表（仅保留必要字段）
$db->exec("
CREATE TABLE IF NOT EXISTS icp_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    record_number TEXT UNIQUE NOT NULL,
    year INTEGER NOT NULL,
    number INTEGER NOT NULL,
    site_name TEXT,
    domain TEXT,
    homepage TEXT,
    site_info TEXT,
    icp_number TEXT,
    owner TEXT,
    update_time TEXT,
    status TEXT
);");

// ========== 断点续爬核心逻辑 ==========
// 读取已爬取的备案号，生成完成列表
$completed = [];
$result = $db->query("SELECT record_number FROM icp_records");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $completed[$row['record_number']] = true;
}
$alreadyCompleted = count($completed);
echo "=== 断点检测 ===\n";
echo "已爬取完成的记录数：{$alreadyCompleted}\n";

// 初始化JSON文件
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_UNESCAPED_UNICODE));
}
$jsonData = json_decode(file_get_contents($jsonFile), true);

// 生成待爬取任务队列（跳过已完成的记录）
$queue = [];
for ($year = $startYear; $year <= $endYear; $year++) {
    for ($number = 0; $number <= 9999; $number++) {
        $recordNumber = sprintf('%04d%04d', $year, $number);
        if (!isset($completed[$recordNumber])) {
            $queue[] = [$year, $number, $recordNumber];
        }
    }
}

// 统计信息
$totalTasks = count($queue);
$totalAll = ($endYear - $startYear + 1) * 10000;
$done = 0;
$success = 0;
$failed = 0;
$startTime = microtime(true);

echo "=== 萌ICP备案爬虫启动 ===\n";
echo "爬取范围：{$startYear}-{$endYear}年 | 备案号：0000-9999\n";
echo "总任务数：{$totalAll} | 已完成：{$alreadyCompleted} | 待爬取：{$totalTasks}\n";
echo "数据将保存到：{$dbFile} 和 {$jsonFile}\n";
echo "===========================\n\n";

// ========== 高并发爬取核心逻辑 ==========
while (!empty($queue)) {
    // 取出当前批次任务
    $batch = array_splice($queue, 0, $batchSize);
    $mh = curl_multi_init();
    $handles = [];

    // 创建curl多线程句柄
    foreach ($batch as $item) {
        list($year, $number, $recordNumber) = $item;
        $url = "https://icp.gov.moe/?keyword={$recordNumber}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => ['Accept: text/html', 'Connection: close'],
            CURLOPT_PRIVATE => json_encode(['year' => $year, 'number' => $number, 'record' => $recordNumber])
        ]);
        
        curl_multi_add_handle($mh, $ch);
        $handles[] = $ch;
    }

    // 执行多线程请求
    $active = null;
    do {
        curl_multi_exec($mh, $active);
        curl_multi_select($mh, 0.2);
    } while ($active > 0);

    // 处理爬取结果
    $batchSaveData = [];
    foreach ($handles as $ch) {
        // 获取请求信息
        $html = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $privateData = json_decode(curl_getinfo($ch, CURLINFO_PRIVATE), true);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);

        $done++;
        $year = $privateData['year'];
        $number = $privateData['number'];
        $recordNumber = $privateData['record'];
        $data = null;

        // 验证请求是否成功
        if ($httpCode == 200 && strlen($html) > 200) {
            // 解析HTML内容
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // 提取核心数据
            $getField = function ($label) use ($xpath) {
                $nodes = $xpath->query("//div[text()='{$label}']/following-sibling::div");
                return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : '';
            };

            $siteName = $getField('网站名称');
            $icpNumber = $getField('萌备案号');

            // 仅保存有有效数据的记录
            if ($siteName || $icpNumber) {
                $data = [
                    'record_number' => $recordNumber,
                    'year' => $year,
                    'number' => $number,
                    'site_name' => $siteName,
                    'domain' => $getField('网站域名'),
                    'homepage' => trim(strip_tags($getField('网站首页'))),
                    'site_info' => $getField('网站信息'),
                    'icp_number' => $icpNumber,
                    'owner' => $getField('所有者'),
                    'update_time' => $getField('更新时间'),
                    'status' => trim(preg_replace('/\s+|反馈|❗+/u', '', $getField('状态')))
                ];
                $batchSaveData[$recordNumber] = $data;
                $success++;
            } else {
                $failed++;
            }
        } else {
            $failed++;
        }
    }

    // ========== 批量保存数据 ==========
    if (!empty($batchSaveData)) {
        // 批量写入数据库（高效）
        $stmt = $db->prepare("INSERT OR REPLACE INTO icp_records 
            (record_number,year,number,site_name,domain,homepage,site_info,icp_number,owner,update_time,status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");

        foreach ($batchSaveData as $d) {
            $stmt->bindValue(1, $d['record_number']);
            $stmt->bindValue(2, $d['year']);
            $stmt->bindValue(3, $d['number']);
            $stmt->bindValue(4, $d['site_name']);
            $stmt->bindValue(5, $d['domain']);
            $stmt->bindValue(6, $d['homepage']);
            $stmt->bindValue(7, $d['site_info']);
            $stmt->bindValue(8, $d['icp_number']);
            $stmt->bindValue(9, $d['owner']);
            $stmt->bindValue(10, $d['update_time']);
            $stmt->bindValue(11, $d['status']);
            $stmt->execute();
        }

        // 批量更新JSON文件
        $jsonData = array_merge($jsonData, $batchSaveData);
        file_put_contents($jsonFile, json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    // 实时输出进度
    $progress = round(($done / $totalTasks) * 100, 2);
    $speed = round($done / (microtime(true) - $startTime), 2);
    $remaining = $totalTasks - $done;
    echo "\r进度：{$done}/{$totalTasks} ({$progress}%) | 成功：{$success} | 失败：{$failed} | 速度：{$speed}/秒 | 剩余：{$remaining}";
}

// ========== 爬取完成统计 ==========
curl_multi_close($mh);
$db->close();

$totalTime = round(microtime(true) - $startTime, 2);
$totalSuccessAll = $alreadyCompleted + $success;

echo "\n\n=== 爬取完成 ===\n";
echo "总耗时：{$totalTime} 秒\n";
echo "累计完成：{$totalSuccessAll}/{$totalAll}\n";
echo "本次新增：成功{$success}条 | 失败{$failed}条\n";
echo "数据已保存至：\n- SQLite数据库：{$dbFile}\n- JSON文件：{$jsonFile}\n";
echo "===========================\n";
?>