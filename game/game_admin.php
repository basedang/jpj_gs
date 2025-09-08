<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <!-- game_admin.php -->
    <!-- 该页面是游戏管理后台页面，主要功能是展示游戏列表，支持分页查看，并且提供对每个游戏的详情查看、评分查看、编辑和删除操作。 -->
    <meta charset="UTF-8">
    <title>游戏管理后台</title>
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

        /* 模态框样式 */
       .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

       .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
        }

       .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

       .close:hover,
       .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php
        session_start();

        if (!isset($_SESSION['loggedUsername']) || empty($_SESSION['loggedUsername'])) {
            echo "<script>alert('请先登录！'); window.location.href = '../page/login.php';</script>";
            exit;
        }

        // 引入后台导航栏
        include_once '../page/nav.php';
        // 数据库连接
        include_once '../module/sql_connet.php';

        // 分页逻辑
        $sql = "SELECT COUNT(id) AS total FROM games";
        $result = mysqli_query($conn, $sql);
        $total = mysqli_fetch_assoc($result)['total'];
        $perPage = 6;
        $page = $_GET['page'] ?? 1;
        include_once '../module/select_page.php';
        paging($total, $perPage);

        // 查询游戏数据
        $sql = "SELECT games.*, ROUND(AVG(ratings.score), 1) as avg_score 
                FROM games 
                LEFT JOIN ratings ON games.id = ratings.game_id 
                GROUP BY games.id 
                ORDER BY games.release_date DESC 
                LIMIT $firstCount, $perPage";
        $result = mysqli_query($conn, $sql);
        $index = ($page - 1) * $perPage + 1;

        // 处理评分提交
        // if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_id']) && isset($_POST['score'])) {
        //     // 检查 loggedUsername 是否存在，若不存在则提示登录（可根据实际登录逻辑调整，这里假设登录后应存在 loggedUsername)
        //     if (!isset($_SESSION['loggedUsername'])) {
        //         echo "<script>alert('请先登录或登录信息异常！'); window.location.href = '../page/login.php';</script>";
        //         exit;
        //     }
        //     $user_id = $_SESSION['loggedUsername'];
        //     $game_id = $_POST['game_id'];
        //     $score = $_POST['score'];

        //     try {
        //         $conn->begin_transaction();

        //         // 插入或更新评分记录（根据(user_id, game_id)唯一约束）
        //         $stmt = $conn->prepare("
        //             INSERT INTO ratings (user_id, game_id, score)
        //             VALUES (?, ?, ?)
        //             ON DUPLICATE KEY UPDATE 
        //                 score = VALUES(score)
        //         ");
        //         $stmt->bind_param("iii", $user_id, $game_id, $score);

        //         if (!$stmt->execute()) {
        //             throw new Exception("评分记录更新失败");
        //         }

        //         // 更新游戏表中的平均分（通过子查询计算最新平均分）
        //         $updateStmt = $conn->prepare("
        //             UPDATE games 
        //             SET avg_score = (SELECT ROUND(AVG(score), 1) FROM ratings WHERE game_id = ?)
        //             WHERE id = ?
        //         ");
        //         $updateStmt->bind_param("ii", $game_id, $game_id);

        //         if (!$updateStmt->execute()) {
        //             throw new Exception("游戏平均分更新失败");
        //         }

        //         $conn->commit(); // 提交事务
        //         echo "<script>alert('评分提交成功');</script>";
        //     } catch (Exception $e) {
        //         $conn->rollback(); // 回滚事务
        //         error_log("评分处理错误: " . $e->getMessage() . " [用户ID: $user_id, 游戏ID: $game_id]");
        //         echo "<script>alert('" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
        //     } finally {
        //         // 资源释放
        //         foreach (['stmt', 'updateStmt'] as $var) {
        //             if (isset($$var) && $$var instanceof mysqli_stmt) {
        //                 $$var->close();
        //             }
        //         }
        //     }
        // }
        ?>
        <div class="main-content">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>序号</th>
                        <th>封面</th>
                        <th>游戏名称</th>
                        <th>平台</th>
                        <th>发售日</th>
                        <th>平均评分</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($game = mysqli_fetch_assoc($result)) {
                        $coverPath = '../uploads/game_covers/' . $game['cover_image'];
                        if (!file_exists($coverPath)) {
                            $coverPath = 'placeholder_image.jpg';
                        }
                        $avg_score = $game['avg_score'] ? $game['avg_score'] : '暂无评分';
                    ?>
                    <tr>
                        <td><?= $index++ ?></td>
                        <td><img src="<?= $coverPath ?>" class="game-cover" alt="<?= $game['title'] ?>封面"></td>
                        <td><?= htmlspecialchars($game['title']) ?></td>
                        <td><?= $game['platform'] ?></td>
                        <td><?= $game['release_date'] ?></td>
                        <td><?= $avg_score ?></td>
                        <td>
                            <a href="../page/game.php?id=<?= $game['id'] ?>&page=<?= $page ?>" class="btn-edit">详情</a>
                            <a href="game_edit.php?id=<?= $game['id'] ?>&page=<?= $page ?>" class="btn-edit">编辑</a>
                            <a href="javascript:confirmDelete(<?= $game['id'] ?>)" class="btn-delete">删除</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="pagination">
                <?= $pageNav // 显示分页导航 ?>
            </div>
        </div>
    </div>

    <!-- 评分模态框 -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRatingModal()">&times;</span>
            <h2>为游戏评分</h2>
            <form id="ratingForm" method="post">
                <input type="hidden" id="gameIdInput" name="game_id">
                <label for="score">请输入 1 - 10 的评分：</label>
                <input type="number" id="score" name="score" min="1" max="10" required>
                <button type="submit">提交评分</button>
            </form>
        </div>
    </div>

    <script>
        // 删除确认函数
        function confirmDelete(gameId) {
            if (confirm('确定要删除该游戏吗？此操作不可恢复！')) {
                window.location.href = 'game_del.php?id=' + gameId + '&page=<?= $page ?>';
            }
        }

        // 打开评分模态框
        function openRatingModal(gameId) {
            document.getElementById('gameIdInput').value = gameId;
            document.getElementById('ratingModal').style.display = "block";
        }

        // 关闭评分模态框
        function closeRatingModal() {
            document.getElementById('ratingModal').style.display = "none";
        }

        // 点击模态框外部关闭模态框
        window.onclick = function (event) {
            var modal = document.getElementById('ratingModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>