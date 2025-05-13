//这个是退出登录页面
<?php
session_start();

// 清空会话数据
$_SESSION = array();

// 清除会话cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 销毁会话
session_destroy();

// 重定向到登录页
header("Location: index.php");
exit;
?>
