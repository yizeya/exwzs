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
$selected_first_status = isset($_GET['first_status']) ? $_GET['first_status'] : ''; // 新增：审查状态
$selected_score_status = isset($_GET['score_status']) ? $_GET['score_status'] : '';

// ================= 导出功能（CSV格式） =================
if (isset($_GET['export']) && $_GET['export'] == 1) {
    // 清除之前可能存在的输出（防止 BOM 或空格干扰）
    while (ob_get_level()) {
        ob_end_clean();
    }

    // 构建筛选条件（与列表页保持一致）
    $where_clauses = [];
    $params = [];
    $types = '';

    // 专业筛选
    if ($selected_speciality !== '' && $selected_speciality !== 'all') {
        $where_clauses[] = "speciality = ?";
        $params[] = $selected_speciality;
        $types .= 's';
    }

    // 审查状态筛选
    if ($selected_first_status !== '' && $selected_first_status !== 'all') {
        if ($selected_first_status === 'unchecked') {
            $where_clauses[] = "first = 0 AND speciality IS NOT NULL AND speciality != ''";
        } elseif ($selected_first_status === 'passed') {
            $where_clauses[] = "first > 0";
        } elseif ($selected_first_status === 'failed') {
            $where_clauses[] = "first < 0";
        }
    }

    // 成绩状态筛选
    if ($selected_score_status !== '' && $selected_score_status !== 'all') {
        if ($selected_score_status === 'pass') {
            $where_clauses[] = "second >= 60";
        } elseif ($selected_score_status === 'fail') {
            $where_clauses[] = "second < 60";
        }
    }

    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // 仅查询需要的字段
    $sql = "SELECT name, telephone, address, speciality FROM user $where_sql ORDER BY id ASC";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        die('导出失败：数据库错误。');
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // 设置 CSV 下载头
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="考生信息_' . date('YmdHis') . '.csv"');
    header('Cache-Control: max-age=0');

    // 创建输出流
    $output = fopen('php://output', 'w');

    // 写入 BOM 以支持中文（UTF-8）
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 写入中文表头
    $headers = ['姓名', '联系电话', '邮寄地址', '报考专业'];
    fputcsv($output, $headers);

    // 输出数据
    while ($row = $result->fetch_assoc()) {
        $row_data = [
            $row['name'] ?? '',
            $row['telephone'] ?? '',
            $row['address'] ?? '',
            $row['speciality'] ?? ''
        ];
        fputcsv($output, $row_data);
    }

    fclose($output);
    $stmt->close();
    exit;
}

// ================= 构建筛选条件（用于显示列表） =================
$where_clauses = [];
$params = [];
$types = '';

// 专业筛选
if ($selected_speciality !== '' && $selected_speciality !== 'all') {
    $where_clauses[] = "speciality = ?";
    $params[] = $selected_speciality;
    $types .= 's';
}

// 审查状态筛选
if ($selected_first_status !== '' && $selected_first_status !== 'all') {
    if ($selected_first_status === 'unchecked') {
        $where_clauses[] = "first = 0 AND speciality IS NOT NULL AND speciality != ''";
    } elseif ($selected_first_status === 'passed') {
        $where_clauses[] = "first > 0";
    } elseif ($selected_first_status === 'failed') {
        $where_clauses[] = "first < 0";
    }
}

// 成绩状态筛选
if ($selected_score_status !== '' && $selected_score_status !== 'all') {
    if ($selected_score_status === 'pass') {
        $where_clauses[] = "second >= 60";
    } elseif ($selected_score_status === 'fail') {
        $where_clauses[] = "second < 60";
    }
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

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
    error_log("students.php: 统计考生总数失败 - " . $link->error);
    $students_count = 0;
}

// 查询当前页的考生数据（用于列表展示）
$sql = "SELECT id, name, first, second, speciality FROM user $where_sql ORDER BY id ASC";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("students.php: 查询考生列表失败 - " . $link->error);
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

