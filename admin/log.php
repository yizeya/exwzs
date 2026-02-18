<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php"); // 未登录跳转到管理员登录页
    exit();
}

include('top.php');
include('../sql.php');

// 查询登录日志
$sql = "SELECT uid, ip, time FROM admin_log ORDER BY time DESC";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("admin_log.php: 查询日志失败 - " . $link->error);
    $log_result = false;
} else {
    $stmt->execute();
    $log_result = $stmt->get_result();
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
                        <span class="glyphicon glyphicon-time"></span> 管理员登录日志
                    </h5>
                    <span class="badge pull-right" style="margin-top:5px; background-color:#fff; color:#337ab7;">
                        <span class="glyphicon glyphicon-list"></span> 
                        <?php 
                        $count = ($log_result && $log_result->num_rows) ? $log_result->num_rows : 0;
                        echo intval($count); 
                        ?> 条记录
                    </span>
                </div>
                
                <div class="panel-body">
                    <div class="alert alert-info" style="margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">登录日志说明</h6>
                                <p class="mb-0">此页面记录所有管理员账户的登录历史，包括登录时间、管理员ID和登录IP地址。</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 日志列表表格 -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="120">
                                        <span class="glyphicon glyphicon-tag"></span> 管理员ID
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-time"></span> 登录时间
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-globe"></span> 登录IP地址
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($log_result && $log_result->num_rows > 0) {
                                    while ($log_row = $log_result->fetch_assoc()) { 
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge">
                                            #<?php echo intval($log_row['uid']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date("Y-m-d H:i:s", $log_row['time']); ?>
                                    </td>
                                    <td>
                                        <code style="background:#f5f5f5; padding:3px 5px;"><?php echo htmlspecialchars($log_row['ip'], ENT_QUOTES, 'UTF-8'); ?></code>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                    $stmt->close();
                                } else { 
                                ?>
                                <tr>
                                    <td colspan="3" class="text-center" style="padding:50px;">
                                        <span class="glyphicon glyphicon-time" style="font-size:48px; color:#ccc;"></span>
                                        <h5 class="text-muted" style="margin-top:10px;">暂无登录记录</h5>
                                        <p class="text-muted">系统中尚未记录任何管理员登录信息。</p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<?php include('bottom.php'); ?>