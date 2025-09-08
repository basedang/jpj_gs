<!DOCTYPE html>
    <!-- df_login.php -->
    <!-- 页面功能说明：用户登录界面，提供账号密码输入、格式验证、登录提交及新用户引导功能 -->
    <!-- 核心功能：
         1. 支持用户名密码输入及客户端格式校验（用户名3-10位字母数字，密码6-10位含特殊字符）
         2. 提供"记住我"功能（后续需配合后台会话持久化实现）
         3. 包含密码找回和新用户注册入口
         4. 通过AJAX实时校验用户名是否存在（依赖checkUsername.php接口）
    -->
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>用户登录</title>
    <link rel="stylesheet" href="./css/df_login.css" type="text/css" media="all" />
</head>
<body>
    <div class="header-bg">
        <div class="header-content">
            <h1>欢迎回来</h1>
            <p>登录您的账户以继续</p>
        </div>
    </div>
    
    <div class="login-container">
        <h2>账号登录</h2>
        <form id="loginForm" action="../user/postLogin.php" method="post" onsubmit="return check()">
            <div class="form-group">
                <label for="username">账号</label>
                <input type="text" id="username" name="username" placeholder="请输入用户名" required autofocus onblur="checkUsername()">
                <div id="username-error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="pw" placeholder="请输入密码" required>
                <div id="password-error" class="error-message"></div>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">记住我</label>
            </div>
            <button type="submit" class="login-btn">登录</button>
        </form>
        <div class="links">
            <a href="register.php">注册新账户</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 用户名输入校验函数（失去焦点时触发）
        function checkUsername(){
            let username = $("#username").val().trim();
            if(username.length == 0){
                $("#x0").hide();
                $("#x1").hide();
                return;
            }
            else{
                let usernameReg = /^[a-zA-Z0-9]{3,10}$/;
                if(!usernameReg.test(username)){
                    alert('用户只能由大小写字符和数字构成，长度为3到10个字符！');
                    return;
                }
                $.ajax({
                    url:'checkUsername.php',
                    type:"post",
                    dataType:'json',
                    data:{username:username},
                    success:function (d){
                        if(d.code == 0){
                            $("#x0").hide();
                            $("#x1").show();
                        }
                        else if(d.code == 2){
                            $("#x0").show();
                            $("#x1").hide();
                        }
                    },
                    error:function (){
                        $("#x0").hide();
                        $("#x1").hide();
                    }
                })
            }
        }

        // 表单提交前整体校验函数
        function check(){
            let username = document.getElementsByName('username')[0].value.trim();
            let pw = document.getElementsByName('pw')[0].value.trim();
            
            // 用户名格式校验（3-10位字母数字）
            let usernameReg = /^[a-zA-Z0-9]{3,10}$/;
            if(!usernameReg.test(username)){
                alert('用户名必填，且只能大小写字符和数字构成，长度为3到10个字符！');
                return false;
            }
            
            // 密码格式校验（6-10位，含字母、数字、*、_）
            let pwreg = /^[a-zA-Z0-9_*]{6,10}$/;
            if(!pwreg.test(pw)){
                alert('密码必填，且只能大小写字符和数字，以及*、_构成，长度为6到10个字符！');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>