<?php
//del.php
// 该页面为用户删除处理模块，仅限管理员使用，用于安全删除非管理员用户
// 启动会话并引入依赖文件（数据库连接、管理员权限验证）
session_start();
include_once '../module/sql_connet.php';
include_once 'checkAdmin.php'; 

// 检查数据库连接，失败则终止脚本并显示错误
if (!$conn) {
    die("数据库连接失败: " . mysqli_connect_error());
}

try {
    // 获取并转义用户名参数（防止SQL注入），参数缺失则抛出异常
    $username = isset($_GET['username']) 
        ? mysqli_real_escape_string($conn, $_GET['username']) 
        : throw new Exception("用户名参数缺失");

    // 预查询用户是否存在及是否为管理员
    $sql = "SELECT admin FROM info WHERE username = ?";
    $stmt = $conn->prepare($sql) 
        or throw new Exception("准备查询语句失败: " . $conn->error);
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result() 
        or throw new Exception("执行查询失败: " . $stmt->error);

    if ($result->num_rows === 0) {
        throw new Exception("用户不存在"); // 处理无效用户请求
    }

    $user = $result->fetch_assoc();
    if ($user['admin']) {
        throw new Exception("无法删除管理员账户"); // 保护管理员账户不被删除
    }

    // 准备并执行删除语句（预处理防止SQL注入）
    $deleteStmt = $conn->prepare("DELETE FROM info WHERE username = ?") 
        or throw new Exception("准备删除语句失败: " . $conn->error);
    
    $deleteStmt->bind_param("s", $username);
    if (!$deleteStmt->execute()) {
        throw new Exception("删除操作失败: " . $deleteStmt->error);
    }

    // 操作成功后重定向回管理员用户列表页（携带当前页码）
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    header("Location: admin.php?page=$page");
    exit;

} catch (Exception $e) {
    // 捕获异常并安全输出（防止XSS攻击），返回上一页
    echo "<script>alert('" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.back();</script>";
    exit;

} finally {
    // 释放所有数据库资源，确保连接关闭
    foreach (['stmt', 'deleteStmt'] as $var) {
        if (isset($$var)) {
            $$var->close();
        }
    }
    if (isset($conn)) {
        $conn->close();
    }
}