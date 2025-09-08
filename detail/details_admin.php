<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>评论管理后台</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            overflow-y: hidden;
            background: #f0f3f7;
        }

       .main {
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }

        table {
            background: #f5fbff;
            margin: 25px auto;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            width: 90%;
        }

        th {
            background: #78b5ff;
            color: white;
            font-weight: 600;
            padding: 16px;
            border-bottom: 2px solid #e0f0ff;
        }

        td {
            padding: 14px;
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid #e8f4ff;
            color: #4a4a4a;
            text-align: center;
        }

        tr:hover td {
            background: #f0f8ff;
        }

        a {
            color: #3d8bfd;
            transition: 0.2s;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
        }

        a:hover {
            background: rgba(61, 139, 253, 0.1);
            color: #2a6ebb;
        }

       .game-cover {
            max-width: 100px;
            max-height: 60px;
            border-radius: 4px;
        }

       .pagination {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php
        session_start();
        // 引入后台导航栏
        include_once '../page/nav.php';
        // 数据库连接
        include_once '../module/sql_connet.php';

        // 分页逻辑
        $sql = "SELECT COUNT(id) AS total FROM comments";
        $result = mysqli_query($conn, $sql);
        $total = mysqli_fetch_assoc($result)['total'];
        $perPage = 6;
        $page = $_GET['page'] ?? 1;
        include_once '../module/select_page.php';
        paging($total, $perPage);

        // 查询评论数据，关联游戏表和用户表
        $sql = "SELECT 
                   comments.*, 
                   games.title AS game_title, 
                   games.cover_image, 
                   info.username 
                FROM 
                   comments 
                JOIN 
                   games ON comments.game_id = games.id 
                JOIN 
                   info ON comments.user_id = info.Id 
                ORDER BY 
                   comments.created_at DESC 
                LIMIT $firstCount, $perPage";
        $result = mysqli_query($conn, $sql);
        $index = ($page - 1) * $perPage + 1;
        ?>
        <div class="main-content">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>序号</th>
                        <th>游戏封面</th>
                        <th>游戏标题</th>
                        <th>用户名</th>
                        <th>评论内容</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($comment = mysqli_fetch_assoc($result)) {
                            $coverPath = '../uploads/game_covers/' . $comment['cover_image'];
                            if (!file_exists($coverPath) || empty($comment['cover_image'])) {
                                $coverPath = 'placeholder_image.jpg';
                            }
                            $content = strip_tags($comment['content']);
                            $createdAt = date('Y-m-d H:i', strtotime($comment['created_at']));
                            $gameTitle = $comment['game_title'] ?? '';
                            $username = $comment['username'] ?? '';
                    ?>
                    <tr>
                        <td><?= $index++ ?></td>
                        <td><img src="<?= $coverPath ?>" class="game-cover" alt="<?= $gameTitle ?>封面"></td>
                        <td><?= htmlspecialchars($gameTitle) ?></td>
                        <td><?= htmlspecialchars($username) ?></td>
                        <td><?= $content ?></td>
                        <td><?= $createdAt ?></td>
                        <td>
                            <a href="../page/game.php?id=<?= $comment['game_id'] ?>&page=<?= $page ?>" class="btn-edit">详情</a>
                            <a href="javascript:confirmDelete(<?= $comment['id'] ?>)" class="btn-delete">删除</a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="7">暂无评论</td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <div class="pagination">
                <?= $pageNav // 显示分页导航 ?>
            </div>
        </div>
    </div>

    <script>
        // 删除确认函数
        function confirmDelete(commentId) {
            if (confirm('确定要删除该评论吗？此操作不可恢复！')) {
                window.location.href = 'comment_del.php?id=' + commentId + '&page=<?= $page ?>';
            }
        }
    </script>
</body>

</html>    