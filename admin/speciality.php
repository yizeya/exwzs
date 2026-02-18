<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$sql = "SELECT id, name, mlzyl, xwsyml, total, school, years, price FROM speciality ORDER BY id ASC";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("speciality_list.php: 查询专业列表失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$stmt->execute();
$box_result = $stmt->get_result();

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<div class="container-fluid" style="margin-top:20px;">
    <div class="row">
        <!-- 侧边导航栏 -->
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>
        
        <!-- 主内容区域 -->
        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading clearfix">
                    <h5 class="panel-title pull-left" style="font-size:18px;">
                        <span class="glyphicon glyphicon-education"></span> 招生专业列表
                    </h5>
                    <div class="pull-right">
                        <span class="badge" style="margin-right:10px; background-color:#fff; color:#337ab7;">
                            <span class="glyphicon glyphicon-list"></span> 
                            <?php echo $box_result->num_rows; ?> 个专业
                        </span>
                        <a href="spe_add.php" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-plus"></span> 添加专业
                        </a>
                    </div>
                </div>
                
                <div class="panel-body">
                    <!-- 操作提示 -->
                    <div class="alert alert-info" style="margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">专业管理说明</h6>
                                <p class="mb-0">当前系统中所有招生专业列表。您可以修改或删除已有专业信息。</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 专业列表表格 -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="80">
                                        <span class="glyphicon glyphicon-tag"></span> 序号
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-home"></span> 招生专业
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-th-large"></span> 门类、专业类
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-certificate"></span> 学位授予门类
                                    </th>
                                    <th width="120">
                                        <span class="glyphicon glyphicon-user"></span> 拟招生人数
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-education"></span> 培养院系
                                    </th>
                                    <th width="80">
                                        <span class="glyphicon glyphicon-calendar"></span> 学制/年
                                    </th>
                                    <th width="100">
                                        <span class="glyphicon glyphicon-credit-card"></span> 学费/年
                                    </th>
                                    <th width="120">
                                        <span class="glyphicon glyphicon-cog"></span> 操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                while ($box_row = $box_result->fetch_assoc()) { 
                                    $row_count++;
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge">
                                            #<?php echo intval($box_row['id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo e($box_row['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo e($box_row['mlzyl']); ?>
                                    </td>
                                    <td>
                                        <?php echo e($box_row['xwsyml']); ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color:#337ab7;">
                                            <?php echo intval($box_row['total']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo e($box_row['school']); ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color:#5bc0de;">
                                            <?php echo intval($box_row['years']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color:#f0ad4e;">
                                            ¥<?php echo intval($box_row['price']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="spe_col.php?id=<?php echo intval($box_row['id']); ?>" 
                                               class="btn btn-primary" title="修改">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                            <button class="btn btn-danger confirm-delete-speciality" 
                                                    data-id="<?php echo intval($box_row['id']); ?>"
                                                    data-name="<?php echo e($box_row['name']); ?>"
                                                    title="删除">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php $stmt->close(); ?>
                                
                                <?php if ($row_count == 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding:50px;">
                                        <span class="glyphicon glyphicon-education" style="font-size:48px; color:#ccc;"></span>
                                        <h5 class="text-muted" style="margin-top:10px;">暂无招生专业</h5>
                                        <p class="text-muted">系统中尚未添加任何招生专业。</p>
                                        <a href="spe_add.php" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-plus"></span> 添加专业
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrf_token" value="<?php echo e($csrf_token); ?>">

<div class="modal fade" id="deleteSpecialityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭" style="color:white;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 确认删除
                </h4>
            </div>
            <div class="modal-body">
                <div class="text-center" style="margin-bottom:20px;">
                    <span class="glyphicon glyphicon-education" style="font-size:48px; color:#d9534f;"></span>
                </div>
                <p id="deleteSpecialityMessage" class="text-center">
                </p>
                <div class="alert alert-warning" style="margin-top:15px;">
                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                    <small>此操作不可恢复，请谨慎操作！</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> 取消
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSpecialityBtn">
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
    let deleteSpecialityId = null;
    let deleteSpecialityName = null;
    
    $('.confirm-delete-speciality').click(function(e) {
        e.preventDefault();
        
        deleteSpecialityId = $(this).data('id');
        deleteSpecialityName = $(this).data('name');
        
        $('#deleteSpecialityMessage').html(
            '确定要删除招生专业 <strong class="text-danger">"' + deleteSpecialityName + '"</strong> 吗？<br>' +
            '<small class="text-muted">专业ID: #' + deleteSpecialityId + '</small>'
        );
        
        $('#deleteSpecialityModal').modal('show');
    });
    
    $('#confirmDeleteSpecialityBtn').click(function() {
        if (!deleteSpecialityId) return;
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<span class="glyphicon glyphicon-hourglass glyphicon-spin"></span> 正在删除...').prop('disabled', true);
        
        var csrfToken = $('#csrf_token').val();
        
        $.ajax({
            url: 'control.php?action=sdel',
            type: 'POST',
            data: {
                id: deleteSpecialityId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteSpecialityModal').modal('hide');
                
                if (data.success) {
                    showResultAlert('success', '删除成功', '专业 "' + deleteSpecialityName + '" 已成功删除。');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showResultAlert('error', '删除失败', data.message || '操作失败，请重试。');
                }
            },
            error: function(xhr, status, error) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteSpecialityModal').modal('hide');
                
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
    
    // 显示浮动提示
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