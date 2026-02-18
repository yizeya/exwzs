<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

$web_info_sql = "SELECT * FROM webinfo LIMIT 1";
$web_info_result = $link->query($web_info_sql);
if ($web_info_result && $web_info_result->num_rows > 0) {
    $web_info_row = $web_info_result->fetch_assoc();
} else {
    $web_info_row = [];
}

// 确保 static 字段有默认值
if (!isset($web_info_row['static'])) {
    $web_info_row['static'] = 0;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<div class="container-fluid" style="margin-top: 20px; margin-bottom: 20px;">
    <div class="row">
        <!-- 侧边导航栏 -->
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>
        
        <!-- 主内容区域 -->
        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <span class="glyphicon glyphicon-cog"></span> 高校信息管理
                    </h4>
                </div>
                <div class="panel-body">
                    <form id="infoForm">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

                        <div class="row">
                            <!-- 网站名称 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="webname" class="control-label">
                                        <span class="glyphicon glyphicon-globe"></span> 高校名称
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-home"></span>
                                        </span>
                                        <input type="text" class="form-control" id="webname" name="webname" 
                                               value="<?php echo e($web_info_row['webname'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 高校代码 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="school_code" class="control-label">
                                        <span class="glyphicon glyphicon-hash"></span> 高校代码
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-barcode"></span>
                                        </span>
                                        <input type="text" class="form-control" id="school_code" name="school_code" 
                                               value="<?php echo e($web_info_row['school_code'] ?? ''); ?>" 
                                               placeholder="例如：10001" required>
                                    </div>
                                    <span class="help-block">请输入教育部统一规定的高校代码，通常为5位数字</span>
                                </div>
                            </div>
                            
                            <!-- 联系电话 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tel" class="control-label">
                                        <span class="glyphicon glyphicon-earphone"></span> 招生办联系电话
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-phone"></span>
                                        </span>
                                        <input type="text" class="form-control" id="tel" name="tel" 
                                               value="<?php echo e($web_info_row['tel'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 联系邮箱 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail" class="control-label">
                                        <span class="glyphicon glyphicon-envelope"></span> 招生办邮箱
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-envelope"></span>
                                        </span>
                                        <input type="email" class="form-control" id="mail" name="mail" 
                                               value="<?php echo e($web_info_row['mail'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ICP备案号 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icp" class="control-label">
                                        <span class="glyphicon glyphicon-file"></span> 高校ICP备案号
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-file"></span>
                                        </span>
                                        <input type="text" class="form-control" id="icp" name="icp" 
                                               value="<?php echo e($web_info_row['icp'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 联系地址 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address" class="control-label">
                                        <span class="glyphicon glyphicon-map-marker"></span> 高校联系地址
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-map-marker"></span>
                                        </span>
                                        <input type="text" class="form-control" id="address" name="address" 
                                               value="<?php echo e($web_info_row['address'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 首页图片 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="banner" class="control-label">
                                        <span class="glyphicon glyphicon-picture"></span> 首页图片URL
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-link"></span>
                                        </span>
                                        <input type="text" class="form-control" id="banner" name="banner" 
                                               value="<?php echo e($web_info_row['banner'] ?? ''); ?>" 
                                               placeholder="输入图片URL地址" required>
                                    </div>
                                    <span class="help-block">请输入完整的图片URL地址，例如：http://example.com/banner.jpg</span>
                                </div>
                            </div>
                            
                            <!-- 招生介绍 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="introduce" class="control-label">
                                        <span class="glyphicon glyphicon-info-sign"></span> 招生介绍
                                    </label>
                                    <textarea class="form-control" id="introduce" name="introduce" rows="8" required>
<?php 
if (!empty($web_info_row['introduce'])) {
    echo e($web_info_row['introduce']);
} else {
    echo "招生对象是本科阶段就读于本校，在校表现良好且毕业时取得了主修专业毕业证书及学士学位证书；或者普通高校本科毕业并获得学士学位的" . date("Y") . "年应届毕业生，以及近三年普通高校本科毕业并获得学士学位、目前未就业的往届生。为全日制普通高等教育" . date("Y") . "年应届本科毕业生。";
}
?>
                                    </textarea>
                                </div>
                            </div>
                            
                            <!-- 操作按钮 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-floppy-disk"></span> 保存修改
                                    </button>
                                    
                                    <!-- 注册开关按钮（无弹窗，直接提交） -->
                                    <?php if (empty($web_info_row['reg'])) { ?>
                                        <a href="javascript:void(0);" 
                                           class="btn btn-success toggle-setting-btn" 
                                           data-action="reg" 
                                           data-value="1"
                                           data-field="reg">
                                            <span class="glyphicon glyphicon-unchecked"></span> 开启注册
                                        </a>
                                    <?php } else { ?>
                                        <a href="javascript:void(0);" 
                                           class="btn btn-warning toggle-setting-btn" 
                                           data-action="reg" 
                                           data-value="0"
                                           data-field="reg">
                                            <span class="glyphicon glyphicon-check"></span> 关闭注册
                                        </a>
                                    <?php } ?>
                                    
                                    <!-- 伪静态开关按钮（新增） -->
                                    <?php if (empty($web_info_row['static'])) { ?>
                                        <a href="javascript:void(0);" 
                                           class="btn btn-success toggle-setting-btn" 
                                           data-action="static" 
                                           data-value="1"
                                           data-field="static">
                                            <span class="glyphicon glyphicon-link"></span> 开启伪静态
                                        </a>
                                    <?php } else { ?>
                                        <a href="javascript:void(0);" 
                                           class="btn btn-warning toggle-setting-btn" 
                                           data-action="static" 
                                           data-value="0"
                                           data-field="static">
                                            <span class="glyphicon glyphicon-link"></span> 关闭伪静态
                                        </a>
                                    <?php } ?>
                                    
                                    <button type="button" class="btn btn-default" onclick="location.reload()">
                                        <span class="glyphicon glyphicon-refresh"></span> 刷新页面
                                    </button>
                                </div>
                                
                                <!-- 结果提示 -->
                                <div id="sResult"></div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- 预览卡片 -->
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info" style="margin-bottom:0;">
                                <strong><span class="glyphicon glyphicon-eye-open"></span> 当前设置预览</strong>
                                <ul class="list-unstyled" style="margin-top:8px;">
                                    <li><small><strong>高校代码：</strong> <?php echo e($web_info_row['school_code'] ?? '未设置'); ?></small></li>
                                    <li><small><strong>注册状态：</strong> 
                                        <?php echo empty($web_info_row['reg']) ? 
                                            '<span class="label label-danger">已关闭</span>' : 
                                            '<span class="label label-success">已开启</span>'; ?>
                                    </small></li>
                                    <li><small><strong>伪静态状态：</strong> 
                                        <?php echo empty($web_info_row['static']) ? 
                                            '<span class="label label-danger">已关闭</span>' : 
                                            '<span class="label label-success">已开启</span>'; ?>
                                    </small></li>
                                    <li><small><strong>最后更新：</strong> 请保存后刷新查看</small></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-default" style="background:#f5f5f5;margin-bottom:0;">
                                <strong><span class="glyphicon glyphicon-lightbulb"></span> 操作提示</strong>
                                <p class="text-muted small" style="margin-top:8px;">
                                    修改信息后点击"保存修改"按钮，系统将自动更新网站设置。注册开关控制用户是否可以注册新账户，伪静态开关控制URL重写功能。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="global_csrf_token" value="<?php echo e($csrf_token); ?>">

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
    $('#introduce').val($.trim($('#introduce').val()));
    
    // 表单保存
    $('#infoForm').on('submit', function(e){
        e.preventDefault();
        $('#sResult').html('<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-hourglass"></span> <strong>正在保存...</strong> 请稍候' +
            '</div>');
        var formData = $(this).serialize();
        $.ajax({
            url: 'control.php?action=webinfo',
            type: 'POST',
            data: formData,
            success: function(response){
                $('#sResult').html('<div class="alert alert-success alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-ok"></span> <strong>保存成功!</strong> ' + response +
                    '</div>');
                setTimeout(function() { location.reload(); }, 3000);
            },
            error: function(error){
                console.log(error);
                $('#sResult').html('<div class="alert alert-danger alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>保存失败!</strong> 请稍后再试或联系管理员。' +
                    '</div>');
            }
        });
    });

    // 统一处理开关按钮（注册/伪静态）
    $('.toggle-setting-btn').click(function(e){
        e.preventDefault();
        var action = $(this).data('action');   // 接口 action：reg 或 static
        var value = $(this).data('value');     // 目标值：1 或 0
        var field = $(this).data('field');     // 字段名（用于提示）
        var csrfToken = $('#global_csrf_token').val();

        // 显示处理中
        $('#sResult').html('<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在处理...</strong> 请稍候' +
            '</div>');

        $.ajax({
            url: 'control.php?action=' + action,
            type: 'POST',
            data: {
                int: value,
                csrf_token: csrfToken
            },
            dataType: 'text',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.indexOf('失败') !== -1) {
                    $('#sResult').html('<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>操作失败!</strong> ' + data +
                        '</div>');
                } else {
                    var successMsg = (field === 'reg' ? '注册功能' : '伪静态功能') + 
                                     (value == 1 ? '已开启' : '已关闭') + '！';
                    $('#sResult').html('<div class="alert alert-success alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-ok"></span> <strong>操作成功!</strong> ' + successMsg +
                        '</div>');
                    // 2秒后刷新页面更新按钮状态
                    setTimeout(function() { location.reload(); }, 2000);
                }
            },
            error: function(xhr, status, error) {
                $('#sResult').html('<div class="alert alert-danger alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>网络错误!</strong> 请求失败，请检查网络连接后再试。' +
                    '</div>');
                console.error('Error:', error);
            }
        });
    });
});
</script>

<?php include('bottom.php'); ?>