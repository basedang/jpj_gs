<?php
session_start();
include_once '../page/nav.php';
include_once '../module/sql_connet.php';

// 处理评分提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    processRatingSubmission();
}

function processRatingSubmission() {
    global $conn;
    
    // 防重复提交令牌验证
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['form_token'] ?? '') {
        showAlertAndExit('非法请求或重复提交！');
    }
    unset($_SESSION['form_token']);

    // 登录验证
    if (!isset($_SESSION['loggedUsername'])) {
        showAlertAndExit('请先登录！', '../page/login.php');
    }

    // 数据验证
    $gameTitle = mysqli_real_escape_string($conn, $_POST['rating-title']);
    $score = intval($_POST['score']);
    
    if (empty($gameTitle) || $score < 1 || $score > 10) {
        showAlertAndExit('请填写正确游戏名称和1-10分的评分！');
    }

    // 获取游戏ID
    $gameId = getGameIdByTitle($gameTitle);
    if (!$gameId) {
        showAlertAndExit('游戏名称不存在，请确认后再提交！');
    }

    // 获取用户ID
    $userId = getUserIdByUsername($_SESSION['loggedUsername']);
    if (!$userId) {
        showAlertAndExit('用户信息异常，请重新登录！', '../page/login.php');
    }

    // 检查是否已评分
    if (hasUserRated($userId, $gameId)) {
        showAlertAndExit('您已对该游戏提交过评分！');
    }

    // 插入评分
    $insertSql = "INSERT INTO ratings (user_id, game_id, score, tags, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("iiis", $userId, $gameId, $score, $_POST['rating-tags']);
    executeStatement($stmt, '评分提交成功！', '评分提交失败');
}

// 检查是否已评分
function hasUserRated($userId, $gameId) {
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM ratings WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $userId, $gameId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

// 获取游戏ID（与评论页共用函数）
function getGameIdByTitle($title) { /* 同上 */ }

// 获取用户ID（与评论页共用函数）
function getUserIdByUsername($username) { /* 同上 */ }

// 执行SQL语句（与评论页共用函数）
function executeStatement($stmt, $successMsg, $errorMsg) { /* 同上 */ }

// 统一错误提示（与评论页共用函数）
function showAlertAndExit($message, $redirect = null) { /* 同上 */ }

// 生成防重复令牌
$_SESSION['form_token'] = uniqid();

// 查询平均评分
$ratingsSql = "SELECT 
                g.title, 
                AVG(r.score) AS average_score,
                COUNT(r.id) AS rating_count
             FROM 
                games g 
             LEFT JOIN 
                ratings r ON g.id = r.game_id 
             GROUP BY 
                g.id, g.title";
$ratingsResult = mysqli_query($conn, $ratingsSql);
if (!$ratingsResult) die("查询评分失败: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>游戏评分系统</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-container { margin-bottom: 30px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f5f5f5; }
        .form-group { margin-bottom: 15px; }
        input, textarea { width: 100%; padding: 8px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <h1>提交游戏评分</h1>
    <form method="post">
        <input type="hidden" name="token" value="<?= $_SESSION['form_token'] ?>">
        
        <div class="form-group">
            <label>游戏名称：</label>
            <input type="text" name="rating-title" required>
        </div>
        
        <div class="form-group">
            <label>评分：</label>
            <select name="score" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>标签（可选）：</label>
            <input type="text" name="rating-tags" placeholder="多个标签用逗号分隔">
        </div>
        
        <button type="submit" name="submit_rating">提交评分</button>
    </form>

    <h2>游戏评分排行榜</h2>
    <table class="comment-list">
        <tr>
            <th>游戏标题</th><th>平均评分</th><th>评分人数</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($ratingsResult)): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= round($row['average_score'], 1) ?: '0.0' ?></td>
            <td><?= $row['rating_count'] ?: 0 ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>