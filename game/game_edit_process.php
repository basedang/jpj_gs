<?php
//game_edit_process.php
// 该页面为游戏信息编辑处理页，用于接收并处理游戏信息修改请求，支持封面图片上传和数据库事务确保数据一致性

session_start();
require_once '../module/sql_connet.php';

// 基础参数校验：确保游戏ID存在
if (!isset($_GET['id'])) die("未提供游戏 ID。");
$game_id = $_GET['id'];

// 配置文件上传参数
$uploadDir = '../uploads/game_covers/';
$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 2 * 1024 * 1024; // 2MB

try {
    // 表单必填字段校验
    $requiredFields = ['title', 'Developer', 'tags', 'platform', 'release_date', 'description'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) throw new Exception("请填写所有必填字段");
    }

    // 数据清洗与转义
    $title = htmlspecialchars(trim($_POST['title']));
    $developer = htmlspecialchars(trim($_POST['Developer']));
    $tags = htmlspecialchars(trim($_POST['tags']));
    $platform = $_POST['platform'];
    $release_date = $_POST['release_date'];
    $description = htmlspecialchars(trim($_POST['description']));
    $safeFilename = null;

    // 文件上传处理（可选操作）
    if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['cover_image']['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) throw new Exception("只支持JPEG和PNG格式");
        if ($_FILES['cover_image']['size'] > $maxSize) throw new Exception("文件大小超过2MB限制");
        
        !file_exists($uploadDir) && mkdir($uploadDir, 0755, true); // 自动创建上传目录
        
        $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $safeFilename = md5(uniqid() . mt_rand()) . '.' . $extension;
        $uploadPath = $uploadDir . $safeFilename;
        
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
            throw new Exception("文件上传失败");
        }
    }

    // 数据库事务处理
    mysqli_begin_transaction($conn);
    
    // 生成动态SQL语句（区分是否上传新封面）
    $sql = $safeFilename ? 
        "UPDATE games SET title=?, platform=?, release_date=?, description=?, cover_image=?, Developer=?, tags=? WHERE id=?" : 
        "UPDATE games SET title=?, platform=?, release_date=?, description=?, Developer=?, tags=? WHERE id=?";
    
    $stmt = mysqli_prepare($conn, $sql);
    $params = array_merge([$title, $platform, $release_date, $description], $safeFilename ? [$safeFilename] : []);
    $params = array_merge($params, [$developer, $tags]);
    $params[] = $game_id;
    
    // 绑定参数并执行
    $types = str_repeat('s', count($params) - 1) . 'i'; // 前七个为字符串，最后 ID 为整数
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    
    if (!$stmt->execute()) throw new Exception("执行失败: " . $stmt->error);
    
    mysqli_commit($conn);
    header('Location: game_list.php?success=1');
    exit;

} catch (Exception $e) {
    // 异常处理：回滚事务+删除临时文件
    mysqli_rollback($conn ?? null);
    isset($uploadPath) && file_exists($uploadPath) && unlink($uploadPath);
    die("操作失败：" . $e->getMessage());

} finally {
    // 资源释放
    isset($stmt) && $stmt->close();
    $conn->close();
}