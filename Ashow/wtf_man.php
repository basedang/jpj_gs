<?php
session_start();
define('BASE_PATH', '../');
include_once BASE_PATH .'module/sql_connet.php'; // 引入数据库连接

// 检查用户登录
if (!isset($_SESSION['loggedUsername']) || empty($_SESSION['loggedUsername'])) {
    header("Location: ". BASE_PATH ."page/login.php");
    exit;
}

// 获取当前用户 ID
$currentUserId = $_SESSION['userId'] ?? 0;

// 查询用户信息
$userInfoSql = "SELECT username, email, sex, fav FROM info WHERE Id = $currentUserId";
$userInfoResult = mysqli_query($conn, $userInfoSql);
$userInfo = mysqli_fetch_assoc($userInfoResult);

// 查询用户评论
$commentsSql = "SELECT c.id, c.content, c.created_at, g.title AS game_title 
                FROM comments c 
                LEFT JOIN games g ON c.game_id = g.id 
                WHERE c.user_id = $currentUserId";
$commentsResult = mysqli_query($conn, $commentsSql);

// 查询用户评分
$ratingsSql = "SELECT r.id, r.score, r.tags, r.created_at, g.title AS game_title 
               FROM ratings r 
               LEFT JOIN games g ON r.game_id = g.id 
               WHERE r.user_id = $currentUserId";
$ratingsResult = mysqli_query($conn, $ratingsSql);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人后台 - <?= htmlspecialchars($userInfo['username'] ?? '') ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>page/css/nav.css">
    <style>
        .content-block {
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .comment-item, .rating-item {
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH .'module/nav.php'; // 引入导航 ?>

    <div class="main-content">
        <h2>个人中心</h2>

        <div class="content-block">
            <h3>个人资料</h3>
            <div class="info-item">用户名：<?= htmlspecialchars($userInfo['username'] ?? '') ?></div>
            <div class="info-item">邮箱：<?= htmlspecialchars($userInfo['email'] ?? '') ?></div>
            <div class="info-item">性别：<?= $userInfo['sex'] === 1 ? '男' : ($userInfo['sex'] === 0 ? '女' : '未设置') ?></div>
            <div class="info-item">爱好：<?= htmlspecialchars($userInfo['fav'] ?? '') ?></div>
            <a href="<?= BASE_PATH ?>user/modify.php?source=member&id=2" class="btn">修改资料</a>
        </div>

        <div class="content-block">
            <h3>我的评论</h3>
            <?php while ($comment = mysqli_fetch_assoc($commentsResult)): ?>
                <div class="comment-item">
                    <p>游戏：<?= htmlspecialchars($comment['game_title'] ?? '未知游戏') ?></p>
                    <p>内容：<?= htmlspecialchars($comment['content']) ?></p>
                    <p>时间：<?= $comment['created_at'] ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="content-block">
            <h3>我的评分</h3>
            <?php while ($rating = mysqli_fetch_assoc($ratingsResult)): ?>
                <div class="rating-item">
                    <p>游戏：<?= htmlspecialchars($rating['game_title'] ?? '未知游戏') ?></p>
                    <p>评分：<?= $rating['score'] ?></p>
                    <p>标签：<?= htmlspecialchars($rating['tags'] ?? '') ?></p>
                    <p>时间：<?= $rating['created_at'] ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>

<?php
// 关闭数据库连接
mysqli_close($conn);
?>