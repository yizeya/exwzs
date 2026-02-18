<?php
    session_start();
    if (isset($_SESSION['id'])) {
?>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#adminNavbar" aria-expanded="false">
                <span class="sr-only">切换导航</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="nav navbar-nav">
                <!-- 首页 -->
                <li>
                    <a href="home.php">
                        <span class="glyphicon glyphicon-home"></span> 首页
                    </a>
                </li>

                <!-- 管理员下拉菜单 -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-user"></span> 管理员 <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin.php"><span class="glyphicon glyphicon-cog"></span> 管理员管理</a></li>
                        <li><a href="admin_col.php"><span class="glyphicon glyphicon-list"></span> 管理员列表</a></li>
                    </ul>
                </li>

                <!-- 登入日志 -->
                <li>
                    <a href="log.php">
                        <span class="glyphicon glyphicon-time"></span> 登入日志
                    </a>
                </li>

                <!-- 文章管理下拉菜单 -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-file"></span> 文章管理 <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="article.php"><span class="glyphicon glyphicon-plus"></span> 添加文章</a></li>
                        <li><a href="article_col.php"><span class="glyphicon glyphicon-th-list"></span> 文章管理</a></li>
                    </ul>
                </li>

                <!-- 招生专业下拉菜单 -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-education"></span> 招生专业 <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="speciality.php"><span class="glyphicon glyphicon-wrench"></span> 管理专业</a></li>
                        <li><a href="spe_add.php"><span class="glyphicon glyphicon-plus-sign"></span> 添加专业</a></li>
                    </ul>
                </li>

                <!-- 考生管理 -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-th"></span> 考生管理 <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="students.php"><span class="glyphicon glyphicon-align-left"></span> 考生列表</a></li>
                        <li><a href="public_notice.php"><span class="glyphicon glyphicon-eye-open"></span> 名单公示</a></li>
                    </ul>
                </li>
            </ul>

            <!-- 右侧用户信息及退出按钮 -->
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-user"></span> 管理员 <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="control.php?action=loginout"><span class="glyphicon glyphicon-log-out"></span> 退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php } else { 
    header("Location: /"); 
} ?>