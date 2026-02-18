<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php");
    exit();
}

include('top.php');
include('../sql.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: article.php");
    exit();
}

$sql = "SELECT id, title, text FROM article WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("edit_article.php: 预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: article.php");
    exit();
}
$article_row = $result->fetch_assoc();
$stmt->close();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<div class="container-fluid" style="margin-top:20px;">
    <div class="row">
        <!-- 侧边导航栏 -->
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>

        <!-- 主内容区域 -->
        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <span class="glyphicon glyphicon-edit"></span> 修改文章
                    </h5>
                </div>

                <div class="panel-body">
                    <form id="admin_article" method="post">
                        <!-- CSRF 令牌 -->
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                        <!-- 文章 ID -->
                        <input type="hidden" name="id" value="<?php echo intval($article_row['id']); ?>">

                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">
                                        <span class="glyphicon glyphicon-tag"></span> 文章ID
                                    </label>
                                    <div class="form-control-static bg-info" style="padding:8px; background-color:#f5f5f5; border-radius:4px;">
                                        #<?php echo intval($article_row['id']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="control-label">
                                        <span class="glyphicon glyphicon-header"></span> 文章标题
                                    </label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo e($article_row['title']); ?>" required maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label">
                                <span class="glyphicon glyphicon-edit"></span> 文章内容
                            </label>
                            <textarea name="text" id="editor_id" class="form-control"
                                style="height:400px;"><?php echo $article_row['text']; ?></textarea>
                        </div>

                        <div class="clearfix">
                            <a href="article.php" class="btn btn-default pull-left">
                                <span class="glyphicon glyphicon-arrow-left"></span> 返回列表
                            </a>
                            <button type="submit" class="btn btn-primary pull-right">
                                <span class="glyphicon glyphicon-ok"></span> 保存修改
                            </button>
                        </div>
                    </form>

                    <div id="ArticleResult" style="margin-top:15px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="/kindeditor/kindeditor-all-min.js"></script>
<link rel="stylesheet" href="/kindeditor/themes/default/default.css" />

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<style>
    .glyphicon-spin {
        -webkit-animation: spin 2s infinite linear;
        animation: spin 2s infinite linear;
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(359deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(359deg);
        }
    }
</style>

<script type="text/javascript">
    var editor;

    KindEditor.ready(function(K) {
        editor = K.create('textarea[name="text"]', {
            width: '100%',
            height: '400px',
            resizeType: 1,
            items: [
                'anchor',
                'bold',
                'clearhtml',
                'copy',
                'cut',
                'flash',
                'fontname',
                'fontsize',
                'forecolor',
                'fullscreen',
                'hilitecolor',
                'hr',
                'image',
                'indent',
                'insertorderedlist',
                'insertunorderedlist',
                'italic',
                'justifycenter',
                'justifyfull',
                'justifyleft',
                'justifyright',
                'lineheight',
                'link',
                'media',
                'outdent',
                'pagebreak',
                'paste',
                'plainpaste',
                'preview',
                'print',
                'quickformat',
                'redo',
                'removeformat',
                'selectall',
                'source',
                'strikethrough',
                'subscript',
                'superscript',
                'table',
                'underline',
                'undo',
                'unlink',
                'wordpaste'
            ]
        });
    });

    $(document).ready(function() {
        $('#admin_article').on('submit', function(e) {
            e.preventDefault();

            $('#ArticleResult').html(
                '<div class="alert alert-info alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在保存修改...</strong> 请稍候' +
                '</div>'
            );

            if (editor) {
                editor.sync();
            }

            var formData = $(this).serialize();

            $.ajax({
                url: 'control.php?action=updatearticle',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#ArticleResult').html(
                            '<div class="alert alert-success alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-ok-sign"></span> <strong>修改成功!</strong> ' + data.message +
                            '</div>'
                        );
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#ArticleResult').html(
                            '<div class="alert alert-danger alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>修改失败!</strong> ' + data.message +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    $('#ArticleResult').html(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<span class="glyphicon glyphicon-remove-sign"></span> <strong>请求失败!</strong> 请稍后再试或联系管理员。' +
                        '</div>'
                    );
                }
            });
        });
    });
</script>

<?php include('bottom.php'); ?>