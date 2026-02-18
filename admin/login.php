<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/log/admin_login.log');

// ================= 获取真实IP函数（支持CDN） =================
function getClientIP() {
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '无效的请求方式']);
    exit;
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    error_log("Admin login CSRF token validation failed for IP: " . getClientIP());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
    exit;
}

include('../sql.php');
if (!$link || $link->connect_error) {
    error_log("Admin login: Database connection failed: " . ($link->connect_error ?? 'Unknown error'));
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($name) || empty($password)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '用户名和密码不能为空']);
    exit;
}

$sql = "SELECT id, name, password FROM admin_user WHERE name = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("Admin login prepare failed: " . $link->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    exit;
}
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    $stmt->close();

    $passwordMatch = false;
    if (password_verify($password, $admin['password'])) {
        $passwordMatch = true;
    } elseif ($admin['password'] === md5($password)) {
        $passwordMatch = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $link->prepare("UPDATE admin_user SET password = ? WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("si", $newHash, $admin['id']);
            $updateStmt->execute();
            $updateStmt->close();
            error_log("Admin password upgraded to hash for ID: " . $admin['id']);
        } else {
            error_log("Failed to prepare password update for admin ID " . $admin['id'] . ": " . $link->error);
        }
    }

    if ($passwordMatch) {
        session_regenerate_id(true);
        $_SESSION['id'] = $admin['id'];

        // 记录登录日志，使用真实IP
        $logSql = "INSERT INTO admin_log (uid, ip, time) VALUES (?, ?, ?)";
        $logStmt = $link->prepare($logSql);
        if ($logStmt) {
            $ip = getClientIP();
            $time = time();
            $logStmt->bind_param("isi", $admin['id'], $ip, $time);
            $logStmt->execute();
            $logStmt->close();
        } else {
            error_log("Admin login log prepare failed: " . $link->error);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => '登录成功',
            'redirect' => 'home.php'
        ]);
        exit;
    } else {
        error_log("Failed admin login attempt for user: $name from IP: " . getClientIP());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        exit;
    }
} else {
    error_log("Failed admin login attempt (user not found): $name from IP: " . getClientIP());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
    exit;
}

$link->close();
?>