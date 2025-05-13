//这个是获取聊天的
<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['username'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}

$username = $_SESSION['username'];
$friend = $_GET['friend'] ?? '';
$userDir = "data/$username";

// 获取与当前好友的聊天记录
$messages = [];
if($friend && file_exists("$userDir/messages.txt")) {
    $lines = file("$userDir/messages.txt", FILE_IGNORE_NEW_LINES);
    foreach($lines as $line) {
        if(strpos($line, ">$friend:") !== false || strpos($line, "$friend>") !== false) {
            $messages[] = htmlspecialchars($line);
        }
    }
}

// 获取好友请求数量
$requests = file_exists("$userDir/requests.txt") ? file("$userDir/requests.txt", FILE_IGNORE_NEW_LINES) : [];
$request_count = count(array_unique($requests));

echo json_encode([
    'messages' => $messages,
    'request_count' => $request_count
]);
?>
