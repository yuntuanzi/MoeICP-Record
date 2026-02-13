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
        }
        .result-list th, .result-list td {
            padding: 12px 15px;
            border: 1px solid #eee;
            text-align: left;
        }
        .result-list th {
            background: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .result-list tr:hover {
            background: #f9f9f9;
        }
        .result-link {
            color: #5698c3;
            text-decoration: none;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>萌备现存备案记录(全量备份)</h1>
        <div class="stats">
            数据范围：2023-2026年 | 备案号：0000-9999 | 最后更新：2026-02-13 15:56:59
            <p>By ❤️讨喜团子 937319686
        </div>
        <div class="search-box">
            <input type="text" class="search-input" id="searchInput" placeholder="支持模糊查询：备案号、站点名称、所有者、域名" autocomplete="off">
            <button class="search-btn" id="searchBtn">查询</button>
        </div>
        <div class="search-tips">提示：输入关键词，支持模糊匹配</div>
        <div class="search-result" id="searchResult">
            <div class="result-title">备案信息查询结果</div>
            <div class="result-count" id="resultCount"></div>
            <div id="resultContent"></div>
            <a href="javascript:hideResult()" class="back-btn">返回列表</a>
        </div>
        <div class="year-section">
            <div class="year-title">2023年</div>
            <div class="number-grid">
                <div class="number-item"><a href="detail.php?year=2023&prefix=0">0000-0999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=1">1000-1999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=2">2000-2999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=3">3000-3999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=4">4000-4999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=5">5000-5999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=6">6000-6999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=7">7000-7999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=8">8000-8999</a></div>
                <div class="number-item"><a href="detail.php?year=2023&prefix=9">9000-9999</a></div>
            </div>
        </div>
        <div class="year-section">
            <div class="year-title">2024年</div>
            <div class="number-grid">
                <div class="number-item"><a href="detail.php?year=2024&prefix=0">0000-0999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=1">1000-1999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=2">2000-2999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=3">3000-3999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=4">4000-4999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=5">5000-5999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=6">6000-6999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=7">7000-7999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=8">8000-8999</a></div>
                <div class="number-item"><a href="detail.php?year=2024&prefix=9">9000-9999</a></div>
            </div>
        </div>
        <div class="year-section">
            <div class="year-title">2025年</div>
            <div class="number-grid">
                <div class="number-item"><a href="detail.php?year=2025&prefix=0">0000-0999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=1">1000-1999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=2">2000-2999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=3">3000-3999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=4">4000-4999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=5">5000-5999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=6">6000-6999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=7">7000-7999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=8">8000-8999</a></div>
                <div class="number-item"><a href="detail.php?year=2025&prefix=9">9000-9999</a></div>
            </div>
        </div>
        <div class="year-section">
            <div class="year-title">2026年</div>
            <div class="number-grid">
                <div class="number-item"><a href="detail.php?year=2026&prefix=0">0000-0999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=1">1000-1999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=2">2000-2999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=3">3000-3999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=4">4000-4999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=5">5000-5999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=6">6000-6999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=7">7000-7999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=8">8000-8999</a></div>
                <div class="number-item"><a href="detail.php?year=2026&prefix=9">9000-9999</a></div>
            </div>
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
        }
        function hideResult() {
            document.getElementById('searchResult').style.display = 'none';
            document.getElementById('searchInput').value = '';
            document.querySelectorAll('.year-section').forEach(section => {
                section.style.display = 'block';
            });
            document.querySelector('.stats').style.display = 'block';
            document.querySelector('.search-tips').style.display = 'block';
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