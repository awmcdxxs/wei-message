//这个是用于发送信息的
<?php
session_start();
date_default_timezone_set('Asia/Shanghai');

$sender = $_SESSION['username'];
$friend = trim($_POST['friend']);
$message = trim($_POST['message']);
$timestamp = date('[Y-m-d H:i:s]');

// 验证好友关系
$friends = file_exists("data/$sender/friends.txt") ? file("data/$sender/friends.txt", FILE_IGNORE_NEW_LINES) : [];
if(!in_array($friend, $friends)) {
    die("错误：非法操作！");
}

// 保存到发送者的消息文件 (格式: [时间] 你>好友:消息)
file_put_contents("data/$sender/messages.txt", "$timestamp 你>$friend:$message".PHP_EOL, FILE_APPEND);

// 保存到接收者的消息文件 (格式: [时间] 发送者>消息)
file_put_contents("data/$friend/messages.txt", "$timestamp $sender>$message".PHP_EOL, FILE_APPEND);

echo "OK";
?>
