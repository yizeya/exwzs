<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/log/admin.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('../sql.php');
    if (!$link || $link->connect_error) {
        error_log("Admin login: Database connection failed: " . ($link->connect_error ?? 'Unknown error'));
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
        exit;
    }

    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        error_log("Admin login CSRF token validation failed for IP: " . $_SERVER['REMOTE_ADDR']);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
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

            $logSql = "INSERT INTO admin_log (uid, ip, time) VALUES (?, ?, ?)";
            $logStmt = $link->prepare($logSql);
            if ($logStmt) {
                $ip = $_SERVER['REMOTE_ADDR'];
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
            error_log("Failed admin login attempt for user: $name from IP: " . $_SERVER['REMOTE_ADDR']);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
            exit;
        }
    } else {
        error_log("Failed admin login attempt (user not found): $name from IP: " . $_SERVER['REMOTE_ADDR']);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        exit;
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include('top.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 招生系统</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
</head>
<body>
    <div class="container" style="margin-top: 50px; margin-bottom: 50px;">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center" style="padding: 15px;">
                        <h2 class="panel-title" style="font-size: 24px; font-weight: normal;">
                            <span class="glyphicon glyphicon-lock" style="margin-right: 8px;"></span>管理登入
                        </h2>
                    </div>
                    <div class="panel-body" style="padding: 30px;">
                        <form id="loginForm" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="form-group" style="margin-bottom: 25px;">
                                <label for="name" style="font-weight: bold;">
                                    <span class="glyphicon glyphicon-user" style="margin-right: 5px;"></span>管理员用户：
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-user"></span>
                                    </span>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="请输入管理员用户名" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 30px;">
                                <label for="password" style="font-weight: bold;">
                                    <span class="glyphicon glyphicon-lock" style="margin-right: 5px;"></span>管理员密码：
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-lock"></span>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block" style="padding: 15px;">
                                    <span class="glyphicon glyphicon-log-in" style="margin-right: 8px;"></span>登入
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning" style="color: #8a6d3b;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <span class="glyphicon glyphicon-exclamation-sign" style="margin-right: 8px;"></span>警告
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-exclamation-sign text-warning" style="font-size: 32px;"></span>
                        </div>
                        <div class="media-body" id="message_alert" style="padding-left: 15px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                        <span class="glyphicon glyphicon-ok" style="margin-right: 5px;"></span>确定
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚信息 -->
    <footer class="footer" style="margin-top: 30px; padding: 20px 0; background-color: #f5f5f5; border-top: 1px solid #ddd;">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-6" style="margin-bottom: 10px;">
                    <span class="glyphicon glyphicon-info-sign" style="margin-right: 5px;"></span>
                    <span class="text-muted">第二学士学位招生系统 v1.0</span>
                </div>
                <div class="col-md-6">
                    <span class="glyphicon glyphicon-lock" style="margin-right: 5px;"></span>
                    <span class="text-muted">安全登录系统</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="/static/js/jquery-3.6.0.min.js"></script>
    <script src="/static/js/bootstrap.min.js"></script>

    <script type="text/javascript">
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#message_alert').html(response.message);
                    $('#alertModal').modal('show');
                    
                    if (response.success && response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    $('#message_alert').html('请求失败，请稍后重试。');
                    $('#alertModal').modal('show');
                }
            });
        });
    </script>
</body>
</html>
<?php include('bottom.php'); ?>