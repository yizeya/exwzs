<?php
session_start();

// 检查用户是否登录（使用会话变量名 id）
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: /");
    exit();
}

include('top.php');
include('info.php');

// 获取专业ID，来自POST，强制整数
$sid = isset($_POST['specialityid']) ? intval($_POST['specialityid']) : 0;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($sid == 0) {
?>
<!-- 未选择专业警告模态框 -->
<div class="modal fade" id="noSelectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#fcf8e3; color:#8a6d3b;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭" onclick="location.href='select-home.php';">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 系统提示
                </h4>
            </div>
            <div class="modal-body text-center">
                <span class="glyphicon glyphicon-remove-circle text-danger" style="font-size:48px;"></span>
                <h5 style="font-weight:bold; margin-top:15px;">请选择报考专业</h5>
                <p class="text-muted">请返回专业选择页面选择您要报考的专业。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.href='select-home.php';">
                    <span class="glyphicon glyphicon-arrow-left"></span> 返回选择
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#noSelectionModal').modal('show');
});
</script>

<?php 
} else {
    // 使用预处理语句查询选中的专业信息，防止 SQL 注入
    $sql = "SELECT * FROM speciality WHERE id = ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        error_log("select.php: 预处理失败 - " . $link->error);
        echo '<div class="alert alert-danger">系统错误，请稍后重试。</div>';
        include('bottom.php');
        exit();
    }
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // 专业不存在
        echo '<div class="alert alert-danger">所选专业不存在，请重新选择。</div>';
        $stmt->close();
        include('bottom.php');
        exit();
    }
    $s_row = $result->fetch_assoc();
    $stmt->close();

    // 安全转义所有输出字段
    $speciality_name = htmlspecialchars($s_row['name'], ENT_QUOTES, 'UTF-8');
    $mlzyl = htmlspecialchars($s_row['mlzyl'], ENT_QUOTES, 'UTF-8');
    $xwsyml = htmlspecialchars($s_row['xwsyml'], ENT_QUOTES, 'UTF-8');
    $total = htmlspecialchars($s_row['total'], ENT_QUOTES, 'UTF-8');
    $school = htmlspecialchars($s_row['school'], ENT_QUOTES, 'UTF-8');
    $years = htmlspecialchars($s_row['years'], ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars($s_row['price'], ENT_QUOTES, 'UTF-8');
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <!-- 步骤指示器 -->
    <div class="panel panel-default" style="margin-bottom:30px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-body" style="padding:20px;">
            <div class="row text-center">
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">1</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">选择招生对象</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">已完成</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">2</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">填写个人信息</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">已完成</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">3</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">选择专业</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">已完成</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#337ab7; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">4</span>
                        <h6 style="font-weight:bold; color:#337ab7; margin:5px 0 0;">报名确认</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">当前步骤</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 报名确认卡片 -->
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <div class="media">
                <div class="media-left">
                    <span class="glyphicon glyphicon-ok-circle" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                </div>
                <div class="media-body">
                    <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">报名确认</h5>
                </div>
            </div>
        </div>
        
        <div class="panel-body" style="padding:20px;">
            <!-- 温馨提示 -->
            <div class="alert alert-info" style="margin-bottom:20px;">
                <div class="media">
                    <div class="media-left">
                        <span class="glyphicon glyphicon-info-sign" style="font-size:20px;"></span>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading" style="font-weight:bold;">重要提示</h6>
                        <p style="margin:0;">请仔细核对所选专业信息，确认后可以更改但需进入资格审查阶段。</p>
                    </div>
                </div>
            </div>
            
            <!-- 专业信息确认表格 -->
            <form method="post" id="selectFrom">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="specialityid" value="<?php echo $sid; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="active">
                            <tr>
                                <th class="text-center">专业代码</th>
                                <th>专业名称</th>
                                <th class="text-center">确认提交</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" style="vertical-align:middle;">
                                    <span class="badge" style="font-size:16px;">#<?php echo $sid; ?></span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-book"></span>
                                        </span>
                                        <input type="text" class="form-control input-lg" name="speciality" 
                                               value="<?php echo $speciality_name; ?>" readonly>
                                    </div>
                                </td>
                                <td class="text-center" style="vertical-align:middle;">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <span class="glyphicon glyphicon-ok-circle"></span> 确认提交
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- 专业详细信息 -->
                <div class="panel panel-default" style="margin-top:20px;">
                    <div class="panel-heading">
                        <h6 class="panel-title" style="font-weight:bold;">
                            <span class="glyphicon glyphicon-info-sign"></span> 专业详细信息
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><span class="glyphicon glyphicon-tags"></span> 门类、专业类：</strong> <?php echo $mlzyl; ?></p>
                                <p><strong><span class="glyphicon glyphicon-certificate"></span> 学位授予门类：</strong> <?php echo $xwsyml; ?></p>
                                <p><strong><span class="glyphicon glyphicon-user"></span> 拟招生人数：</strong> <?php echo $total; ?> 人</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><span class="glyphicon glyphicon-education"></span> 培养院系：</strong> <?php echo $school; ?></p>
                                <p><strong><span class="glyphicon glyphicon-calendar"></span> 学制：</strong> <?php echo $years; ?> 年</p>
                                <p><strong><span class="glyphicon glyphicon-credit-card"></span> 学费：</strong> <?php echo $price; ?> 元/年</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 操作按钮 -->
                <div class="text-center" style="margin-top:30px;">
                    <button type="button" class="btn btn-default btn-lg" style="margin-right:10px;" onclick="location.href='select-home.php';">
                        <span class="glyphicon glyphicon-arrow-left"></span> 返回修改
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" style="padding-left:30px; padding-right:30px;">
                        <span class="glyphicon glyphicon-ok-circle"></span> 确认提交报名
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="submitResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9edf7; color:#31708f;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-cloud-upload"></span> 提交结果
                </h4>
            </div>
            <div class="modal-body">
                <div id="selectResult" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.href='point.php';">
                    <span class="glyphicon glyphicon-ok"></span> 前往资格审查
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 引入 jQuery 和 Bootstrap 3 JS (生产环境路径) -->
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<!-- 自定义旋转动画（可选） -->
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
    $('#selectFrom').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'update.php?action=select',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#selectResult').empty().html(response);
                $('#submitResultModal').modal('show');
            },
            error: function(error) {
                console.log(error);
                $('#selectResult').html('<div class="text-danger"><span class="glyphicon glyphicon-remove-sign" style="font-size:48px;"></span><p>提交失败，请稍后再试。</p></div>');
                $('#submitResultModal').modal('show');
            }
        });
    });
});
</script>

<?php 
} 
include('bottom.php'); 
?>