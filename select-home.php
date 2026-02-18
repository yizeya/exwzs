<?php
session_start();

// 检查用户是否登录（使用会话变量名 id）
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: /");
    exit();
}

include('top.php');
include('sql.php');
include('info.php');

$uid = intval($_SESSION['id']); // 确保整数

// 使用预处理语句查询用户信息，防止 SQL 注入
$user_sql = "SELECT * FROM user WHERE id = ?";
$user_stmt = $link->prepare($user_sql);
if (!$user_stmt) {
    error_log("select-home.php: 用户查询预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$user_stmt->bind_param("i", $uid);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
} else {
    // 用户不存在，跳转
    header("Location: /");
    exit();
}
$user_stmt->close();

// 使用预处理语句查询所有专业
$box_sql = "SELECT * FROM speciality ORDER BY id ASC";
$box_stmt = $link->prepare($box_sql);
if (!$box_stmt) {
    error_log("select-home.php: 专业查询预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$box_stmt->execute();
$box_result = $box_stmt->get_result();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$reg_enabled = isset($web_info_row['reg']) ? (bool)$web_info_row['reg'] : false;
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
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
                        <span class="badge" style="border-radius:50%; background-color:#337ab7; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">3</span>
                        <h6 style="font-weight:bold; color:#337ab7; margin:5px 0 0;">选择专业</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">当前步骤</p>
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
    
    <!-- 专业选择卡片 -->
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <div class="media">
                <div class="media-left">
                    <span class="glyphicon glyphicon-list-alt" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                </div>
                <div class="media-body">
                    <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">选择专业</h5>
                </div>
            </div>
        </div>
        
        <div class="panel-body" style="padding:20px;">
            <div class="alert alert-info" style="margin-bottom:20px;">
                <div class="media">
                    <div class="media-left">
                        <span class="glyphicon glyphicon-info-sign" style="font-size:20px;"></span>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading" style="font-weight:bold;">请注意</h6>
                        <p style="margin:0;">第二学士学位仅可选择1个专业，请仔细阅读专业信息后做出选择。</p>
                    </div>
                </div>
            </div>
            
            <form method="post" action="result-home.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover table-condensed">
                        <thead class="active">
                            <tr>
                                <th class="text-center">专业序号</th>
                                <th>招生专业</th>
                                <th>门类、专业类</th>
                                <th>学位授予门类</th>
                                <th class="text-center">拟招生人数</th>
                                <th>培养院系</th>
                                <th class="text-center">学制/年</th>
                                <th class="text-center">学费/年</th>
                                <th class="text-center">选择</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($box_row = $box_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold"><?php echo htmlspecialchars($box_row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="glyphicon glyphicon-book" style="margin-right:5px;"></span>
                                    <?php echo htmlspecialchars($box_row['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($box_row['mlzyl'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($box_row['xwsyml'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center fw-bold"><?php echo htmlspecialchars($box_row['total'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="glyphicon glyphicon-education" style="margin-right:5px;"></span>
                                    <?php echo htmlspecialchars($box_row['school'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($box_row['years'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center">
                                    <span class="badge" style="background-color:#f5f5f5; color:#333;"><?php echo htmlspecialchars($box_row['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="specialityid" value="<?php echo htmlspecialchars($box_row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php $box_stmt->close(); ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center" style="margin-top:30px;">
                    <?php 
                    // 判断所有必填字段是否已填写
                    $required_fields = ['photo', 'cardz', 'cardf', 'address', 'school', 'bkzy', 'zylb', 'years', 'xlzs', 'xwzs', 'xlzsphoto', 'xwzsphoto'];
                    $all_filled = true;
                    
                    if (isset($row) && !empty($row)) {
                        foreach ($required_fields as $field) {
                            if (empty($row[$field])) {
                                $all_filled = false;
                                break;
                            }
                        }
                    } else {
                        $all_filled = false;
                    }
                    ?>
                    
                    <?php if ($all_filled): ?>
                        <?php if (!$reg_enabled): ?>
                            <div class="alert alert-warning text-center" style="margin-bottom:0;">
                                <span class="glyphicon glyphicon-exclamation-sign"></span>
                                <strong>系统已停止操作</strong>
                            </div>
                        <?php else: ?>
                            <?php if (empty($row['second'])): ?>
                                <button type="submit" class="btn btn-primary btn-lg" style="padding-left:30px; padding-right:30px; margin-right:10px;">
                                    <span class="glyphicon glyphicon-ok-circle"></span> 确认选择
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-default btn-lg" style="padding-left:30px; padding-right:30px; margin-right:10px;" disabled>
                                    <span class="glyphicon glyphicon-ok-circle"></span> 您已取得成绩
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="my-home.php" class="btn btn-default btn-lg" style="padding-left:30px; padding-right:30px;">
                        <span class="glyphicon glyphicon-arrow-left"></span> 上一步 填写信息
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('bottom.php'); ?>