<?php
session_start();

// 检查管理员是否登录（会话变量使用 id）
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php"); // 未登录跳转到管理员登录页
    exit();
}

include('top.php');
include('../sql.php'); // 数据库连接文件（请确保路径正确且安全）

// 生成 CSRF 令牌（若尚未存在）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 辅助函数：安全输出文本
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
                        <span class="glyphicon glyphicon-education"></span> 添加招生专业
                    </h5>
                </div>
                
                <div class="panel-body">
                    <form method="post" id="sForm">
                        <!-- CSRF 令牌 -->
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

                        <div class="row" style="margin-bottom:15px;">
                            <!-- 专业名称 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="speciality" class="control-label">
                                        <span class="glyphicon glyphicon-home"></span> 专业名称
                                    </label>
                                    <input type="text" class="form-control" id="speciality" name="speciality" 
                                           placeholder="请输入专业名称" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 学位授予门类 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">
                                        <span class="glyphicon glyphicon-certificate"></span> 学位授予门类
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-diagram-3"></span>
                                        </span>
                                        <?php 
                                        // 包含学位授予门类下拉框（已安全加固）
                                        include('../xw.php'); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 门类、专业类 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">
                                        <span class="glyphicon glyphicon-th-large"></span> 门类、专业类
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-th"></span>
                                        </span>
                                        <?php 
                                        // 包含门类专业类下拉框（已安全加固）
                                        include('../mlzyl.php'); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 学制 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="years" class="control-label">
                                        <span class="glyphicon glyphicon-calendar"></span> 学制
                                    </label>
                                    <input type="text" class="form-control" id="years" name="years" 
                                           value="2" placeholder="请输入学制" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 培养院系 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="school" class="control-label">
                                        <span class="glyphicon glyphicon-education"></span> 培养院系
                                    </label>
                                    <input type="text" class="form-control" id="school" name="school" 
                                           placeholder="请输入培养院系" required maxlength="100">
                                </div>
                            </div>
                            
                            <!-- 拟招生人数 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total" class="control-label">
                                        <span class="glyphicon glyphicon-user"></span> 拟招生人数
                                    </label>
                                    <input type="text" class="form-control" id="total" name="total" 
                                           placeholder="请输入拟招生人数" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 学费 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="price" class="control-label">
                                        <span class="glyphicon glyphicon-credit-card"></span> 学费
                                    </label>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           placeholder="请输入学费" required maxlength="10">
                                </div>
                            </div>
                            
                            <!-- 操作按钮 -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <span class="glyphicon glyphicon-plus"></span> 添加专业
                                    </button>
                                </div>
                                
                                <!-- 结果提示 -->
                                <div id="sResult" style="margin-top:15px;"></div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- 预览卡片 -->
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info" style="margin-bottom:0;">
                                <h6><span class="glyphicon glyphicon-info-sign"></span> 填写说明</h6>
                                <ul class="list-unstyled small">
                                    <li><span class="glyphicon glyphicon-ok-sign"></span> 请准确填写专业信息</li>
                                    <li><span class="glyphicon glyphicon-ok-sign"></span> 学制通常为2年（第二学士学位）</li>
                                    <li><span class="glyphicon glyphicon-ok-sign"></span> 所有字段均为必填项</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-default" style="background:#f5f5f5; margin-bottom:0;">
                                <h6><span class="glyphicon glyphicon-time"></span> 操作提示</h6>
                                <p class="small">填写完成后点击"添加专业"按钮，系统将保存专业信息并添加到招生专业列表中。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 引入 jQuery 和 Bootstrap 3 JS (生产环境路径) -->
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<!-- 旋转动画样式（用于加载图标） -->
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
    // 确保下拉框使用 Bootstrap 3 样式
    $('select').addClass('form-control');
    
    $('#sForm').on('submit', function(e){
        e.preventDefault();
        
        // 显示加载状态
        $('#sResult').html(
            '<div class="alert alert-info alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在添加专业...</strong> 请稍候' +
            '</div>'
        );
        
        var formData = $(this).serialize();

        $.ajax({
            url: 'control.php?action=speciality',
            type: 'POST',
            data: formData,
            dataType: 'json', // 重要：指定 JSON 类型
            success: function(data) { // 注意参数名为 data
                if (data.success) {
                    // 添加成功，显示成功提示
                    $('#sResult').html(
                        '<div class="alert alert-success alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-ok-sign"></span> <strong>添加成功!</strong> ' + data.message +
                        '</div>'
                    );
                    
                    // 清空表单
                    $('#sForm')[0].reset();
                    
                    // 2秒后刷新页面
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // 添加失败
                    $('#sResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>添加失败!</strong> ' + data.message +
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