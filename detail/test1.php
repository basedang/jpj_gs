<?php
session_start();
include_once '../page/nav.php';
include_once '../module/sql_connet.php';

// 处理评论提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    processCommentSubmission();
}

function processCommentSubmission() {
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
    $gameTitle = mysqli_real_escape_string($conn, $_POST['game_title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    if (empty($gameTitle) || empty($content)) {
        showAlertAndExit('请填写完整游戏名称和评论内容！');
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

    // 处理图片上传
    $screenshotPath = handleFileUpload();

    // 插入评论
    $insertSql = "INSERT INTO comments (user_id, game_id, content, created_at, screenshot) 
                  VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("iiss", $userId, $gameId, $content, $screenshotPath);
    executeStatement($stmt, '评论发表成功！', '评论发表失败');
}

// 获取游戏ID
function getGameIdByTitle($title) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM games WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_assoc()['id'] ?? null;
}

// 获取用户ID
function getUserIdByUsername($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT Id FROM info WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_assoc()['Id'] ?? null;
}

// 处理文件上传
function handleFileUpload() {
    global $conn;
    $uploadDir = '../uploads/comments_screenshots/';
    if (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['screenshot']['tmp_name']);
    if (strpos($mimeType, 'image/') !== 0) {
        showAlertAndExit('仅支持图片格式！');
    }

    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $extension = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
    $safeFilename = md5(uniqid() . mt_rand()) . '.' . $extension;
    $path = $uploadDir . $safeFilename;
    
    if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $path)) {
        showAlertAndExit('截图上传失败！');
    }
    return $path;
}

// 执行SQL语句
function executeStatement($stmt, $successMsg, $errorMsg) {
    global $conn;
    if ($stmt->execute()) {
        echo "<script>
            alert('$successMsg');
            window.location.href = '" . $_SERVER['REQUEST_URI'] . "'; // 返回当前页
        </script>";
        exit;
    } else {
        echo "<script>
            window.location.href = '" . $_SERVER['REQUEST_URI'] . "';
        </script>";
        exit;
    }
    $stmt->close();
}

// 统一错误提示
function showAlertAndExit($message, $redirect = null) {
    $redirect = $redirect ?: $_SERVER['REQUEST_URI'];
    echo "<script>alert('$message'); window.location.href='$redirect';</script>";
    exit;
}

// 生成防重复令牌
$_SESSION['form_token'] = uniqid();

// 查询评论列表
$commentsSql = "SELECT 
                c.id, c.content, c.created_at, c.updated_at, c.screenshot,
                g.title AS game_title, i.username 
             FROM 
                comments c 
             JOIN 
                games g ON c.game_id = g.id 
             JOIN 
                info i ON c.user_id = i.Id 
             ORDER BY 
                c.created_at DESC";
$commentsResult = mysqli_query($conn, $commentsSql);
if (!$commentsResult) die("查询评论失败: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>游戏评论系统</title>
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
    <h1>发表游戏评论</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= $_SESSION['form_token'] ?>">
        
        <div class="form-group">
            <label>游戏名称：</label>
            <input type="text" name="game_title" required>
        </div>
        
        <div class="form-group">
            <label>评论内容：</label>
            <textarea name="content" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label>评论截图（可选）：</label>
            <input type="file" name="screenshot" accept="image/*">
        </div>
        
        <button type="submit" name="submit_comment">提交评论</button>
    </form>

    <h2>最新评论列表</h2>
    <table class="comment-list">
        <tr>
            <th>游戏标题</th><th>用户名</th><th>评论内容</th><th>发表时间</th><th>截图</th><th>操作</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($commentsResult)): ?>
        <tr>
            <td><?= htmlspecialchars($row['game_title']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= strip_tags($row['content']) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
            <td><?= $row['screenshot'] ? '<img src="' . htmlspecialchars($row['screenshot']) . '" width="100">' : '无' ?></td>
            <td><button onclick="showEditForm(<?= $row['id'] ?>)">编辑</button></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <script>
        function showEditForm(id) {
            document.getElementById('comment-id').value = id;
            document.getElementById('edit-form').classList.remove('hidden');
        }
    </script>
</body>
</html>