<?php
//game_upload.php（样式调整版）
session_start();
if (!isset($_SESSION['loggedUsername'])) {
    header("Location: ../login.php");
    exit;
}

// 获取游戏 ID
if (isset($_GET['id'])) {
    $game_id = $_GET['id'];
    // 查询游戏信息
    require_once '../module/sql_connet.php';
    $sql = "SELECT * FROM games WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $game_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $game = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改游戏 - 鸡噗鸡游戏平台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* 全局布局优化 */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            background: #f0f3f7; 
            min-height: 100vh; 
            padding: 20px; 
        }
        .main-container { 
            max-width: 900px; 
            width: 92%; 
            margin: 30px auto; 
            padding: 30px 20px; 
            background: #fff; 
            border-radius: 12px; 
            box-shadow: 0 6px 15px rgba(103, 151, 255, 0.1); 
            border: 1px solid #e0e8f7; 
        }

        /* 标题与表单间距 */
        .form-title { 
            text-align: center; 
            color: #2c6cbf; 
            font-size: 1.8rem; 
            margin-bottom: 25px; 
        }
        .form-group { 
            margin-bottom: 20px; 
        }

        /* 输入组件样式（统一紧凑化） */
        .form-control {
            width: 100%;
            padding: 10px 16px; 
            border: 1px solid #e0e8f7; 
            border-radius: 6px; 
            background: #f5f9ff; 
            font-size: 15px; 
            transition: border-color 0.2s ease;
        }
        .form-control:focus { 
            border-color: #78b5ff; 
            box-shadow: 0 0 0 2px rgba(120, 181, 255, 0.2); 
            outline: none; 
        }
        select { 
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='%2378b5ff' d='M2 4l5 5 5-5'/%3e%3c/svg%3e"); 
            background-size: 12px; 
            background-position: right 12px center; 
        }
        textarea { 
            resize: vertical; 
            min-height: 80px; 
            max-height: 150px; 
            padding: 10px 16px; 
        }

        /* 行内布局（开发者/标签、平台/日期等） */
        .inline-form-group {
            display: flex;
            gap: 15px;
            align-items: stretch;
        }
        .inline-form-group .form-group {
            flex: 1;
        }

        /* 文件上传区域 */
        .file-upload { 
            border: 1px dashed #e0e8f7; 
            border-radius: 6px; 
            padding: 20px; 
            text-align: center; 
            background: #f5f9ff; 
        }
        .file-upload label { 
            color: #78b5ff; 
            font-size: 1rem; 
            margin-bottom: 8px; 
            display: block; 
        }

        /* 按钮样式 */
        .submit-btn { 
            width: 100%; 
            max-width: 280px; 
            margin: 0 auto; 
            padding: 14px 40px; 
            background: #78b5ff; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            box-shadow: 0 4px 12px rgba(120, 181, 255, 0.2); 
            cursor: pointer; 
            transition: transform 0.1s ease;
        }
        .submit-btn:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 6px 15px rgba(120, 181, 255, 0.25); 
        }

        /* 响应式适配 */
        @media (max-width: 768px) {
            .main-container { 
                padding: 20px 15px; 
                border-radius: 10px; 
            }
            .inline-form-group { 
                flex-direction: column; 
                gap: 10px; 
            }
        }
    </style>
</head>
<body>
    <?php include_once '../page/nav.php'; ?>

    <div class="main-container">
        <h2 class="form-title">修改游戏</h2>
        <form 
            action="game_edit_process.php?id=<?= $game_id ?>" 
            method="post" 
            enctype="multipart/form-data"
            novalidate  
        >

            <!-- 游戏标题 -->
            <div class="form-group">
                <label>游戏标题：</label>
                <input 
                    type="text" 
                    name="title" 
                    class="form-control" 
                    placeholder="请输入游戏正式名称（如：塞尔达传说：王国之泪）"
                    value="<?= isset($game) ? htmlspecialchars($game['title']) : '' ?>"
                    required
                >
            </div>

            <!-- 开发者 & 标签（同行） -->
            <div class="inline-form-group">
                <div class="form-group">
                    <label>开发者</label>
                    <input 
                        type="text" 
                        name="Developer" 
                        class="form-control" 
                        placeholder="输入游戏开发者名称（如：暴雪、腾讯游戏）"
                        value="<?= isset($game) ? htmlspecialchars($game['Developer']) : '' ?>"
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
                        value="<?= isset($game) ? htmlspecialchars($game['tags']) : '' ?>"
                        required  
                    >
                </div>
            </div>

            <!-- 游戏平台 & 发布日期（同行） -->
            <div class="inline-form-group">
                <div class="form-group">
                    <label>游戏平台</label>
                    <select 
                        name="platform" 
                        class="form-control" 
                        required
                    >
                        <option value="" disabled selected>请选择游戏平台</option>
                        <option value="PC" <?= isset($game) && $game['platform'] === 'PC' ? 'selected' : '' ?>>PC</option>
                        <option value="PS5" <?= isset($game) && $game['platform'] === 'PS5' ? 'selected' : '' ?>>PlayStation 5</option>
                        <option value="Xbox" <?= isset($game) && $game['platform'] === 'Xbox' ? 'selected' : '' ?>>Xbox Series X/S</option>
                        <option value="Switch" <?= isset($game) && $game['platform'] === 'Switch' ? 'selected' : '' ?>>Nintendo Switch</option>
                        <option value="Mobile" <?= isset($game) && $game['platform'] === 'Mobile' ? 'selected' : '' ?>>手机游戏</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>发布日期</label>
                    <input 
                        type="date" 
                        name="release_date" 
                        id="release_date" 
                        class="form-control" 
                        value="<?= isset($game) ? $game['release_date'] : '' ?>"
                        required
                    >
                </div>
            </div>

            <!-- 游戏描述 -->
            <div class="form-group">
                <label>游戏描述</label>
                <textarea 
                    name="description" 
                    class="form-control" 
                    placeholder="请输入游戏详细描述（至少50字，介绍玩法、剧情等）"
                    required
                    minlength="50"  
                ><?= isset($game) ? htmlspecialchars($game['description']) : '' ?></textarea>
            </div>

            <!-- 封面图片上传 -->
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
                <button type="submit" class="submit-btn">立即修改</button>
            </div>

        </form>
    </div>

    <!-- 日期选择器脚本（功能不变） -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/zh.js"></script>
    <script>
        flatpickr("#release_date", {
            dateFormat: "Y-m-d",
            locale: "zh",
            allowInput: true,
            position: "auto"
        });
    </script>
</body>
</html>