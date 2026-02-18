<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: students.php");
    exit();
}

$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("detail.php: 预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: students.php");
    exit();
}
$detail_row = $result->fetch_assoc();
$stmt->close();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function safeImagePath($path) {
    $path = trim($path ?? '');
    if (empty($path)) return '';
    if (strpos($path, '/students/') !== 0) {
        return ''; // 非法路径
    }
    if (strpos($path, '../') !== false || strpos($path, '..\\') !== false) {
        return '';
    }
    return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
}

// 将 first 转为整数，便于后续判断
$first_val = intval($detail_row['first']);
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
                        <span class="glyphicon glyphicon-user"></span> 考生详细资料
                    </h5>
                    <div class="pull-right">
                        <span class="badge" style="margin-right:10px; background-color:#fff; color:#337ab7;">
                            考生ID: #<?php echo intval($detail_row['id']); ?>
                        </span>
                        <a href="students.php" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-arrow-left"></span> 返回列表
                        </a>
                    </div>
                </div>
                
                <div class="panel-body">
                    <!-- 考生基本信息卡片 -->
                    <div class="panel panel-default" style="margin-bottom:25px;">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <span class="glyphicon glyphicon-credit-card"></span> 基本信息
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6" style="margin-bottom:12px;">
                                            <strong><span class="glyphicon glyphicon-user"></span> 姓名：</strong>
                                            <?php echo e($detail_row['name']); ?>
                                        </div>
                                        <div class="col-md-6" style="margin-bottom:12px;">
                                            <strong><span class="glyphicon glyphicon-credit-card"></span> 证件号：</strong>
                                            <?php echo e($detail_row['number']); ?>
                                        </div>
                                        <div class="col-md-6" style="margin-bottom:12px;">
                                            <strong><span class="glyphicon glyphicon-earphone"></span> 手机号码：</strong>
                                            <?php echo e($detail_row['telephone']); ?>
                                        </div>
                                        <div class="col-md-6" style="margin-bottom:12px;">
                                            <strong><span class="glyphicon glyphicon-envelope"></span> 邮箱地址：</strong>
                                            <?php echo e($detail_row['mail']); ?>
                                        </div>
                                        <div class="col-md-12" style="margin-bottom:12px;">
                                            <strong><span class="glyphicon glyphicon-map-marker"></span> 邮寄地址：</strong>
                                            <?php echo e($detail_row['address']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div style="margin-bottom:10px;">
                                        <strong><span class="glyphicon glyphicon-picture"></span> 照片：</strong>
                                    </div>
                                    <img src="<?php echo safeImagePath($detail_row['photo']); ?>" 
                                         class="img-thumbnail" 
                                         style="max-height:150px;" 
                                         alt="考生照片" 
                                         onerror="this.style.display='none'">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default" style="margin-bottom:25px;">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <span class="glyphicon glyphicon-education"></span> 学历信息
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-home"></span> 毕业院校：</strong>
                                    <?php echo e($detail_row['school']); ?>
                                </div>
                                <div class="col-md-4" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-calendar"></span> 毕业年份：</strong>
                                    <?php echo e($detail_row['years']); ?>
                                </div>
                                <div class="col-md-4" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-book"></span> 本科专业：</strong>
                                    <?php echo e($detail_row['bkzy']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-tags"></span> 本科专业类别：</strong>
                                    <?php echo e($detail_row['zylb']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-education"></span> 报考专业：</strong>
                                    <?php echo e($detail_row['speciality']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-file"></span> 学历证书编号：</strong>
                                    <?php echo e($detail_row['xlzs']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-certificate"></span> 学位证书编号：</strong>
                                    <?php echo e($detail_row['xwzs']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-barcode"></span> 准考证号：</strong>
                                    <?php echo e($detail_row['exnumber']); ?>
                                </div>
                                <div class="col-md-6" style="margin-bottom:12px;">
                                    <strong><span class="glyphicon glyphicon-time"></span> 注册时间：</strong>
                                    <?php echo date("Y-m-d H:i:s", $detail_row['time']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default" style="margin-bottom:25px;">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <span class="glyphicon glyphicon-picture"></span> 证件与材料图片
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-credit-card"></span> 身份证正面：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['cardz']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="身份证正面"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-credit-card"></span> 身份证反面：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['cardf']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="身份证反面"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-file"></span> 报名申请表图片：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['sqb']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="报名申请表"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-check"></span> 承诺书图片：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['cns']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="承诺书"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-file"></span> 学历证书图片：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['xlzsphoto']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="学历证书"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="text-center">
                                        <strong><span class="glyphicon glyphicon-certificate"></span> 学位证书图片：</strong>
                                        <div style="margin-top:8px;">
                                            <img src="<?php echo safeImagePath($detail_row['xwzsphoto']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-height:200px; max-width:100%;" 
                                                 alt="学位证书"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default" style="margin-bottom:25px;">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <span class="glyphicon glyphicon-download-alt"></span> 相关文件下载
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="media" style="margin-bottom:15px;">
                                        <div class="media-left">
                                            <span class="glyphicon glyphicon-file text-primary" style="font-size:24px;"></span>
                                        </div>
                                        <div class="media-body">
                                            <strong>学历验证报告单：</strong>
                                            <?php if(!empty($detail_row['bgd'])): ?>
                                            <a href="<?php echo safeImagePath($detail_row['bgd']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary" style="margin-left:8px;">
                                                <span class="glyphicon glyphicon-download-alt"></span> 下载文件
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted" style="margin-left:8px;">未上传</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="media" style="margin-bottom:15px;">
                                        <div class="media-left">
                                            <span class="glyphicon glyphicon-list-alt text-primary" style="font-size:24px;"></span>
                                        </div>
                                        <div class="media-body">
                                            <strong>本科阶段成绩单：</strong>
                                            <?php if(!empty($detail_row['cjd'])): ?>
                                            <a href="<?php echo safeImagePath($detail_row['cjd']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary" style="margin-left:8px;">
                                                <span class="glyphicon glyphicon-download-alt"></span> 下载文件
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted" style="margin-left:8px;">未上传</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default" style="margin-bottom:15px;">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <span class="glyphicon glyphicon-check"></span> 资格审查操作
                            </h6>
                        </div>
                        <div class="panel-body">
                            <!-- 修改：资格审查状态显示 -->
                            <div class="alert 
                                <?php 
                                if($first_val == 0) {
                                    echo 'alert-warning';
                                } elseif ($first_val > 0) {
                                    echo 'alert-success';
                                } else {
                                    echo 'alert-danger';
                                }
                                ?>" style="margin-bottom:25px;">
                                <div class="media">
                                    <div class="media-left">
                                        <span class="glyphicon 
                                            <?php 
                                            if($first_val == 0) {
                                                echo 'glyphicon-hourglass';
                                            } elseif ($first_val > 0) {
                                                echo 'glyphicon-ok-sign';
                                            } else {
                                                echo 'glyphicon-remove-sign';
                                            }
                                            ?>" style="font-size:24px;"></span>
                                    </div>
                                    <div class="media-body">
                                        <strong>当前状态：</strong>
                                        <?php 
                                        if($first_val == 0) {
                                            echo '资格尚未审查';
                                        } elseif ($first_val > 0) {
                                            echo '资格审查通过';
                                        } else {
                                            echo '资格审查未通过';
                                        }
                                        ?>
                                        <?php if($first_val < 0 && !empty($detail_row['why'])): ?>
                                            <div style="margin-top:8px;">
                                                <strong>不通过理由：</strong>
                                                <?php echo e($detail_row['why']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="csrf_token" value="<?php echo e($csrf_token); ?>">
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="panel panel-success" style="margin-bottom:0;">
                                        <div class="panel-body text-center">
                                            <h6 class="text-success" style="margin-bottom:12px;">
                                                <span class="glyphicon glyphicon-ok-circle"></span> 通过资格审查
                                            </h6>
                                            <p class="small text-muted" style="margin-bottom:12px;">
                                                如果考生符合报考条件，点击下方按钮通过资格审查。
                                            </p>
                                            <button type="button" class="btn btn-success btn-block" id="passBtn">
                                                <span class="glyphicon glyphicon-ok"></span> 通过审查
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="panel panel-danger" style="margin-bottom:0;">
                                        <div class="panel-body text-center">
                                            <h6 class="text-danger" style="margin-bottom:12px;">
                                                <span class="glyphicon glyphicon-remove-circle"></span> 不通过资格审查
                                            </h6>
                                            <p class="small text-muted" style="margin-bottom:12px;">
                                                如果考生不符合报考条件，请填写理由后提交。
                                            </p>
                                            <form id="disableForm">
                                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                                <div class="form-group" style="margin-bottom:12px;">
                                                    <textarea class="form-control" name="why" id="whyTextarea" rows="2" 
                                                              placeholder="请填写不通过的理由..." required><?php echo e($detail_row['why']); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger btn-block">
                                                    <span class="glyphicon glyphicon-remove"></span> 提交不通过审查
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="operationResult" style="margin-top:15px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reasonRequiredModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#f0ad4e; color:white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭" style="color:white;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 请填写理由
                </h4>
            </div>
            <div class="modal-body">
                <div class="text-center" style="margin-bottom:20px;">
                    <span class="glyphicon glyphicon-pencil text-warning" style="font-size:48px;"></span>
                </div>
                <p class="text-center">
                    请填写不通过审查的理由，以便考生了解具体原因。
                </p>
                <div class="alert alert-warning" style="margin-top:15px;">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    <small>理由填写后，系统将保存并通知考生。</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">
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

    // 修改：通过审查时发送 point = 1（正数）
    $('#passBtn').on('click', function() {
        var csrfToken = $('#csrf_token').val();
        var id = <?php echo $id; ?>;

        $('#operationResult').html(
            '<div class="alert alert-info"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> 正在提交...</div>'
        );

        $.ajax({
            url: 'control.php?action=studentspass',
            type: 'POST',
            data: {
                id: id,
                point: 1,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(data) {
                $('#operationResult').empty();
                if (data.success) {
                    showResultAlert('success', '操作成功', data.message);
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showResultAlert('error', '操作失败', data.message);
                }
            },
            error: function() {
                $('#operationResult').empty();
                showResultAlert('error', '请求失败', '请稍后再试');
            }
        });
    });

    $('#disableForm').on('submit', function(e) {
        e.preventDefault();

        var why = $('#whyTextarea').val().trim();
        if (why === '') {
            $('#reasonRequiredModal').modal('show');
            return;
        }

        var formData = $(this).serialize();

        $('#operationResult').html(
            '<div class="alert alert-info"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> 正在提交...</div>'
        );

        $.ajax({
            url: 'control.php?action=disable',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                $('#operationResult').empty();
                if (data.success) {
                    showResultAlert('success', '操作成功', data.message);
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showResultAlert('error', '操作失败', data.message);
                }
            },
            error: function() {
                $('#operationResult').empty();
                showResultAlert('error', '请求失败', '请稍后再试');
            }
        });
    });

    $('#reasonRequiredModal').on('hidden.bs.modal', function () {
        $('#whyTextarea').focus();
    });
});
</script>

<?php include('bottom.php'); ?>