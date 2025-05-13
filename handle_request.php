//这个是添加好友的
<?php
session_start();
$username = $_SESSION['username'];
$action = $_GET['action']; // 'accept' 或 'reject'
$requester = $_GET['from'];

// 验证请求是否真实存在
$requests = file_exists("data/$username/requests.txt") ? file("data/$username/requests.txt", FILE_IGNORE_NEW_LINES) : [];
if(!in_array($requester, $requests)) {
    die("无效的请求！");
}

// 处理请求
if($action === 'accept') {
    // 互相添加好友
    file_put_contents("data/$username/friends.txt", "$requester\n", FILE_APPEND);
    file_put_contents("data/$requester/friends.txt", "$username\n", FILE_APPEND);
    
    // 添加系统消息
    $time = date('[Y-m-d H:i:s]');
    file_put_contents("data/$username/messages.txt", "$time 系统> 你已和 $requester 成为好友！\n", FILE_APPEND);
    file_put_contents("data/$requester/messages.txt", "$time 系统> 你已和 $username 成为好友！\n", FILE_APPEND);
}

// 移除请求
$newRequests = array_diff($requests, [$requester]);
file_put_contents("data/$username/requests.txt", implode("\n", $newRequests));

header("Location: chat.php");
?>
