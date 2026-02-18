<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/log/reg-error.log');

include('sql.php');

// 检查数据库连接
if (!$link || $link->connect_error) {
    error_log("reg_post.php: 数据库连接失败 - " . ($link->connect_error ?? 'Unknown error'));
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 系统错误，请稍后重试。
          </div>';
    exit;
}

// 验证请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign"></span> 无效的请求方式。
          </div>';
    exit;
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    error_log("reg_post.php: CSRF token validation failed for IP: " . $_SERVER['REMOTE_ADDR']);
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign"></span> 安全验证失败，请刷新页面重试。
          </div>';
    exit;
}

$number   = trim($_POST['number'] ?? '');
$password = $_POST['password'] ?? '';
$name     = trim($_POST['name'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$mail     = trim($_POST['mail'] ?? '');

// 基本输入验证
$errors = [];
if (empty($number)) {
    $errors[] = '证件号不能为空';
} elseif (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $number)) {
    $errors[] = '证件号格式不正确（6-20位字母或数字）';
}
if (empty($password)) {
    $errors[] = '密码不能为空';
} elseif (strlen($password) < 6 || strlen($password) > 20) {
    $errors[] = '密码长度必须在6-20位之间';
}
if (empty($name)) {
    $errors[] = '姓名不能为空';
} elseif (mb_strlen($name, 'UTF-8') > 20) {
    $errors[] = '姓名长度不能超过20个字符';
}
if (empty($telephone)) {
    $errors[] = '手机号码不能为空';
} elseif (!preg_match('/^1[3-9]\d{9}$/', $telephone)) { // 中国大陆手机号简单验证
    $errors[] = '手机号码格式不正确';
}
if (empty($mail)) {
    $errors[] = '邮箱不能为空';
} elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '邮箱格式不正确';
}

if (!empty($errors)) {
    // 将所有错误拼接输出，并转义
    $errorMsg = '';
    foreach ($errors as $err) {
        $errorMsg .= '<span class="glyphicon glyphicon-exclamation-sign"></span> ' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '<br>';
    }
    echo '<div class="alert alert-danger" role="alert">' . $errorMsg . '</div>';
    exit;
}

// 检查证件号是否已存在（使用预处理语句）
$check_sql = "SELECT id FROM user WHERE number = ?";
$check_stmt = $link->prepare($check_sql);
if (!$check_stmt) {
    error_log("reg_post.php: 预处理检查失败 - " . $link->error);
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 系统错误，请稍后重试。
          </div>';
    exit;
}
$check_stmt->bind_param("s", $number);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    $check_stmt->close();
    echo '<div class="alert alert-warning" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign"></span> 证件号码已存在！请直接登录。
          </div>';
    exit;
}
$check_stmt->close();

// 获取学校代码（使用预处理）
$webinfo_sql = "SELECT school_code FROM webinfo LIMIT 1";
$webinfo_stmt = $link->prepare($webinfo_sql);
if (!$webinfo_stmt) {
    error_log("reg_post.php: 获取学校代码失败 - " . $link->error);
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 系统错误，请稍后重试。
          </div>';
    exit;
}
$webinfo_stmt->execute();
$webinfo_result = $webinfo_stmt->get_result();
if ($webinfo_result->num_rows === 0) {
    error_log("reg_post.php: webinfo 表无数据");
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 系统配置错误，请联系管理员。
          </div>';
    $webinfo_stmt->close();
    exit;
}
$webinfo_row = $webinfo_result->fetch_assoc();
$school_code = $webinfo_row['school_code'] ?? '';
$webinfo_stmt->close();
$password_hash = md5($password);

// 获取客户端IP
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();

// 插入用户数据（使用预处理语句）
$insert_sql = "INSERT INTO user (number, password, name, telephone, mail, ip, time, exnumber, photo, address) 
               VALUES (?, ?, ?, ?, ?, ?, ?, '', '', '')";
$insert_stmt = $link->prepare($insert_sql);
if (!$insert_stmt) {
    error_log("reg_post.php: 插入预处理失败 - " . $link->error);
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 系统错误，请稍后重试。
          </div>';
    exit;
}
$insert_stmt->bind_param("ssssssi", $number, $password_hash, $name, $telephone, $mail, $ip, $time);

if ($insert_stmt->execute()) {
    $user_id = $insert_stmt->insert_id; // 获取插入的ID
    $insert_stmt->close();

    // 生成准考证号：学校代码 + 年份后两位 + 用户ID（补零至4位）
    $current_year = date('y'); // 两位年份
    $exnumber = $school_code . $current_year . str_pad($user_id, 4, '0', STR_PAD_LEFT);

    // 更新准考证号
    $update_sql = "UPDATE user SET exnumber = ? WHERE id = ?";
    $update_stmt = $link->prepare($update_sql);
    if (!$update_stmt) {
        error_log("reg_post.php: 更新准考证号预处理失败 - " . $link->error);
        // 即使更新失败，用户已注册，可输出部分成功信息
        echo '<div class="alert alert-warning" role="alert">
                <span class="glyphicon glyphicon-ok-sign"></span> 注册成功，但准考证号生成失败，请联系管理员。
              </div>';
        exit;
    }
    $update_stmt->bind_param("si", $exnumber, $user_id);
    if ($update_stmt->execute()) {
        $update_stmt->close();
        // 成功，输出安全转义后的信息
        echo '<div class="alert alert-success" role="alert">
                <span class="glyphicon glyphicon-ok-sign"></span> 恭喜您，注册成功！
                <div style="margin-top:10px;">
                    <p class="mb-1"><strong>准考证号：</strong>' . htmlspecialchars($exnumber, ENT_QUOTES, 'UTF-8') . '</p>
                    <p class="mb-0"><strong>请妥善保管您的准考证号！</strong></p>
                </div>
              </div>';
    } else {
        error_log("reg_post.php: 更新准考证号失败 - " . $update_stmt->error);
        $update_stmt->close();
        echo '<div class="alert alert-warning" role="alert">
                <span class="glyphicon glyphicon-ok-sign"></span> 注册成功，但准考证号生成失败，请联系管理员。
              </div>';
    }
} else {
    error_log("reg_post.php: 插入用户失败 - " . $insert_stmt->error);
    $insert_stmt->close();
    echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign"></span> 注册失败，请稍后重试。
          </div>';
}

// 关闭数据库连接（可选）
$link->close();
?>