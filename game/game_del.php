<?php
//game_del.php
// 该页面为游戏删除处理页，仅限管理员访问，用于删除指定游戏及其关联的评论和评分数据，通过数据库事务保证操作的原子性

// 启动会话并引入依赖文件
session_start();            // 管理用户会话状态
include_once '../user/checkAdmin.php'; // 验证管理员权限
include_once '../module/sql_connet.php'; // 建立数据库连接

// 获取并验证游戏ID及当前页码
$gameId = (int)$_GET['id'];       // 强制转换为整数防止非法参数
$page = (int)($_GET['page'] ?? 1); // 页码默认值为1

// 检查游戏ID合法性，非法时终止并提示
if ($gameId < 1) {
    die('<script>alert("非法游戏ID");history.go(-1);</script>');
}

try {
    mysqli_begin_transaction($conn); // 开启事务确保数据一致性
    
    // 级联删除关联数据（评论→评分→游戏主记录）
    mysqli_query($conn, "DELETE FROM comments WHERE game_id = $gameId");
    mysqli_query($conn, "DELETE FROM ratings WHERE game_id = $gameId");
    mysqli_query($conn, "DELETE FROM games WHERE id = $gameId");
    
    mysqli_commit($conn); // 所有操作成功则提交事务
    
    // 操作成功后跳转回游戏管理页面
    echo '<script>alert("游戏删除成功");window.location.href="game_admin.php?page='.$page.'";</script>';
    
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn); // 出错时回滚事务
    
    // 显示错误信息并返回上一页
    echo '<script>alert("删除失败：'.addslashes($e->getMessage()).'");history.go(-1);</script>';
}