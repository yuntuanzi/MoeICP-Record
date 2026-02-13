<?php
// ========== 缓存核心配置 ==========
$cacheFile = 'icp_cache.txt';  // 缓存文件路径
$cacheExpire = 86400;          // 缓存有效期：86400秒 = 1天
$cacheData = null;             // 缓存数据
$isCacheValid = false;         // 缓存是否有效

// ========== 读取缓存逻辑 ==========
// 检查缓存文件是否存在且未过期
if (file_exists($cacheFile)) {
    $cacheStat = stat($cacheFile);
    $cacheCreateTime = $cacheStat['mtime'];
    $currentTime = time();
    
    // 判断缓存是否在有效期内（1天）
    if ($currentTime - $cacheCreateTime < $cacheExpire) {
        // 读取缓存内容
        $cacheContent = file_get_contents($cacheFile);
        $cacheData = json_decode($cacheContent, true);
        
        // 验证缓存数据格式是否正确
        if ($cacheData && isset($cacheData['timestamp']) && isset($cacheData['html'])) {
            $isCacheValid = true;
        }
    }
}

// ========== 缓存失效/不存在时重新生成 ==========
if (!$isCacheValid) {
    // 连接SQLite数据库
    $dbFile = 'icp_records.db';
    $db = null;
    $hasData = false;
    $years = [];
    $yearsData = [];
    $totalRecords = 0;
    $lastUpdate = '暂无数据';
    
    // 检查数据库文件是否存在
    if (file_exists($dbFile)) {
        $db = new SQLite3($dbFile);
        $db->exec("PRAGMA encoding = 'UTF-8'");
        
        // 获取总记录数
        $res = $db->query("SELECT COUNT(*) as total FROM icp_records");
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $totalRecords = $row['total'];
        
        // 获取最后更新时间（取最新的update_time）
        $res = $db->query("SELECT update_time FROM icp_records WHERE update_time != '' ORDER BY update_time DESC LIMIT 1");
        $row = $res->fetchArray(SQLITE3_ASSOC);
        if ($row && $row['update_time']) {
            $lastUpdate = $row['update_time'];
        } else {
            // 如果没有update_time，取文件修改时间
            $lastUpdate = date('Y-m-d H:i:s', filemtime($dbFile));
        }
        
        // 获取所有有数据的年份（去重并排序）
        $res = $db->query("SELECT DISTINCT year FROM icp_records ORDER BY year ASC");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $year = (int)$row['year'];
            $years[] = $year;
            
            // 获取该年份下的备案号范围
            $res2 = $db->query("SELECT MIN(number) as min_num, MAX(number) as max_num FROM icp_records WHERE year = {$year}");
            $range = $res2->fetchArray(SQLITE3_ASSOC);
            $yearsData[$year] = [
                'min' => (int)$range['min_num'],
                'max' => (int)$range['max_num']
            ];
            
            // 获取该年份下每个分段的记录数
            for ($prefix = 0; $prefix <= 9; $prefix++) {
                $start = $prefix * 1000;
                $end = $prefix * 1000 + 999;
                $res3 = $db->query("SELECT COUNT(*) as cnt FROM icp_records WHERE year = {$year} AND number BETWEEN {$start} AND {$end}");
                $cntRow = $res3->fetchArray(SQLITE3_ASSOC);
                $yearsData[$year]['prefixes'][$prefix] = $cntRow['cnt'];
            }
        }
        
        $hasData = !empty($years);
        if ($db) $db->close();
    }
    
    // 生成年份列表HTML
    ob_start(); // 开启输出缓冲区
    ?>
    <!-- 动态生成的年份列表内容 -->
    <?php if ($hasData): ?>
        <?php foreach ($years as $year): ?>
            <?php 
            $minNum = $yearsData[$year]['min'];
            $maxNum = $yearsData[$year]['max'];
            ?>
            <div class="year-section">
                <div class="year-title"><?=$year?>年（备案号范围：<?=sprintf('%04d', $minNum)?>-<?=sprintf('%04d', $maxNum)?>）</div>
                <div class="number-grid">
                    <?php for ($prefix = 0; $prefix <= 9; $prefix++): ?>
                        <?php
                        $start = $prefix * 1000;
                        $end = $prefix * 1000 + 999;
                        $cnt = $yearsData[$year]['prefixes'][$prefix];
                        ?>
                        <div class="number-item">
                            <a href="detail.php?year=<?=$year?>&prefix=<?=$prefix?>" 
                               style="<?= $cnt == 0 ? 'color:#999;cursor:not-allowed;' : '' ?>">
                                <?=sprintf('%04d', $start)?>-<?=sprintf('%04d', $end)?>
                                <?php if ($cnt > 0): ?><br>
                                    <small>(<?=$cnt?>条)</small>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-data">
            数据库中暂无备案数据，请先执行爬虫脚本爬取数据后再访问本页面。
        </div>
    <?php endif; ?>
    <?php
    $yearListHtml = ob_get_clean(); // 获取缓冲区内容并关闭
    
    // 组装缓存数据（包含时间戳和核心数据）
    $cacheData = [
        'timestamp' => time(),                  // 缓存生成时间戳（秒）
        'timestamp_str' => date('Y-m-d H:i:s'), // 缓存生成时间字符串
        'total_records' => $totalRecords,       // 总记录数
        'last_update' => $lastUpdate,           // 数据最后更新时间
        'years' => $years,                      // 有数据的年份列表
        'html' => $yearListHtml                 // 年份列表HTML
    ];
    
    // 写入缓存文件（JSON格式，便于读取）
    file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_UNICODE));
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>萌ICP备备案记录</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; color: #333; }
        .search-box {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            outline: none;
        }
        .search-input:focus {
            border-color: #5698c3;
            box-shadow: 0 0 0 2px rgba(86, 152, 195, 0.2);
        }
        .search-btn {
            padding: 10px 30px;
            background: #5698c3;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-btn:hover {
            background: #4587b2;
        }
        .search-tips {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: -10px 0 20px 0;
        }
        
        .year-section { margin: 30px 0; }
        .year-title { font-size: 24px; color: #5698c3; border-bottom: 2px solid #5698c3; padding: 10px 0; }
        .number-grid { display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px; margin: 10px 0; }
        .number-item { padding: 10px; text-align: center; background: #f0f0f0; border-radius: 4px; }
        .number-item a { text-decoration: none; color: #333; }
        .number-item a:hover { color: #5698c3; }
        .stats { text-align: center; margin: 20px 0; color: #666; }
        .cache-info {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin: -15px 0 20px 0;
        }
        .search-result {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: none;
        }
        .result-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .result-count {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .result-empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        .result-list {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            table-layout: fixed;
        }
        .result-list th, .result-list td {
            padding: 8px 10px;
            border: 1px solid #eee;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .result-list th {
            background: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .result-list tr:hover {
            background: #f9f9f9;
        }
        .result-list th:nth-child(1),.result-list td:nth-child(1){width:12%;}
        .result-list th:nth-child(2),.result-list td:nth-child(2){width:28%;}
        .result-list th:nth-child(3),.result-list td:nth-child(3){width:20%;}
        .result-list th:nth-child(4),.result-list td:nth-child(4){width:15%;}
        .result-list th:nth-child(5),.result-list td:nth-child(5){width:10%;}
        .result-list th:nth-child(6),.result-list td:nth-child(6){width:15%;}
        .result-link {
            color: #5698c3;
            text-decoration: none;
            white-space: nowrap;
        }
        .result-link:hover {
            text-decoration: underline;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-btn:hover {
            background: #e0e0e0;
        }
        .footer {
            margin-top: 50px;
            padding: 20px 0;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        .footer-info {
            margin: 5px 0;
        }
        .empty-data {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>萌备现存备案记录(全量备份)</h1>
        
        <!-- 动态统计信息（从缓存读取） -->
        <div class="stats">
            <?php if (!empty($cacheData['years'])): ?>
                数据范围：<?=implode('-', $cacheData['years'])?>年 | 总记录数：<?=$cacheData['total_records']?> | 最后更新：<?=$cacheData['last_update']?>
            <?php else: ?>
                暂无爬取数据，请先运行爬虫脚本获取数据
            <?php endif; ?>
            <p>By ❤️讨喜团子 937319686</p>
        </div>
        
        <!-- 缓存信息提示 -->
        <div class="cache-info">
            缓存更新时间：<?=$cacheData['timestamp_str']?> | 缓存有效期：1天（自动刷新）
        </div>
        
        <!-- 搜索框 -->
        <div class="search-box">
            <input type="text" class="search-input" id="searchInput" placeholder="支持模糊查询：备案号、站点名称、所有者、域名" autocomplete="off">
            <button class="search-btn" id="searchBtn">查询</button>
        </div>
        <div class="search-tips">提示：输入关键词，支持模糊匹配</div>
        
        <!-- 搜索结果展示区 -->
        <div class="search-result" id="searchResult">
            <div class="result-title">备案信息查询结果</div>
            <div class="result-count" id="resultCount"></div>
            <div id="resultContent"></div>
            <a href="javascript:hideResult()" class="back-btn">返回列表</a>
        </div>
        
        <!-- 年份列表（从缓存读取HTML） -->
        <?=$cacheData['html']?>
        
        <!-- 页脚 -->
        <div class="footer">
            <div class="footer-info">作者：讨喜团子</div>
            <div class="footer-info">QQ：937319686</div>
        </div>
    </div>

    <script>
        function showResult() {
            document.getElementById('searchResult').style.display = 'block';
            document.querySelectorAll('.year-section').forEach(section => {
                section.style.display = 'none';
            });
            document.querySelector('.stats').style.display = 'none';
            document.querySelector('.search-tips').style.display = 'none';
            document.querySelector('.cache-info').style.display = 'none';
            document.querySelector('.footer').style.display = 'none';
        }
        
        function hideResult() {
            document.getElementById('searchResult').style.display = 'none';
            document.getElementById('searchInput').value = '';
            document.querySelectorAll('.year-section').forEach(section => {
                section.style.display = 'block';
            });
            document.querySelector('.stats').style.display = 'block';
            document.querySelector('.search-tips').style.display = 'block';
            document.querySelector('.cache-info').style.display = 'block';
            document.querySelector('.footer').style.display = 'block';
        }
        
        function htmlEscape(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;')
                      .replace(/</g, '&lt;')
                      .replace(/>/g, '&gt;')
                      .replace(/"/g, '&quot;')
                      .replace(/'/g, '&#39;');
        }
        
        function handleSearch() {
            const keyword = document.getElementById('searchInput').value.trim();
            if (!keyword) {
                alert('请输入查询关键词！');
                return;
            }
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search.php?keyword=' + encodeURIComponent(keyword), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        const result = JSON.parse(xhr.responseText);
                        showResult();
                        const countEl = document.getElementById('resultCount');
                        const contentEl = document.getElementById('resultContent');
                        
                        if (result.code === 0 && result.data.length > 0) {
                            const total = result.data.length;
                            countEl.textContent = `共找到 ${total} 条匹配记录`;
                            let html = '<table class="result-list">';
                            html += `
                                <tr>
                                    <th>备案号</th>
                                    <th>网站名称</th>
                                    <th>域名</th>
                                    <th>所有者</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            `;
                            
                            result.data.forEach(item => {
                                html += `
                                    <tr>
                                        <td>${htmlEscape(item.record_number)}</td>
                                        <td>${htmlEscape(item.site_name)}</td>
                                        <td>${htmlEscape(item.domain)}</td>
                                        <td>${htmlEscape(item.owner)}</td>
                                        <td>${htmlEscape(item.status)}</td>
                                        <td><a href="record_detail.php?id=${item.id}" class="result-link">查看详情</a></td>
                                    </tr>
                                `;
                            });
                            
                            html += '</table>';
                            contentEl.innerHTML = html;
                        } else {
                            countEl.textContent = '';
                            contentEl.innerHTML = `<div class="result-empty">未找到与「${keyword}」匹配的备案信息</div>`;
                        }
                    } else {
                        alert('查询失败，请重试！');
                    }
                }
            };
            xhr.send();
        }
        
        document.getElementById('searchBtn').addEventListener('click', handleSearch);
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    </script>
</body>
</html>
