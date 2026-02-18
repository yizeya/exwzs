<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['id'] <= 0) {
    header("Location: index.php");
    exit();
}

include('top.php');
include('info.php');

$user_id = intval($_SESSION['id']);

$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    error_log("my-home.php: 预处理失败 - " . $link->error);
    die('系统错误，请稍后重试。');
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    header("Location: index.php");
    exit();
}
$stmt->close();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container" style="padding-top:20px; padding-bottom:20px;">
    <div class="panel panel-default" style="margin-bottom:30px; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-body" style="padding:20px;">
            <div class="row text-center">
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">1</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">选择招生对象</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">已完成</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#337ab7; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">2</span>
                        <h6 style="font-weight:bold; color:#337ab7; margin:5px 0 0;">填写个人信息</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">当前步骤</p>
                    </div>
                </div>
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">3</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">选择专业</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">下一步</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <span class="badge" style="border-radius:50%; background-color:#ccc; color:white; width:50px; height:50px; font-size:24px; line-height:48px; padding:0; margin-bottom:8px;">4</span>
                        <h6 style="font-weight:bold; color:#777; margin:5px 0 0;">报名确认</h6>
                        <p class="small text-muted" style="margin:2px 0 0;">最后一步</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 个人信息表单 -->
    <div class="panel panel-default" style="border:none; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div class="panel-heading" style="background:#f5f5f5; border:none; padding:15px;">
            <div class="media">
                <div class="media-left">
                    <span class="glyphicon glyphicon-user" style="font-size:24px; background-color:#337ab7; color:white; padding:10px; border-radius:4px;"></span>
                </div>
                <div class="media-body">
                    <h5 class="media-heading" style="font-weight:bold; margin-top:8px;">填写个人信息</h5>
                </div>
            </div>
        </div>
        
        <div class="panel-body" style="padding:20px;">
            <form method="post" id="myForm" action="update.php?action=my">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-user" style="margin-right:5px;"></span>考生姓名
                        </label>
                        <div class="input-group">
                            <input type="text" name="name" readonly class="form-control" value="<?php echo isset($row['name']) ? htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">注册成功不可修改</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-credit-card" style="margin-right:5px;"></span>证件号码
                        </label>
                        <div class="input-group">
                            <input type="text" name="number" readonly class="form-control" value="<?php echo isset($row['number']) ? htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">注册成功不可修改</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-barcode" style="margin-right:5px;"></span>准考证号
                        </label>
                        <div class="input-group">
                            <input type="text" name="exnumber" readonly class="form-control" value="<?php echo isset($row['exnumber']) ? htmlspecialchars($row['exnumber'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">准考证号由注册时生成</span>
                        </div>
                    </div>
                </div>
                
                <!-- 上传资料提示 -->
                <div class="alert alert-info" style="margin-bottom:20px;">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-cloud-upload" style="font-size:24px;"></span>
                        </div>
                        <div class="media-body">
                            <h6 class="media-heading" style="font-weight:bold;">资料上传提示</h6>
                            <p style="margin:0;">请先<a href="upload.php" target="_blank" class="alert-link" style="font-weight:bold;">点击上传资料</a>，以下对话框将自动填写。</p>
                        </div>
                    </div>
                </div>
                
                <!-- 资料上传字段 -->
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-picture" style="margin-right:5px;"></span>上传照片
                        </label>
                        <div class="input-group">
                            <input type="text" name="photo" class="form-control" value="<?php echo !empty($row['photo']) ? htmlspecialchars($row['photo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">白底或蓝底，不少于2M的jpg/png</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-credit-card" style="margin-right:5px;"></span>身份证正面
                        </label>
                        <div class="input-group">
                            <input type="text" name="cardz" class="form-control" value="<?php echo !empty($row['cardz']) ? htmlspecialchars($row['cardz'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描身份证正面</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-credit-card" style="margin-right:5px;"></span>身份证反面
                        </label>
                        <div class="input-group">
                            <input type="text" name="cardf" class="form-control" value="<?php echo !empty($row['cardf']) ? htmlspecialchars($row['cardf'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描身份证反面</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-file" style="margin-right:5px;"></span>报名申请表
                        </label>
                        <div class="input-group">
                            <input type="text" name="sqb" class="form-control" value="<?php echo !empty($row['sqb']) ? htmlspecialchars($row['sqb'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描报名申请表</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-check" style="margin-right:5px;"></span>考生诚信承诺书
                        </label>
                        <div class="input-group">
                            <input type="text" name="cns" class="form-control" value="<?php echo !empty($row['cns']) ? htmlspecialchars($row['cns'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描考生诚信承诺书</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-list-alt" style="margin-right:5px;"></span>学历验证报告单
                        </label>
                        <div class="input-group">
                            <input type="text" name="bgd" class="form-control" value="<?php echo !empty($row['bgd']) ? htmlspecialchars($row['bgd'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描学历验证报告单</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-list" style="margin-right:5px;"></span>本科阶段成绩单
                        </label>
                        <div class="input-group">
                            <input type="text" name="cjd" class="form-control" value="<?php echo !empty($row['cjd']) ? htmlspecialchars($row['cjd'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">不需要可留空</span>
                        </div>
                    </div>
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-picture" style="margin-right:5px;"></span>毕业证书图片
                        </label>
                        <div class="input-group">
                            <input type="text" name="xlzsphoto" class="form-control" value="<?php echo !empty($row['xlzsphoto']) ? htmlspecialchars($row['xlzsphoto'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描毕业证书</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-picture" style="margin-right:5px;"></span>学位证书图片
                        </label>
                        <div class="input-group">
                            <input type="text" name="xwzsphoto" class="form-control" value="<?php echo !empty($row['xwzsphoto']) ? htmlspecialchars($row['xwzsphoto'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请清晰拍摄或扫描学位证书</span>
                        </div>
                    </div>
                </div>
                
                <!-- 联系信息 -->
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-map-marker" style="margin-right:5px;"></span>邮寄地址
                        </label>
                        <div class="input-group">
                            <input type="text" name="address" class="form-control" value="<?php echo !empty($row['address']) ? htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">用于接收录取通知书的真实地址</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-education" style="margin-right:5px;"></span>毕业院校
                        </label>
                        <div class="input-group">
                            <input type="text" name="school" class="form-control" value="<?php echo !empty($row['school']) ? htmlspecialchars($row['school'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请真实填写毕业院校</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-book" style="margin-right:5px;"></span>本科专业
                        </label>
                        <div class="input-group">
                            <input type="text" name="bkzy" class="form-control" value="<?php echo !empty($row['bkzy']) ? htmlspecialchars($row['bkzy'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <span class="input-group-addon" style="background:#f5f5f5;">请真实填写本科专业全称</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-tags" style="margin-right:5px;"></span>门类、专业类
                        </label>
                        <div class="input-group">
                            <?php if (empty($row['zylb'])): ?>
                                <?php include('mlzyl.php'); ?>
                            <?php else: ?>
                                <input type="text" name="zylb" class="form-control" value="<?php echo htmlspecialchars($row['zylb'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                            <span class="input-group-addon" style="background:#f5f5f5;">
                                <a href="http://www.moe.gov.cn/srcsite/A08/moe_1034/s4930/202304/W020230419336779992203.pdf" target="_blank">
                                    <span class="glyphicon glyphicon-search"></span> 查询
                                </a>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-calendar" style="margin-right:5px;"></span>毕业年份
                        </label>
                        <div class="input-group">
                            <input type="text" name="years" class="form-control" value="<?php echo !empty($row['years']) ? htmlspecialchars($row['years'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="2024、2023、2022、2021">
                            <span class="input-group-addon" style="background:#f5f5f5;">请填写毕业年份</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-certificate" style="margin-right:5px;"></span>学历证书号
                        </label>
                        <div class="input-group">
                            <input type="text" name="xlzs" class="form-control" value="<?php echo !empty($row['xlzs']) ? htmlspecialchars($row['xlzs'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="18位">
                            <span class="input-group-addon" style="background:#f5f5f5;">已取得毕业证书必填</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <label style="font-weight:bold;">
                            <span class="glyphicon glyphicon-certificate" style="margin-right:5px;"></span>学位证书号
                        </label>
                        <div class="input-group">
                            <input type="text" name="xwzs" class="form-control" value="<?php echo !empty($row['xwzs']) ? htmlspecialchars($row['xwzs'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="16位">
                            <span class="input-group-addon" style="background:#f5f5f5;">已取得毕业证书必填</span>
                        </div>
                    </div>
                </div>
                
                <!-- 免责声明 -->
                <div class="alert alert-warning" style="margin-bottom:20px;">
                    <div class="media">
                        <div class="media-left">
                            <span class="glyphicon glyphicon-exclamation-sign" style="font-size:24px;"></span>
                        </div>
                        <div class="media-body">
                            <h6 class="media-heading" style="font-weight:bold;">重要提醒</h6>
                            <p style="margin:0;">以上信息（除准考证外）确保真实，如填写错误或者无效，一切后果由考生承担！</p>
                        </div>
                    </div>
                </div>
                
                <!-- 按钮区域 -->
                <div class="text-center" style="margin-top:20px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding-left:30px; padding-right:30px; margin-right:10px;">
                        <span class="glyphicon glyphicon-floppy-disk" style="margin-right:5px;"></span>保存并提交信息
                    </button>
                    <a href="select-home.php" class="btn btn-default btn-lg" style="padding-left:30px; padding-right:30px;">
                        <span class="glyphicon glyphicon-arrow-right" style="margin-right:5px;"></span>前往专业选择
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="alertModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#fcf8e3; color:#8a6d3b;">
                <button type="button" class="close" data-dismiss="modal" aria-label="关闭">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> 系统提示
                </h4>
            </div>
            <div class="modal-body">
                <div id="selectResult" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.reload();">
                    <span class="glyphicon glyphicon-ok"></span> 确定
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#myForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'update.php?action=my',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#selectResult').empty().html(response);
                    $('#alertModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    $('#selectResult').html('保存失败，请稍后再试。');
                    $('#alertModal').modal('show');
                }
            });
        });
    });
</script>

<?php include('bottom.php'); ?>