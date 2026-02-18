<?php
if (!isset($link) || !$link) {
    error_log("mlzyl.php: 数据库连接未定义");
    echo '<select name="zylb" class="form-control"><option value="0">系统错误，请稍后重试</option></select>';
    return;
}

$sql = "SELECT mlzyl FROM mlzyl ORDER BY id ASC";
$result = mysqli_query($link, $sql);

if (!$result) {
    error_log("mlzyl.php 查询失败: " . mysqli_error($link));
    echo '<select name="zylb" class="form-control"><option value="0">数据加载失败</option></select>';
    return;
}
?>
<select name="zylb" class="form-control">
    <option value="0">请正确选择门类、专业类</option>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php 
        $value = htmlspecialchars($row['mlzyl'], ENT_QUOTES, 'UTF-8'); 
        ?>
        <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
    <?php endwhile; ?>
</select>
<?php
mysqli_free_result($result);
?>