<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

// 查询所有管理员
$box_sql = "SELECT id, name FROM admin_user ORDER BY id ASC";
$box_result = $link->query($box_sql);
if (!$box_result) {
    error_log("admin_list.php: 查询管理员失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$admin_count = $box_result->num_rows;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container-fluid" style="margin-top: 20px;">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>
        
        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading clearfix">
                    <h5 class="panel-title pull-left" style="font-size: 18px;">
                        <span class="glyphicon glyphicon-user"></span> 管理员列表
                    </h5>
                    <span class="badge pull-right" style="margin-top: 5px; background-color: #fff; color: #337ab7;">
                        <span class="glyphicon glyphicon-user"></span> 
                        <?php echo intval($admin_count); ?> 位管理员
                    </span>
                </div>
                
                <div class="panel-body">
                    <div class="alert alert-info" style="margin-bottom: 20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size: 24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">管理员账户管理</h6>
                                <p class="mb-1">当前系统中所有管理员账户列表。请注意：删除管理员账户将永久移除该账户的所有访问权限。</p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <span class="glyphicon glyphicon-exclamation-sign"></span>
                                        系统至少需要保留一个管理员账户以确保正常管理功能。
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="100">
                                        <span class="glyphicon glyphicon-tag"></span> ID
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-user"></span> 管理员用户名
                                    </th>
                                    <th width="150">
                                        <span class="glyphicon glyphicon-cog"></span> 操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while ($box_row = $box_result->fetch_assoc()) {
                                    $admin_name = htmlspecialchars($box_row['name'], ENT_QUOTES, 'UTF-8');
                                    $admin_id = intval($box_row['id']);
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge">
                                            #<?php echo $admin_id; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="media">
                                            <div class="media-left">
                                                <span class="glyphicon glyphicon-user" style="font-size: 20px; color: #337ab7;"></span>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="media-heading" style="margin-bottom: 0;"><?php echo $admin_name; ?></h6>
                                                <small class="text-muted">
                                                    <?php 
                                                    if (isset($_SESSION['admin_name']) && $_SESSION['admin_name'] === $box_row['name']) {
                                                        echo '<span class="label label-success">当前登录</span>';
                                                    } else {
                                                        echo '管理员账户';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($admin_count <= 1): ?>
                                                <button class="btn btn-default btn-sm" disabled title="系统至少需要保留一个管理员账户">
                                                    <span class="glyphicon glyphicon-ban-circle"></span> 不可删除
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm confirm-delete" 
                                                        data-id="<?php echo $admin_id; ?>"
                                                        data-name="<?php echo $admin_name; ?>">
                                                    <span class="glyphicon glyphicon-trash"></span> 删除
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                
                                <?php if ($admin_count == 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center" style="padding: 50px;">
                                        <span class="glyphicon glyphicon-user" style="font-size: 48px; color: #ccc;"></span>
                                        <h5 class="text-muted" style="margin-top: 10px;">暂无管理员账户</h5>
                                        <p class="text-muted">系统中尚未添加任何管理员账户。</p>
                                        <a href="admin.php" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-plus"></span> 添加管理员
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h6 class="panel-title">
                                        <span class="glyphicon glyphicon-lock"></span> 安全建议
                                    </h6>
                                    <ul class="list-unstyled" style="margin-top: 10px;">
                                        <li class="mb-2">
                                            <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                            <small>定期审核管理员账户</small>
                                        </li>
                                        <li class="mb-2">
                                            <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                            <small>为不同管理员分配不同权限</small>
                                        </li>
                                        <li>
                                            <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                            <small>离职员工账户应及时删除</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h6 class="panel-title">
                                        <span class="glyphicon glyphicon-lamp"></span> 快速操作
                                    </h6>
                                    <div style="margin-top: 10px;">
                                        <a href="admin.php" class="btn btn-primary btn-block" style="margin-bottom: 5px;">
                                            <span class="glyphicon glyphicon-plus"></span> 添加新管理员
                                        </a>
                                        <a href="log.php" class="btn btn-default btn-block">
                                            <span class="glyphicon glyphicon-time"></span> 查看登录日志
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #d9534f; color: white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 确认删除
                </h4>
            </div>
            <div class="modal-body">
                <div class="text-center" style="margin-bottom: 20px;">
                    <span class="glyphicon glyphicon-trash" style="font-size: 48px; color: #d9534f;"></span>
                </div>
                <p id="deleteConfirmMessage" class="text-center">
                </p>
                <div class="alert alert-warning" style="margin-top: 15px;">
                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                    <small>此操作不可恢复，请谨慎操作！</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> 取消
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="glyphicon glyphicon-trash"></span> 确认删除
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
$(document).ready(function() {
    let deleteAdminId = null;
    let deleteAdminName = null;
    
    $('.confirm-delete').click(function(e) {
        e.preventDefault();
        
        deleteAdminId = $(this).data('id');
        deleteAdminName = $(this).data('name');
        
        $('#deleteConfirmMessage').html(
            '确定要删除管理员账户 <strong class="text-danger">' + deleteAdminName + '</strong> 吗？<br>' +
            '<small class="text-muted">管理员ID: #' + deleteAdminId + '</small>'
        );
        
        $('#deleteConfirmModal').modal('show');
    });
    
    $('#confirmDeleteBtn').click(function() {
        if (!deleteAdminId) return;
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<span class="glyphicon glyphicon-hourglass glyphicon-spin"></span> 正在删除...').prop('disabled', true);
        
        var csrfToken = $('#csrf_token').val();
        
        $.ajax({
            url: 'control.php?action=admindel',
            type: 'POST',
            data: {
                id: deleteAdminId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteConfirmModal').modal('hide');
                
                if (data.success) {
                    showResultAlert('success', '删除成功', '管理员账户 "' + deleteAdminName + '" 已成功删除。');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showResultAlert('error', '删除失败', data.message || '操作失败，请重试。');
                }
            },
            error: function(xhr, status, error) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteConfirmModal').modal('hide');
                
                try {
                    var response = JSON.parse(xhr.responseText);
                    var errorMsg = response.message || '请求失败';
                } catch (e) {
                    var errorMsg = '请求失败，请检查网络连接。';
                }
                showResultAlert('error', '网络错误', errorMsg);
                console.error('Error:', error);
            }
        });
    });
    
    function showResultAlert(type, title, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'glyphicon-ok-sign' : 'glyphicon-exclamation-sign';
        
        $('.result-alert').remove();
        
        var alertHtml = 
            '<div class="alert ' + alertClass + ' alert-dismissible fade in result-alert" ' +
                 'style="position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px;" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '<span class="glyphicon ' + icon + '"></span> ' +
                '<strong>' + title + '</strong> ' + message +
            '</div>';
        
        $('body').append(alertHtml);
        
        setTimeout(function() {
            $('.result-alert').alert('close');
        }, 5000);
    }
});
</script>

<?php include('bottom.php'); ?>