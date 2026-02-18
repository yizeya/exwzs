<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: /");
    exit();
}

include('top.php');
include('info.php');
include('../sql.php'); // 引入数据库连接

$uid = intval($_SESSION['id']); // 确保整数

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 获取当前用户所有资料字段（用于显示已上传文件）
$sql = "SELECT photo, cardz, cardf, sqb, cns, cjd, bgd, xlzsphoto, xwzsphoto FROM user WHERE id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$user_files = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 定义资料项配置
$file_fields = [
    'photo'      => ['name' => '照片',              'icon' => 'glyphicon-picture'],
    'cardz'      => ['name' => '身份证正面',        'icon' => 'glyphicon-credit-card'],
    'cardf'      => ['name' => '身份证反面',        'icon' => 'glyphicon-credit-card'],
    'sqb'        => ['name' => '考生申请表',        'icon' => 'glyphicon-file'],
    'cns'        => ['name' => '考生承诺书',        'icon' => 'glyphicon-check'],
    'cjd'        => ['name' => '本科阶段成绩单',    'icon' => 'glyphicon-list-alt'],
    'bgd'        => ['name' => '学历验证报告单',    'icon' => 'glyphicon-check'],
    'xlzsphoto'  => ['name' => '学历证书',          'icon' => 'glyphicon-picture'],
    'xwzsphoto'  => ['name' => '学位证书',          'icon' => 'glyphicon-picture']
];

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <!-- 资料上传卡片 -->
    <div class="panel panel-default" style="margin-bottom:20px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <div class="media">
                <div class="media-left">
                    <span class="glyphicon glyphicon-cloud-upload" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                </div>
                <div class="media-body">
                    <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">资料上传</h5>
                </div>
            </div>
        </div>
        
        <div class="panel-body">
            <!-- 各个资料项独立上传框 -->
            <div class="row" style="margin-bottom:20px;">
                <?php foreach ($file_fields as $field => $info): 
                    $current_file = $user_files[$field] ?? '';
                    $has_file = !empty($current_file);
                ?>
                <div class="col-md-6" style="margin-bottom:30px;">
                    <div class="panel panel-default" style="border:1px solid #ddd; margin-bottom:0;">
                        <div class="panel-heading" style="background:#f9f9f9;">
                            <h6 class="panel-title" style="font-weight:bold;">
                                <span class="glyphicon <?php echo $info['icon']; ?>" style="margin-right:5px;"></span>
                                <?php echo $info['name']; ?>
                            </h6>
                        </div>
                        <div class="panel-body">
                            <!-- 显示当前文件（如果有） -->
                            <?php if ($has_file): ?>
                            <div style="margin-bottom:15px; padding:8px; background:#f5f5f5; border-radius:4px;">
                                <span class="glyphicon glyphicon-file" style="margin-right:5px;"></span>
                                <a href="<?php echo e($current_file); ?>" target="_blank" rel="noopener">
                                    <?php echo e(basename($current_file)); ?>
                                </a>
                                <span class="label label-info" style="margin-left:8px;">已上传</span>
                            </div>
                            <?php else: ?>
                            <div class="text-muted" style="margin-bottom:15px; padding:8px; background:#fafafa; border-radius:4px; border:1px dashed #ccc;">
                                <span class="glyphicon glyphicon-ban-circle"></span> 未上传
                            </div>
                            <?php endif; ?>
                            
                            <!-- 上传表单 -->
                            <form class="upload-form" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="type" value="<?php echo $field; ?>">
                                
                                <div class="input-group">
                                    <input type="file" name="imageUpload" class="form-control" required accept=".jpg,.jpeg,.png,.pdf">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-upload"></span> 上传
                                        </button>
                                    </span>
                                </div>
                                <span class="help-block small text-muted">
                                    支持 JPG/PNG/PDF，不超过2MB
                                </span>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 已上传文件列表（原有表格） -->
            <div>
                <h6 style="font-weight:bold; margin-bottom:15px;">
                    <span class="glyphicon glyphicon-list-alt" style="margin-right:5px;"></span>已上传文件列表
                </h6>
                
                <?php
                // 查询上传记录
                $sql = "SELECT * FROM upload WHERE uid = ? ORDER BY time DESC";
                $stmt = $link->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $uid);
                    $stmt->execute();
                    $upload_result = $stmt->get_result();
                } else {
                    error_log("upload.php: 查询预处理失败 - " . $link->error);
                    $upload_result = false;
                }
                
                if ($upload_result && $upload_result->num_rows > 0):
                    $i = 1;
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-condensed">
                        <thead>
                            <tr>
                                <th class="text-center">序号</th>
                                <th>文件名</th>
                                <th>文件类型</th>
                                <th>上传时间</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($upload_row = $upload_result->fetch_assoc()): 
                                $file_path = e($upload_row['files']);
                                $file_name = e(basename($upload_row['files']));
                                $file_id = intval($upload_row['id']);
                                $file_type = e($upload_row['type']);
                                $upload_time = date("Y-m-d H:i:s", $upload_row['time']);
                                
                                $typeNames = [
                                    'photo' => '照片', 'cardz' => '身份证正面', 'cardf' => '身份证反面',
                                    'sqb' => '申请表', 'cns' => '承诺书', 'cjd' => '本科阶段成绩单',
                                    'bgd' => '学历验证报告单', 'xlzsphoto' => '学历证书', 'xwzsphoto' => '学位证书'
                                ];
                                $display_type = $typeNames[$upload_row['type']] ?? '未知类型';
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td>
                                    <a href="<?php echo $file_path; ?>" target="_blank" rel="noopener">
                                        <span class="glyphicon glyphicon-file"></span> <?php echo $file_name; ?>
                                    </a>
                                </td>
                                <td><?php echo e($display_type); ?></td>
                                <td><?php echo $upload_time; ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $file_id; ?>, '<?php echo addslashes($file_path); ?>')">
                                        <span class="glyphicon glyphicon-trash"></span> 删除
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php $stmt->close(); ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center" style="padding:20px;">
                    <span class="glyphicon glyphicon-inbox" style="font-size:3em; color:#ccc;"></span>
                    <p class="text-muted" style="margin-top:10px;">暂无上传文件</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 上传说明（不变） -->
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <h6 class="panel-title" style="font-weight:bold;">
                <span class="glyphicon glyphicon-info-sign"></span> 上传说明
            </h6>
        </div>
        <div class="panel-body">
            <div class="alert alert-info" style="margin-bottom:15px;">
                <div class="media">
                    <div class="media-left">
                        <span class="glyphicon glyphicon-exclamation-sign" style="font-size:20px;"></span>
                    </div>
                    <div class="media-body">
                        <p style="margin-bottom:5px;">请考生仔细阅读上传规则：</p>
                        <ul style="margin-bottom:0;">
                            <li>每个资料项均有独立的上传按钮，请分别上传对应的文件。</li>
                            <li>请将扫描（拍照）的文件名称改为英文或数字。</li>
                            <li>上传完成后，对应项会显示已上传的文件名，点击可预览。</li>
                            <li>如需修改，请删除原先的文件（在下表中操作）后重新上传。</li>
                            <li>请勿上传大于2MB且非JPG、PNG、PDF类型的文件。</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning">
                <div class="media">
                    <div class="media-left">
                        <span class="glyphicon glyphicon-lamp" style="font-size:20px;"></span>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading" style="font-weight:bold;">注意事项</h6>
                        <p style="margin:0;">
                            正常情况下，应有您的照片、身份证正面与反面、考生申请表、考生承诺书、学历验证报告单、学历证书（毕业证书）、学位证书共8张图片（如果需要本科阶段成绩单则为9张）。图片上传功能不限上传次数，如果您需要修改某张照片可删除原先的资料。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 上传结果模态框（不变） -->
