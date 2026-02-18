<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php"); // 未登录跳转到管理员登录页
    exit();
}

include('top.php');
include('../sql.php'); // 数据库连接文件（请确保路径正确且安全）

// 查询网站配置信息（用于页面展示，如站点名称等）
$web_info_sql = "SELECT * FROM webinfo LIMIT 1";
$web_info_result = $link->query($web_info_sql);
if ($web_info_result && $web_info_result->num_rows > 0) {
    $web_info_row = $web_info_result->fetch_assoc();
    $web_info_result->free();
} else {
    $web_info_row = [];
    error_log("admin.php: webinfo table query failed: " . $link->error);
}

// 生成 CSRF 令牌（若尚未存在）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container-fluid" style="margin-top: 20px; margin-bottom: 20px;">
    <div class="row">
        <!-- 侧边导航栏 -->
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>
        
        <div class="col-md-9 col-lg-10">
            <div class="row">
                <div class="col-md-6" style="margin-bottom: 20px;">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <span class="glyphicon glyphicon-plus"></span> 添加管理员
                            </h5>
                        </div>
                        <div class="panel-body">
                            <form id="infoForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="form-group">
                                    <label for="user" class="control-label">
                                        <span class="glyphicon glyphicon-user"></span> 管理员用户名
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-user"></span>
                                        </span>
                                        <input type="text" class="form-control" id="user" name="user" 
                                               placeholder="请输入管理员用户名" required>
                                    </div>
                                    <span class="help-block">请设置一个唯一的用户名，建议使用字母和数字组合</span>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <label for="password" class="control-label">
                                        <span class="glyphicon glyphicon-lock"></span> 管理员密码
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-lock"></span>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="请输入管理员密码" required minlength="8">
                                    </div>
                                    <span class="help-block">密码至少8位，建议包含字母、数字和特殊字符</span>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <span class="glyphicon glyphicon-plus"></span> 添加管理员
                                </button>
                                
                                <!-- 结果提示 -->
                                <div id="sResult" style="margin-top: 15px;"></div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- 修改密码卡片 -->
                <div class="col-md-6" style="margin-bottom: 20px;">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <span class="glyphicon glyphicon-cog"></span> 修改当前管理员密码
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info" style="margin-bottom: 20px;">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                <strong>注意：</strong> 此操作将修改当前登录管理员的密码
                            </div>
                            
                            <form id="passForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="form-group">
                                    <label for="newPassword" class="control-label">
                                        <span class="glyphicon glyphicon-lock"></span> 新密码
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-lock"></span>
                                        </span>
                                        <input type="password" class="form-control" id="newPassword" name="password" 
                                               placeholder="请输入新密码" required minlength="8">
                                    </div>
                                    <span class="help-block">为保障安全，请使用强密码并定期更换</span>
                                </div>
                                
                                <button type="submit" class="btn btn-warning btn-lg btn-block">
                                    <span class="glyphicon glyphicon-check"></span> 修改密码
                                </button>
                                
                                <div id="passResult" style="margin-top: 15px;"></div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <span class="glyphicon glyphicon-list"></span> 管理员账户管理
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                <strong>操作说明</strong>
                                <p><strong>添加管理员：</strong> 创建新的管理员账户，用于分配不同管理权限。</p>
                                <p><strong>修改密码：</strong> 修改当前登录管理员账户的登录密码，建议定期更换以确保安全。</p>
                            </div>
                            
                            <div class="alert alert-warning" style="margin-bottom:0;">
                                <span class="glyphicon glyphicon-exclamation-sign"></span>
                                更多管理员管理功能（如权限设置、账户删除等）请联系系统管理员。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="resultModalLabel">操作结果</h4>
            </div>
            <div class="modal-body" id="resultModalContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
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
    $('#infoForm').on('submit', function(e){
        e.preventDefault();
        
        var password = $('#password').val();
        if (password.length < 8) {
            $('#sResult').html(
                '<div class="alert alert-danger alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>密码至少需要8位！</strong>' +
                '</div>'
            );
            return;
        }

        $('#sResult').html(
            '<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在添加管理员...</strong> 请稍候' +
            '</div>'
        );
        
        var formData = $(this).serialize();

        $.ajax({
            url: 'control.php?action=admin',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data){
                if (data.success) {
                    $('#sResult').html(
                        '<div class="alert alert-success alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-ok"></span> <strong>添加成功!</strong> ' + data.message +
                        '</div>'
                    );
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    $('#sResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>添加失败!</strong> ' + data.message +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error){
                console.error(error);
                $('#sResult').html(
                    '<div class="alert alert-danger alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-remove"></span> <strong>请求失败!</strong> 请稍后再试或联系管理员。' +
                    '</div>'
                );
            }
        });
    });
    
    $('#passForm').on('submit', function(e){
        e.preventDefault();

        var newPass = $('#newPassword').val();
        if (newPass.length < 8) {
            $('#passResult').html(
                '<div class="alert alert-danger alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>密码至少需要8位！</strong>' +
                '</div>'
            );
            return;
        }
        
        if (!confirm('确定要修改当前登录管理员的密码吗？')) {
            return;
        }
        
        $('#passResult').html(
            '<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在修改密码...</strong> 请稍候' +
            '</div>'
        );
        
        var formData = $(this).serialize();

        $.ajax({
            url: 'control.php?action=pass',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data){
                if (data.success) {
                    $('#passResult').html(
                        '<div class="alert alert-success alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-ok"></span> <strong>修改成功!</strong> ' + data.message +
                        '</div>'
                    );
                    
                    setTimeout(function() {
                        alert('密码修改成功，请使用新密码重新登录。');
                        window.location.href = 'control.php?action=loginout';
                    }, 2000);
                } else {
                    $('#passResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>修改失败!</strong> ' + data.message +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error){
                console.error(error);
                $('#passResult').html(
                    '<div class="alert alert-danger alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-remove"></span> <strong>请求失败!</strong> 请稍后再试或联系管理员。' +
                    '</div>'
                );
            }
        });
    });
});

function showResultModal(title, message, type = 'success') {
    var icon = type === 'success' 
        ? '<span class="glyphicon glyphicon-ok-sign text-success"></span>' 
        : '<span class="glyphicon glyphicon-exclamation-sign text-danger"></span>';
    
    var titleClass = type === 'success' ? 'text-success' : 'text-danger';
    
    $('#resultModalLabel').html(icon + ' ' + title).removeClass('text-success text-danger').addClass(titleClass);
    $('#resultModalContent').html('<p>' + message + '</p>');
    
    $('#resultModal').modal('show');
}
</script>

<?php include('bottom.php'); ?>