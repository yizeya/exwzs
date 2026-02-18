<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header('Location: /');
    exit;
}

include('top.php');
include('info.php');

if (!isset($web_info_row) || !is_array($web_info_row)) {
    $web_info_row = [];
}

// 安全获取招生介绍，若不存在则显示默认文本
$introduce = isset($web_info_row['introduce']) 
    ? htmlspecialchars($web_info_row['introduce'], ENT_QUOTES, 'UTF-8') 
    : '暂无介绍';
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <div class="panel panel-default" style="margin-bottom:30px;">
        <div class="panel-body">
            <div class="row text-center">
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#337ab7; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">1</span>
                        <h6 style="font-weight:bold; color:#337ab7; margin:5px 0 0;">选择招生对象</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">当前步骤</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">2</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">填写个人信息</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">下一步</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">3</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">选择专业</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">第三步</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">4</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">报名确认</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">最后一步</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-bottom:20px;">
        <!-- 资料下载区域 -->
        <div class="col-lg-8 col-md-8">
            <div class="panel panel-default" style="height:100%;">
                <div class="panel-heading" style="background:#f5f5f5;">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-download" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">资料下载</h5>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <p class="text-muted" style="margin-bottom:20px;">
                        <span class="glyphicon glyphicon-info-sign" style="margin-right:5px;"></span>
                        报名申请表与考生诚信承诺书请考生下载填写后亲笔签名打印后扫描或者清晰地拍照将其上传，并且将资料地址准确地填写至正确位置！
                    </p>

                    <div class="row">
                        <div class="col-md-6" style="margin-bottom:15px;">
                            <div class="well" style="text-align:center; height:100%;">
                                <span class="glyphicon glyphicon-file text-primary" style="font-size:40px; margin-bottom:10px;"></span>
                                <h6 style="font-weight:bold; margin-bottom:10px;">报名申请表</h6>
                                <a href="sqb.docx" class="btn btn-default btn-sm" style="color:#337ab7; border-color:#337ab7;">
                                    <span class="glyphicon glyphicon-download"></span> 下载文档
                                </a>
                                <p class="small text-muted" style="margin-top:8px;">.docx 格式</p>
                            </div>
                        </div>

                        <div class="col-md-6" style="margin-bottom:15px;">
                            <div class="well" style="text-align:center; height:100%;">
                                <span class="glyphicon glyphicon-file text-success" style="font-size:40px; margin-bottom:10px;"></span>
                                <h6 style="font-weight:bold; margin-bottom:10px;">考生诚信承诺书</h6>
                                <a href="cns.docx" class="btn btn-default btn-sm" style="color:#5cb85c; border-color:#5cb85c;">
                                    <span class="glyphicon glyphicon-download"></span> 下载文档
                                </a>
                                <p class="small text-muted" style="margin-top:8px;">.docx 格式</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 招生对象区域 -->
        <div class="col-lg-4 col-md-4">
            <div class="panel panel-default" style="height:100%;">
                <div class="panel-heading" style="background:#f5f5f5;">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-user" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">招生对象</h5>
                        </div>
                    </div>
                </div>
                <div class="panel-body" style="display:flex; flex-direction:column;">
                    <div style="flex:1; margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign text-primary"></span>
                            </div>
                            <div class="media-body">
                                <p><?php echo $introduce; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center" style="border-top:1px solid #eee; padding-top:20px;">
                        <a href="my-home.php" class="btn btn-primary btn-lg" style="padding-left:30px; padding-right:30px;">
                            <span class="glyphicon glyphicon-play"></span> 开始报名
                        </a>
                        <p class="small text-muted" style="margin-top:10px;">
                            <span class="glyphicon glyphicon-time"></span> 预计需要10-15分钟完成报名
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 提示信息 -->
    <div class="alert alert-info" role="alert">
        <div class="media">
            <div class="media-left">
                <span class="glyphicon glyphicon-lamp" style="font-size:24px;"></span>
            </div>
            <div class="media-body">
                <h6 class="media-heading">报名须知</h6>
                <ul class="list-unstyled small">
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 请确保所有填写信息真实有效</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 请提前准备好身份证、毕业证等相关证件</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 报名过程中请勿刷新页面或关闭浏览器</li>
                    <li><span class="glyphicon glyphicon-ok-sign text-success"></span> 如有疑问，请及时联系招生老师</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include('bottom.php'); ?>