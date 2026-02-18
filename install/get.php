<?php
// 关闭错误显示，避免敏感信息泄露
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/log/install.log');

// 仅接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('无效的请求方法');
}

// 获取并清理输入
$url      = trim($_POST['url'] ?? '');
$username = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? ''; // 密码不 trim，保留原样
$database = trim($_POST['db'] ?? '');
$port     = trim($_POST['port'] ?? '');

// 基本验证
if (empty($url) || empty($username) || empty($database) || empty($port)) {
    die('所有字段均为必填');
}
if (!is_numeric($port) || $port < 1 || $port > 65535) {
    die('端口号无效');
}
// 数据库名、用户名等只允许字母数字下划线（根据实际需求调整）
if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
    die('数据库名称只能包含字母、数字和下划线');
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    die('用户名只能包含字母、数字和下划线');
}
// 主机名可以包含字母、数字、点、破折号，但禁止特殊字符
if (!preg_match('/^[a-zA-Z0-9.-]+$/', $url)) {
    die('数据库地址格式不正确');
}

// 测试数据库连接（使用提交的凭据）
$test_link = @new mysqli($url, $username, $password, $database, $port);
if ($test_link->connect_error) {
    // 记录日志，但向用户显示通用错误
    error_log("Install: Database connection failed: " . $test_link->connect_error);
    die('数据库连接失败，请检查配置');
}
$test_link->close();

// 生成安全的 sql.php 文件内容（使用 var_export 避免注入）
$config_content = "<?php
/**
 * 数据库连接配置文件 - 由安装程序自动生成
 */

// 数据库配置
\$db_config = [
    'host'     => " . var_export($url, true) . ",
    'port'     => " . var_export((int)$port, true) . ",
    'user'     => " . var_export($username, true) . ",
    'password' => " . var_export($password, true) . ",
    'database' => " . var_export($database, true) . ",
    'charset'  => 'utf8mb4',
];

// 错误报告设置
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/log/data.log');

// 创建连接
\$link = new mysqli(
    \$db_config['host'],
    \$db_config['user'],
    \$db_config['password'],
    \$db_config['database'],
    \$db_config['port']
);

if (\$link->connect_error) {
    error_log('Database connection failed: ' . \$link->connect_error);
    die('系统错误，请稍后重试。');
}

\$link->set_charset(\$db_config['charset']);

// 查询网站配置信息
\$web_info_result = \$link->query('SELECT * FROM webinfo LIMIT 1');
if (\$web_info_result && \$web_info_result->num_rows > 0) {
    \$web_info_row = \$web_info_result->fetch_assoc();
    \$web_info_result->free();
} else {
    \$web_info_row = [];
    error_log('webinfo table query failed: ' . \$link->error);
}
";

// 写入配置文件到上一级目录的 sql.php
$config_path = __DIR__ . '/../sql.php';
$config_dir = dirname($config_path);
if (!is_dir($config_dir)) {
    mkdir($config_dir, 0750, true);
}
if (file_put_contents($config_path, $config_content, LOCK_EX) === false) {
    die('无法写入配置文件，请检查目录权限');
}
chmod($config_path, 0640);

// 创建数据库表（使用固定 SQL，无用户输入）
$createTablesSQL = "
SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
START TRANSACTION;
SET time_zone = \"+00:00\";

