<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: /");
    exit();
}

include('top.php');
include('info.php');

$uid = intval($_SESSION['id']);

$sql = "SELECT speciality, first, second, why FROM user WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("point.php: 预处理失败 - " . $link->error);
    $query_error = true;
} else {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
}
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?php if (isset($query_error) || !$user_data): ?>
                <!-- 查询出错或用户不存在 -->
                <div class="panel panel-default" style="margin-bottom:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <div class="panel-body text-center" style="padding:30px;">
                        <span class="glyphicon glyphicon-exclamation-sign text-danger" style="font-size:48px;"></span>
                        <h5 class="text-muted" style="font-weight:bold; margin:15px 0;">数据加载失败</h5>
                        <p class="text-muted" style="margin-bottom:20px;">请稍后重试或联系管理员</p>
                        <a href="home.php" class="btn btn-primary">
                            <span class="glyphicon glyphicon-home" style="margin-right:5px;"></span>返回首页
                        </a>
                    </div>
                </div>
            <?php elseif (empty($user_data['speciality'])): ?>
                <!-- 尚未选择专业 -->
                <div class="panel panel-default" style="margin-bottom:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <div class="panel-body text-center" style="padding:30px;">
                        <span class="glyphicon glyphicon-exclamation-sign text-warning" style="font-size:48px;"></span>
                        <h5 class="text-muted" style="font-weight:bold; margin:15px 0;">尚未选择专业</h5>
                        <p class="text-muted" style="margin-bottom:20px;">请先完成专业选择流程，然后查看资格审查与成绩</p>
                        <a href="select-home.php" class="btn btn-primary">
                            <span class="glyphicon glyphicon-arrow-right" style="margin-right:5px;"></span>前往专业选择
                        </a>
                    </div>
                </div>
            <?php else: 
                $speciality = htmlspecialchars($user_data['speciality'], ENT_QUOTES, 'UTF-8');
                $first = $user_data['first'];
                $second = $user_data['second'];
                $why = isset($user_data['why']) ? htmlspecialchars($user_data['why'], ENT_QUOTES, 'UTF-8') : '';
                $first_val = intval($first);
            ?>
                <!-- 专业名称卡片 -->
                <div class="panel panel-default" style="margin-bottom:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <div class="panel-heading" style="background-color:#d9534f; color:white; border:none;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-book" style="background:rgba(255,255,255,0.2); padding:8px; border-radius:50%;"></span>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading" style="font-weight:bold; margin-top:5px;"><?php echo $speciality; ?></h5>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel-body" style="padding:20px;">
                        <!-- 资格审查状态 -->
                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-6" style="margin-bottom:15px;">
                                <div class="panel panel-default" style="margin-bottom:0; height:100%;">
                                    <div class="panel-heading" style="background:#f5f5f5;">
                                        <h6 class="panel-title" style="font-weight:bold;">
                                            <span class="glyphicon glyphicon-check" style="margin-right:5px;"></span>资格审查
                                        </h6>
                                    </div>
                                    <div class="panel-body text-center" style="padding:20px;">
                                        <?php if ($first_val == 0): ?>
                                            <div class="alert alert-warning" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-time" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">资格尚未审查</h6>
                                                        <p class="small" style="margin:0;">请耐心等待招生办审核</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($first_val > 0): ?>
                                            <div class="alert alert-success" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-ok-sign" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">资格审查通过</h6>
                                                        <p class="small" style="margin:0;">已获得参加考试的资格</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: // $first_val < 0 ?>
                                            <div class="alert alert-danger" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-remove-sign" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">资格审查未通过</h6>
                                                        <p class="small" style="margin:0;"><?php echo $why; ?>，请修改个人信息后再次提交报名专业。</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 招生考试状态 -->
                            <?php if ($first_val > 0): ?>
                            <div class="col-md-6" style="margin-bottom:15px;">
                                <div class="panel panel-default" style="margin-bottom:0; height:100%;">
                                    <div class="panel-heading" style="background:#f5f5f5;">
                                        <h6 class="panel-title" style="font-weight:bold;">
                                            <span class="glyphicon glyphicon-edit" style="margin-right:5px;"></span>招生考试
                                        </h6>
                                    </div>
                                    <div class="panel-body text-center" style="padding:20px;">
                                        <?php if (empty($second)): ?>
                                            <div class="alert alert-warning" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-time" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">未参加考试</h6>
                                                        <p class="small" style="margin:0;">请等待考试通知</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($second >= 60): ?>
                                            <div class="alert alert-success" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-ok-sign" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">考试通过</h6>
                                                        <p class="small" style="margin-bottom:5px;">成绩：<strong><?php echo htmlspecialchars($second, ENT_QUOTES, 'UTF-8'); ?></strong> 分</p>
                                                        <span class="label label-success">合格</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-danger" style="margin-bottom:0;">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <span class="glyphicon glyphicon-remove-sign" style="font-size:24px;"></span>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h6 class="media-heading" style="font-weight:bold;">考试未通过</h6>
                                                        <p class="small" style="margin-bottom:5px;">成绩：<strong><?php echo htmlspecialchars($second, ENT_QUOTES, 'UTF-8'); ?></strong> 分</p>
                                                        <span class="label label-danger">不合格</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 流程说明 -->
                        <div class="alert alert-info" style="margin-top:20px;">
                            <div class="media">
                                <div class="media-left">
                                    <span class="glyphicon glyphicon-info-sign" style="font-size:20px;"></span>
                                </div>
                                <div class="media-body">
                                    <h6 class="media-heading" style="font-weight:bold;">审核流程说明</h6>
                                    <ol class="small" style="margin-bottom:0;">
                                        <li>资格审查：审核报名资料是否齐全、真实有效</li>
                                        <li>如资格审查未通过，请按提示修改后重新选择专业后再次提交</li>
                                        <li>资格审查通过后，方可参加招生考试</li>
                                        <li>考试通过后，进入录取等待阶段</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 操作按钮 -->
                        <div class="text-center" style="margin-top:20px;">
                            <?php if ($first_val < 0): ?>
                                <a href="my-home.php" class="btn btn-warning" style="margin-right:5px;">
                                    <span class="glyphicon glyphicon-pencil" style="margin-right:5px;"></span>修改个人信息
                                </a>
                            <?php endif; ?>
                            <a href="select-home.php" class="btn btn-default" style="margin-right:5px;">
                                <span class="glyphicon glyphicon-eye-open" style="margin-right:5px;"></span>查看专业详情
                            </a>
                            <a href="home.php" class="btn btn-default">
                                <span class="glyphicon glyphicon-home" style="margin-right:5px;"></span>返回首页
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('bottom.php'); ?>