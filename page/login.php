<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <title>噗鸡噗后台管理系统登录</title>
    <!-- Meta 标签 -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <meta name="keywords"
        content="管理员登录" />
    <meta name="description" content="后台管理系统安全登录入口">
    <!-- 样式表 -->
    <link rel="stylesheet" href="./css/adm_login_style.css" type="text/css" media="all" />
</head>

<body>
    <!-- 登录表单区块 -->
    <section class="w3l-hotair-form">
        <h1 class="system-title">后台管理系统</h1>
        <div class="container">
            <!-- 表单主体 -->
            <div class="workinghny-form-grid">
                <div class="main-hotair">
                    <div class="content-wthree">
                        <h2>管理员登录</h2>
                        <form action="../user/postLogin.php" method="post" onsubmit="return check()">
                            <!-- 用户名输入 -->
                            <div class="form-group">
                                <input type="text" class="text" name="username" id="username" 
                                       placeholder="管理员账号" required autofocus>
                            </div>

                            <!-- 密码输入 -->
                            <div class="form-group">
                                <input type="password" class="password" name="pw" 
                                       placeholder="登录密码" required>
                            </div>

                            <!-- 验证码
                            <div class="form-group code-section">
                                <input type="text" name="code" 
                                       placeholder="请输入验证码" class="code-input">
                                <img src="code.php" 
                                     onclick="this.src='code.php?'+new Date().getTime()"
                                     class="code-image"
                                     alt="验证码">
                            </div> -->

                            <!-- 操作按钮 -->
                            <div class="form-actions">
                                <button class="btn" type="submit">立即登录</button>
                            </div>
                        </form>

                        <p class="account">忘记密码请<a href="#reset">联系系统管理员</a></p>
                    </div>
                    
                    <!-- 右侧图片 -->
                    <div class="w3l_form align-self">
                        <div class="left_grid_info">
                            <img src="images/1.png" alt="系统安全认证图示" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 版权声明 -->
        <div class="copyright text-center">
            <p class="copy-footer-29">© 2025 后台管理系统 版权所有 | <a target="_blank" href="#" title="技术支持">信息中心</a></p>
        </div>
    </section>
    <!-- //登录表单区块 -->
     
    <script src="https://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
    <script>
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
                    url:'../user/checkUsername.php',
                    type:"post",
                    dataType:'json',
                    data:{username:username},
                    success:function (d){
                        if(d.code == 0){
                            //表明用户名正确
                            $("#x0").hide();
                            $("#x1").show();
                        }
                        else if(d.code == 2){
                            //说明用户名不正确
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
        function check(){
            let username = document.getElementsByName('username')[0].value.trim();
            let pw = document.getElementsByName('pw')[0].value.trim();
            let usernameReg = /^[a-zA-Z0-9]{3,10}$/;
            if(!usernameReg.test(username)){
                alert('用户名必填，且只能大小写字符和数字构成，长度为3到10个字符！');
                return false;
            }
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