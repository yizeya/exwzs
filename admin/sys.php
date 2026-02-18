<?php
    session_start();
    include('top.php');
    include('../sql.php');
    $web_info = "select * from webinfo";
    $web_info_result = $link->query($web_info);
    $web_info_row = mysqli_fetch_array($web_info_result);
?>
<div class="container-fluid">
    <div class="row">
        <!-- 侧边导航栏（nav.php 已适配 Bootstrap 3） -->
        <?php include('nav.php'); ?>
        
        <!-- 主内容区域：Bootstrap 3 栅格偏移调整 -->
        <div class="col-md-9 col-md-offset-3 col-lg-10 col-lg-offset-2" style="margin-top:20px;">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h5 class="panel-title">系统信息</h5>
                </div>
                <div class="panel-body">
                    <div id="sys"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // 创建 Bootstrap 3 表格
    var table = document.createElement('table');
    table.className = 'table table-bordered table-hover';
    
    var headerRow = document.createElement('tr');
    headerRow.className = 'active'; // Bootstrap 3 使用 active 类高亮行
    
    var headerCell1 = document.createElement('th');
    headerCell1.scope = 'col';
    headerCell1.textContent = 'gTi-Exwzs 版本';
    
    var headerCell2 = document.createElement('th');
    headerCell2.scope = 'col';
    headerCell2.textContent = '2.0.2028';
    
    headerRow.appendChild(headerCell1);
    headerRow.appendChild(headerCell2);
    table.appendChild(headerRow);
    
    var sysDiv = document.getElementById('sys');
    sysDiv.appendChild(table);
</script>

<?php include('bottom.php'); ?>