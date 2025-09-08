<?php
// game.php
session_start();
require '../module/sql_connet.php';

$gameId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$game = [];
$stmt = $conn->prepare("SELECT *, tags, Developer FROM games WHERE id = ?");
$stmt->bind_param("i", $gameId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("游戏不存在");
$game = $result->fetch_assoc();
$stmt->close();

$allRatings = [];
$stmt = $conn->prepare("
    SELECT r.id AS rating_id, r.score, r.tags, r.created_at AS rating_date, u.username 
    FROM ratings r JOIN info u ON r.user_id = u.Id
    WHERE r.game_id = ? ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $gameId);
$stmt->execute();
$allRatings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalScore = 0;
$tagCounts = [];
foreach ($allRatings as $r) {
    $totalScore += $r['score'];
    if (!empty($r['tags'])) {
        foreach (explode(',', $r['tags']) as $tag) { // 正确使用逗号分割
            $cleanTag = trim($tag);
            if ($cleanTag) $tagCounts[$cleanTag]++;
        }
    }
}
$averageScore = count($allRatings) ? round($totalScore / count($allRatings), 1) : 0;
arsort($tagCounts);
$topTags = array_slice(array_keys($tagCounts), 0, 5);

$allComments = [];
$stmt = $conn->prepare("
    SELECT c.id AS comment_id, c.content, c.screenshot, c.created_at, u.username
    FROM comments c JOIN info u ON c.user_id = u.Id
    WHERE c.game_id = ? ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $gameId);
$stmt->execute();
$allComments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) die("请先登录");
    $userId = $_SESSION['user_id'];

    if (isset($_POST['score'])) {
        $score = max(1, min(10, (int)$_POST['score']));
        $tags = strip_tags($_POST['tags'] ?? '');
        $stmt = $conn->prepare("
            INSERT INTO ratings (user_id, game_id, score, tags, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE score=VALUES(score), tags=VALUES(tags), created_at=NOW()
        ");
        $stmt->bind_param("iiis", $userId, $gameId, $score, $tags);
        $stmt->execute();
        header("Location: game.php?id=$gameId");
        exit;
    }

    if (isset($_POST['content'])) {
        $content = strip_tags($_POST['content'], '<p><br>');
        $screenshotPath = null;
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['screenshot']['tmp_name']);
            if (in_array($mime, $allowedTypes)) {
                $uploadDir = 'uploads/screenshots/';
                mkdir($uploadDir, 0755, true);
                $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $_FILES['screenshot']['name']);
                $filename = uniqid() . '_' . $safeName;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetPath)) {
                    $screenshotPath = $targetPath;
                }
            }
        }
        $stmt = $conn->prepare("
            INSERT INTO comments (user_id, game_id, content, screenshot, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiss", $userId, $gameId, $content, $screenshotPath);
        $stmt->execute();
        header("Location: game.php?id=$gameId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($game['title']) ?> - 游戏详情</title>
    <style>
        :root {
            --primary-color: #2c6cbf;
            --secondary-color: #4a90e2;
            --bg-color: #f5f7fa;
            --text-color: #333;
            --subtext-color: #666;
            --border-color: #e0e8f7;
            --max-width: 1200px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Microsoft YaHei', sans-serif;
            background: var(--bg-color);
            line-height: 1.6;
        }

        .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* 游戏头部布局 */
        .game-header {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(103, 151, 255, 0.1);
        }

        .game-cover {
            width: 250px;
            height: 350px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        .no-cover {
            width: 250px;
            height: 350px;
            background: #f5f9ff;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            color: var(--subtext-color);
        }

        .game-info {
            flex: 1;
        }

        .game-title {
            font-size: 2.2rem;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .meta-info {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .meta-info p {
            color: var(--subtext-color);
            font-size: 0.95rem;
            margin: 10px 0;
        }

        .tag {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* 新增：标签间距样式（替代逗号分隔） */
        .tag:not(:last-child) {
            margin-right: 8px;
        }

        .description-box {
            line-height: 1.8;
            color: var(--subtext-color);
        }

        .description-content {
            white-space: pre-wrap;
            font-size: 1rem;
        }

        /* 评分模块 */
        .section {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(103, 151, 255, 0.1);
        }

        .average-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .avg-score {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .tag-cloud {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 0.9rem;
        }

        .rating-list {
            gap: 20px;
        }

        .rating-item {
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .rating-item:hover {
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(103, 151, 255, 0.15);
        }

        .user-info {
            color: var(--subtext-color);
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        /* 评论模块 */
        .comments-section {
            position: relative;
        }

        .comment-item {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .user-header {
            color: var(--subtext-color);
            font-size: 0.95rem;
            margin-bottom: 15px;
        }

        .comment-content {
            color: var(--text-color);
            font-size: 1rem;
            line-height: 1.7;
        }

        .screenshot-box {
            margin-top: 15px;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
            cursor: zoom-in;
        }

        .screenshot-box img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        /* 表单样式 */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 600px;
            margin: 30px auto 0;
        }

        input, textarea {
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #f5f9ff;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        button {
            padding: 14px 30px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(44, 108, 191, 0.2);
        }

        .login-prompt {
            text-align: center;
            color: var(--subtext-color);
            margin: 20px 0;
        }

        .login-prompt a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        /* 响应式设计 */
        @media (max-width: 992px) {
            .game-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }

            .game-cover, .no-cover {
                width: 100%;
                max-width: 350px;
                height: 280px;
            }
        }

        @media (max-width: 768px) {
            .average-box {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .screenshot-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 游戏基础信息区块 -->
        <section class="game-header">
            <!-- 封面图或占位图 -->
            <?php if (!empty($game['cover_image'])): ?>
                <img src="../uploads/game_covers/<?= htmlspecialchars($game['cover_image']) ?>" 
                     alt="<?= htmlspecialchars($game['title']) ?>" class="game-cover">
            <?php else: ?>
                <div class="no-cover">暂无封面图</div>
            <?php endif; ?>

            <!-- 详细信息 -->
            <div class="game-info">
                <h1 class="game-title"><?= htmlspecialchars($game['title']) ?></h1>
                <div class="meta-info">
                    <p><strong>平台：</strong><?= htmlspecialchars($game['platform']) ?></p>
                    <p><strong>发行日期：</strong><?= date('Y年m月d日', strtotime($game['release_date'])) ?></p>
                    <p><strong>开发者：</strong><?= htmlspecialchars($game['Developer']) ?></p>
                    <?php if (!empty($game['tags'])): ?>
                        <p><strong>标签：</strong>
                            <?php 
                            // 处理游戏标签（数据库中逗号分隔的字符串）
                            $gameTags = explode(',', $game['tags']);
                            foreach ($gameTags as $tag): 
                                $cleanTag = trim($tag);
                                if (!empty($cleanTag)):
                            ?>
                                <span class="tag"><?= htmlspecialchars($cleanTag) ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="description-box">
                    <h3>游戏简介</h3>
                    <?php if (!empty($game['description'])): ?>
                        <div class="description-content"><?= nl2br(htmlspecialchars($game['description'])) ?></div>
                    <?php else: ?>
                        <p class="no-description">暂无游戏简介</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- 玩家评分模块 -->
        <section class="rating-section section">
            <h2>玩家评分</h2>
            <?php if (!empty($allRatings)): ?>
                <div class="average-box">
                    <div class="avg-score">平均分：<span class="score"><?= $averageScore ?></span>/10</div>
                    <?php if (!empty($topTags)): ?>
                        <div class="tag-cloud">热门标签：
                            <?php foreach ($topTags as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="rating-list">
                    <?php foreach ($allRatings as $rating): ?>
                    <div class="rating-item">
                        <div class="user-info"><?= htmlspecialchars($rating['username']) ?> • <?= $rating['score'] ?>分 • 
                            <?= date('m-d H:i', strtotime($rating['rating_date'])) ?></div>
                        <?php if (!empty($rating['tags'])): ?>
                        <div class="tags">
                            <?php 
                            // 修正：正确使用逗号分割标签
                            $ratingTags = explode(',', $rating['tags']); 
                            foreach ($ratingTags as $tag): 
                                $cleanTag = trim($tag);
                                if (!empty($cleanTag)):
                            ?>
                                <span class="tag"><?= htmlspecialchars($cleanTag) ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">😢 还没有玩家评分</div>
            <?php endif; ?>

            <!-- 评分表单 -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form class="rating-form" method="POST">
                    <div><input type="number" name="score" min="1" max="10" placeholder="请输入1-10分" required></div>
                    <div><input type="text" name="tags" placeholder="添加标签（用逗号分隔）"></div>
                    <button type="submit">提交评分</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">请<a href="/login.php">登录</a>后参与评分</p>
            <?php endif; ?>
        </section>

        <!-- 玩家评论模块 -->
        <section class="comments-section section">
            <h2>玩家评论（<?= count($allComments) ?>条）</h2>
            <?php if (!empty($allComments)): ?>
                <div class="comment-list">
                    <?php foreach ($allComments as $comment): ?>
                    <div class="comment-item">
                        <div class="user-header"><?= htmlspecialchars($comment['username']) ?> • 
                            <?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></div>
                        <?php if (!empty($comment['content'])): ?>
                        <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($comment['screenshot'])): ?>
                        <div class="screenshot-box">
                            <!-- 修正截图路径 -->
                            <img src="../<?= htmlspecialchars($comment['screenshot']) ?>" 
                                 alt="玩家截图" onclick="openLightbox(this.src)">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">💬 还没有玩家评论</div>
            <?php endif; ?>

            <!-- 评论表单 -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form class="comment-form" method="POST" enctype="multipart/form-data">
                    <textarea name="content" rows="4" placeholder="写下你的游戏体验（至少10个字）..." required minlength="10"></textarea>
                    <div><input type="file" name="screenshot" accept="image/jpeg,image/png,image/gif"></div>
                    <button type="submit">发表评论</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">请<a href="/login.php">登录</a>后发表评论</p>
            <?php endif; ?>
        </section>
    </div>

    <script>
        // 优化版灯箱功能（支持ESC键关闭和图片缩放）
        function openLightbox(imgSrc) {
            const overlay = document.createElement('div');
            overlay.className = 'lightbox-overlay';
            overlay.style = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                background: rgba(0,0,0,0.9); display: flex; justify-content: center; 
                align-items: center; z-index: 10000;
            `;

            const img = document.createElement('img');
            img.src = imgSrc;
            img.className = 'lightbox-image';
            img.style = `
                max-width: 90%; max-height: 90%; border-radius: 12px; 
                object-fit: contain; cursor: zoom-out; transition: transform 0.3s;
            `;

            // 点击缩放
            img.addEventListener('click', (e) => {
                if (e.target === img) {
                    img.style.transform = img.style.transform.includes('scale(1.5)') ? 'scale(1)' : 'scale(1.5)';
                }
            });

            // ESC键关闭
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.body.removeChild(overlay);
                }
            });

            overlay.appendChild(img);
            document.body.appendChild(overlay);
        }
    </script>
</body>
</html>