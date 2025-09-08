<?php
//game_list.php
// 该页面为游戏列表展示页，用于查询并展示数据库中所有游戏信息，支持按发布时间倒序排列，提供新游戏上传入口

session_start();
require_once '../module/sql_connet.php';
include_once '../page/nav.php';
try {
    // 查询所有游戏数据并按发布时间倒序排列
    $query = "SELECT * FROM games ORDER BY release_date DESC";
    $result = mysqli_query($conn, $query) or throw new Exception("查询失败: " . mysqli_error($conn));
    
    $games = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // 生成描述摘要（截取前60个字符并过滤HTML标签）
        $row['short_desc'] = mb_substr(strip_tags($row['description']), 0, 60) . '...';
        $games[] = $row;
    }
    mysqli_free_result($result);

} catch (Exception $e) {
    // 记录错误日志并显示友好提示
    error_log(date('[Y-m-d H:i:s]') . " 数据库错误: " . $e->getMessage() . PHP_EOL, 3, "errors.log");
    die("系统暂时不可用，请稍后重试");
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>游戏列表</title>
    <style>
        :root {
            --primary-color: #2c6cbf;
            --hover-color: #245fa3;
            --card-bg: #ffffff;
            --tag-bg: #e9f2ff;
            --tag-text: #2c6cbf;
            --text-color: #333333;
            --subtext-color: #666666;
            --border-color: #e0e8f7;
            --shadow: 0 3px 6px rgba(0,0,0,0.1);
            --shadow-hover: 0 8px 15px rgba(0,0,0,0.15);
        }

        body {
            font-family: 'Microsoft YaHei', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 30px;
        }

        .container { max-width: 1400px; margin: 0 auto; }
        
        .header-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
            padding: 20px; 
            background: var(--card-bg); 
            border-radius: 12px; 
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .header-bar:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .game-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px; 
            padding: 0 15px; 
        }
        
        .game-card { 
            background: var(--card-bg); 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: var(--shadow); 
            transition: transform 0.2s, box-shadow 0.2s; 
            display: flex; 
            flex-direction: column;
            border: 1px solid var(--border-color);
            cursor: pointer; /* 添加指针样式 */
        }
        
        .game-card:hover { 
            transform: translateY(-5px); 
            box-shadow: var(--shadow-hover);
            border-color: rgba(44, 108, 191, 0.3);
        }
        
        .card-image { 
            width: 100%; 
            height: 200px; 
            object-fit: cover; 
            border-bottom: 3px solid var(--primary-color);
            transition: transform 0.3s ease;
        }
        
        .game-card:hover .card-image {
            transform: scale(1.02);
        }
        
        .card-content { 
            padding: 20px; 
            flex-grow: 1;
        }
        
        .card-title { 
            font-size: 1.2rem; 
            margin: 0 0 15px; 
            color: var(--text-color);
            line-height: 1.3;
        }
        
        .card-meta { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 15px; 
            font-size: 0.9rem; 
        }
        
        .platform-tag { 
            background: var(--primary-color); 
            color: white; 
            padding: 4px 12px; 
            border-radius: 15px; 
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .release-date { 
            color: var(--subtext-color); 
        }
        
        .game-info {
            margin-bottom: 15px;
        }
        
        .game-info p {
            margin: 8px 0;
            color: var(--text-color);
        }
        
        .game-info strong {
            color: var(--primary-color);
            display: inline-block;
            width: 70px;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 8px;
        }
        
        .tag {
            background: var(--tag-bg);
            color: var(--tag-text);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .card-desc { 
            color: var(--subtext-color); 
            font-size: 0.95rem; 
            line-height: 1.5; 
            margin-top: 15px;
        }
        
        .upload-btn { 
            background: var(--primary-color); 
            color: white !important; 
            padding: 12px 25px; 
            border-radius: 8px; 
            text-decoration: none; 
            transition: background 0.2s, transform 0.2s; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px;
            box-shadow: 0 2px 5px rgba(44, 108, 191, 0.2);
        }
        
        .upload-btn:hover { 
            background: var(--hover-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(44, 108, 191, 0.3);
        }
        
        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            grid-column: 1 / -1; 
        }
        
        @media (max-width: 768px) { 
            .game-grid { grid-template-columns: 1fr; gap: 20px; } 
            .header-bar { flex-direction: column; gap: 20px; text-align: center; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>游戏列表</h1>
            <a href="game_upload.php" class="upload-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="margin-right:5px;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                上传新游戏
            </a>
        </div>

        <div class="game-grid">
            <?php if (count($games) > 0): ?>
                <?php foreach ($games as $game): ?>
                <article class="game-card" onclick="window.open('../page/game.php?id=<?= $game['id'] ?>', '_blank')">
                    <?php // 条件渲染游戏封面，无封面时显示占位图 ?>
                    <?php if (!empty($game['cover_image'])): ?>
                    <img src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" class="card-image" alt="<?= htmlspecialchars($game['title']) ?>封面">
                    <?php else: ?>
                    <div class="card-image" style="background: #eaeef3; display: flex; align-items: center; justify-content: center;">
                        <svg width="60" height="60" fill="#ccc" viewBox="0 0 24 24"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z"/></svg>
                    </div>
                    <?php endif; ?>

                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($game['title']) ?></h3>
                        <div class="card-meta">
                            <span class="platform-tag"><?= htmlspecialchars($game['platform']) ?></span>
                            <span class="release-date"><?= htmlspecialchars($game['release_date']) ?></span>
                        </div>
                        
                        <!-- 优化后的开发者和标签显示（处理空值情况） -->
                        <div class="game-info">
                            <p><strong>开发者：</strong><?= htmlspecialchars($game['Developer'] ?: '暂无此项') ?></p>
                            <p><strong>标签：</strong>
                                <?php 
                                // 统一处理分隔符和标签内容
                                $raw_tags = $game['tags'] ?? '';
                                if (empty(trim($raw_tags))) {
                                    echo '暂无此项';
                                } else {
                                    // 步骤1：替换中文逗号为英文逗号
                                    $normalized = str_replace('，', ',', $raw_tags);
                                    
                                    // 步骤2：分割标签并过滤空值
                                    $tags = array_filter(
                                        array_map('trim', explode(',', $normalized)),
                                        function($tag) {
                                            return !empty($tag);
                                        }
                                    );
                                    
                                    // 步骤3：清理标签内的逗号
                                    $clean_tags = array_map(function($tag) {
                                        return str_replace([',', '，'], '', $tag);
                                    }, $tags);

                                    // 最终输出
                                    foreach ($clean_tags as $tag) {
                                        echo '<span class="tag">' . htmlspecialchars($tag) . '</span>';
                                    }
                                }
                                ?>
                            </p>
                        </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="#ccc" style="margin-bottom:20px;"><path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V6h5.17l2 2H20v10zm-7-4h2v2h-2zm0-6h2v4h-2z"/></svg>
                    <h3 style="margin-bottom:15px;">还没有游戏记录</h3>
                    <a href="game_upload.php" class="upload-btn">立即上传第一个游戏</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>