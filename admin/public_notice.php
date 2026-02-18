<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ================= 筛选参数处理 =================
$selected_speciality = isset($_GET['speciality']) ? trim($_GET['speciality']) : '';
$selected_score_status = isset($_GET['score_status']) ? $_GET['score_status'] : '';

// ================= 构建筛选条件 =================
$where_clauses = [];
$params = [];
$types = '';

// 默认只显示资格审查通过且成绩合格的考生（可根据需要调整）
$where_clauses[] = "first > 0 AND second >= 60";

if ($selected_speciality !== '' && $selected_speciality !== 'all') {
    $where_clauses[] = "speciality = ?";
    $params[] = $selected_speciality;
    $types .= 's';
}

if ($selected_score_status !== '' && $selected_score_status !== 'all') {
    if ($selected_score_status === 'pass') {
        $where_clauses[] = "second >= 60";
    } elseif ($selected_score_status === 'fail') {
        $where_clauses[] = "second < 60";
    }
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// 统计符合条件的考生总数
$count_sql = "SELECT COUNT(*) as total FROM user $where_sql";
$count_stmt = $link->prepare($count_sql);
if ($count_stmt) {
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_row = $count_result->fetch_assoc();
    $students_count = $total_row['total'];
    $count_stmt->close();
} else {
    error_log("public_notice.php: 统计总数失败 - " . $link->error);
    $students_count = 0;
}

// 查询公示数据（姓名、准考证号、专业）
$sql = "SELECT name, exnumber, speciality FROM user $where_sql ORDER BY id ASC";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("public_notice.php: 查询失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students_result = $stmt->get_result();

// 获取所有不同的专业用于下拉菜单
$specialities = [];
$spec_sql = "SELECT DISTINCT speciality FROM user WHERE speciality IS NOT NULL AND speciality != '' ORDER BY speciality";
$spec_stmt = $link->prepare($spec_sql);
if ($spec_stmt) {
    $spec_stmt->execute();
    $spec_result = $spec_stmt->get_result();
    while ($spec_row = $spec_result->fetch_assoc()) {
        $specialities[] = $spec_row['speciality'];
    }
    $spec_stmt->close();
}

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
                <div class="panel-heading clearfix">
                    <h5 class="panel-title pull-left" style="font-size:18px;">
                        <span class="glyphicon glyphicon-list-alt"></span> 公示名单管理
                    </h5>
                    <span class="badge pull-right" style="margin-top:5px; background-color:#fff; color:#337ab7;">
                        <span class="glyphicon glyphicon-user"></span> 
                        <?php echo intval($students_count); ?> 位考生
                    </span>
                </div>
                
                <div class="panel-body">
                    <!-- 说明提示 -->
                    <div class="alert alert-info" style="margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">公示名单说明</h6>
                                <p class="mb-1">默认显示资格审查通过且成绩合格的考生。您可以使用筛选功能查看特定专业或成绩状态下的考生。若需要公示可直接复制表格粘贴至Xls文档中。</p>
                                <p class="mb-0"><small>表格仅展示姓名、准考证号、报考专业。</small></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 筛选表单 -->
                    <div class="well well-sm" style="margin-bottom:20px;">
                        <form method="get" class="form-inline">
                            <div class="form-group">
                                <label for="speciality" class="control-label" style="margin-right:10px;">报考专业：</label>
                                <select name="speciality" id="speciality" class="form-control" style="width:200px;">
                                    <option value="all">全部专业</option>
                                    <?php foreach ($specialities as $spec): ?>
                                        <option value="<?php echo e($spec); ?>" <?php echo $selected_speciality === $spec ? 'selected' : ''; ?>>
                                            <?php echo e($spec); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-left:15px;">
                                <label for="score_status" class="control-label" style="margin-right:10px;">成绩状态：</label>
                                <select name="score_status" id="score_status" class="form-control" style="width:120px;">
                                    <option value="all">全部</option>
                                    <option value="pass" <?php echo $selected_score_status === 'pass' ? 'selected' : ''; ?>>通过（≥60）</option>
                                    <option value="fail" <?php echo $selected_score_status === 'fail' ? 'selected' : ''; ?>>不通过（<60）</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-left:15px;">
                                <span class="glyphicon glyphicon-filter"></span> 筛选
                            </button>
                            <a href="public_notice.php" class="btn btn-default" style="margin-left:5px;">
                                <span class="glyphicon glyphicon-remove"></span> 清除筛选
                            </a>
                        </form>
                    </div>
                    
                    <!-- 公示名单表格 -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>姓名</th>
                                    <th>准考证号</th>
                                    <th>报考专业</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                while ($row = $students_result->fetch_assoc()) { 
                                    $row_count++;
                                ?>
                                <tr>
                                    <td><?php echo $row_count; ?></td>
                                    <td><?php echo e($row['name']); ?></td>
                                    <td><?php echo e($row['exnumber'] ?: '未填写'); ?></td>
                                    <td><?php echo e($row['speciality'] ?: '未填写'); ?></td>
                                </tr>
                                <?php } ?>
                                <?php $stmt->close(); ?>
                                
                                <?php if ($row_count == 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="padding:50px;">
                                        <span class="glyphicon glyphicon-user" style="font-size:48px; color:#ccc;"></span>
                                        <h5 class="text-muted" style="margin-top:10px;">暂无符合条件的考生</h5>
                                        <p class="text-muted">请尝试修改筛选条件。</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
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