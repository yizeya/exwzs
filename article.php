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

// 根据开关生成列表页链接
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

// ================= 安全过滤函数 =================
function sanitize_html($dirty_html) {
    // 优先使用 HTML Purifier（推荐）
    if (class_exists('HTMLPurifier')) {
        $config = HTMLPurifier_Config::createDefault();
        // 配置允许的标签和属性，可根据需要调整
        $config->set('HTML.Allowed', 
            'p,br,strong,em,u,ins,del,h1,h2,h3,h4,h5,h6,blockquote,ul,ol,li,' .
            'a[href|target|title],img[src|alt|title|width|height],' .
            'span[style],div[style]'
        );
        $config->set('CSS.AllowedProperties', 
            'font,font-size,font-weight,font-style,font-family,text-decoration,' .
            'color,background-color,text-align,margin,padding'
        );
        $config->set('HTML.TargetBlank', true); // 为链接添加 target="_blank"
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($dirty_html);
    }

    // 备用方案：strip_tags + 移除危险属性（降级使用，无法保证100%安全）
    $allowed_tags = '<p><br><strong><em><u><ins><del><h1><h2><h3><h4><h5><h6><blockquote><ul><ol><li><a><img><span><div>';
    $html = strip_tags($dirty_html, $allowed_tags);

    // 移除所有 on* 事件属性和 javascript: 协议
    $html = preg_replace_callback('/<([^>]+)>/', function($matches) {
        $tag = $matches[1];
        // 移除 on\w+ 属性
        $tag = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $tag);
        // 移除 href/src 中的 javascript: 协议
        $tag = preg_replace('/\b(href|src)\s*=\s*"(javascript|vbscript):[^"]*"/i', '', $tag);
        $tag = preg_replace("/\b(href|src)\s*=\s*'(javascript|vbscript):[^']*'/i", '', $tag);
        // 移除其他属性中的 javascript:
        $tag = preg_replace('/javascript:[^\s"\']*/i', '', $tag);
        return '<' . $tag . '>';
    }, $html);

    return $html;
}
// ==================================================

// 获取并验证ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

// 使用预处理语句查询主文章
$sql = "SELECT * FROM article WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("Database error: " . $link->error);
    header("Location: list.php");
    exit();
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    $stmt->close();
    header("Location: list.php");
    exit();
}
$stmt->close();
?>

