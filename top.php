<?php
if (file_exists('sql.php')) {
    include('sql.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $web_info_row['webname']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <span class="navbar-brand mb-0 h1 w-100 text-center"><?php echo $web_info_row['webname']; ?></span>
        </div>
    </nav>
<?php
} else {
    header("Location: /install/");
    exit;
}
?>