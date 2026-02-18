<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

$box_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($box_id <= 0) {
    header("Location: speciality.php");
    exit();
}

$sql = "SELECT * FROM speciality WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("edit_speciality.php: 预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$stmt->bind_param("i", $box_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: speciality.php");
    exit();
}
$box_row = $result->fetch_assoc();
$stmt->close();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <span class="glyphicon glyphicon-edit"></span> 修改招生专业
                    </h5>
                </div>
                
                <div class="panel-body">
                    <form method="post" id="sForm">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                        <input type="hidden" name="id" value="<?php echo $box_id; ?>">
                        
                        <div class="row" style="margin-bottom:15px;">
                            <!-- 专业名称 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="speciality" class="control-label">
                                        <span class="glyphicon glyphicon-home"></span> 专业名称
                                    </label>
                                    <input type="text" class="form-control" id="speciality" name="speciality" 
                                           value="<?php echo e($box_row['name']); ?>" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 学位授予门类 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zylb" class="control-label">
                                        <span class="glyphicon glyphicon-certificate"></span> 学位授予门类
                                    </label>
                                    <input type="text" class="form-control" id="zylb" name="zylb" 
                                           value="<?php echo e($box_row['mlzyl']); ?>" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 门类、专业类 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="xw" class="control-label">
                                        <span class="glyphicon glyphicon-th-large"></span> 门类、专业类
                                    </label>
                                    <input type="text" class="form-control" id="xw" name="xw" 
                                           value="<?php echo e($box_row['xwsyml']); ?>" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 学制 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="years" class="control-label">
                                        <span class="glyphicon glyphicon-calendar"></span> 学制
                                    </label>
                                    <input type="text" class="form-control" id="years" name="years" 
                                           value="<?php echo e($box_row['years']); ?>" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 培养院系 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="school" class="control-label">
                                        <span class="glyphicon glyphicon-education"></span> 培养院系
                                    </label>
                                    <input type="text" class="form-control" id="school" name="school" 
                                           value="<?php echo e($box_row['school']); ?>" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 拟招生人数 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total" class="control-label">
                                        <span class="glyphicon glyphicon-user"></span> 拟招生人数
                                    </label>
                                    <input type="text" class="form-control" id="total" name="total" 
                                           value="<?php echo e($box_row['total']); ?>" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 学费 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="price" class="control-label">
                                        <span class="glyphicon glyphicon-credit-card"></span> 学费
                                    </label>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           value="<?php echo e($box_row['price']); ?>" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 操作按钮 -->
                            <div class="col-md-12">
                                <div class="clearfix" style="margin-bottom:15px;">
                                    <a href="speciality.php" class="btn btn-default pull-left">
                                        <span class="glyphicon glyphicon-arrow-left"></span> 返回列表
                                    </a>
                                    <button type="submit" class="btn btn-primary pull-right">
                                        <span class="glyphicon glyphicon-ok"></span> 保存修改
                                    </button>
                                </div>
                                
                                <div id="sResult"></div>
                            </div>
                        </div>
                    </form>
                </div>
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
    $('#sForm').on('submit', function(e){
        e.preventDefault();
        
        $('#sResult').html(
            '<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在保存修改...</strong> 请稍候' +
            '</div>'
        );
        
        var formData = $(this).serialize();

        $.ajax({
            url: 'control.php?action=updatespeciality',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data){
                if (data.success) {
                    $('#sResult').html(
                        '<div class="alert alert-success alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-ok-sign"></span> <strong>修改成功!</strong> ' + data.message +
                        '</div>'
                    );
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $('#sResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>修改失败!</strong> ' + data.message +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error){
                console.log(error);
                $('#sResult').html(
                    '<div class="alert alert-danger alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-remove-sign"></span> <strong>请求失败!</strong> 请稍后再试或联系管理员。' +
                    '</div>'
                );
            }
        });
    });
});
</script>

<?php include('bottom.php'); ?>