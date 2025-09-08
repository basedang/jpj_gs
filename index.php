<?php
session_start();
include_once '../module/sql_connet.php';
$id = 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>鸡噗鸡游戏</title>
    <style>
        :root {
            --primary-color: #FFA500;
            --secondary-color: #FFD700;
            --dark-color: #FF8C00;
            --light-color: #FFFACD;
            --text-color: #333;
            --text-light: #666;
            --bg-color: white;
            --sidebar-width: 200px;
            --sidebar-collapsed-width: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'PingFang SC', 'Microsoft YaHei', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* 导航栏 */
        .header {
            background: linear-gradient(135deg, yellow, #FF8C00);
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-img {
            width: 100px;
            height: 50px;
            object-fit: contain;
            margin-right: 100px;
        }

        .search-container {
            display: flex;
            align-items: center;
            flex-grow: 1;
            max-width: 600px;
            margin: 0 20px;
        }

        .search-input {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            width: 80%;
            outline: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            font-size: 16px;
        }

        .search-button {
            background: none;
            border: none;
            margin-left: -40px;
            cursor: pointer;
        }

        .search-button svg {
            width: 20px;
            height: 20px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            color: white;
        }

        .user-menu-logged-in,
        .user-menu-not-logged-in {
            display: flex;
            align-items: center;
        }

        .user-name {
            margin-right: 15px;
        }

        .user-menu-item {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-menu-item svg {
            margin-right: 5px;
        }

        /* 左侧导航栏 */
        .sidebar {
            position: fixed;
            left: 0;
            top: 75px;
            width: var(--sidebar-width);
            height: calc(100vh - 75px);
            background-color: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 90;
            padding: 20px 0;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-item {
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            font-weight: 600;
            padding: 12px 20px;
            color: var(--text-color);
            text-decoration: none;
            text-indent: 20px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            white-space: nowrap;
        }

        .sidebar-link:hover {
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        .sidebar-link.active {
            background-color: var(--light-color);
            border-left: 4px solid var(--primary-color);
            color: var(--dark-color);
        }

        .sidebar-icon {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-container">
            <a href="/Ashow/main.php" class="logo-link">
                <img src="/page/images/logo.png" alt="鸡噗鸡游戏" class="logo-img">
            </a>
            <div class="search-container">
                <!-- 修改表单的 action 属性为新页面的地址 -->
                <form action="search.php" method="get">
                    <input type="text" class="search-input" name="search" placeholder="搜索游戏...">
                    <button type="submit" class="search-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
            </div>
            <div class="user-menu">
                <?php if (isset($_SESSION['loggedUsername']) && $_SESSION['loggedUsername'] !== ''): ?>
                    <div class="user-menu-logged-in">
                        <div class="user-name">
                            欢迎，<?= htmlspecialchars($_SESSION['loggedUsername']) ?>
                        </div>
                        <div class="user-menu-item" onclick="logout()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>注销登录</span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 未登录状态下的菜单 -->
                    <div class="user-menu-not-logged-in">
                        <div class="user-name">未登录</div>
                        <div class="user-menu-item" onclick="login()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            <span>登录/注册</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <aside class="sidebar">
        <nav>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="../Ashow/main.php?id=1" <?= $id == 1? 'class="current sidebar-link"' : 'class="sidebar-link"'?>>
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span class="sidebar-text">首页</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../Ashow/charts.php?id=2&source=member" <?= $id == 2? 'class="current sidebar-link"' : 'class="sidebar-link"'?>>
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                        <span class="sidebar-text">排行榜</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../user/admin.php?id=3" <?= $id == 3? 'class="current sidebar-link"' : 'class="sidebar-link"'?>>
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="sidebar-text">用户管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../game/game_upload.php?id=4" <?= $id == 4? 'class="current sidebar-link"' : 'class="sidebar-link"'?>>
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <line x1="8" y1="10" x2="16" y2="10"></line>
                            <line x1="8" y1="14" x2="14" y2="14"></line>
                        </svg>
                        <span class="sidebar-text">游戏上传</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    <script>
        function login() {
            window.location.href = '../page/df_login.php'; // 替换为实际的登录页面地址
        }
        function logout() {
            window.location.href = '../module/logout.php'; // 替换为实际的注销页面地址
        }
    </script>
</body>
</html>    