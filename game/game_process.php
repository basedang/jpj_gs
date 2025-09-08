<?php
//game_process.php
// 该页面用于处理游戏信息的上传，包括表单验证、文件上传和数据库插入操作。若操作过程中出现异常，会进行事务回滚和已上传文件的删除，并显示错误信息。

session_start();
require_once '../module/sql_connet.php'; // 注意：原文件中可能拼写错误，应为 sql_connect.php

// 文件上传相关配置
$uploadDir = '../uploads/game_covers/';
$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 2 * 1024 * 1024; // 2MB

try {
    // 表单验证（新增开发者和标签字段校验）
    $requiredFields = ['title', 'platform', 'release_date', 'description', 'Developer', 'tags']; // 补充字段
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("请填写所有必填字段");
        }
    }

    // 文件上传验证
    if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] != UPLOAD_ERR_OK) {
        throw new Exception("请上传封面图片");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['cover_image']['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("只支持JPEG和PNG格式");
    }

    if ($_FILES['cover_image']['size'] > $maxSize) {
        throw new Exception("文件大小超过2MB限制");
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $safeFilename = md5(uniqid() . mt_rand()) . '.' . $extension;
    $uploadPath = $uploadDir . $safeFilename;

    if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
        throw new Exception("文件上传失败");
    }

    // 读取开发者和标签字段（注意大小写与前端一致）
    $developer = trim($_POST['Developer']); // 前端name="Developer"（首字母大写）
    $tags = trim($_POST['tags']); // 保留逗号分隔符，过滤首尾空格

    // 数据库操作（新增developer和tags字段）
    mysqli_begin_transaction($conn);

    // SQL语句添加developer和tags字段
    $sql = "INSERT INTO games (title, developer, tags, platform, release_date, description, cover_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql) or throw new Exception("SQL预处理失败: " . mysqli_error($conn));

    // 预处理参数绑定（所有字段均为字符串类型，使用's'）
    $title = trim($_POST['title']); // 移除htmlspecialchars（数据库存储无需转义，由预处理自动处理）
    $platform = $_POST['platform'];
    $release_date = $_POST['release_date'];
    $description = trim($_POST['description']); // 移除htmlspecialchars

    mysqli_stmt_bind_param($stmt, "sssssss", 
        $title,          // title
        $developer,      // developer
        $tags,           // tags
        $platform,       // platform
        $release_date,   // release_date
        $description,    // description
        $safeFilename   // cover_image
    ) or throw new Exception("参数绑定失败: " . mysqli_stmt_error($stmt));

    mysqli_stmt_execute($stmt) or throw new Exception("执行失败: " . mysqli_stmt_error($stmt));

    mysqli_commit($conn);

    // 操作成功，跳转到游戏列表页
    header('Location: game_list.php?success=1');
    exit;
} catch (Exception $e) {
    // 异常处理：回滚事务，删除已上传文件
    mysqli_rollback($conn ?? null);
    isset($uploadPath) && file_exists($uploadPath) && unlink($uploadPath);

    // 显示错误信息（生产环境建议跳转至错误页面或记录日志）
    die("操作失败：" . $e->getMessage());
} finally {
    // 关闭数据库资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}