<?php
//modify.php
// 初始化会话，获取来源和页码参数
session_start();
$source = $_GET['source'] ?? '';
$page = $_GET['page'] ?? '';

// 验证页面来源合法性（必须为admin或member）
if (!$source || !in_array($source, ['admin', 'member'])) {
    echo "<script>alert('页面来源错误');location.href='../index.php';</script>";
    exit;
}

// 验证页码参数（存在时必须为数字）
if ($page && !is_numeric($page)) {
    echo "<script>alert('参数错误');location.href='../index.php';</script>";
    exit;
}
?>
<html lang="cn">
<head>
    <title>后台管理系统</title>
    <style>
        /* 页面整体样式：重置默认样式，设置背景和字体 */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            background: #f0f3f7;
        }

        /* 主要内容区域：居中显示，渐变背景，卡片式设计 */
        .main {
            width: 40%;
            margin: 30px auto;
            padding: 30px;
            background: linear-gradient(145deg, #ffffff 0%, #f5f9ff 100%);
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(103, 151, 255, 0.15),
                inset 0 -2px 8px rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(120, 181, 255, 0.2);
        }

        /* 表单表格样式：响应式设计，分隔线样式 */
        table { width: 100%; border-collapse: collapse; }
        td { padding: 18px; border-bottom: 1px solid rgba(107, 144, 220, 0.3); background: #f5f9ff; }
        td[align="right"] { width: 15%; font-weight: 500; } /* 标签列样式 */

        /* 输入框通用样式：统一边框和交互效果 */
        input:not([type="radio"]):not([type="checkbox"]) {
            width: 90%; padding: 10px; border: 1px solid #cce4ff; border-radius: 6px;
        }
        input:focus { border-color: #78b5ff; box-shadow: 0 0 8px rgba(120, 181, 255, 0.2); }

        /* 按钮样式：突出操作按钮，弹性布局 */
        .button-container { display: flex; gap: 15px; justify-content: flex-end; width: 50%; }
        input[type="submit"], input[type="reset"] {
            padding: 10px 25px; background: #78b5ff; color: white; border: none; cursor: pointer;
        }
    </style>
</head>
<body>
<div>
    <?php
    // 引入导航栏和数据库连接
    include_once '../page/nav.php';
    include_once '../module/sql_connet.php';
    
    // 获取用户名参数（来源为admin时通过URL获取，否则使用当前登录用户）
    $username = $_GET['username'] ?? '';
    $isAdmin = $source === 'admin'; // 判断是否为管理员操作
    
    // 根据来源构建查询语句：管理员可修改任意用户，普通用户只能修改自己
    $sql = $isAdmin ? "SELECT * FROM info WHERE username = '$username'" : 
                     "SELECT * FROM info WHERE username = '" . $_SESSION['loggedUsername'] . "'";
    
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) === 0) {
        die("未找到有效用户！"); // 防止无效用户访问
    }
    $info = mysqli_fetch_array($result);
    $fav = explode(",", $info['fav']); // 解析爱好数组
    ?>
    <div class="main">
        <!-- 用户资料修改表单，提交前触发前端验证 -->
        <form action="postModify.php" method="post" onsubmit="return check()">
            <table align="center" cellpadding="10" cellspacing="0">
                <tr>
                    <td align="right">用户名</td>
                    <td align="left">
                        <!-- 用户名只读，显示当前用户标识 -->
                        <input name="username" readonly value="<?php echo $info['username']; ?>">
                    </td>
                </tr>
                <tr>
                    <td align="right">密码</td>
                    <td align="left">
                        <!-- 密码输入框，留空表示不修改密码 -->
                        <input type="password" name="pw" placeholder="不修改密码请留空">
                    </td>
                </tr>
                <tr>
                    <td align="right">确认密码</td>
                    <td align="left">
                        <!-- 确认密码输入框，需与密码一致 -->
                        <input type="password" name="cpw" placeholder="不修改密码请留空">
                    </td>
                </tr>
                <tr>
                    <td align="right">性别</td>
                    <td align="left">
                        <!-- 性别单选框，根据数据库值默认选中 -->
                        <input name="sex" type="radio" <?php if ($info['sex']) echo "checked"; ?> value="1">男
                        <input name="sex" type="radio" <?php if (!$info['sex']) echo "checked"; ?> value="0">女
                    </td>
                </tr>
                <tr>
                    <td align="right">信箱</td>
                    <td align="left">
                        <!-- 邮箱输入框，支持格式验证 -->
                        <input name="email" value="<?php echo $info['email']; ?>">
                    </td>
                </tr>
                <tr>
                    <td align="right">爱好</td>
                    <td align="left">
                        <!-- 爱好多选框，根据存储值回显选中状态 -->
                        <input name="fav[]" type="checkbox" <?php if (in_array('听音乐', $fav)) echo 'checked'; ?> value="听音乐">听音乐
                        <input name="fav[]" type="checkbox" <?php if (in_array('玩游戏', $fav)) echo 'checked'; ?> value="玩游戏">玩游戏
                        <input name="fav[]" type="checkbox" <?php if (in_array('踢足球', $fav)) echo 'checked'; ?> value="踢足球">
                        <input name="fav[]" type="checkbox" <?php if (in_array('其他', $fav)) echo 'checked'; ?> value="其他">其他
                    </td>
                </tr>
                <tr>
                    <td align="right"></td>
                    <td align="left" class="button-container">
                        <input type="submit" value="提交"> <!-- 提交表单按钮 -->
                        <input type="reset" value="重置"> <!-- 重置表单按钮 -->
                        <!-- 隐藏域传递来源和页码参数，用于跳转回原页面 -->
                        <input type="hidden" name="source" value="<?php echo $source; ?>">
                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script>
    function check() {
        // 获取表单值并去除前后空格
        const pw = document.getElementsByName('pw')[0].value.trim();
        const cpw = document.getElementsByName('cpw')[0].value.trim();
        const email = document.getElementsByName('email')[0].value.trim();
        
        // 密码格式验证：6-10位，包含字母、数字、*、_
        const pwreg = /^[a-zA-Z0-9_*]{6,10}$/;
        if (pw.length > 0 && (!pwreg.test(pw) || pw !== cpw)) {
            alert(pw !== cpw ? '密码和确认密码必须相同！' : 
                  '密码需6-10位，由字母、数字、*、_组成！');
            return false;
        }
        
        // 邮箱格式验证：基本邮箱格式检查
        const emailReg = /^[a-zA-Z0-9_\-]+@([a-zA-Z0-9]+\.)+(com|cn|net|org)$/;
        if (email.length > 0 && !emailReg.test(email)) {
            alert('信箱格式不正确！');
            return false;
        }
        
        return true; // 所有验证通过后提交表单
    }
</script>
</body>
</html>