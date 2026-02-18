<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include('top.php');
?>

<div class="container" style="padding-top:30px; padding-bottom:30px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            <!-- 注册卡片 -->
            <div class="panel panel-primary" style="border:none; box-shadow:0 0.5rem 1rem rgba(0,0,0,0.15);">
                <div class="panel-heading text-center" style="background-color:#337ab7; color:white; padding:20px; border:none;">
                    <span class="glyphicon glyphicon-plus-sign" style="font-size:48px;"></span>
                    <h3 style="margin:10px 0 0;">在线注册</h3>
                    <p style="margin:5px 0 0; opacity:0.8;">请填写真实信息完成注册</p>
                </div>
                
                <div class="panel-body" style="padding:30px;">
                    <form id="registrationForm" method="post" action="reg_post.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- 证件号码 -->
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="font-weight:bold;">
                                <span class="glyphicon glyphicon-credit-card" style="margin-right:5px;"></span>证件号码
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-list-alt"></span>
                                </span>
                                <input type="text" class="form-control input-lg" id="number" name="number" placeholder="请输入证件号码" required>
                            </div>
                            <span class="help-block text-warning">
                                <span class="glyphicon glyphicon-exclamation-sign" style="margin-right:3px;"></span>
                                请务必填写考生本人的真实证件号码
                            </span>
                        </div>

                        <!-- 登入密码 -->
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="font-weight:bold;">
                                <span class="glyphicon glyphicon-lock" style="margin-right:5px;"></span>登入密码
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-lock"></span>
                                </span>
                                <input type="password" class="form-control input-lg" id="password" name="password" placeholder="请输入密码" required minlength="6" maxlength="20">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" id="togglePassword">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </button>
                                </span>
                            </div>
                            <span class="help-block text-warning">
                                <span class="glyphicon glyphicon-exclamation-sign" style="margin-right:3px;"></span>
                                请务必填写易记并安全的密码（6-20位）
                            </span>
                        </div>

                        <!-- 真实姓名 -->
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="font-weight:bold;">
                                <span class="glyphicon glyphicon-user" style="margin-right:5px;"></span>真实姓名
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-user"></span>
                                </span>
                                <input type="text" class="form-control input-lg" id="name" name="name" placeholder="请输入真实姓名" required>
                            </div>
                            <span class="help-block text-warning">
                                <span class="glyphicon glyphicon-exclamation-sign" style="margin-right:3px;"></span>
                                请务必填写考生本人的真实姓名，并与证件一致
                            </span>
                        </div>

                        <!-- 手机号码 -->
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="font-weight:bold;">
                                <span class="glyphicon glyphicon-phone" style="margin-right:5px;"></span>手机号码
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-phone"></span>
                                </span>
                                <input type="tel" class="form-control input-lg" id="telephone" name="telephone" placeholder="请输入手机号码" required pattern="[0-9]{11}">
                            </div>
                            <span class="help-block text-warning">
                                <span class="glyphicon glyphicon-exclamation-sign" style="margin-right:3px;"></span>
                                请务必填写考生本人的真实可用的手机号码（11位数字）
                            </span>
                        </div>

                        <!-- 电子邮箱 -->
                        <div class="form-group" style="margin-bottom:30px;">
                            <label style="font-weight:bold;">
                                <span class="glyphicon glyphicon-envelope" style="margin-right:5px;"></span>电子邮箱
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-envelope"></span>
                                </span>
                                <input type="email" class="form-control input-lg" id="mail" name="mail" placeholder="请输入电子邮箱" required>
                            </div>
                            <span class="help-block text-warning">
                                <span class="glyphicon glyphicon-exclamation-sign" style="margin-right:3px;"></span>
                                请务必填写考生本人的真实可用的电子邮箱
                            </span>
                        </div>

                        <!-- 注册按钮 -->
                        <?php 
                        $reg_enabled = isset($web_info_row['reg']) ? (bool)$web_info_row['reg'] : false;
                        ?>
                        <?php if (!$reg_enabled): ?>
                            <div class="alert alert-warning text-center">
                                <span class="glyphicon glyphicon-exclamation-sign"></span> <strong>系统已停止注册</strong>
                            </div>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <span class="glyphicon glyphicon-plus"></span> 立即注册
                            </button>
                        <?php endif; ?>
                    </form>
                    
                    <!-- 注册结果区域 -->
                    <div id="registrationResult" style="margin-top:20px;"></div>
                    
                    <!-- 已有账号提示 -->
                    <div class="text-center" style="margin-top:20px; padding-top:20px; border-top:1px solid #eee;">
                        <p class="text-muted">
                            已有账号？
                            <a href="index.php" style="font-weight:bold;">
                                <span class="glyphicon glyphicon-log-in"></span> 立即登录
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- 注册提示卡片 -->
            <div class="well" style="margin-top:20px;">
                <h6 style="font-weight:bold; margin-bottom:15px;">
                    <span class="glyphicon glyphicon-info-sign"></span> 注册须知
                </h6>
                <ul class="list-unstyled small">
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 请使用本人真实信息进行注册</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 证件号码和姓名一经注册不可修改</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 请妥善保管您的登录密码</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 注册后请及时完善个人信息</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<style>
.glyphicon-spin {
    -webkit-animation: spin 2s infinite linear;
    animation: spin 2s infinite linear;
}
@-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(359deg); }
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(359deg); }
}
</style>

<script type="text/javascript">
    $(document).ready(function(){
        // 密码显示/隐藏切换
        $('#togglePassword').click(function(){
            var password = $('#password');
            var type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            var icon = type === 'password' ? 'glyphicon-eye-open' : 'glyphicon-eye-close';
            $(this).find('span').removeClass('glyphicon-eye-open glyphicon-eye-close').addClass(icon);
        });
        
        // 表单提交处理
        $('#registrationForm').on('submit', function(e){
            e.preventDefault();
            
            var formData = $(this).serialize();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // 显示加载状态
            submitBtn.html('<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> 注册中...').prop('disabled', true);
            
            $('#registrationResult').empty();
            
            $.ajax({
                url: 'reg_post.php',
                type: 'POST',
                data: formData,
                dataType: 'html',
                success: function(response){
                    submitBtn.html(originalText).prop('disabled', false);
                    
                    if (response.includes('成功') || response.includes('注册完成')) {
                        $('#registrationResult').html(
                            '<div class="alert alert-success alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-ok-sign"></span> ' + response +
                            '</div>'
                        );
                        
                        if (response.includes('成功') || response.includes('注册完成')) {
                            setTimeout(function(){
                                $('#registrationForm')[0].reset();
                            }, 2000);
                        }
                    } else {
                        $('#registrationResult').html(
                            '<div class="alert alert-danger alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-exclamation-sign"></span> ' + response +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error){
                    submitBtn.html(originalText).prop('disabled', false);
                    
                    $('#registrationResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-remove-sign"></span> 注册失败，请稍后再试。错误代码：' + xhr.status +
                        '</div>'
                    );
                    console.error('注册请求失败:', error);
                }
            });
        });
    });
</script>

<?php include('bottom.php'); ?>