<?php
session_start();
include_once "../module/sql_connet.php"; // 数据库连接文件（需包含 $conn 变量）

// 获取用户输入并过滤
$username = trim($_POST['username']);
$pw = trim($_POST['pw']);

// 验证输入格式（保留原有逻辑）
if (!$username || !$pw) {
    echo "<script>alert('用户名和密码都必须填写');history.back();</script>";
    exit;
}
if (!preg_match('/^[a-zA-Z0-9]{3,10}$/', $username)) {
    echo "<script>alert('用户名格式错误');history.back();</script>";
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_*]{6,10}$/', $pw)) {
    echo "<script>alert('密码格式错误');history.back();</script>";
    exit;
}

// 【关键】使用预处理语句（防SQL注入），并明确查询 admin 字段
$sql = "SELECT username, admin FROM info WHERE username = ? AND pw = MD5(?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $pw); // 绑定参数（自动处理特殊字符）
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result); // 获取包含 admin 字段的用户信息
$num = mysqli_num_rows($result);

if ($num) {
    // 【关键】从数据库获取 admin 字段（0或1），转为整数确保类型正确
    $isAdmin = (int) $user['admin'];
    
    // 设置会话变量（明确使用 admin 字段的值）
    $_SESSION['loggedUsername'] = $user['username'];
    $_SESSION['isAdmin'] = $isAdmin; // 直接使用数据库中的 admin 值（0或1）

    // 【关键】登录后统一跳转至 id=1 页面（根据需求设置目标路径）
    $target = $isAdmin 
        ? "/module/default.php?id=1" // 管理员后台首页
        : "/Ashow/main.php?id=1"; // 普通用户首页

    echo "<script>alert('登录成功！');location.href = '$target';</script>";
} else {
    // 登录失败，清除会话
    unset($_SESSION['loggedUsername'], $_SESSION['isAdmin']);
    echo "<script>alert('登录失败！用户名或密码错误');history.back();</script>";
}

// 关闭数据库连接
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>