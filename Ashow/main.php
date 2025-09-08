<!DOCTYPE html>
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
    <title>鸡噗鸡游戏评分</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <!-- 主要内容 -->
    <main class="main" id="mainContent">
        <!-- 轮播图容器 -->
        <div class="banner-container">
            <div class="banner" id="banner">
                <?php
                // 从数据库获取轮播图数据
                $sql = "SELECT id, title, cover_image, tags FROM games ORDER BY release_date DESC LIMIT 4"; 
                $result = mysqli_query($conn, $sql);
                
                // 检查查询是否成功
                if ($result === false) {
                    die("数据库查询失败: " . mysqli_error($conn));
                }
                
                // 动态生成轮播项
                while ($row = mysqli_fetch_assoc($result)) : 
                ?>
                <a href="../page/game.php?id=<?= $row['id'] ?>">
                    <div class="banner-slide" style="background-image: url('../uploads/game_covers/<?= htmlspecialchars($row['cover_image']) ?>')">
                        <div class="banner-content">
                            <h2 class="banner-title"><?= htmlspecialchars($row['title']) ?></h2>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
    
            <!-- 动态生成指示器 -->
            <div class="banner-indicators" id="bannerIndicators">
                <?php
                // 重置结果集指针以便重新遍历
                mysqli_data_seek($result, 0);
                $index = 0;
                while ($row = mysqli_fetch_assoc($result)) :
                ?>
                <div 
                    class="banner-indicator <?= $index === 0 ? 'active' : '' ?>" 
                    data-index="<?= $index++ ?>"
                ></div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- 热门游戏 -->
        <h2 class="section-title">热门游戏</h2>
        <div class="game-grid">
            <?php
            // 获取高评分游戏数据（平均分降序排列）
            $sql = "
                SELECT 
                    games.id,
                    games.title,
                    games.cover_image,
                    games.tags,
                    AVG(ratings.score) AS avg_score,
                    COUNT(ratings.id) AS rating_count
                FROM games
                LEFT JOIN ratings ON games.id = ratings.game_id
                GROUP BY games.id
                HAVING rating_count >= 1  -- 至少1个评分才参与排名
                ORDER BY avg_score DESC 
                LIMIT 4
            ";

            $result = mysqli_query($conn, $sql);
            
            if (!$result) {
                die("查询失败: " . mysqli_error($conn));
            }

            while ($game = mysqli_fetch_assoc($result)) :
                // 计算星级（每2分一个★）
                $fullStars = floor($game['avg_score'] / 2);
                $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
            ?>
            <a href="../page/game.php?id=<?= $game['id'] ?>">
                <div class="game-card" data-game-id="<?= $game['id'] ?>">
                    <!-- 封面图 -->
                    <img 
                        src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                        alt="<?= htmlspecialchars($game['title']) ?>" 
                        class="game-cover"
                    >
                    
                    <!-- 游戏信息 -->
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        
                        <!-- 评分信息 -->
                        <div class="game-rating">
                            <span class="stars"><?= $stars ?></span>
                            <span class="score">
                                <?= number_format($game['avg_score'], 1) ?>/10
                                <small>(<?= $game['rating_count'] ?>人评分)</small>
                            </span>
                        </div>

                        <!-- 标签 -->
                        <?php if(!empty($game['tags'])) : ?>
                        <div class="game-tags">
                            <?php foreach(explode(',', $game['tags']) as $tag): ?>
                                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- 新游推荐 -->
        <h2 class="section-title">新游推荐</h2>
        <div class="game-grid">
            <?php
            // 获取最新发布的游戏（按发布日期降序排列）
            $sql = "
                SELECT 
                    games.id,
                    games.title,
                    games.cover_image,
                    games.tags,
                    AVG(ratings.score) AS avg_score,
                    COUNT(ratings.id) AS rating_count
                FROM games
                LEFT JOIN ratings ON games.id = ratings.game_id
                GROUP BY games.id
                ORDER BY games.release_date DESC  -- 按发布日期降序
                LIMIT 4
            ";

            $result = mysqli_query($conn, $sql);
            
            if (!$result) {
                die("查询失败: " . mysqli_error($conn));
            }

            while ($game = mysqli_fetch_assoc($result)) :
                // 处理评分显示逻辑
                $avg_score = $game['avg_score'];
                $rating_count = $game['rating_count'];
                if ($rating_count >= 1) {
                    $fullStars = floor($avg_score / 2);
                    $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                    $score_display = number_format($avg_score, 1) . '/10 <small>(' . $rating_count . '人评分)</small>';
                } else {
                    $stars = '暂无评分';
                    $score_display = '暂无评分';
                }
            ?>
            <a href="../page/game.php?id=<?= $game['id'] ?>">
                <div class="game-card" data-game-id="<?= $game['id'] ?>">
                    <!-- 封面图 -->
                    <img 
                        src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                        alt="<?= htmlspecialchars($game['title']) ?>" 
                        class="game-cover"
                    >
                    
                    <!-- 游戏信息 -->
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        
                        <!-- 评分信息 -->
                        <div class="game-rating">
                            <span class="stars"><?= $stars ?></span>
                            <span class="score"><?= $score_display ?></span>
                        </div>

                        <!-- 标签 -->
                        <?php if(!empty($game['tags'])) : ?>
                        <div class="game-tags">
                            <?php foreach(explode(',', $game['tags']) as $tag): ?>
                                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- PC平台 -->
        <h2 class="section-title">PC平台</h2>
        <div class="game-grid">
            <?php
            // 获取PC平台最新发布的游戏（按发布日期降序排列）
            $sql = "
                SELECT 
                    games.id,
                    games.title,
                    games.cover_image,
                    games.release_date,
                    games.tags,
                    AVG(ratings.score) AS avg_score,
                    COUNT(ratings.id) AS rating_count
                FROM games 
                LEFT JOIN ratings ON games.id = ratings.game_id
                WHERE platform = 'PC'  -- 添加平台筛选条件
                GROUP BY games.id
                ORDER BY release_date DESC 
                LIMIT 4
            ";

            $result = mysqli_query($conn, $sql);
            
            if (!$result) {
                die("查询失败: " . mysqli_error($conn));
            }

            while ($game = mysqli_fetch_assoc($result)) :
                // 日期格式化（示例：显示为 "03月15日"）
                $release_date = date('m月d日', strtotime($game['release_date']));
                
                // 处理评分显示逻辑
                $avg_score = $game['avg_score'];
                $rating_count = $game['rating_count'];
                if ($rating_count >= 1) {
                    $fullStars = floor($avg_score / 2);
                    $stars = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                    $score_display = number_format($avg_score, 1) . '/10 <small>(' . $rating_count . '人评分)</small>';
                } else {
                    $stars = '暂无评分';
                    $score_display = '暂无评分';
                }
            ?>
            <a href="../page/game.php?id=<?= $game['id'] ?>">
                <div class="game-card" data-game-id="<?= $game['id'] ?>">
                    <!-- 封面图 -->
                    <img 
                        src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                        alt="<?= htmlspecialchars($game['title']) ?>" 
                        class="game-cover"
                    >
                    
                    <!-- 游戏信息 -->
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        
                        <!-- 发布日期 -->
                        <div class="release-date">
                            <i class="icon-calendar"></i>
                            <?= $release_date ?> 上线
                        </div>

                        <!-- 动态评分 -->
                        <div class="game-rating">
                            <span class="stars"><?= $stars ?></span>
                            <span class="score"><?= $score_display ?></span>
                        </div>

                        <!-- 标签 -->
                        <?php if(!empty($game['tags'])) : ?>
                        <div class="game-tags">
                            <?php foreach(explode(',', $game['tags']) as $tag): ?>
                                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </main>
    
    <script>
        // 轮播图功能
        document.addEventListener('DOMContentLoaded', function() {
            const banner = document.getElementById('banner');
            const slides = banner.querySelectorAll('.banner-slide');
            const indicators = document.querySelectorAll('.banner-indicator');
            let currentSlide = 0;
            const slideCount = slides.length;
            
            // 初始化轮播
            function initSlider() {
                slides[0].classList.add('active');
                indicators[0].classList.add('active');
                
                // 自动轮播
                setInterval(nextSlide, 7000);
            }
            
            // 切换到下一张幻灯片
            function nextSlide() {
                goToSlide((currentSlide + 1) % slideCount);
            }
            
            // 跳转到指定幻灯片
            function goToSlide(index) {
                slides[currentSlide].classList.remove('active');
                indicators[currentSlide].classList.remove('active');
                
                currentSlide = index;
                
                slides[currentSlide].classList.add('active');
                indicators[currentSlide].classList.add('active');
            }
            
            // 为指示器添加点击事件
            indicators.forEach(indicator => {
                indicator.addEventListener('click', function() {
                    const slideIndex = parseInt(this.getAttribute('data-index'));
                    goToSlide(slideIndex);
                });
            });
            
            // 初始化轮播图
            initSlider();
        });
    </script>
</body>
</html>    