<div class="container" style="margin-bottom: 30px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <!-- 面包屑导航 -->
            <ol class="breadcrumb" style="margin-bottom: 20px;">
                <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> 首页</a></li>
                <li><a href="<?php echo list_url(); ?>"><span class="glyphicon glyphicon-list"></span> 通知公告</a></li>
                <li class="active">文章详情</li>
            </ol>

            <!-- 文章面板 -->
            <div class="panel panel-default" style="margin-bottom: 30px;">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="glyphicon glyphicon-file"></span> 
                        <?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                </div>
                
                <div class="panel-body">
                    <!-- 文章信息 -->
                    <div class="row" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                        <div class="col-xs-6">
                            <span class="label label-default">
                                <span class="glyphicon glyphicon-calendar"></span> 发布时间：<?php echo date("Y-m-d H:i:s", $row['time']); ?>
                            </span>
                        </div>
                        <div class="col-xs-6 text-right">
                            <small class="text-muted">
                                <span class="glyphicon glyphicon-time"></span> ID：<?php echo $row['id']; ?>
                            </small>
                        </div>
                    </div>

                    <!-- 文章正文（富文本） -->
                    <div class="article-content" style="line-height: 1.8; font-size: 16px; padding: 0 5px;">
                        <?php 
                        // 对正文进行安全过滤，允许富文本输出
                        $safe_content = sanitize_html($row['text']);
                        echo $safe_content;
                        ?>
                    </div>
                    
                    <!-- 附件区域 -->
                    <?php if (!empty($row['attachment'])): 
                        // 对附件链接进行安全过滤
                        $attachment = trim($row['attachment']);
                        $safe_attachment = '';
                        
                        // 判断是否为绝对路径（包含协议）
                        if (preg_match('/^(https?:)?\/\//i', $attachment)) {
                            // 只允许http/https协议
                            if (preg_match('/^https?:\/\//i', $attachment)) {
                                $safe_attachment = htmlspecialchars($attachment, ENT_QUOTES, 'UTF-8');
                            }
                        } else {
                            // 相对路径（站内文件）直接转义输出
                            $safe_attachment = htmlspecialchars($attachment, ENT_QUOTES, 'UTF-8');
                        }
                    ?>
                        <?php if ($safe_attachment): ?>
                        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                            <h5><span class="glyphicon glyphicon-paperclip"></span> 附件下载</h5>
                            <div class="list-group">
                                <a href="<?php echo $safe_attachment; ?>" class="list-group-item">
                                    <span class="glyphicon glyphicon-file"></span> 相关附件
                                    <span class="badge">下载</span>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="panel-footer">
                    <div class="row">
                        <!-- 上一篇 -->
                        <div class="col-xs-6">
                            <?php 
                            // 使用预处理语句查询上一篇
                            $prev_sql = "SELECT id, title FROM article WHERE id < ? ORDER BY id DESC LIMIT 1";
                            $prev_stmt = $link->prepare($prev_sql);
                            if ($prev_stmt) {
                                $prev_stmt->bind_param("i", $id);
                                $prev_stmt->execute();
                                $prev_result = $prev_stmt->get_result();
                                $prev_row = $prev_result->fetch_assoc();
                                $prev_stmt->close();
                            } else {
                                $prev_row = null;
                                error_log("Database error: " . $link->error);
                            }
                            ?>
                            <?php if ($prev_row): ?>
                                <a href="<?php echo article_url($prev_row['id']); ?>" class="btn btn-link btn-sm" style="padding-left:0; text-align:left;">
                                    <span class="glyphicon glyphicon-chevron-left"></span> 上一篇
                                    <div class="small text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php 
                                        $prev_title = $prev_row['title'];
                                        if (mb_strlen($prev_title, 'UTF-8') > 20) {
                                            $prev_title = mb_substr($prev_title, 0, 20, 'UTF-8') . '...';
                                        }
                                        echo htmlspecialchars($prev_title, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </div>
                                </a>
                            <?php else: ?>
                                <span class="btn btn-link btn-sm disabled" style="padding-left:0;">
                                    <span class="glyphicon glyphicon-chevron-left"></span> 上一篇
                                    <div class="small text-muted">没有了</div>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 下一篇 -->
                        <div class="col-xs-6 text-right">
                            <?php 
                            // 使用预处理语句查询下一篇
                            $next_sql = "SELECT id, title FROM article WHERE id > ? ORDER BY id ASC LIMIT 1";
                            $next_stmt = $link->prepare($next_sql);
                            if ($next_stmt) {
                                $next_stmt->bind_param("i", $id);
                                $next_stmt->execute();
                                $next_result = $next_stmt->get_result();
                                $next_row = $next_result->fetch_assoc();
                                $next_stmt->close();
                            } else {
                                $next_row = null;
                                error_log("Database error: " . $link->error);
                            }
                            ?>
                            <?php if ($next_row): ?>
                                <a href="<?php echo article_url($next_row['id']); ?>" class="btn btn-link btn-sm" style="padding-right:0; text-align:right;">
                                    下一篇 <span class="glyphicon glyphicon-chevron-right"></span>
                                    <div class="small text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php 
                                        $next_title = $next_row['title'];
                                        if (mb_strlen($next_title, 'UTF-8') > 20) {
                                            $next_title = mb_substr($next_title, 0, 20, 'UTF-8') . '...';
                                        }
                                        echo htmlspecialchars($next_title, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </div>
                                </a>
                            <?php else: ?>
                                <span class="btn btn-link btn-sm disabled" style="padding-right:0;">
                                    下一篇 <span class="glyphicon glyphicon-chevron-right"></span>
                                    <div class="small text-muted">没有了</div>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="text-center" style="margin: 30px 0 20px;">
                <a href="<?php echo list_url(); ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-list"></span> 返回列表
                </a>
                <button class="btn btn-default" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                    <span class="glyphicon glyphicon-arrow-up"></span> 回到顶部
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('bottom.php'); ?>