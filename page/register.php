<?php
//register.php
//用户注册页面
session_start();
require '../module/sql_connet.php'; // 假设此文件中定义了 $conn（过程式数据库连接资源）

$error = '';
$success = '';

// 开启错误报告（开发环境使用，生产环境请关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取并清理输入
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['pw']; 
    $confirm_password = $_POST['confirm-password'];

    // 基础验证功能区块
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '所有字段都必须填写';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } elseif (strlen($password) < 6) {
        $error = '密码长度不能少于6位';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } else {
        // 检查用户名/邮箱是否已存在功能区块（过程式预处理语句）
        $check_sql = "SELECT Id FROM info WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);

        if (!mysqli_stmt_execute($check_stmt)) {
            $error = '系统错误，请稍后再试（数据库查询失败: ' . mysqli_stmt_error($check_stmt) . '）';
        } else {
            $check_result = mysqli_stmt_get_result($check_stmt);
            if (mysqli_num_rows($check_result) > 0) {
                $error = '用户名或邮箱已被注册';
            } else {
                // 插入新用户功能区块（过程式预处理语句）
                $hashed_password = md5($password); 
                $insert_sql = "INSERT INTO info (username, pw, email) VALUES (?, ?, ?)"; 
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                // 绑定参数与字段数量匹配（"sss" 对应三个字符串）
                mysqli_stmt_bind_param($insert_stmt, "sss", $username, $hashed_password, $email); 

                if (mysqli_stmt_execute($insert_stmt)) {
                    $_SESSION['user_id'] = mysqli_insert_id($conn);
                    $_SESSION['username'] = $username;
                    $success = '注册成功！正在跳转...';
                    header("Refresh: 2; url=df_login.php");
                    exit; // 终止脚本执行，确保跳转
                } else {
                    $error = '注册失败，请稍后再试（数据库插入失败: ' . mysqli_stmt_error($insert_stmt) . '）';
                }
                mysqli_stmt_close($insert_stmt);
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <link rel="stylesheet" href="./css/register.css" type="text/css" media="all" />
</head>
<body>
    <div class="top-banner">
        <div class="banner-content">
            <div class="banner-title">欢迎加入我们的社区</div>
            <div class="banner-subtitle">请您注册一个账号，开启全新体验</div>
        </div>
    </div>

    <!-- 注册表单 -->
    <div class="main-container">
        <div class="register-container">
            <h2>创建账号</h2>
            <!-- 显示成功信息 -->
            <?php if (!empty($success)): ?>
                <div class="success-message"><?= $success ?></div>
            <?php endif; ?>
            <!-- 显示错误信息 -->
            <?php if (!empty($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            <form id="registerForm" method="post" action="register.php"> 
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required placeholder="输入您的用户名">
                    <div class="error-message" id="username-error">用户名不能为空</div>
                </div>
                
                <div class="form-group">
                    <label for="email">电子邮箱</label>
                    <input type="email" id="email" name="email" required placeholder="输入您的邮箱地址">
                    <div class="error-message" id="email-error">请输入有效的邮箱地址</div>
                </div>
                
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="pw" required placeholder="设置密码(至少6位)">
                    <div class="error-message" id="password-error">密码长度不能少于6位</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">确认密码</label>
                    <input type="password" id="confirm-password" name="confirm-password" required placeholder="再次输入密码">
                    <div class="error-message" id="confirm-error">两次输入的密码不一致</div>
                </div>
                
                <button type="submit" class="submit-btn">立即注册</button>
                <div class="login-link">
                    已有账号？<a href="df_login.php">点击登录</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        
        function resetErrors() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.classList.remove('error');
            });
            
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.style.display = 'none';
            });
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(`${fieldId}-error`);
            
            field.classList.add('error');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>
</html>