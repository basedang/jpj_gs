<?php
//连接数据库服务器,注意用的是mysqli扩展。

//第一步，连接数据库服务器
$servername = "localhost:3306";
$username = "root";
$password = "root"; // 确保这与你在 MySQL 中设置的密码一致
$dbname = "jipujidb";
//声明
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'jipujidb');
define('DB_USER', 'game_admin');
define('DB_PASS', 'SecurePass123!');

// 创建连接
$conn = mysqli_connect($servername, $username, $password, $dbname);
if(!$conn){
    die("连接数据库服务器失败".mysqli_connect_error());
}
//第二步，设置字符集
mysqli_query($conn,"set names utf8");
?>