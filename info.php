<?php
session_start();
$uid = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

if ($uid > 0) {
    $sql = "SELECT * FROM user WHERE id = $uid";
    $result = $link->query($sql);
    $row = mysqli_fetch_array($result);
?>
<div class="container" style="margin-top:20px; margin-bottom:20px;">
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-body" style="padding:15px;">
            <div class="row">
                <div class="col-md-4" style="margin-bottom:15px;">
                    <div style="display:flex; align-items:center;">
                        <div style="width:50px; height:50px; border-radius:50%; background-color:#337ab7; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                            <span class="glyphicon glyphicon-user" style="color:white; font-size:24px;"></span>
                        </div>
                        <div>
                            <h6 style="font-weight:bold; margin:0 0 5px 0;"><?php echo htmlspecialchars($row['name']); ?></h6>
                            <p class="text-muted" style="margin:0; font-size:12px;">
                                <span class="glyphicon glyphicon-credit-card" style="margin-right:5px;"></span>
                                <?php echo htmlspecialchars($row['number']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="row" style="margin:-5px;">
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="select-home.php" class="btn btn-primary btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-edit" style="margin-right:5px;"></span> 专业选择
                            </a>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="log.php" class="btn btn-default btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-time" style="margin-right:5px;"></span> 登入日志
                            </a>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="password.php" class="btn btn-warning btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-lock" style="margin-right:5px;"></span> 修改密码
                            </a>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="upload.php" class="btn btn-info btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-cloud-upload" style="margin-right:5px;"></span> 资料上传
                            </a>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="point.php" class="btn btn-success btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-search" style="margin-right:5px;"></span> 审查与成绩
                            </a>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3" style="padding:5px;">
                            <a href="update.php?action=loginout" class="btn btn-danger btn-block" style="text-align:left;">
                                <span class="glyphicon glyphicon-log-out" style="margin-right:5px;"></span> 退出登入
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="alert alert-info" role="alert" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="media">
            <div class="media-left">
                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
            </div>
            <div class="media-body">
                <h6 class="media-heading" style="font-weight:bold; margin-bottom:5px;">招考流程说明</h6>
                <p class="mb-0" style="margin:0;">
                    考生您好，第二学士学位招考流程：
                    <span style="font-weight:bold;">注册登入 → 报名 → 填写个人信息（包括上传资料） → 资格审查（通过） → 参与考试 → 成绩查询（通过） → 等待录取</span>
                </p>
            </div>
        </div>
    </div>
</div>
<?php } else { 
    header("Location: /"); 
    exit();
} ?>