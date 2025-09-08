<?php
// game_update.php
session_start();
if (!isset($_SESSION['loggedUsername'])) {
    header("Location: ../login.php");
    exit;
}

include_once '../module/sql_connet.php';

// 验证表单数据
$required = ['id', 'title', 'developer', 'tags', 'platform', 'release_date', 'description'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        die("缺少必填字段：$field");
    }
}

// 处理文件上传
$cover_image = null;
if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/game_covers/';
    $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $filename);
    $cover_image = $filename;
}

try {
    // 构建更新SQL
    $sql = "UPDATE games SET 
            title = ?,
            developer = ?,
            tags = ?,
            platform = ?,
            release_date = ?,
            description = ?"
            . ($cover_image ? ", cover_image = ?" : "") 
            . " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    
    $params = [
        $_POST['title'],
        $_POST['developer'],
        $_POST['tags'],
        $_POST['platform'],
        $_POST['release_date'],
        $_POST['description']
    ];
    
    if ($cover_image) {
        $params[] = $cover_image;
    }
    
    $params[] = $_POST['id'];
    
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("更新失败: " . $stmt->error);
    }
    
    header("Location: game_admin.php?page=" . ($_GET['page'] ?? 1));
    exit;

} catch (Exception $e) {
    die("操作失败: " . $e->getMessage());
} finally {
    $stmt->close();
    $conn->close();
}