<?php
include_once '../index.php';
include_once '../module/sql_connet.php';
session_start();
?>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>鸡噗鸡游戏评分 | 排行榜</title>
    <link rel="stylesheet" href="./css/charts.css">
</head>
<body>

    <!-- 排行榜主要内容 -->
    <main class="ranking-main">
        <h1 class="section-title">游戏排行榜</h1>
        
        <!-- 排行榜分类导航 -->
        <div class="ranking-tabs">
            <div class="ranking-tab active" data-tab="popular">热门榜</div>
            <div class="ranking-tab" data-tab="rating">评分榜</div>
            <div class="ranking-tab" data-tab="new">新游榜</div>
        </div>
        
        <!-- 人气排行榜 -->
        <div class="ranking-content active" id="popular-ranking">
            <div class="ranking-list">
                <div class="ranking-header">
                    <div>排名</div>
                    <div>游戏</div>
                    <div>评分</div>
                </div>
                
                <?php
                // 获取游戏人气排行（按评分人数降序）
                $sql = "
                    SELECT 
                        games.id,
                        games.title,
                        games.cover_image,  -- 注意字段名需与实际表结构一致
                        games.platform,     -- 新增平台字段
                        COUNT(ratings.id) AS rating_count,
                        AVG(ratings.score) AS avg_score
                    FROM games
                    LEFT JOIN ratings ON games.id = ratings.game_id
                    GROUP BY games.id
                    ORDER BY rating_count DESC 
                    LIMIT 10
                ";

                $result = mysqli_query($conn, $sql);
                
                if (!$result) {
                    die("查询失败: " . mysqli_error($conn));
                }

                $rank = 1;
                while ($game = mysqli_fetch_assoc($result)) :
                    // 计算星级（每2分一个★）
                    $normalizedScore = $game['avg_score'] ?? 0;
                    $fullStars = floor($normalizedScore / 2);
                    $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                    
                    // 处理没有评分的情况
                    $ratingDisplay = $game['rating_count'] > 0 
                        ? "{$stars} " . number_format($game['avg_score'], 1)
                        : "暂无评分";
                ?>
                <div class="ranking-item" data-game-id="<?= $game['id'] ?>" onclick="goToGameDetail(<?= $game['id'] ?>, '<?= htmlspecialchars($game['title']) ?>')">
                    <div class="rank <?= $rank <= 3 ? "rank-{$rank}" : '' ?>"><?= $rank++ ?></div>
                    <div class="game-info">
                        <img 
                            src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                            alt="<?= htmlspecialchars($game['title']) ?>" 
                            class="game-cover"
                        >
                        <div>
                            <div class="game-title"><?= htmlspecialchars($game['title']) ?></div>
                            <!-- 显示平台信息 -->
                            <div class="game-platform">
                                <?= htmlspecialchars($game['platform']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="rating"><?= $ratingDisplay ?></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- 新游榜 -->
        <div class="ranking-content" id="new-ranking">
    <div class="ranking-list">
        <div class="ranking-header">
            <div>排名</div>
            <div>游戏</div>
            <div>评分</div>
        </div>
        
        <?php
        // 获取最新发布的游戏（按发布日期降序）
        $sql = "
            SELECT 
                games.id,
                ANY_VALUE(games.title) AS title,
                ANY_VALUE(games.cover_image) AS cover_image,
                ANY_VALUE(games.release_date) AS release_date,
                ANY_VALUE(games.platform) AS platform,
                AVG(ratings.score) AS avg_score,
                COUNT(ratings.id) AS rating_count
            FROM games
            LEFT JOIN ratings ON games.id = ratings.game_id
            WHERE games.release_date <= CURDATE()
            GROUP BY games.id
            ORDER BY games.release_date DESC 
            LIMIT 10
        ";

        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            die("数据库查询失败: " . mysqli_error($conn));
        }

        $rank = 1;
        while ($game = mysqli_fetch_assoc($result)) :
            // 处理评分和日期
            $normalizedScore = $game['avg_score'] ?? 0;
            $fullStars = floor($normalizedScore / 2);
            $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
            
            // 日期格式化（含有效性校验）
            $releaseDate = ($game['release_date'] && strtotime($game['release_date']))
                ? date('Y-m-d', strtotime($game['release_date']))
                : '日期未定';
        ?>
        <div class="ranking-item" data-game-id="<?= $game['id'] ?>" onclick="goToGameDetail(<?= $game['id'] ?>, '<?= htmlspecialchars($game['title']) ?>')">
            <div class="rank rank-<?= $rank ?>"><?= $rank++ ?></div>
            <div class="game-info">
                <img 
                    src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                    alt="<?= htmlspecialchars($game['title']) ?>" 
                    class="game-cover"
                    onerror="this.src='../images/default_cover.jpg'"  >
                <div>
                    <div class="game-title"><?= htmlspecialchars($game['title']) ?></div>
                    <div class="game-meta">
                        <span class="release-date"><?= $releaseDate ?> 上线</span>
                        <span class="platform-tag"><?= htmlspecialchars($game['platform'] ?? '多平台') ?></span>
                    </div>
                </div>
            </div>
            <div class="rating">
                <?php if ($game['rating_count'] > 0): ?>
                    <?= $stars ?> 
                    <?= number_format($game['avg_score'], 1) ?>
                <?php else: ?>
                    <span class="no-rating">暂无评分</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
        <!-- 评分排行榜 -->
        <div class="ranking-content" id="rating-ranking">
            <div class="ranking-list">
                <div class="ranking-header">
                    <div>排名</div>
                    <div>游戏</div>
                    <div>评分</div>
                </div>
                
                <?php
                // 获取高评分游戏（按平均分降序）
                $sql = "
                    SELECT 
                        games.id,
                        games.title,
                        games.cover_image,
                        games.platform,
                        AVG(ratings.score) AS avg_score,
                        COUNT(ratings.id) AS rating_count
                    FROM games
                    LEFT JOIN ratings ON games.id = ratings.game_id
                    GROUP BY games.id
                    HAVING rating_count >= 2  -- 保留筛选逻辑
                    ORDER BY avg_score DESC 
                    LIMIT 10
                ";

                $result = mysqli_query($conn, $sql);
                
                if (!$result) {
                    die("数据库查询失败: " . mysqli_error($conn));
                }

                $rank = 1;
                while ($game = mysqli_fetch_assoc($result)) :
                    // 计算星级（每2分一个★）
                    $normalizedScore = $game['avg_score'] ?? 0;
                    $fullStars = floor($normalizedScore / 2);
                    $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                ?>
                <div class="ranking-item" data-game-id="<?= $game['id'] ?>" onclick="goToGameDetail(<?= $game['id'] ?>, '<?= htmlspecialchars($game['title']) ?>')">
                    <div class="rank <?= $rank <= 3 ? "rank-{$rank}" : '' ?>"><?= $rank++ ?></div>
                    <div class="game-info">
                        <img 
                            src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                            alt="<?= htmlspecialchars($game['title']) ?>" 
                            class="game-cover"
                        >
                        <div>
                            <div class="game-title"><?= htmlspecialchars($game['title']) ?></div>
                            <div class="game-platform">
                                <?= htmlspecialchars($game['platform'] ) ?>
                            </div>
                        </div>
                    </div>
                    <div class="rating">
                        <?php if($game['rating_count'] > 0): ?>
                            <?= $stars ?> 
                            <?= number_format($game['avg_score'], 1) ?>
                        <?php else: ?>
                            暂无评分
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script>
        // 跳转到游戏详情页
        function goToGameDetail(gameId, gameTitle) {
            // 添加点击动画效果
            const item = document.querySelector(`.ranking-item[data-game-id="${gameId}"]`);
            if (item) {
                item.style.transform = 'scale(0.98)';
                item.style.opacity = '0.9';
            }
            
            // 编码游戏标题
            const encodedTitle = encodeURIComponent(gameTitle);
            
            // 延迟跳转以显示动画效果
            setTimeout(() => {
                window.location.href = `../page/game.php?id=${gameId}&title=${encodedTitle}`;
            }, 200);
        }

        // 排行榜标签切换
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.ranking-tab');
            const contents = document.querySelectorAll('.ranking-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // 移除所有active类
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    
                    // 添加active类到当前标签
                    this.classList.add('active');
                    
                    // 显示对应内容
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-ranking`).classList.add('active');
                });
            });

            // 为图片添加点击事件
            const gameCovers = document.querySelectorAll('.game-cover');
            gameCovers.forEach(cover => {
                cover.addEventListener('click', function() {
                    const gameId = this.closest('.ranking-item').dataset.gameId;
                    const gameTitle = this.closest('.ranking-item').querySelector('.game-title').textContent;
                    goToGameDetail(gameId, gameTitle);
                });
            });
        });
    </script>
</body>
</html>