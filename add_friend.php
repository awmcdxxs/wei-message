//添加朋友
<?php
session_start();
if(!isset($_SESSION['username'])) {
    die("请先登录！");
}

$sender = $_SESSION['username'];
$receiver = trim($_POST['friend_name']);
$receiver = preg_replace('/[^a-zA-Z0-9]/', '', $receiver);

// 检查接收者是否存在
$users = file_exists("data/users.txt") ? file("data/users.txt", FILE_IGNORE_NEW_LINES) : [];
if(!in_array($receiver, $users)) {
    die("错误：用户不存在！");
}

// 检查是否是自己
if($receiver === $sender) {
    die("错误：不能添加自己为好友！");
}

// 检查是否已经是好友
$friends = file_exists("data/$sender/friends.txt") ? file("data/$sender/friends.txt", FILE_IGNORE_NEW_LINES) : [];
if(in_array($receiver, $friends)) {
    die("错误：该用户已经是你的好友！");
}

// 检查是否已发送过请求
$requests = file_exists("data/$receiver/requests.txt") ? file("data/$receiver/requests.txt", FILE_IGNORE_NEW_LINES) : [];
if(in_array($sender, $requests)) {
    die("错误：已发送过好友请求，请等待对方处理！");
}

// 添加好友请求
file_put_contents("data/$receiver/requests.txt", "$sender\n", FILE_APPEND);

// 添加系统消息
$time = date('[Y-m-d H:i:s]');
file_put_contents("data/$receiver/messages.txt", "$time 系统> $sender 向你发送了好友请求！\n", FILE_APPEND);

echo "好友请求已发送！";
header("Refresh:2; url=chat.php");
?>