CREATE TABLE `admin_log` (
  `uid` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin_user` (`id`, `name`, `password`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3');

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mlzyl` (
  `id` int(11) NOT NULL,
  `mlzyl` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mlzyl` (`id`, `mlzyl`) VALUES
(1, '哲学类'),
(2, '经济学类'),
(3, '财政学类'),
(4, '金融学类'),
(5, '经济与贸易类'),
(6, '法学类'),
(7, '政治学类'),
(8, '社会学类'),
(9, '民族学类'),
(10, '马克思主义理论类'),
(11, '公安学类'),
(12, '教育学类'),
(13, '体育学类'),
(14, '中国语言文学类'),
(15, '外国语言文学类'),
(16, '新闻传播学类'),
(17, '历史学类'),
(18, '数学类'),
(19, '物理学类'),
(20, '化学类'),
(21, '天文学类'),
(22, '地理科学类'),
(23, '大气科学类'),
(24, '海洋科学类'),
(25, '地球物理学类'),
(26, '地质学类'),
(27, '生物科学类'),
(28, '心理学类 '),
(29, '统计学类'),
(30, '力学类'),
(31, '机械类'),
(32, '仪器类'),
(33, '材料类'),
(34, '能源动力类'),
(35, '电气类'),
(36, '电子信息类'),
(37, '自动化类'),
(38, '计算机类'),
(39, '土木类'),
(40, '水利类'),
(41, '测绘类'),
(42, '化工与制药类'),
(43, '地质类'),
(44, '矿业类'),
(45, '纺织类'),
(46, '轻工类'),
(47, '交通运输类'),
(48, '海洋工程类'),
(49, '航空航天类'),
(50, '兵器类'),
(51, '核工程类'),
(52, '农业工程类'),
(53, '林业工程类'),
(54, '环境科学与工程类'),
(55, '生物医学工程类'),
(56, '食品科学与工程类'),
(57, '建筑类'),
(58, '安全科学与工程类'),
(59, '生物工程类'),
(60, '公安技术类'),
(61, '交叉工程类'),
(62, '植物生产类'),
(63, '自然保护与环境生态类'),
(64, '动物生产类'),
(65, '动物医学类'),
(66, '林学类'),
(67, '水产类'),
(68, '草学类'),
(69, '基础医学类'),
(70, '临床医学类'),
(71, '口腔医学类'),
(72, '公共卫生与预防医学类'),
(73, '中医学类'),
(74, '中西医结合类'),
(75, '药学类'),
(76, '中药学类'),
(77, '法医学类'),
(78, '医学技术类'),
(79, '护理学类'),
(80, '管理科学与工程类'),
(81, '工商管理类'),
(82, '农业经济管理类'),
(83, '公共管理类'),
(84, '图书情报与档案管理类'),
(85, '物流管理与工程类'),
(86, '工业工程类'),
(87, '电子商务类'),
(88, '旅游管理类'),
(89, '艺术学理论类'),
(90, '音乐与舞蹈学类'),
(91, '戏剧与影视学类'),
(92, '美术学类'),
(93, '设计学类');

CREATE TABLE `speciality` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '专业',
  `mlzyl` varchar(255) NOT NULL COMMENT '门类专业类',
  `xwsyml` varchar(255) NOT NULL COMMENT '学位授予门类',
  `years` int(11) NOT NULL COMMENT '学制',
  `school` varchar(255) NOT NULL COMMENT '培养院系',
  `total` int(11) NOT NULL COMMENT '招生人数',
  `price` int(11) NOT NULL COMMENT '学费/年'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `upload` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `files` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id` int(11) NOT NULL COMMENT '考生序号',
  `number` text NOT NULL COMMENT '考生身份证号',
  `password` varchar(255) NOT NULL COMMENT '登入密码',
  `name` varchar(255) NOT NULL COMMENT '真实姓名',
  `telephone` varchar(11) NOT NULL COMMENT '电话号码',
  `mail` varchar(255) NOT NULL COMMENT '邮箱',
  `ip` varchar(20) NOT NULL COMMENT '注册IP地址',
  `time` int(11) NOT NULL COMMENT '注册时间',
  `exnumber` varchar(255) NOT NULL COMMENT '准考证号',
  `photo` varchar(255) NOT NULL COMMENT '考生照片',
  `cardz` varchar(255) NOT NULL COMMENT '身份证正面',
  `cardf` varchar(255) NOT NULL COMMENT '身份证反面',
  `sqb` varchar(255) NOT NULL COMMENT '考生申请表',
  `cns` varchar(255) NOT NULL COMMENT '考生承诺书',
  `bgd` varchar(255) NOT NULL COMMENT '学历验证报告单',
  `cjd` varchar(255) NOT NULL COMMENT '本科成绩单',
  `address` text NOT NULL COMMENT '邮寄地址',
  `school` varchar(255) NOT NULL COMMENT '毕业院校',
  `bkzy` varchar(255) NOT NULL COMMENT '本科专业',
  `zylb` varchar(255) NOT NULL COMMENT '本科专业门类',
  `years` varchar(255) NOT NULL COMMENT '毕业年份',
  `xlzs` varchar(255) NOT NULL COMMENT '学历证书',
  `xwzs` varchar(255) NOT NULL COMMENT '学位证书',
  `xlzsphoto` varchar(255) NOT NULL COMMENT '学历证书照片',
  `xwzsphoto` varchar(255) NOT NULL COMMENT '学位证书照片',
  `speciality` varchar(255) NOT NULL COMMENT '报考专业',
  `first` int(11) NOT NULL COMMENT '资格审查',
  `why` text NOT NULL COMMENT '未通过原因',
  `second` int(11) NOT NULL COMMENT '参与考试'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_log` (
  `uid` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `webinfo` (
  `id` int(11) NOT NULL,
  `webname` varchar(255) NOT NULL,
  `school_code` int(11) NOT NULL,
  `tel` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `reg` int(11) NOT NULL,
  `icp` varchar(255) NOT NULL,
  `banner` varchar(255) NOT NULL,
  `introduce` text NOT NULL,
  `static` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `xw` (
  `id` int(11) NOT NULL,
  `xw` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `xw` (`id`, `xw`) VALUES
(1, '哲学'),
(2, '经济学'),
(3, '法学'),
(4, '文学'),
(5, '历史学'),
(6, '理学'),
(7, '工学'),
(8, '医学'),
(9, '农学'),
(10, '管理学'),
(11, '教育学'),
(12, '艺术学');

ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlzyl`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `speciality`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `upload`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `webinfo`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `xw`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlzyl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

ALTER TABLE `speciality`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `upload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '考生序号';

ALTER TABLE `webinfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `xw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;
";

// 重新连接数据库（因为之前测试后关闭了）
$conn = new mysqli($url, $username, $password, $database, $port);
if ($conn->connect_error) {
    error_log("Install: Reconnect failed: " . $conn->connect_error);
    die('数据库连接失败，无法创建表');
}

// 执行多查询创建表
if ($conn->multi_query($createTablesSQL)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "数据库连接成功！数据表创建成功！请删除根目录下 /install/ 文件夹。";
} else {
    error_log("Install: Table creation failed: " . $conn->error);
    echo "数据表创建失败，请检查错误日志。";
}

$conn->close();