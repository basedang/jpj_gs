<?php
//checkAdmin.php
//判断是不是管理员,如果不是管理员，则跳转到登录页面。
session_start();
if(!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']){
    //说明isAdmin不存在或者存在，但值为0
    echo "<script>alert('请以管理员身份登录后访问本页面');location.href='../page/login.php';</script>";
    exit;
}
?>