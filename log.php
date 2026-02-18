<?php
session_start();
include('top.php');
include('info.php');
$uid = $_SESSION['id'];
$log = "SELECT * FROM user_log WHERE uid = $uid ORDER BY time DESC";
$log_result = $link->query($log);
?>

<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <div class="media">
                <div class="media-left">
                    <span class="glyphicon glyphicon-time" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:50%;"></span>
                </div>
                <div class="media-body">
                    <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">考生登入日志</h5>
                </div>
            </div>
        </div>
        
        <div class="panel-body" style="padding:20px;">
            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>登入时间</th>
                            <th>登入IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log_row = mysqli_fetch_array($log_result)): ?>
                        <tr>
                            <td>
                                <span class="glyphicon glyphicon-calendar" style="margin-right:8px;"></span>
                                <?php echo date("Y-m-d H:i:s", $log_row['time']); ?>
                            </td>
                            <td>
                                <span class="glyphicon glyphicon-laptop" style="margin-right:8px;"></span>
                                <?php echo htmlspecialchars($log_row['ip']); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(mysqli_num_rows($log_result) === 0): ?>
            <div class="text-center" style="padding:20px;">
                <span class="glyphicon glyphicon-inbox" style="font-size:3em; color:#ccc;"></span>
                <p class="text-muted" style="margin-top:10px;">暂无登入记录</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('bottom.php'); ?>