<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: login.php"); // 未登录跳转到管理员登录页
    exit();
}

include('top.php');
include('../sql.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container-fluid" style="margin-top:20px;">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <?php include('nav.php'); ?>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <span class="glyphicon glyphicon-file"></span> 发布新文章
                    </h5>
                </div>

                <div class="panel-body">
                    <form id="admin_article">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-group">
                            <label for="title" class="control-label">
                                <span class="glyphicon glyphicon-header"></span> 文章标题
                            </label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="请输入文章标题" required style="font-size:16px; height:45px;">
                        </div>

                        <div class="form-group" style="margin-top:20px;">
                            <label for="editor_id" class="control-label">
                                <span class="glyphicon glyphicon-edit"></span> 文章内容
                            </label>
                            <textarea name="text" id="editor_id" class="form-control"
                                placeholder="请输入文章内容"></textarea>
                        </div>

                        <div class="form-group" style="margin-top:20px;">
                            <button type="submit" class="btn btn-primary btn-lg btn-block" style="padding:12px;">
                                <span class="glyphicon glyphicon-send"></span> 发布文章
                            </button>
                        </div>
                    </form>

                    <div id="ArticleResult" style="margin-top:15px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<script type="text/javascript" src="../kindeditor/kindeditor-all-min.js"></script>
<link rel="stylesheet" href="../kindeditor/themes/default/default.css" />

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

<script>
    var editor;

    KindEditor.ready(function(K) {
        editor = K.create('textarea[name="text"]', {
            width: '100%',
            height: '300px',
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
                '<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <strong>正在发布文章...</strong> 请稍候' +
                '</div>'
            );

            if (editor) {
                editor.sync();
            }

            var title = $('#title').val().trim();
            var content = $('#editor_id').val().trim();

            if (!title) {
                $('#ArticleResult').html(
                    '<div class="alert alert-warning alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>请填写文章标题!</strong>' +
                    '</div>'
                );
                return false;
            }

            if (!content) {
                $('#ArticleResult').html(
                    '<div class="alert alert-warning alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>请填写文章内容!</strong>' +
                    '</div>'
                );
                return false;
            }

            var formData = new FormData();
            formData.append('title', title);
            formData.append('text', content);
            var csrfToken = $('input[name="csrf_token"]').val();
            formData.append('csrf_token', csrfToken);
            console.log('提交数据:', {
                title: title,
                content_length: content.length
            });

            $.ajax({
                url: 'control.php?action=article',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    console.log('服务器响应:', data);
                    if (data.success) {
                        $('#ArticleResult').html(
                            '<div class="alert alert-success alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-ok-sign"></span> <strong>发表成功!</strong> ' + data.message +
                            '</div>'
                        );

                        setTimeout(function() {
                            window.location.href = "article_col.php";
                        }, 1000);
                    } else {
                        $('#ArticleResult').html(
                            '<div class="alert alert-danger alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>发表失败!</strong> ' + data.message +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.log('请求错误:', error);
                    console.log('状态:', status);
                    console.log('响应:', xhr.responseText);
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