<div class="modal fade" id="uploadResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9edf7; color:#31708f;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-cloud-upload"></span> 上传结果
                </h4>
            </div>
            <div class="modal-body">
                <div id="imageUrl" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="location.reload();">
                    <span class="glyphicon glyphicon-ok"></span> 确定
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框（不变） -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#fcf8e3; color:#8a6d3b;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 确认删除
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-center">您确定要删除这个文件吗？此操作不可撤销。</p>
                <input type="hidden" id="deleteId">
                <input type="hidden" id="deleteFile">
                <input type="hidden" id="deleteCsrf" value="<?php echo e($csrf_token); ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" onclick="performDelete()">
                    <span class="glyphicon glyphicon-trash"></span> 确认删除
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 引入 jQuery 和 Bootstrap 3 JS -->
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<style>
.glyphicon-spin {
    -webkit-animation: spin 2s infinite linear;
    animation: spin 2s infinite linear;
}
@-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(359deg); }
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(359deg); }
}
</style>

<script type="text/javascript">
    // 删除确认函数
    function confirmDelete(id, file) {
        $('#deleteId').val(id);
        $('#deleteFile').val(file);
        $('#deleteConfirmModal').modal('show');
    }
    
    function performDelete() {
        var id = $('#deleteId').val();
        var file = $('#deleteFile').val();
        var csrf = $('#deleteCsrf').val();
        
        $.ajax({
            url: 'update.php?action=delfiles',
            type: 'POST',
            data: {
                id: id,
                files: file,
                csrf_token: csrf
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('删除失败，请稍后再试。');
                console.error('删除请求失败:', error);
            }
        });
    }
    
    // 文件上传处理：为所有 .upload-form 绑定 submit 事件，使用 AJAX 提交
    $(document).ready(function(){
        $('.upload-form').on('submit', function(e){
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(form[0]);
            var button = form.find('button[type="submit"]');
            var originalText = button.html();
            
            // 前端检查文件大小和类型
            var fileInput = form.find('input[name="imageUpload"]')[0];
            if (!fileInput.files[0]) {
                alert('请选择要上传的文件');
                return;
            }
            var file = fileInput.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('文件大小不能超过2MB');
                return;
            }
            var allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (allowedTypes.indexOf(file.type) === -1 && !file.name.match(/\.(jpg|jpeg|png|pdf)$/i)) {
                alert('只允许上传JPG、PNG或PDF格式的文件');
                return;
            }
            
            // 显示上传中状态
            button.html('<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> 上传中...');
            button.prop('disabled', true);
            
            $.ajax({
                url: 'update.php?action=upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    button.html(originalText);
                    button.prop('disabled', false);
                    $('#imageUrl').html(response);
                    $('#uploadResultModal').modal('show');
                },
                error: function(xhr, status, error) {
                    button.html(originalText);
                    button.prop('disabled', false);
                    $('#imageUrl').html('<div class="text-danger"><span class="glyphicon glyphicon-remove-sign" style="font-size:48px;"></span><p>上传失败，请稍后再试</p></div>');
                    $('#uploadResultModal').modal('show');
                    console.error('上传请求失败:', error);
                }
            });
        });
    });
</script>

<?php include('bottom.php'); ?>