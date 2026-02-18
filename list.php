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
    // 伪静态模式
    if (is_null($page) || $page <= 1) {
        return 'list.htm';  // 首页
    }
    return "list-{$page}.htm";
}
// ==================================================

// ================= 安全分页函数 =================
// 获取并验证页码
$per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// 使用预处理语句获取总文章数
$count_sql = "SELECT COUNT(*) as total FROM article";
$count_stmt = $link->prepare($count_sql);
if (!$count_stmt) {
    error_log("列表页总数查询失败: " . $link->error);
    die('系统错误，请稍后重试。');
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_row = $count_result->fetch_assoc();
$total_articles = (int)$total_row['total'];
$count_stmt->close();

// 计算总页数和偏移量
$total_pages = ceil($total_articles / $per_page);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

// 使用预处理语句获取当前页文章（LIMIT 参数需绑定整数）
$info_sql = "SELECT id, title, time FROM article ORDER BY id DESC LIMIT ?, ?";
$info_stmt = $link->prepare($info_sql);
if (!$info_stmt) {
    error_log("列表页文章查询失败: " . $link->error);
    die('系统错误，请稍后重试。');
}
$info_stmt->bind_param("ii", $offset, $per_page);
$info_stmt->execute();
$info_result = $info_stmt->get_result();
?>
<div class="container" style="margin-top: 20px; margin-bottom: 20px;">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <!-- 面包屑导航 -->
            <ol class="breadcrumb" style="margin-bottom: 20px;">
                <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> 首页</a></li>
                <li class="active">通知公告</li>
            </ol>

            <!-- 公告面板 -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-8">
                            <span class="glyphicon glyphicon-bullhorn" style="margin-right: 8px;"></span>
                            <strong>通知公告</strong>
                        </div>
                        <div class="col-xs-4 text-right">
                            <span class="badge">共 <?php echo htmlspecialchars($total_articles, ENT_QUOTES, 'UTF-8'); ?> 条</span>
                        </div>
                    </div>
                </div>
                
                <div class="panel-body" style="padding: 0;">
                    <!-- 表头 -->
                    <div class="row" style="margin: 0; padding: 8px 15px; background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                        <div class="col-md-8 col-xs-6">
                            <strong>新闻标题</strong>
                        </div>
                        <div class="col-md-4 col-xs-6 text-right">
                            <strong>发布时间</strong>
                        </div>
                    </div>
                    
                    <!-- 文章列表 -->
                    <?php if ($info_result->num_rows > 0): ?>
                        <?php while ($info_row = $info_result->fetch_assoc()): 
                            $title = htmlspecialchars($info_row['title'], ENT_QUOTES, 'UTF-8');
                            $shortTitle = (mb_strlen($title, 'UTF-8') > 40) 
                                ? mb_substr($title, 0, 40, 'UTF-8') . '…' 
                                : $title;
                            $date = date("Y-m-d", $info_row['time']);
                            $id = intval($info_row['id']);
                        ?>
                            <div class="row" style="margin: 0; padding: 12px 15px; border-bottom: 1px solid #eee;">
                                <div class="col-md-8 col-xs-6">
                                    <a href="<?php echo article_url($id); ?>" style="color: #333;">
                                        <span class="glyphicon glyphicon-file" style="margin-right: 8px; color: #337ab7;"></span>
                                        <span title="<?php echo $title; ?>">
                                            <?php echo $shortTitle; ?>
                                        </span>
                                    </a>
                                </div>
                                <div class="col-md-4 col-xs-6 text-right">
                                    <small class="text-muted">
                                        <span class="glyphicon glyphicon-calendar" style="margin-right: 4px;"></span>
                                        <?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php $info_stmt->close(); ?>
                    <?php else: ?>
                        <div class="text-center" style="padding: 50px;">
                            <span class="glyphicon glyphicon-inbox" style="font-size: 48px; color: #ccc;"></span>
                            <p class="text-muted" style="margin-top: 10px;">暂无通知公告</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 分页 -->
            <?php if ($total_pages > 1): ?>
            <div class="row" style="margin-top: 20px;">
                <div class="col-xs-12">
                    <div class="pull-left" style="line-height: 34px;">
                        <small class="text-muted">
                            显示 <?php echo htmlspecialchars($offset + 1, ENT_QUOTES, 'UTF-8'); ?> - 
                            <?php echo htmlspecialchars(min($offset + $per_page, $total_articles), ENT_QUOTES, 'UTF-8'); ?> 条，
                            共 <?php echo htmlspecialchars($total_articles, ENT_QUOTES, 'UTF-8'); ?> 条
                        </small>
                    </div>
                    
                    <nav class="pull-right">
                        <ul class="pagination" style="margin: 0;">
                            <!-- 上一页 -->
                            <li class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo list_url($page - 1); ?>">
                                        <span class="glyphicon glyphicon-chevron-left"></span>
                                    </a>
                                <?php else: ?>
                                    <span><span class="glyphicon glyphicon-chevron-left"></span></span>
                                <?php endif; ?>
                            </li>
                            
                            <?php
                            // 计算分页范围
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            if ($end_page - $start_page < 4) {
                                $start_page = max(1, $end_page - 4);
                            }
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a href="<?php echo list_url($i); ?>">
                                        <?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- 下一页 -->
                            <li class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <?php if ($page < $total_pages): ?>
                                    <a href="<?php echo list_url($page + 1); ?>">
                                        <span class="glyphicon glyphicon-chevron-right"></span>
                                    </a>
                                <?php else: ?>
                                    <span><span class="glyphicon glyphicon-chevron-right"></span></span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </nav>
                    
                    <div class="clearfix"></div>
                    
                    <!-- 跳转（跳转后仍使用伪静态格式） -->
                    <div class="text-center" style="margin-top: 10px;">
                        <span class="text-muted">跳转到</span>
                        <div class="input-group" style="display: inline-block; width: 100px; margin-left: 5px;">
                            <input type="number" id="jumpPage" class="form-control input-sm" 
                                   min="1" max="<?php echo htmlspecialchars($total_pages, ENT_QUOTES, 'UTF-8'); ?>" 
                                   value="<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" 
                                   style="width: 70px; display: inline-block;">
                            <span class="input-group-btn" style="display: inline-block;">
                                <button class="btn btn-primary btn-sm" type="button" onclick="jumpToPage()">
                                    <span class="glyphicon glyphicon-arrow-right"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<script>
function jumpToPage() {
    var pageInput = document.getElementById('jumpPage');
    var page = parseInt(pageInput.value);
    var totalPages = <?php echo json_encode($total_pages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    
    if(isNaN(page) || page < 1) page = 1;
    if(page > totalPages) page = totalPages;
    
    // 根据伪静态开关状态跳转
    <?php if ($static_enabled): ?>
        if (page === 1) {
            window.location.href = 'list.htm';
        } else {
            window.location.href = 'list-' + page + '.htm';
        }
    <?php else: ?>
        window.location.href = 'list.php?page=' + encodeURIComponent(page);
    <?php endif; ?>
}

// 回车跳转
document.getElementById('jumpPage').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        jumpToPage();
    }
});
</script>

<?php include('bottom.php'); ?>