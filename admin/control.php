<?php
session_start();

include('../sql.php');

// 检查用户是否登录（所有操作都需要登录）
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}
$uid = intval($_SESSION['id']);

// 获取 action 参数
if (!isset($_GET['action'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '缺少操作参数']);
    exit;
}
$action = $_GET['action'];

// 辅助函数：验证 CSRF 令牌（所有写操作必须验证）
function validateCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
        exit;
    }
}

// 辅助函数：返回 JSON 并退出
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// 辅助函数：记录错误日志
function logError($msg) {
    error_log(date('Y-m-d H:i:s') . " control.php: " . $msg);
}

switch ($action) {
    case 'loginout':
        // 退出登录（无需 CSRF）
        session_destroy();
        header("Location: /");
        break;

    case 'studentspass':
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $point = isset($_POST['point']) ? intval($_POST['point']) : 0;
        if ($id <= 0) {
            jsonResponse(false, '无效的考生ID');
        }
        // 移除对 point 的 in_array 限制，允许任意整数（0 或正数用于通过）
        // 如果需要限制非负，可加：if ($point < 0) jsonResponse(false, '无效的状态值');

        $stmt = $link->prepare("UPDATE user SET first = ? WHERE id = ?");
        if (!$stmt) {
            logError("studentspass prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ii", $point, $id);
        if ($stmt->execute()) {
            jsonResponse(true, '操作成功');
        } else {
            logError("studentspass execute failed: " . $stmt->error);
            jsonResponse(false, '操作失败');
        }
        $stmt->close();
        break;

    case 'updatearticle':
        // 修改文章（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = trim($_POST['title'] ?? '');
        $text = $_POST['text'] ?? '';

        if ($id <= 0 || empty($title)) {
            jsonResponse(false, '参数错误');
        }

        $stmt = $link->prepare("UPDATE article SET title = ?, text = ? WHERE id = ?");
        if (!$stmt) {
            logError("updatearticle prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ssi", $title, $text, $id);
        if ($stmt->execute()) {
            jsonResponse(true, '修改成功');
        } else {
            logError("updatearticle execute failed: " . $stmt->error);
            jsonResponse(false, '修改失败');
        }
        $stmt->close();
        break;

    case 'admin':
        // 添加管理员（需要 CSRF）
        validateCsrfToken();

        $user = trim($_POST['user'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($user)) {
            jsonResponse(false, '管理员用户名不能为空');
        }
        if (empty($password)) {
            jsonResponse(false, '管理员密码不能为空');
        }
        if (strlen($password) < 6) {
            jsonResponse(false, '密码长度至少6位');
        }

        // 检查用户名是否已存在
        $checkStmt = $link->prepare("SELECT id FROM admin_user WHERE name = ?");
        if (!$checkStmt) {
            logError("admin check prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $checkStmt->bind_param("s", $user);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            jsonResponse(false, '用户名已存在');
        }
        $checkStmt->close();

        // 密码哈希（使用 password_hash）
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $link->prepare("INSERT INTO admin_user (name, password) VALUES (?, ?)");
        if (!$stmt) {
            logError("admin insert prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ss", $user, $hashedPassword);
        if ($stmt->execute()) {
            jsonResponse(true, '添加成功！');
        } else {
            logError("admin insert execute failed: " . $stmt->error);
            jsonResponse(false, '添加失败');
        }
        $stmt->close();
        break;

    case 'pass':
        // 修改当前管理员密码（需要 CSRF）
        validateCsrfToken();

        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            jsonResponse(false, '管理员密码不能为空');
        }
        if (strlen($password) < 6) {
            jsonResponse(false, '密码长度至少6位');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $link->prepare("UPDATE admin_user SET password = ? WHERE id = ?");
        if (!$stmt) {
            logError("pass update prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("si", $hashedPassword, $uid);
        if ($stmt->execute()) {
            jsonResponse(true, '修改成功');
        } else {
            logError("pass update execute failed: " . $stmt->error);
            jsonResponse(false, '修改失败');
        }
        $stmt->close();
        break;

    case 'sdel':
        // 删除专业（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            jsonResponse(false, '无效的专业ID');
        }

        // 检查该专业是否被考生使用（可选，根据业务需求）
        // 如果有考生选择了该专业，则不允许删除，或级联处理。这里简单示例检查。
        $checkStmt = $link->prepare("SELECT id FROM user WHERE speciality = (SELECT name FROM speciality WHERE id = ?) LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                jsonResponse(false, '该专业已有考生选择，无法删除');
            }
            $checkStmt->close();
        }

        $stmt = $link->prepare("DELETE FROM speciality WHERE id = ?");
        if (!$stmt) {
            logError("sdel prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            jsonResponse(true, '删除成功');
        } else {
            logError("sdel execute failed: " . $stmt->error);
            jsonResponse(false, '删除失败');
        }
        $stmt->close();
        break;

    case 'admindel':
        // 删除管理员（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            jsonResponse(false, '无效的管理员ID');
        }

        // 不能删除自己
        if ($id == $uid) {
            jsonResponse(false, '不能删除当前登录的管理员');
        }

        // 检查是否至少保留一个管理员
        $countStmt = $link->prepare("SELECT COUNT(*) as cnt FROM admin_user");
        if (!$countStmt) {
            logError("admindel count prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $countStmt->close();
        if ($countRow['cnt'] <= 1) {
            jsonResponse(false, '系统中至少需要保留一个管理员账户');
        }

        $stmt = $link->prepare("DELETE FROM admin_user WHERE id = ?");
        if (!$stmt) {
            logError("admindel delete prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            jsonResponse(true, '删除成功');
        } else {
            logError("admindel delete execute failed: " . $stmt->error);
            jsonResponse(false, '删除失败');
        }
        $stmt->close();
        break;

    case 'delarticle':
        // 删除文章（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            jsonResponse(false, '无效的文章ID');
        }

        $stmt = $link->prepare("DELETE FROM article WHERE id = ?");
        if (!$stmt) {
            logError("delarticle prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            jsonResponse(true, '删除成功');
        } else {
            logError("delarticle execute failed: " . $stmt->error);
            jsonResponse(false, '删除失败');
        }
        $stmt->close();
        break;

    case 'disable':
        // 标记资格审查不通过并填写理由（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $why = trim($_POST['why'] ?? '');
        if ($id <= 0) {
            jsonResponse(false, '无效的考生ID');
        }
        if (empty($why)) {
            jsonResponse(false, '请填写不通过理由');
        }

        // 修改：将 first 设为负数（-1）表示不通过
        $stmt = $link->prepare("UPDATE user SET first = -1, why = ? WHERE id = ?");
        if (!$stmt) {
            logError("disable prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("si", $why, $id);
        if ($stmt->execute()) {
            jsonResponse(true, '操作成功');
        } else {
            logError("disable execute failed: " . $stmt->error);
            jsonResponse(false, '操作失败');
        }
        $stmt->close();
        break;

    case 'second':
        // 登记成绩（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $second = isset($_POST['second']) ? floatval($_POST['second']) : 0;
        if ($id <= 0) {
            jsonResponse(false, '无效的考生ID');
        }
        if ($second < 0 || $second > 100) {
            jsonResponse(false, '成绩必须在0-100之间');
        }

        $stmt = $link->prepare("UPDATE user SET second = ? WHERE id = ?");
        if (!$stmt) {
            logError("second prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("di", $second, $id);
        if ($stmt->execute()) {
            jsonResponse(true, '成绩登记成功');
        } else {
            logError("second execute failed: " . $stmt->error);
            jsonResponse(false, '登记失败');
        }
        $stmt->close();
        break;

    case 'speciality':
        // 添加专业（需要 CSRF）
        validateCsrfToken();

        $speciality = trim($_POST['speciality'] ?? '');
        $mlzyl = trim($_POST['zylb'] ?? '');
        $xw = trim($_POST['xw'] ?? '');
        $years = trim($_POST['years'] ?? '');
        $school = trim($_POST['school'] ?? '');
        $total = trim($_POST['total'] ?? '');
        $price = trim($_POST['price'] ?? '');

        if (empty($speciality) || empty($mlzyl) || empty($xw) || empty($years) || empty($school) || empty($total) || empty($price)) {
            jsonResponse(false, '所有字段均为必填');
        }
        // 简单数字验证
        if (!is_numeric($years) || !is_numeric($total) || !is_numeric($price)) {
            jsonResponse(false, '学制、人数、学费必须为数字');
        }

        $stmt = $link->prepare("INSERT INTO speciality (name, mlzyl, xwsyml, years, school, total, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            logError("speciality insert prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("sssssii", $speciality, $mlzyl, $xw, $years, $school, $total, $price);
        if ($stmt->execute()) {
            jsonResponse(true, '添加成功！');
        } else {
            logError("speciality insert execute failed: " . $stmt->error);
            jsonResponse(false, '添加失败');
        }
        $stmt->close();
        break;

    case 'updatespeciality':
        // 修改专业（需要 CSRF）
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $speciality = trim($_POST['speciality'] ?? '');
        $mlzyl = trim($_POST['zylb'] ?? '');
        $xw = trim($_POST['xw'] ?? '');
        $years = trim($_POST['years'] ?? '');
        $school = trim($_POST['school'] ?? '');
        $total = trim($_POST['total'] ?? '');
        $price = trim($_POST['price'] ?? '');

        if ($id <= 0) {
            jsonResponse(false, '无效的专业ID');
        }
        if (empty($speciality) || empty($mlzyl) || empty($xw) || empty($years) || empty($school) || empty($total) || empty($price)) {
            jsonResponse(false, '所有字段均为必填');
        }
        if (!is_numeric($years) || !is_numeric($total) || !is_numeric($price)) {
            jsonResponse(false, '学制、人数、学费必须为数字');
        }

        $stmt = $link->prepare("UPDATE speciality SET name = ?, mlzyl = ?, xwsyml = ?, years = ?, school = ?, total = ?, price = ? WHERE id = ?");
        if (!$stmt) {
            logError("updatespeciality prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("sssssiii", $speciality, $mlzyl, $xw, $years, $school, $total, $price, $id);
        if ($stmt->execute()) {
            jsonResponse(true, '修改成功！');
        } else {
            logError("updatespeciality execute failed: " . $stmt->error);
            jsonResponse(false, '修改失败');
        }
        $stmt->close();
        break;

    case 'article':
        // 发布文章（需要 CSRF）
        validateCsrfToken();

        $title = trim($_POST['title'] ?? '');
        $text = $_POST['text'] ?? '';
        $time = time();

        if (empty($title)) {
            jsonResponse(false, '文章标题不能为空');
        }
        if (empty($text)) {
            jsonResponse(false, '文章内容不能为空');
        }

        $stmt = $link->prepare("INSERT INTO article (title, text, time) VALUES (?, ?, ?)");
        if (!$stmt) {
            logError("article insert prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ssi", $title, $text, $time);
        if ($stmt->execute()) {
            jsonResponse(true, '发表成功！');
        } else {
            logError("article insert execute failed: " . $stmt->error);
            jsonResponse(false, '发表失败');
        }
        $stmt->close();
        break;

    case 'webinfo':
    // 更新网站信息（需要 CSRF）
    validateCsrfToken();

    $webname = trim($_POST['webname'] ?? '');
    $school_code = trim($_POST['school_code'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mail = trim($_POST['mail'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $icp = trim($_POST['icp'] ?? '');
    $banner = trim($_POST['banner'] ?? '');
    $introduce = $_POST['introduce'] ?? '';

    if (empty($webname) || empty($school_code) || empty($address) || empty($mail) || empty($tel) || empty($banner) || empty($introduce)) {
        jsonResponse(false, '必填项不能为空');
    }

    // 检查记录是否存在，并获取当前 static 值
    $checkStmt = $link->prepare("SELECT id, static FROM webinfo WHERE id = 1");
    if (!$checkStmt) {
        logError("webinfo check prepare failed: " . $link->error);
        jsonResponse(false, '系统错误，请稍后重试');
    }
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $exists = true;
        $row = $checkResult->fetch_assoc();
        $static = $row['static']; // 保持当前 static 值不变
    } else {
        $exists = false;
        $static = 0; // 默认关闭
    }
    $checkStmt->close();

    if ($exists) {
        $stmt = $link->prepare("UPDATE webinfo SET webname = ?, school_code = ?, mail = ?, tel = ?, address = ?, icp = ?, banner = ?, introduce = ?, static = ? WHERE id = 1");
        if (!$stmt) {
            logError("webinfo update prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ssssssssi", $webname, $school_code, $mail, $tel, $address, $icp, $banner, $introduce, $static);
    } else {
        $stmt = $link->prepare("INSERT INTO webinfo (id, webname, school_code, mail, tel, address, icp, banner, introduce, static) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            logError("webinfo insert prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("ssssssssi", $webname, $school_code, $mail, $tel, $address, $icp, $banner, $introduce, $static);
    }

    if ($stmt->execute()) {
        jsonResponse(true, '更新成功');
    } else {
        logError("webinfo execute failed: " . $stmt->error);
        // 临时返回具体错误（调试后可以改回通用消息）
        jsonResponse(false, '更新失败：' . $stmt->error);
    }
    $stmt->close();
    break;

    case 'reg':
        // 开启/关闭注册（需要 CSRF）
        validateCsrfToken();

        $int = isset($_POST['int']) ? intval($_POST['int']) : 0;
        $int = ($int == 1) ? 1 : 0;

        $stmt = $link->prepare("UPDATE webinfo SET reg = ? WHERE id = 1");
        if (!$stmt) {
            logError("reg update prepare failed: " . $link->error);
            jsonResponse(false, '系统错误，请稍后重试');
        }
        $stmt->bind_param("i", $int);
        if ($stmt->execute()) {
            $message = $int ? "注册功能已开启" : "注册功能已关闭";
            jsonResponse(true, $message);
        } else {
            logError("reg update execute failed: " . $stmt->error);
            jsonResponse(false, '更新失败');
        }
        $stmt->close();
        break;
        
        case 'static':
    // 开启/关闭伪静态（需要 CSRF）
    validateCsrfToken();

    $int = isset($_POST['int']) ? intval($_POST['int']) : 0;
    $int = ($int == 1) ? 1 : 0;

    $stmt = $link->prepare("UPDATE webinfo SET static = ? WHERE id = 1");
    if (!$stmt) {
        logError("static update prepare failed: " . $link->error);
        jsonResponse(false, '系统错误，请稍后重试');
    }
    $stmt->bind_param("i", $int);
    if ($stmt->execute()) {
        $message = $int ? "伪静态已开启" : "伪静态已关闭";
        jsonResponse(true, $message);
    } else {
        logError("static update execute failed: " . $stmt->error);
        jsonResponse(false, '更新失败');
    }
    $stmt->close();
    break;

    default:
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '无效的操作']);
        exit;
}