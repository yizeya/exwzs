<?php
session_start();

include('sql.php');

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '请先登录']);
    exit;
}
$uid = intval($_SESSION['id']);

$time = time();
$ip = $_SERVER["REMOTE_ADDR"];

function validateCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('安全验证失败，请刷新页面重试');
    }
}

function generateSafeFileName($originalName, $uid) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeExt = preg_replace('/[^a-z0-9]/', '', $ext);
    if (!in_array($safeExt, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
        return false;
    }
    return $uid . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
}

function fileBelongsToUser($filePath, $uid) {
    $baseDir = realpath(__DIR__ . '/students/');
    $fullPath = realpath($filePath);
    if ($fullPath === false) return false;
    return strpos($fullPath, $baseDir . DIRECTORY_SEPARATOR . $uid . DIRECTORY_SEPARATOR) === 0;
}

if (!isset($_GET['action'])) {
    http_response_code(400);
    exit('缺少操作参数');
}

$action = $_GET['action'];

switch ($action) {
    case 'loginout':
        session_destroy();
        header("Location: /");
        break;

    case 'upload':
        validateCsrfToken();

        $type = $_POST['type'] ?? '';
        $allowedTypes = ['photo', 'cardz', 'cardf', 'sqb', 'cns', 'cjd', 'bgd', 'xlzsphoto', 'xwzsphoto'];
        if (!in_array($type, $allowedTypes)) {
            echo '无效的文件类型';
            exit;
        }

        if (empty($_FILES['imageUpload'])) {
            echo '请选择要上传的文件';
            exit;
        }

        $file = $_FILES['imageUpload'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo '文件上传错误：' . $file['error'];
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo '文件大小不能超过2MB';
            exit;
        }

        $safeName = generateSafeFileName($file['name'], $uid);
        if (!$safeName) {
            echo '只允许 JPG, JPEG, PNG, GIF, PDF 文件';
            exit;
        }

        $targetDir = __DIR__ . '/students/' . $uid . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0750, true);
        }

        $targetFile = $targetDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $stmt = $link->prepare("INSERT INTO upload (uid, files, type, time) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                error_log("upload prepare failed: " . $link->error);
                echo '数据库错误';
                exit;
            }
            $relativePath = '/students/' . $uid . '/' . $safeName;
            $stmt->bind_param("issi", $uid, $relativePath, $type, $time);
            $stmt->execute();
            $stmt->close();

            $updateStmt = $link->prepare("UPDATE user SET $type = ? WHERE id = ?");
            if (!$updateStmt) {
                error_log("update user prepare failed: " . $link->error);
                echo '数据库错误';
                exit;
            }
            $updateStmt->bind_param("si", $relativePath, $uid);
            $updateStmt->execute();
            $updateStmt->close();

            echo '上传成功：' . htmlspecialchars($relativePath);
        } else {
            echo '文件保存失败，请检查目录权限';
        }
        break;

    case 'delfiles':
        validateCsrfToken();

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $filePath = isset($_POST['files']) ? trim($_POST['files']) : '';

        if ($id <= 0 || empty($filePath)) {
            echo '参数错误';
            exit;
        }

        $checkStmt = $link->prepare("SELECT files FROM upload WHERE id = ? AND uid = ?");
        $checkStmt->bind_param("ii", $id, $uid);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            echo '无权删除此文件';
            exit;
        }
        $row = $checkResult->fetch_assoc();
        $dbFilePath = $row['files'];
        $checkStmt->close();

        if ($dbFilePath !== $filePath) {
            echo '文件信息不匹配';
            exit;
        }

        $fullPath = __DIR__ . $filePath;
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            echo '文件不存在';
            exit;
        }

        if (!fileBelongsToUser($fullPath, $uid)) {
            echo '无权操作此文件';
            exit;
        }

        if (unlink($fullPath)) {
            $delStmt = $link->prepare("DELETE FROM upload WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();

            $typeStmt = $link->prepare("SELECT type FROM upload WHERE id = ?");
            $typeStmt->bind_param("i", $id);
            $typeStmt->execute();
            $typeResult = $typeStmt->get_result();
            if ($typeResult->num_rows > 0) {
                $typeRow = $typeResult->fetch_assoc();
                $field = $typeRow['type'];
                $typeStmt->close();

                $updateStmt = $link->prepare("UPDATE user SET $field = '' WHERE id = ?");
                $updateStmt->bind_param("i", $uid);
                $updateStmt->execute();
                $updateStmt->close();
            }

            header("Location: " . $_SERVER["HTTP_REFERER"] ?? 'upload.php');
        } else {
            echo '文件删除失败，请检查权限';
        }
        break;

    case 'my':
        validateCsrfToken();

        $fields = [
            'cardz', 'cardf', 'sqb', 'cns', 'bgd', 'cjd', 'exnumber', 'photo',
            'address', 'school', 'years', 'bkzy', 'zylb', 'xlzs', 'xwzs',
            'xlzsphoto', 'xwzsphoto'
        ];
        $data = [];
        foreach ($fields as $field) {
            $value = isset($_POST[$field]) ? trim($_POST[$field]) : '';
            if ($field === 'cjd' && empty($value)) {
                $value = '非必要项';
            }
            $data[$field] = $value;
        }

        $required = ['cardz', 'cardf', 'exnumber', 'photo', 'address', 'school',
            'bkzy', 'zylb', 'years', 'xlzs', 'xwzs', 'xlzsphoto', 'xwzsphoto', 'sqb', 'cns', 'bgd'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $fieldNames = [
                    'cardz' => '身份证正面', 'cardf' => '身份证反面', 'exnumber' => '准考证号',
                    'photo' => '照片', 'address' => '邮寄地址', 'school' => '毕业院校',
                    'bkzy' => '本科专业', 'zylb' => '本科门类', 'years' => '毕业年份',
                    'xlzs' => '学历证书编号', 'xwzs' => '学位证书编号',
                    'xlzsphoto' => '学历证书图片', 'xwzsphoto' => '学位证书图片',
                    'sqb' => '报名申请表', 'cns' => '诚信承诺书', 'bgd' => '学历验证报告单'
                ];
                echo '请填写/上传 ' . ($fieldNames[$field] ?? $field);
                exit;
            }
        }

        $setParts = [];
        $params = [];
        $types = '';

        foreach ($fields as $field) {
            $setParts[] = "$field = ?";
            $params[] = $data[$field];
            $types .= 's';
        }
        $setParts[] = "id = ?";
        $params[] = $uid;
        $types .= 'i';

        $sql = "UPDATE user SET " . implode(', ', $setParts) . " WHERE id = ?";
        $setParts = [];
        $params = [];
        $types = '';
        foreach ($fields as $field) {
            $setParts[] = "$field = ?";
            $params[] = $data[$field];
            $types .= 's';
        }
        $sql = "UPDATE user SET " . implode(', ', $setParts) . " WHERE id = ?";
        $params[] = $uid;
        $types .= 'i';

        $stmt = $link->prepare($sql);
        if (!$stmt) {
            error_log("my update prepare failed: " . $link->error);
            echo '保存失败，请稍后重试';
            exit;
        }

        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            echo '<div style="margin-top: 36px;">保存成功！</div>';
        } else {
            error_log("my update execute failed: " . $stmt->error);
            echo '保存失败';
        }
        $stmt->close();
        break;

    case 'password':
        validateCsrfToken();

        $newPassword = $_POST['password'] ?? '';
        if (empty($newPassword)) {
            echo '请输入新密码';
            exit;
        }
        if (strlen($newPassword) < 6) {
            echo '密码长度至少6位';
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $link->prepare("UPDATE user SET password = ? WHERE id = ?");
        if (!$stmt) {
            error_log("password update prepare failed: " . $link->error);
            echo '修改失败，请稍后重试';
            exit;
        }
        $stmt->bind_param("si", $hashedPassword, $uid);
        if ($stmt->execute()) {
            echo '修改成功！';
        } else {
            error_log("password update execute failed: " . $stmt->error);
            echo '修改失败！';
        }
        $stmt->close();
        break;

    case 'select':
        validateCsrfToken();

        $speciality = isset($_POST['speciality']) ? trim($_POST['speciality']) : '';
        if (empty($speciality)) {
            echo '专业不能为空';
            exit;
        }

        $stmt = $link->prepare("UPDATE user SET speciality = ?, first = 0, second = 0 WHERE id = ?");
        if (!$stmt) {
            error_log("select update prepare failed: " . $link->error);
            echo '提交失败，请稍后重试';
            exit;
        }
        $stmt->bind_param("si", $speciality, $uid);
        if ($stmt->execute()) {
            echo '提交成功';
        } else {
            error_log("select update execute failed: " . $stmt->error);
            echo '提交失败';
        }
        $stmt->close();
        break;

    default:
        http_response_code(400);
        echo '无效的操作';
        break;
}