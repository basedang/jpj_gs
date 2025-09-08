<?php
//admin.php
// 引入权限检查文件，确保仅管理员可访问
include_once 'checkAdmin.php';
?>
<html lang="cn">
<head>
    <!-- 设置页面标题为后台管理系统 -->
    <title>后台管理系统</title>
    <style>
        body {
            /* 去除边距、设置字体、隐藏滚动条和背景颜色 */
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            background: #f0f3f7;
        }
        .main {
            /* 设置内容区宽度、居中及文本对齐 */
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }
        table {
            /* 设置表格样式，包括背景、边距、边框、圆角、阴影和宽度 */
            background: #f5fbff;
            margin: 25px auto;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            width: 90%;
        }

        th {
            /* 设置表头样式，包括背景、文字颜色、粗细、内边距和边框 */
            background: #78b5ff;
            color: white;
            font-weight: 600;
            padding: 16px;
            border-bottom: 2px solid #e0f0ff;
        }

        td {
            /* 设置单元格样式，包括内边距、背景、边框、文字颜色和对齐 */
            padding: 14px;
            background: rgba(255,255,255,0.9);
            border-bottom: 1px solid #e8f4ff;
            color: #4a4a4a;
            text-align: center
        }

        tr:hover td {
            /* 设置表格行悬停背景颜色 */
            background: #f0f8ff;
        }

        a {
            /* 设置链接样式，包括颜色、过渡、内边距、圆角和去除下划线 */
            color: #3d8bfd;
            transition: 0.2s;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none; 
        }

        a:hover {
            /* 设置链接悬停样式，包括背景和文字颜色及去除下划线 */
            background: rgba(61, 139, 253, 0.1);
            color: #2a6ebb;
            text-decoration: none; 
        }
    </style>
</head>
<body>
<div>
    <?php
    // 引入导航栏、数据库连接和分页处理文件
    include_once '../page/nav.php';
    include_once '../module/sql_connet.php';
    include_once '../module/select_page.php';

    // 统计记录总数
    $sql = "select count(id) as total from info";
    $result = mysqli_query($conn, $sql);
    $info = mysqli_fetch_array($result);
    $total = $info['total'];

    // 设置每页显示数量和读取当前页码
    $perPage = 10;
    $page = $_GET['page'] ?? 1;

    // 调用分页函数
    paging($total, $perPage);

    // 查询当前页数据
    $sql = "select * from info order by id desc limit $firstCount,$perPage";
    $result = mysqli_query($conn, $sql);
    ?>
 <div class="main">
 <table cellspacing="0" cellpadding="10" width="90%">
        <tr>
            <!-- 定义表格表头 -->
            <td>序号</td>
            <td>用户名</td>
            <td>性别</td>
            <td>邮箱</td>
            <td>爱好</td>
            <td>是否管理员</td>
            <td>操作</td>
        </tr>
        <?php
        // 计算当前页数据起始序号并循环显示数据
        $i = ($page - 1) * $perPage + 1;
        while ($info = mysqli_fetch_array($result)) {
            ?>
            <tr>
                <!-- 显示序号、用户信息及操作链接 -->
                <td><?php echo $i; ?></td>
                <td><?php echo $info['username']; ?></td>
                <td><?php echo $info['sex'] ? '男' : '女'; ?></td>
                <td><?php echo $info['email']; ?></td>
                <td><?php echo $info['fav']; ?></td>
                <td><?php echo $info['admin'] ? '是' : '否'; ?></td>
                <td>
                    <!-- 提供修改资料链接 -->
                    <a href="modify.php?id=2&username=<?php echo $info['username']; ?>&source=admin&page=<?php echo $page; ?>">修改资料</a>
                    <?php if ($info['username'] !== 'admin') : ?>
                        <!-- 非admin用户提供删除链接 -->
                        <a href="javascript:del('<?= urlencode($info['username']) ?>');">删除用户</a>
                    <?php else : ?>
                        <!-- admin用户显示灰色删除文字 -->
                        <span style="color:gray">删除用户</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <?php
    // 显示分页导航
    echo $pageNav;
    ?>
 </div>
</div>
<script>
    function del(name) {
        // 弹出确认删除提示框，确认后跳转删除页面
        if (confirm('您确定要删除用户 ' + decodeURIComponent(name) + ' ?')) {
            location.href = 'del.php?username=' + name;
        }
    }
</script>
</body>
</html>    