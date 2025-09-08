<?php
//postModify.php
// 用户资料更新处理页面（修复密码修改失败问题）

// 开启错误报告（调试用，生产环境需关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 初始化会话
session_start();

// 获取表单数据
$username = trim($_POST['username']);       // 用户名（去除首尾空格）
$pw = trim($_POST['pw']);                   // 新密码
$cpw = trim($_POST['cpw']);                 // 确认密码
$sex = $_POST['sex'];                        // 性别（0/1）
$email = trim($_POST['email']);              // 邮箱
$fav = @implode(",", $_POST['fav']);         // 爱好（数组转字符串）
$source = $_POST['source'];                  // 页面来源（admin/member）
$page = $_POST['page'];                      // 当前页码

// --------------------------- 权限验证 ---------------------------
// 普通用户只能修改自己的资料，管理员可修改任意用户
if ($source !== 'admin' && $username !== $_SESSION['loggedUsername']) {
    echo "<script>alert('无权限修改此用户');history.back();</script>";
    exit;
}

// --------------------------- 数据验证逻辑 ---------------------------
// 验证用户名（必填且格式正确）
if (empty($username)) {
    echo "<script>alert('用户名必须填写');history.back();</script>";
    exit;
}
if (!preg_match('/^[a-zA-Z0-9]{3,10}$/', $username)) {
    echo "<script>alert('用户名需3-10位字母/数字');history.back();</script>";
    exit;
}

// 验证密码（若填写则需符合规则且两次一致）
if (!empty($pw)) {
    if ($pw !== $cpw) {
        echo "<script>alert('两次输入的密码不一致');history.back();</script>";
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9_*]{6,10}$/', $pw)) {
        echo "<script>alert('密码需6-10位，含字母/数字/*/_');history.back();</script>";
        exit;
    }
}

// 验证邮箱（若填写则需格式正确）
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('邮箱格式不正确');history.back();</script>";
    exit;
}

// --------------------------- 数据库更新逻辑 ---------------------------
include_once '../module/sql_connet.php';      // 引入数据库连接

// 检查数据库连接
if (!$conn) {
    die("数据库连接失败: " . mysqli_connect_error());
}

// 构建更新语句（保留MD5加密，不做预处理）
if (!empty($pw)) {
    $sql = "UPDATE info SET 
                pw = '". md5($pw) ."', 
                email = '$email', 
                sex = '$sex', 
                fav = '$fav' 
            WHERE username = '$username'";
    $defaultUrl = $source === 'admin' 
        ? "../module/default.php?id=1&page={$page}" 
        : "../module/logout.php"; // 普通用户改密码后建议重新登录
} else {
    $sql = "UPDATE info SET 
                email = '$email', 
                sex = '$sex', 
                fav = '$fav' 
            WHERE username = '$username'";
    $defaultUrl = $source === 'admin' 
        ? "../module/default.php?id=1&page={$page}" 
        : "../module/default.php"; // 未改密码时跳转个人主页
}

// 执行更新并检查结果
$result = mysqli_query($conn, $sql);
$affectedRows = mysqli_affected_rows($conn);

// --------------------------- 结果处理 ---------------------------
if ($result && $affectedRows > 0) {
    // 清除可能的旧会话密码（如需重新登录）
    if ($source !== 'admin') {
        unset($_SESSION['loggedUsername']); // 强制退出，需重新登录
    }
    echo "<script>alert('更新成功！');location.href='$defaultUrl';</script>";
} else {
    // 显示详细错误（调试用）
    echo "数据库错误：" . mysqli_error($conn) . "<br>";
    echo "<script>alert('更新失败！请检查用户名是否存在');history.back();</script>";
}

// 关闭数据库连接
mysqli_close($conn);
?>