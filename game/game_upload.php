<?php
//game_upload.php
// 游戏上传页，要求登录后访问，支持上传新游戏的详细信息
session_start();

// 验证用户登录状态，未登录则重定向
if (!isset($_SESSION['loggedUsername'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上传新游戏 - 鸡噗鸡游戏平台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* 全局样式重置与基础布局 */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            background: #f0f3f7; 
            min-height: 100vh; 
            padding: 20px; /* 页面整体内边距调小 */
        }
        .main-container { 
            max-width: 900px; 
            width: 92%; 
            margin: 30px auto; /* 容器上下边距调小 */
            padding: 30px 20px; /* 容器内边距调小 */
            background: linear-gradient(145deg, #ffffff 0%, #f8faff 100%); 
            border-radius: 12px; /* 圆角调小 */
            box-shadow: 0 8px 20px rgba(103, 151, 255, 0.1); /* 阴影调弱调小 */
            border: 1px solid rgba(120, 181, 255, 0.1); 
        }
        
        /* 表单标题样式 */
        .form-title { 
            text-align: center; 
            color: #2c6cbf; 
            font-size: 2rem; /* 标题字体调小 */
            margin-bottom: 30px; 
        }

        /* 表单组通用样式 */
        .form-group { 
            margin-bottom: 25px; /* 表单组间距调小 */
        }

        /* 输入框通用样式（进一步紧凑） */
        .form-control {
            width: 100%;
            padding: 10px 16px; /* 内边距调小，输入框高度降低 */
            border: 1px solid #e0e8f7; /* 边框颜色调浅 */
            border-radius: 6px; /* 圆角进一步调小 */
            background: #f5f9ff; /* 背景色调浅 */
            font-size: 15px; 
            transition: border-color 0.2s ease;
        }
        .form-control:focus { 
            border-color: #78b5ff; 
            box-shadow: 0 0 0 2px rgba(120, 181, 255, 0.2); /* 聚焦阴影调小 */
        }
        select { 
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='%2378b5ff' d='M2 4l5 5 5-5'/%3e%3c/svg%3e"); 
            background-size: 12px; /* 下拉箭头调小 */
            background-position: right 12px center; 
        }

        /* 文本域（游戏描述）样式调整 */
        textarea { 
            resize: vertical; 
            min-height: 80px; /* 描述框最小高度调至80px */
            max-height: 150px; /* 限制最大高度 */
            padding: 10px 16px; /* 内边距同步调小 */
        }

        /* 文件上传区域（封面图片）样式 */
        .file-upload { 
            border: 1px dashed #e0e8f7; /* 边框粗细调小，颜色调浅 */
            border-radius: 6px; 
            padding: 20px; /* 内边距调小，区域缩小 */
            text-align: center; 
            background: #f5f9ff; 
        }
        .file-upload label { 
            color: #78b5ff; 
            font-weight: 500; 
            font-size: 1rem; /* 标签字体调小 */
            margin-bottom: 8px; 
        }

        /* 行内布局样式（开发者/标签、标签/平台等） */
        .inline-form-group {
            display: flex;
            gap: 15px; /* 行内元素间距调小 */
        }
        .inline-form-group .form-group {
            flex: 1;
        }

        /* 日期选择器样式 */
        .date-input { 
            max-width: 500px; /* 日期输入框宽度调小 */
        }

        /* 提交按钮样式 */
        .submit-btn { 
            background: #78b5ff; 
            color: white; 
            padding: 14px 40px; /* 按钮内边距微调 */
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            box-shadow: 0 4px 12px rgba(120, 181, 255, 0.2); /* 按钮阴影调小 */
            width: 100%; 
            max-width: 280px; 
            display: block; 
            margin: 0 auto; 
        }
        .submit-btn:hover { 
            background: #66a0e6; 
            transform: translateY(-1px); /* 悬停位移调小 */
        }

        /* 响应式适配（移动端优化） */
        @media (max-width: 768px) {
            .main-container { 
                padding: 20px 15px; 
                border-radius: 10px; 
            }
            .inline-form-group {
                flex-direction: column; /* 小屏幕下自动垂直排列 */
            }
            textarea { 
                min-height: 100px; 
            }
        }
    </style>
</head>
<body>
    <?php include_once '../page/nav.php'; ?> <!-- 引入导航栏 -->

    <div class="main-container">
        <h2 class="form-title">上传新游戏</h2>
        <form 
            action="game_process.php" 
            method="post" 
            enctype="multipart/form-data"
            novalidate  
        >

            <!-- 游戏标题（第一行，宽度调小） -->
            <div class="form-group">
                <label>游戏标题：</label>
                <input 
                    type="text" 
                    name="title" 
                    class="form-control" 
                    placeholder="请输入游戏正式名称（如：塞尔达传说：王国之泪）"
                    required
                >
            </div>

            <!-- 开发者和标签（同一行） -->
            <div class="inline-form-group">
                <div class="form-group">
                    <label>开发者</label>
                    <input 
                        type="text" 
                        name="Developer" 
                        class="form-control" 
                        placeholder="输入游戏开发者名称（如：暴雪、腾讯游戏）"
                        required  
                    >
                </div>
                <div class="form-group">
                    <label>标签（逗号分隔）</label>
                    <input 
                        type="text" 
                        name="tags" 
                        class="form-control" 
                        placeholder="输入游戏标签（如：角色扮演,开放世界,冒险）"
                        required  
                    >
                </div>
            </div>

            <!-- 游戏平台和发布日期（同一行） -->
            <div class="inline-form-group">
                <div class="form-group">
                    <label>游戏平台</label>
                    <select 
                        name="platform" 
                        class="form-control" 
                        required
                    >
                        <option value="" disabled selected>请选择游戏平台</option>
                        <option value="PC">PC</option>
                        <option value="PS5">PlayStation 5</option>
                        <option value="Xbox">Xbox Series X/S</option>
                        <option value="Switch">Nintendo Switch</option>
                        <option value="Mobile">手机游戏</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>发布日期</label>
                    <input 
                        type="date" 
                        name="release_date" 
                        id="release_date" 
                        class="form-control date-input" 
                        required
                    >
                </div>
            </div>

            <!-- 游戏描述（高度调小） -->
            <div class="form-group">
                <label>游戏描述</label>
                <textarea 
                    name="description" 
                    class="form-control" 
                    placeholder="请输入游戏详细描述（至少50字，介绍玩法、剧情等）"
                    required
                    minlength="50"  
                ></textarea>
            </div>

            <!-- 封面图片上传（区域调小） -->
            <div class="form-group">
                <label>封面图片</label>
                <div class="file-upload">
                    <input 
                        type="file" 
                        name="cover_image" 
                        accept="image/jpeg,image/png"
                        required
                    >
                    <label>点击选择文件或拖放图片到此区域</label>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="form-group">
                <button type="submit" class="submit-btn">立即上传</button>
            </div>

        </form>
    </div>

    <!-- 日期选择器脚本 -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/zh.js"></script>
    <script>
        flatpickr("#release_date", {
            dateFormat: "Y-m-d",
            locale: "zh",
            allowInput: true,
            position: "auto",
            maxDate: "today" // 可选：限制可选日期为今天及之前
        });
    </script>
</body>
</html>