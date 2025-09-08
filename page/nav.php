<?php
// 开启会话，用于获取和管理用户登录状态，包括用户名、管理员权限等信息
session_start();

// 获取URL中的页面标识参数`id`，若未设置则默认显示首页（id=1），该参数用于侧边栏导航的当前页面高亮和功能模块区分
$id = $_GET['id'] ?? 1; 

// 检查用户是否已登录
if (!isset($_SESSION['loggedUsername']) || empty($_SESSION['loggedUsername'])) {
    // 若未登录，重定向到登录页面
    header("Location: ../page/login.php"); 
    exit;
}

// 获取管理员标识，默认值为 0
$isAdmin = $_SESSION['isAdmin'] ?? 0; 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>后台管理系统</title>
    <!-- 引入导航栏样式表，用于控制页面布局和视觉样式 -->
    <link rel="stylesheet" href="../page/css/nav.css">
</head>
<body>
    <div>
        <!-- 页面头部区域，显示系统标题和用户登录信息 -->
        <div class="header">
            <h1>鸡噗鸡后台管理系统</h1>
            <div class="logged">
                <!-- 显示当前登录用户名，使用htmlspecialchars防止XSS攻击 -->
                当前登录者：<strong><?= htmlspecialchars($_SESSION['loggedUsername']) ?></strong> 
                <?php if ($isAdmin): ?>
                <span style="color: #FFD700; margin-right: 15px;">管理员模式</span>
                <?php endif; ?> 
                <!-- 注销登录链接，点击后跳转至注销处理页面 -->
                <span class="logout"><a href="../module/logout.php">注销登录</a></span>
            </div>
        </div>

        <!-- 侧边栏导航菜单，按功能模块分组排列 -->
        <div class="sidebar">
            <!-- 系统基础功能：首页（默认页面，id=1） -->
            <a href="../module/default.php?id=1" <?= $id == 1 ? 'class="current"' : '' ?>>首页</a>

            <!-- 用户管理功能组，按操作频率排序，先个人操作后全局管理 -->
            <!-- 个人资料修改：普通用户和管理员均可访问，id=2，携带来源参数区分操作入口 -->
            <a href="../user/modify.php?id=2&source=member" <?= $id == 2 ? 'class="current"' : '' ?>>资料修改</a>

            <!-- 用户管理：仅限管理员访问，id=3，用于管理所有用户信息 -->
            <?php if ($isAdmin): ?>
            <a href="../user/admin.php?id=3" <?= $id == 3 ? 'class="current"' : '' ?>>用户管理</a>
            <?php endif; ?>

            <!-- 游戏管理功能组，按操作流程排序，先内容上传后内容管理 -->
            <!-- 游戏上传：允许有权限的用户上传新游戏，id=4，为流程起点 -->
            <a href="../game/game_upload.php?id=4" <?= $id == 4 ? 'class="current"' : '' ?>>游戏上传</a>

            <!-- 游戏管理：对已上传的游戏进行审核、编辑、删除等操作，id=5（原id=6调整为5以保持连续编号，便于功能扩展和维护） -->
            <a href="../game/game_admin.php?id=5" <?= $id == 5 ? 'class="current"' : '' ?>>游戏管理</a>
             <!-- 评论管理 -->
            <a href="../detail/details_admin.php?id=6" <?= $id == 6? 'class="current"' : '' ?>>评论管理</a>
            <!-- 测试 -->            
            <a href="../detail/test1.php?id=7" <?= $id == 7? 'class="current"' : '' ?>>评论测试1</a>
            <a href="../detail/test2.php?id=8" <?= $id == 8? 'class="current"' : '' ?>>评分测试2</a>
            <a href="../game/game_list.php?id=9" <?= $id == 9? 'class="current"' : '' ?>>列表测试3</a>    
        </div>
    </div>
</body>
</html>    