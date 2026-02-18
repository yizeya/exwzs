<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: index.php");
    exit();
}

include('top.php');
include('info.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-lock" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">修改密码</h5>
                        </div>
                    </div>
                </div>
                
                <div class="panel-body" style="padding:20px;">
                    <form id="passForm" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-group" style="margin-bottom:20px;">
                            <label for="password" style="font-weight:bold;">
                                <span class="glyphicon glyphicon-lock" style="margin-right:5px;"></span>新密码
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required minlength="6" maxlength="20">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" id="togglePassword">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </button>
                                </span>
                            </div>
                            <span class="help-block text-muted">
                                <span class="glyphicon glyphicon-info-sign" style="margin-right:5px;"></span>请输入新的登录密码（6-20位）
                            </span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="PassSubmit">
                            <span class="glyphicon glyphicon-ok" style="margin-right:5px;"></span>修改密码
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9edf7; color:#31708f;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-lock"></span> 密码修改结果
                </h4>
            </div>
            <div class="modal-body">
                <div id="passResult" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.href='password.php';">
                    <span class="glyphicon glyphicon-ok"></span> 确定
                </button>
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
        $('#togglePassword').click(function(){
            var password = $('#password');
            var type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            var icon = type === 'password' ? 'glyphicon-eye-open' : 'glyphicon-eye-close';
            $(this).find('span').removeClass('glyphicon-eye-open glyphicon-eye-close').addClass(icon);
        });
        
        $('#passForm').on('submit', function(e) {
            e.preventDefault();
            var password = $('input[name="password"]').val();

            if (password === '') {
                $('#passResult').html('<div class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> 请输入密码</div>');
                $('#passResultModal').modal('show');
                return;
            }
            if (password.length < 6 || password.length > 20) {
                $('#passResult').html('<div class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> 密码长度必须在6-20位之间</div>');
                $('#passResultModal').modal('show');
                return;
            }

            var formData = $(this).serialize();

            $.ajax({
                url: 'update.php?action=password',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#passResult').html(response);
                    $('#passResultModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    $('#passResult').html('<div class="text-danger"><span class="glyphicon glyphicon-remove-sign"></span> 修改失败，请稍后再试。</div>');
                    $('#passResultModal').modal('show');
                }
            });
        });
    });
</script>

<?php include('bottom.php'); ?>