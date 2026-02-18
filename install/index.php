<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Gti 本科第二学士学位招生报名系统</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <span class="navbar-brand">Gti 本科第二学士学位招生报名系统</span>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h5>数据库配置</h5>
                        </div>
                        <div class="panel-body" id="form-container">
                            <!-- 初始显示表单，若检测到已安装会被 JS 替换 -->
                            <form method="post" action="get.php">
                                <div class="form-group">
                                    <label for="url">数据库地址：</label>
                                    <input type="text" class="form-control" id="url" name="url" value="localhost" required>
                                </div>
                                <div class="form-group">
                                    <label for="name">数据库用户名：</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">数据库密码：</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="form-group">
                                    <label for="db">数据库：</label>
                                    <input type="text" class="form-control" id="db" name="db" required>
                                </div>
                                <div class="form-group">
                                    <label for="port">端口：</label>
                                    <input type="text" class="form-control" id="port" name="port" value="3306" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">提交</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container text-center">
            <span>版权所有 &copy; Gti 本科第二学士学位招生报名系统</span>
        </div>
    </footer>

    <script src="/static/js/jquery.min.js"></script>
    <script src="/static/js/bootstrap.min.js"></script>

    <script>
        window.onload = function() {
            var xhr = new XMLHttpRequest();
            xhr.open('HEAD', '../sql.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    var container = document.getElementById('form-container');
                    if (xhr.status === 200) {
                        container.innerHTML = `
                            <div class="alert alert-success" role="alert">
                                <h4>系统已安装</h4>
                                <p>数据库连接已配置成功，无需重复配置。</p>
                            </div>`;
                    } else {
                        container.style.display = 'block';
                    }
                }
            };
            xhr.send();
        };
    </script>
</body>
</html>