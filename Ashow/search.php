<?php
session_start();
require '../module/sql_connet.php';
// 初始化搜索关键词变量
$searchKeyword = '';
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    // 获取搜索关键词并进行安全处理
    $searchKeyword = mysqli_real_escape_string($conn, $_GET['search']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>搜索结果 - 鸡噗鸡游戏</title>
    <style>
               body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .game-container {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .game-container:hover {
            transform: translateY(-5px);
        }

        .game-container a {
            text-decoration: none;
            color: #333;
        }

        .game-container h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .game-container p {
            margin: 5px 0;
            color: #666;
        }

        .game-container img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        .no-result {
            text-align: center;
            color: #999;
        }
    </style>
</head>

<body>
    <h1>搜索结果: <?php echo htmlspecialchars($searchKeyword); ?></h1>
    <?php
    if (!empty($searchKeyword)) {
        // 构建 SQL 查询语句
        $sql = "SELECT * FROM games WHERE title LIKE '%$searchKeyword%'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $gameId = $row['id'];
                $title = $row['title'];
                $platform = $row['platform'];
                $releaseDate = $row['release_date'];
                $description = htmlspecialchars($row['description']);
                $coverImage = $row['cover_image'];
                // 每个游戏数据用一个容器包裹
                echo '<div class="game-container">';
                echo "<a href='../page/game.php?id={$gameId}'>";
                echo "<h2>{$title}</h2>";
                echo '</a>';
                echo "<p>平台: {$platform}</p>";
                echo "<p>发布日期: {$releaseDate}</p>";
                echo "<p>描述: {$description}</p>";
                echo "<img src='{$coverImage}' alt='{$title}封面'>";
                echo '</div>';
            }
        } else {
            echo '<p class="no-result">未找到匹配的游戏</p>';
        }
    }
    ?>
</body>

</html>    