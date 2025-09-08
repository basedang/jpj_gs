<?php
//default.php
// 该页面为后台管理系统首页，主要功能包括：
// 1. 登录状态验证：检查用户是否登录，未登录则重定向至登录页面
// 2. 欢迎模块：显示当前登录用户名、日期和实时时间（带呼吸灯效果）
// 3. 功能导航：通过响应式网格展示用户管理、内容管理等功能模块，支持鼠标悬停动画交互

session_start();
// 登录验证：未登录则重定向
if (!isset($_SESSION['loggedUsername'])) {
    header("Location: ../login.php");
    exit;
}
?>
<html lang="cn">
<head>
    <title>后台管理系统</title>
    <style>
        /* 全局样式 */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            background: #f0f3f7;
        }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        
        /* 欢迎模块样式 */
        .welcome-section {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .welcome-title { color: #2c3e50; font-size: 2.5em; margin-bottom: 10px; }
        
        /* 功能模块网格 */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .dashboard-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            text-decoration: none !important;
            color: inherit;
        }
        .dashboard-card:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .card-icon { font-size: 2.5em; color: #78b5ff; margin-bottom: 15px; }
        .card-title { color: #34495e; font-size: 1.2em; margin: 10px 0; }
        
        /* 实时时间样式 */
        #liveTime {
            color: #78b5ff;
            font-size: 1.4em;
            font-weight: bold;
            text-shadow: 0 0 8px rgba(255,215,0,0.3);
            border-radius: 5px;
        }
    </style>
    <!-- 引入Font Awesome图标库（用于功能模块图标） -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div>
        <?php include_once '../page/nav.php'; ?>
        
        <div class="container">
            <!-- 欢迎模块：显示登录用户、日期和实时时间 -->
            <div class="welcome-section">
                <h1 class="welcome-title">欢迎回来，<?= htmlspecialchars($_SESSION['loggedUsername']); ?>！</h1>
                <p>
                    今天是 <?= date("Y年m月d日"); ?> 
                    <span id="liveTime"><?= date("H:i:s"); ?></span>，祝您工作顺利！
                </p>
            </div>

            <!-- 功能模块网格：四个响应式卡片，支持悬停缩放和阴影效果 -->
            <div class="dashboard-grid">
                <a href="#" class="dashboard-card">
                    <i class="fas fa-users card-icon"></i>
                    <h3 class="card-title">用户管理</h3>
                    <p class="card-description">管理注册用户信息，设置权限和角色</p>
                </a>

                <a href="#" class="dashboard-card">
                    <i class="fas fa-file-alt card-icon"></i>
                    <h3 class="card-title">内容管理</h3>
                    <p class="card-description">管理网站内容，编辑文章和页面</p>
                </a>

                <a href="#" class="dashboard-card">
                    <i class="fas fa-chart-line card-icon"></i>
                    <h3 class="card-title">数据统计</h3>
                    <p class="card-description">查看网站访问数据和用户行为分析</p>
                </a>

                <a href="#" class="dashboard-card">
                    <i class="fas fa-cog card-icon"></i>
                    <h3 class="card-title">系统设置</h3>
                    <p class="card-description">调整系统参数和全局配置</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        // 实时时间更新（带呼吸灯效果）
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('zh-CN', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('liveTime').textContent = timeString;
            
            // 呼吸灯效果：根据秒数动态调整透明度
            const seconds = now.getSeconds();
            document.getElementById('liveTime').style.opacity = 0.9 + Math.sin(seconds * Math.PI / 30) * 0.1;
        }
        // 初始化并定时更新时间（每秒一次）
        updateTime();
        setInterval(updateTime, 1000);

        // 功能卡片悬停动画增强
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.addEventListener('mouseover', () => card.style.transform = 'translateY(-7px) scale(1.02)');
            card.addEventListener('mouseout', () => card.style.transform = 'translateY(0) scale(1)');
        });
    </script>
</body>
</html>