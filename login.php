<?php
session_start();

include('sql.php'); // 数据库连接配置

// ================= 获取真实IP函数（支持CDN） =================
function getClientIP() {
    $ip = '';
    // 按优先级检查常见的IP头
    $headers = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',   // 标准代理转发
        'HTTP_X_REAL_IP',         // Nginx 代理
        'HTTP_TRUE_CLIENT_IP',    // 某些 CDN
        'REMOTE_ADDR'             // 最后回退到原始地址
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip_list = $_SERVER[$header];
            // X-Forwarded-For 可能包含逗号分隔的IP列表，取第一个（最原始的客户端IP）
            if (strpos($ip_list, ',') !== false) {
                $ip_list = explode(',', $ip_list)[0];
            }
            $ip = trim($ip_list);
            // 验证是否为有效IP（不排除私有地址，因为CDN回源可能是内网）
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    // 如果以上都未获得有效IP，返回 REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'];
}
// =============================================================

// 检查数据库连接
if (!$link || $link->connect_error) {
    error_log("Database connection failed: " . ($link->connect_error ?? 'Unknown error'));
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    exit;
}

// 仅接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '无效的请求方式']);
    exit;
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $client_ip = getClientIP(); // 记录真实IP
    error_log("CSRF token validation failed for IP: " . $client_ip);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
    exit;
}

$number = trim($_POST['number'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($number) || empty($password)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '证件号和密码不能为空']);
    exit;
}

$sql = "SELECT id, number, password FROM user WHERE number = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $link->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    exit;
}

$stmt->bind_param("s", $number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $passwordMatch = false;
    if (password_verify($password, $user['password'])) {
        $passwordMatch = true;
    } elseif ($user['password'] === md5($password)) {
        $passwordMatch = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $link->prepare("UPDATE user SET password = ? WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("si", $newHash, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            error_log("Password upgraded to hash for user ID: " . $user['id']);
        } else {
            error_log("Failed to prepare password update: " . $link->error);
        }
    }

    if ($passwordMatch) {
        session_regenerate_id(true);
        $_SESSION['id'] = $user['id'];

        // 记录登录日志，使用真实IP
        $logSql = "INSERT INTO user_log (uid, ip, time) VALUES (?, ?, ?)";
        $logStmt = $link->prepare($logSql);
        if ($logStmt) {
            $ip = getClientIP(); // 获取真实IP
            $time = time();
            $logStmt->bind_param("isi", $user['id'], $ip, $time);
            $logStmt->execute();
            $logStmt->close();
        } else {
            error_log("Failed to prepare log statement: " . $link->error);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => '登录成功',
            'redirect' => 'home.php'
        ]);
        exit;
    } else {
        $client_ip = getClientIP();
        error_log("Failed login attempt for user: $number from IP: " . $client_ip);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        exit;
    }
} else {
    $client_ip = getClientIP();
    error_log("Failed login attempt (user not found): $number from IP: " . $client_ip);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
    exit;
}

$stmt->close();
$link->close();
?>