// 构建导出链接（保留当前筛选参数）
$current_params = $_GET;
unset($current_params['export']);
$export_url = 'students.php?' . http_build_query(array_merge($current_params, ['export' => 1]));

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
                        <span class="glyphicon glyphicon-user"></span> 考生资格审查与成绩登记
                    </h5>
                    <span class="badge pull-right" style="margin-top:5px; background-color:#fff; color:#337ab7;">
                        <span class="glyphicon glyphicon-user"></span> 
                        <?php echo intval($students_count); ?> 位考生
                    </span>
                </div>
                
                <div class="panel-body">
                    <!-- 操作提示 -->
                    <div class="alert alert-info" style="margin-bottom:20px;">
                        <div class="media">
                            <div class="media-left">
                                <span class="glyphicon glyphicon-info-sign" style="font-size:24px;"></span>
                            </div>
                            <div class="media-body">
                                <h6 class="media-heading">考生管理说明</h6>
                                <p class="mb-1">此页面用于管理所有考生信息，包括资格审查状态和成绩登记。</p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <span class="glyphicon glyphicon-exclamation-sign"></span>
                                        请仔细审核考生资料，确保资格审查的准确性。
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 筛选表单及导出按钮（新增审查状态下拉） -->
                    <div class="well well-sm" style="margin-bottom:20px;">
                        <form method="get" class="form-inline" style="display:inline-block;">
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
                                <label for="first_status" class="control-label" style="margin-right:10px;">审查状态：</label>
                                <select name="first_status" id="first_status" class="form-control" style="width:120px;">
                                    <option value="all">全部</option>
                                    <option value="unchecked" <?php echo ($selected_first_status === 'unchecked') ? 'selected' : ''; ?>>未审查</option>
                                    <option value="passed" <?php echo ($selected_first_status === 'passed') ? 'selected' : ''; ?>>通过</option>
                                    <option value="failed" <?php echo ($selected_first_status === 'failed') ? 'selected' : ''; ?>>不通过</option>
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
                            <a href="students.php" class="btn btn-default" style="margin-left:5px;">
                                <span class="glyphicon glyphicon-remove"></span> 清除筛选
                            </a>
                        </form>
                        <a href="<?php echo e($export_url); ?>" class="btn btn-success" style="margin-left:15px;">
                            <span class="glyphicon glyphicon-download-alt"></span> 导出CSV
                        </a>
                    </div>
                    
                    <!-- 考生列表表格（不变） -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="80">
                                        <span class="glyphicon glyphicon-tag"></span> 序号
                                    </th>
                                    <th width="220">
                                        <span class="glyphicon glyphicon-user"></span> 考生信息
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-education"></span> 报考专业
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-file"></span> 查看资料
                                    </th>
                                    <th>
                                        <span class="glyphicon glyphicon-check"></span> 状态
                                    </th>
                                    <th width="200">
                                        <span class="glyphicon glyphicon-pencil"></span> 成绩登记
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                while ($students_row = $students_result->fetch_assoc()) { 
                                    $row_count++;
                                    $student_id = intval($students_row['id']);
                                    $student_name = e($students_row['name']);
                                    $speciality_raw = $students_row['speciality'];
                                    $speciality_display = e($speciality_raw ?: '未填写');
                                    $first_val = intval($students_row['first']);
                                    
                                    // 根据专业是否填写决定状态显示
                                    if (empty($speciality_raw)) {
                                        $status_display = '<span class="label label-default"><span class="glyphicon glyphicon-minus"></span> 专业未填写</span>';
                                    } else {
                                        if ($first_val == 0) {
                                            $status_display = '<span class="label label-warning"><span class="glyphicon glyphicon-hourglass"></span> 资格尚未审查</span>';
                                        } elseif ($first_val > 0) {
                                            $status_display = '<span class="label label-success"><span class="glyphicon glyphicon-ok-sign"></span> 资格审查通过</span>';
                                        } else { // $first_val < 0
                                            $status_display = '<span class="label label-danger"><span class="glyphicon glyphicon-remove-sign"></span> 资格审查未通过</span>';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge">
                                            #<?php echo $student_id; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo $student_name; ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                考生序号：<?php echo $student_id; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="label label-primary">
                                            <?php echo $speciality_display; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail.php?id=<?php echo $student_id; ?>" 
                                           target="_blank" 
                                           class="btn btn-info btn-sm">
                                            <span class="glyphicon glyphicon-eye-open"></span> 查看详细资料
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $status_display; ?>
                                    </td>
                                    <td>
                                        <form method="post" class="score-form" 
                                              action="control.php?action=second">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                            <input type="hidden" name="id" value="<?php echo $student_id; ?>">
                                            
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="second" class="form-control" 
                                                       value="<?php echo intval($students_row['second']); ?>" 
                                                       min="0" max="100" step="0.1" 
                                                       placeholder="输入成绩" required>
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary">
                                                        <span class="glyphicon glyphicon-ok"></span>
                                                    </button>
                                                </span>
                                            </div>
                                            <div id="scoreResult<?php echo $student_id; ?>" class="small" style="margin-top:5px;"></div>
                                        </form>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php $stmt->close(); ?>
                                
                                <?php if ($row_count == 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding:50px;">
                                        <span class="glyphicon glyphicon-user" style="font-size:48px; color:#ccc;"></span>
                                        <h5 class="text-muted" style="margin-top:10px;">暂无符合条件的考生</h5>
                                        <p class="text-muted">请尝试清除筛选条件或添加新考生。</p>
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
    // 成绩登记表单提交处理
    $('.score-form').on('submit', function(e){
        e.preventDefault();
        
        var form = $(this);
        var studentId = form.find('input[name="id"]').val();
        var resultDiv = $('#scoreResult' + studentId);
        
        // 显示加载状态
        resultDiv.html(
            '<span class="text-info">' +
                '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> 正在提交...' +
            '</span>'
        );
        
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data){
                if (data.success) {
                    resultDiv.html(
                        '<span class="text-success">' +
                            '<span class="glyphicon glyphicon-ok-sign"></span> ' + data.message +
                        '</span>'
                    );
                    
                    // 1秒后清除提示
                    setTimeout(function() {
                        resultDiv.empty();
                    }, 1000);
                } else {
                    // 提交失败
                    resultDiv.html(
                        '<span class="text-danger">' +
                            '<span class="glyphicon glyphicon-exclamation-sign"></span> ' + data.message +
                        '</span>'
                    );
                }
            },
            error: function(xhr, status, error){
                console.log(error);
                resultDiv.html(
                    '<span class="text-danger">' +
                        '<span class="glyphicon glyphicon-exclamation-sign"></span> 请求失败，请稍后再试' +
                    '</span>'
                );
            }
        });
    });
});
</script>

<?php include('bottom.php'); ?>