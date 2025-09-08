<?php
//details.php
// 此页面用于展示特定游戏的详情信息，包含游戏的基本信息、平均评分、评论列表，
// 同时为已登录用户提供评分表单

session_start();
// 引入数据库连接文件
require '../module/sql_connet.php';

// 验证游戏ID是否有效
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$game_id) {
    die("无效的游戏ID");
}

// 查询游戏详情及平均评分
$gameQuery = "
    SELECT 
        games.*,
        COALESCE(ROUND(AVG(ratings.score), 1), 0) AS avg_score,
        COUNT(ratings.id) AS rating_count
    FROM games
    LEFT JOIN ratings ON games.id = ratings.game_id
    WHERE games.id = ?
    GROUP BY games.id
";
$stmt = $conn->prepare($gameQuery);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();

// 查询评论
$commentQuery = "
    SELECT 
        comments.content,
        comments.created_at,
        users.username
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE game_id = ?
    ORDER BY comments.created_at DESC
";
$commentStmt = $conn->prepare($commentQuery);
$commentStmt->bind_param("i", $game_id);
$commentStmt->execute();
$commentResult = $commentStmt->get_result();
$comments = $commentResult->fetch_all(MYSQLI_ASSOC);

// 关闭数据库连接
$stmt->close();
$commentStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <!-- 页面标题，显示游戏名称 -->
    <title><?= htmlspecialchars($game['title']) ?> - 游戏详情</title>
    <link rel="stylesheet" href="../page/css/nav.css">
    <style>
        .game-meta { margin: 15px 0; color: #666; }
        .comment { margin: 20px 0; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        form { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <!-- 显示游戏标题 -->
        <h1><?= htmlspecialchars($game['title']) ?></h1>
        <div class="game-meta">
            <!-- 显示游戏平台 -->
            <span>平台：<?= htmlspecialchars($game['platform']) ?></span>
            <!-- 显示游戏平均评分 -->
            <span>平均评分：⭐ <?= htmlspecialchars($game['avg_score']) ?></span>
        </div>

        <!-- 评分表单，已登录用户可看到 -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <form action="submit_rating.php" method="POST">
            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
            <label>评分（1-10）：
                <input type="number" name="score" min="1" max="10" required>
            </label>
            <button type="submit">提交评分</button>
        </form>
        <?php else: ?>
            <!-- 未登录用户提示登录 -->
            <p>请<a href="login.php">登录</a>后参与评分</p>
        <?php endif; ?>

        <!-- 评论列表 -->
        <div class="comments">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <!-- 显示评论用户的用户名 -->
                    <strong><?= htmlspecialchars($comment['username']) ?></strong>
                    <!-- 显示评论时间 -->
                    <span><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></span>
                    <!-- 显示评论内容 -->
                    <p><?= htmlspecialchars($comment['content']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>