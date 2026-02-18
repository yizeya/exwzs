<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

$article_sql = "SELECT id, title, time FROM article ORDER BY time DESC";
$article_result = $link->query($article_sql);
if (!$article_result) {
    error_log("查询文章失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container-fluid" style="margin-top:20px;">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>
        
        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading clearfix">
                    <h5 class="panel-title pull-left" style="font-size:18px;">
                        <span class="glyphicon glyphicon-file"></span> 文章管理
                    </h5>
                    <a href="article_col.php" class="btn btn-sm btn-default pull-right">
                        <span class="glyphicon glyphicon-plus"></span> 发布新文章
                    </a>
                </div>
                
                <div class="panel-body">
                    <div class="alert alert-info" style="margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">文章管理</h6>
                                <p class="mb-0">当前系统中所有文章列表。您可以修改或删除已有文章。</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 文章列表表格 -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <span class="glyphicon glyphicon-header"></span> 文章标题
                                    </th>
                                    <th width="180">
                                        <span class="glyphicon glyphicon-time"></span> 发布时间
                                    </th>
                                    <th width="150">
                                        <span class="glyphicon glyphicon-cog"></span> 操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                while ($article_row = $article_result->fetch_assoc()) { 
                                    $row_count++;
                                    $title = htmlspecialchars($article_row['title'], ENT_QUOTES, 'UTF-8');
                                    $id = intval($article_row['id']);
                                    $time = date("Y-m-d H:i:s", $article_row['time']);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $title; ?></strong>
                                    </td>
                                    <td>
                                        <?php echo $time; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="update.php?id=<?php echo $id; ?>" 
                                               class="btn btn-primary">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                            <a href="javascript:void(0);" 
                                               class="btn btn-danger confirm-delete-article" 
                                               data-id="<?php echo $id; ?>"
                                               data-title="<?php echo $title; ?>">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                
                                <?php if ($row_count == 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center" style="padding:50px;">
                                        <span class="glyphicon glyphicon-file" style="font-size:48px; color:#ccc;"></span>
                                        <h5 class="text-muted" style="margin-top:10px;">暂无文章</h5>
                                        <p class="text-muted">系统中尚未发布任何文章。</p>
                                        <a href="article_col.php" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-plus"></span> 发布新文章
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

<input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

<div class="modal fade" id="deleteArticleModal" tabindex="-1" role="dialog">
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
                    <span class="glyphicon glyphicon-trash" style="font-size:48px; color:#d9534f;"></span>
                </div>
                <p id="deleteArticleMessage" class="text-center">
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
                <button type="button" class="btn btn-danger" id="confirmDeleteArticleBtn">
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
    let deleteArticleId = null;
    let deleteArticleTitle = null;
    
    $('.confirm-delete-article').click(function(e) {
        e.preventDefault();
        
        deleteArticleId = $(this).data('id');
        deleteArticleTitle = $(this).data('title');
        
        $('#deleteArticleMessage').html(
            '确定要删除文章 <strong class="text-danger">"' + deleteArticleTitle + '"</strong> 吗？<br>' +
            '<small class="text-muted">文章ID: #' + deleteArticleId + '</small>'
        );
        
        $('#deleteArticleModal').modal('show');
    });
    
    $('#confirmDeleteArticleBtn').click(function() {
        if (!deleteArticleId) return;
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<span class="glyphicon glyphicon-hourglass glyphicon-spin"></span> 正在删除...').prop('disabled', true);
        
        var csrfToken = $('#csrf_token').val();
        
        $.ajax({
            url: 'control.php?action=delarticle',
            type: 'POST',
            data: {
                id: deleteArticleId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteArticleModal').modal('hide');
                
                if (data.success) {
                    showResultAlert('success', '删除成功', '文章 "' + deleteArticleTitle + '" 已成功删除。');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showResultAlert('error', '删除失败', data.message || '操作失败，请重试。');
                }
            },
            error: function(xhr, status, error) {
                $btn.html(originalText).prop('disabled', false);
                $('#deleteArticleModal').modal('hide');
                
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