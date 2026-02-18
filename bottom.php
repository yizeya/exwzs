<?php
// 确保 $web_info_row 变量存在且为数组，并提取需要输出的值，设置默认空字符串
if (file_exists('sql.php')) {
    // 安全获取 webname 和 icp，若不存在则使用空字符串
    $webname = isset($web_info_row['webname']) ? htmlspecialchars($web_info_row['webname'], ENT_QUOTES, 'UTF-8') : '';
    $icp     = isset($web_info_row['icp'])     ? htmlspecialchars($web_info_row['icp'], ENT_QUOTES, 'UTF-8') : '';
?>
<footer style="background-color: #222; color: #fff; padding: 20px 0;">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-left">
                <p style="margin-bottom: 5px;">版权所有 &copy; <?php echo $webname; ?></p>
                <?php if (!empty($icp)): ?>
                <p style="margin-bottom: 5px;">
                    <a href="https://beian.miit.gov.cn" target="_blank" style="color: white; text-decoration: none;">
                        <?php echo $icp; ?>
                    </a>
                </p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-right">
                <p style="margin-bottom: 0;">
                    本站由 <a href="https://gitee.com/greentox/exwzs" target="_blank" style="color: white; text-decoration: none;">Gti本科第二学士学位招生报名系统</a> 提供
                </p>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
<?php
}
?>