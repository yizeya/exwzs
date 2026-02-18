<?php
include('top.php');

// ================= 获取伪静态开关状态 =================
$static_enabled = 0;
$webinfo_sql = "SELECT static FROM webinfo WHERE id = 1 LIMIT 1";
$webinfo_result = $link->query($webinfo_sql);
if ($webinfo_result && $webinfo_result->num_rows > 0) {
    $webinfo_row = $webinfo_result->fetch_assoc();
    $static_enabled = (int)($webinfo_row['static'] ?? 0);
}

// 根据开关生成文章详情链接
function article_url($id) {
    global $static_enabled;
    $id = intval($id);
    return $static_enabled ? "article-{$id}.htm" : "article.php?id={$id}";
}

// 根据开关生成列表页链接（支持分页）
function list_url($page = null) {
    global $static_enabled;
    if (!$static_enabled) {
        return is_null($page) || $page <= 1 ? 'list.php' : 'list.php?page=' . intval($page);
    }
    if (is_null($page) || $page <= 1) {
        return 'list.htm';
    }
    return "list-{$page}.htm";
}
// ==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!file_exists('sql.php')) {
    exit('系统未安装或配置文件丢失');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页 - 通知公告系统</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <style>
        .spinning {
            animation: spin 1s infinite linear;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <?php
    // 注意：$web_info_row 来自 top.php 包含的全局查询，应已存在
    $banner = isset($web_info_row['banner']) ? htmlspecialchars($web_info_row['banner'], ENT_QUOTES, 'UTF-8') : '';
    $reg_enabled = isset($web_info_row['reg']) ? $web_info_row['reg'] : false;
    $tel = isset($web_info_row['tel']) ? htmlspecialchars($web_info_row['tel'], ENT_QUOTES, 'UTF-8') : '';
    $mail = isset($web_info_row['mail']) ? htmlspecialchars($web_info_row['mail'], ENT_QUOTES, 'UTF-8') : '';
    $address = isset($web_info_row['address']) ? htmlspecialchars($web_info_row['address'], ENT_QUOTES, 'UTF-8') : '';
    ?>

    <!-- 横幅区域（背景图已安全转义） -->
    <div class="index_banner" style="background-image: url('<?php echo $banner; ?>'); height: 500px; background-position: center center; background-repeat: no-repeat; background-size: cover; position: relative;">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-4 col-md-6 col-sm-8 col-lg-offset-8 col-md-offset-6 col-sm-offset-4">
                    <div class="panel panel-default">
                        <div class="panel-body" style="padding: 30px;">
                            <div class="text-center" style="margin-bottom: 20px;">
                                <h4 class="text-primary" style="font-weight: bold;">
                                    <span class="glyphicon glyphicon-log-in" style="margin-right: 8px;"></span>用户登录
                                </h4>
                                <p class="text-muted">请输入您的凭证信息</p>
                            </div>
                            <form id="loginForm" method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-group">
                                    <label for="number">
                                        <span class="glyphicon glyphicon-user" style="margin-right: 8px;"></span>证件号：
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-user"></span>
                                        </span>
                                        <input type="text" class="form-control" id="number" name="number" placeholder="请输入证件号" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password">
                                        <span class="glyphicon glyphicon-lock" style="margin-right: 8px;"></span>密码：
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-lock"></span>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="togglePassword">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 25px;">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        登入系统
                                        <span class="glyphicon glyphicon-arrow-right" style="margin-left: 8px;"></span>
                                    </button>
                                </div>
                                <div class="text-center" style="padding-top: 15px; border-top: 1px solid #eee;">
                                    <?php if (empty($reg_enabled)): ?>
                                        <p class="text-warning" style="font-weight: bold;">
                                            <span class="glyphicon glyphicon-exclamation-sign" style="margin-right: 8px;"></span>请使用证件号和密码登录
                                        </p>
                                    <?php else: ?>
                                        <a href="reg.php" class="text-decoration-none">
                                            <span class="glyphicon glyphicon-plus-sign" style="margin-right: 5px;"></span>第一次登入？请快速立即注册。
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 内容区域 -->
    <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default" style="height: 100%;">
                    <div class="panel-heading" style="background-color: #f2f2f2; border-bottom: 1px solid #ddd;">
                        <div class="row">
                            <div class="col-xs-6">
                                <span class="badge" style="background-color: #337ab7; padding: 8px; margin-right: 10px;">
                                    <span class="glyphicon glyphicon-bell" style="color: white;"></span>
                                </span>
                                <h5 style="display: inline; font-weight: bold; color: #337ab7;">通知公告</h5>
                            </div>
                            <div class="col-xs-6 text-right">
                                <a href="<?php echo list_url(); ?>" class="btn btn-primary btn-sm">
                                    更多...
                                    <span class="glyphicon glyphicon-arrow-right" style="margin-left: 5px;"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="list-group" style="margin-bottom: 0;">
                        <?php
                        $sql = "SELECT id, title, time FROM article ORDER BY id DESC LIMIT 5";
                        $stmt = $link->prepare($sql);
                        if ($stmt) {
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $has_rows = false;
                            while ($row = $result->fetch_assoc()) {
                                $has_rows = true;
                                // 安全转义标题
                                $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                                // 截取标题长度（基于字符）
                                if (mb_strlen($title, 'UTF-8') > 18) {
                                    $shortTitle = mb_substr($title, 0, 18, 'UTF-8') . '…';
                                } else {
                                    $shortTitle = $title;
                                }
                                // 时间戳转为日期，整数无需转义
                                $date = date("Y-m-d", $row['time']);
                                // ID必须为整数
                                $id = intval($row['id']);
                        ?>
                        <a href="<?php echo article_url($id); ?>" class="list-group-item" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <span class="glyphicon glyphicon-file" style="margin-right: 10px; color: #337ab7;"></span>
                                <?php echo $shortTitle; ?>
                            </span>
                            <span class="text-muted small">
                                <span class="glyphicon glyphicon-calendar" style="margin-right: 5px;"></span>
                                <?php echo $date; ?>
                            </span>
                        </a>
                        <?php
                            }
                            $stmt->close();
                            if (!$has_rows) {
                                echo '<div class="list-group-item text-center" style="padding: 30px;">';
                                echo '<span class="glyphicon glyphicon-inbox" style="font-size: 48px; color: #ccc;"></span>';
                                echo '<p class="text-muted" style="margin-top: 10px;">暂无通知公告</p>';
                                echo '</div>';
                            }
                        } else {
                            error_log("首页公告查询预处理失败: " . $link->error);
                            echo '<div class="list-group-item text-center text-danger">数据获取失败，请稍后重试。</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default" style="height: 100%;">
                    <div class="panel-body">
                        <div class="text-center" style="margin-bottom: 25px;">
                            <span class="glyphicon glyphicon-phone-alt" style="font-size: 48px; color: #337ab7;"></span>
                            <h4 style="color: #337ab7; font-weight: bold;">咨询电话</h4>
                            <p style="font-size: 28px; font-weight: bold; margin-top: 15px;"><?php echo $tel; ?></p>
                        </div>
                        
                        <div style="border-top: 1px solid #eee; padding-top: 20px;">
                            <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                <span class="glyphicon glyphicon-envelope" style="color: #337ab7; font-size: 18px; margin-right: 15px;"></span>
                                <div>
                                    <h6 style="color: #777; margin-bottom: 5px;">邮箱地址</h6>
                                    <p style="margin-bottom: 0;"><?php echo $mail; ?></p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: flex-start;">
                                <span class="glyphicon glyphicon-map-marker" style="color: #337ab7; font-size: 18px; margin-right: 15px;"></span>
                                <div>
                                    <h6 style="color: #777; margin-bottom: 5px;">办公地址</h6>
                                    <p style="margin-bottom: 0; font-size: 13px;"><?php echo $address; ?></p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 提示模态框 -->
    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #fcf8e3; color: #8a6d3b;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" style="font-weight: bold;">
                        <span class="glyphicon glyphicon-exclamation-sign" style="margin-right: 8px;"></span>系统提示
                    </h5>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div style="display: flex;">
                        <span class="glyphicon glyphicon-info-sign" style="color: #337ab7; font-size: 24px; margin-right: 15px;"></span>
                        <div id="message_alert" class="flex-grow-1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="location.href='home.php';">
                        <span class="glyphicon glyphicon-ok" style="margin-right: 5px;"></span>确定
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript 依赖 -->
    <script src="/static/js/jquery.min.js"></script>
    <script src="/static/js/bootstrap.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#togglePassword').click(function() {
                var passwordField = $('#password');
                var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).find('span').toggleClass('glyphicon-eye-open glyphicon-eye-close');
            });
            
            $('#loginForm').submit(function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();
                submitBtn.html('<span class="glyphicon glyphicon-refresh spinning"></span> 登录中...');
                submitBtn.prop('disabled', true);
                
                $.ajax({
                    type: 'POST',
                    url: 'login.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        $('#message_alert').text(response.message || '操作完成');
                        $('#alertModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        alert('请求失败，请稍后重试。');
                        console.error('Login AJAX error:', error);
                    },
                    complete: function() {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>

<?php
include('bottom.php');
?>