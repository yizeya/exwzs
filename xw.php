<?php
// 确保数据库连接存在（此文件通常被其他页面包含，$link 应已定义）
if (!isset($link) || !$link) {
    error_log("xw_select.php: 数据库连接未定义");
    echo '<select name="xw" class="form-control"><option value="0">系统错误，请稍后重试</option></select>';
    return;
}

// 使用预处理语句查询学位授予门类数据
$sql = "SELECT xw FROM xw ORDER BY id ASC"; // 假设有 id 字段用于排序，若无则去掉 ORDER BY
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("xw_select.php: 预处理失败 - " . $link->error);
    echo '<select name="xw" class="form-control"><option value="0">数据加载失败</option></select>';
    return;
}

$stmt->execute();
$result = $stmt->get_result();
?>
<select name="xw" class="form-control">
    <option value="0">请选择学位授予门类</option>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php 
        // 对输出内容进行 HTML 转义，防止 XSS 攻击
        $value = htmlspecialchars($row['xw'], ENT_QUOTES, 'UTF-8'); 
        ?>
        <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
    <?php endwhile; ?>
</select>
<?php
// 释放资源
$stmt->close();
$result->free();
?>