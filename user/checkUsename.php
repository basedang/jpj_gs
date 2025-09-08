<?php
//checkUsename.php
// 引入数据库连接文件，用于后续数据库操作
include_once '../module/sql_connet.php';

// 从POST请求获取用户输入的用户名
$username = $_POST['username'];

// 初始化数组，用于存储检查结果和消息
$a = array();

// 检查用户名是否为空，若为空则返回错误信息
if (empty($username)) {
    $a['code'] = 1;
    $a['msg'] = '用户名不能为空';
} else {
    // 构建SQL查询语句，检查数据库中是否已存在该用户名
    $sql = "select 1 from info where username = '$username'";
    $result = mysqli_query($conn, $sql);

    // 根据查询结果判断用户名是否可用
    if (mysqli_num_rows($result)) {
        $a['code'] = 0;
        $a['msg'] = '此用户名不可用';
    } else {
        $a['code'] = 2;
        $a['msg'] = '此用户名可用';
    }
}

// 将结果数组转换为JSON格式并输出
echo json_encode($a);
?>    