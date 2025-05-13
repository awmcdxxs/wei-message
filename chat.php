//这个是聊天的页面
<?php
/* ========== 功能函数 ========== */
function ensureUserDir($username) {
    $userDir = "data/$username";
    if(!file_exists($userDir)) {
        mkdir($userDir, 0777, true) or die("无法创建用户目录");
        file_put_contents("$userDir/friends.txt", "") or die("无法创建好友列表");
        file_put_contents("$userDir/messages.txt", "") or die("无法创建消息记录");
        file_put_contents("$userDir/requests.txt", "") or die("无法创建请求记录");
    }
    return $userDir;
}

function getAllUsers() {
    $users = [];
    if(file_exists("data/users.txt")) {
        $lines = file("data/users.txt", FILE_IGNORE_NEW_LINES);
        foreach($lines as $line) {
            $parts = explode('|', $line);
            if(!empty($parts[0])) $users[] = $parts[0];
        }
    }
    return $users;
}

/* ========== 用户认证 ========== */
if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$current_friend = $_GET['friend'] ?? '';
$userDir = ensureUserDir($username);

/* ========== 请求处理 ========== */
// 处理好友请求动作
if(isset($_GET['action']) && isset($_GET['from'])) {
    $action = $_GET['action'];
    $requester = trim($_GET['from']);
    
    if(preg_match('/^[a-zA-Z0-9]+$/', $requester)) {
        $requests = file_exists("$userDir/requests.txt") 
            ? file("$userDir/requests.txt", FILE_IGNORE_NEW_LINES) 
            : [];
        
        if(in_array($requester, $requests)) {
            if($action === 'accept') {
                // 互相添加好友
                $myFriends = file_exists("$userDir/friends.txt") 
                    ? file("$userDir/friends.txt", FILE_IGNORE_NEW_LINES) 
                    : [];
                
                if(!in_array($requester, $myFriends)) {
                    file_put_contents("$userDir/friends.txt", "$requester\n", FILE_APPEND);
                }
                
                $requesterFriendsFile = "data/$requester/friends.txt";
                if(file_exists($requesterFriendsFile)) {
                    $requesterFriends = file($requesterFriendsFile, FILE_IGNORE_NEW_LINES);
                    if(!in_array($username, $requesterFriends)) {
                        file_put_contents($requesterFriendsFile, "$username\n", FILE_APPEND);
                    }
                }
                
                // 系统消息
                $time = date('[Y-m-d H:i:s]');
                file_put_contents("$userDir/messages.txt", "$time 系统> 你已和 $requester 成为好友！\n", FILE_APPEND);
                file_put_contents("data/$requester/messages.txt", "$time 系统> 你已和 $username 成为好友！\n", FILE_APPEND);
            }
            
            // 移除请求
            $newRequests = array_diff($requests, [$requester]);
            file_put_contents("$userDir/requests.txt", implode("\n", $newRequests));
            
            header("Location: chat.php?friend=$requester");
            exit;
        }
    }
}

// 处理表单提交
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['message']) && $current_friend) {
        // 发送消息
        $message = trim($_POST['message']);
        if(!empty($message)) {
            $timestamp = date('[Y-m-d H:i:s]');
            file_put_contents("$userDir/messages.txt", "$timestamp 你>$current_friend: $message\n", FILE_APPEND);
            file_put_contents("data/$current_friend/messages.txt", "$timestamp $username>$message\n", FILE_APPEND);
        }
    } 
    elseif(isset($_POST['friend_name'])) {
        // 添加好友
        $new_friend = trim($_POST['friend_name']);
        if(preg_match('/^[a-zA-Z0-9]+$/', $new_friend)) {
            $all_users = getAllUsers();
            
            if(in_array($new_friend, $all_users)) {
                $friends = file_exists("$userDir/friends.txt") 
                    ? file("$userDir/friends.txt", FILE_IGNORE_NEW_LINES) 
                    : [];
                
                if(!in_array($new_friend, $friends)) {
                    $requestFile = "data/$new_friend/requests.txt";
                    $existingRequests = file_exists($requestFile) 
                        ? file($requestFile, FILE_IGNORE_NEW_LINES) 
                        : [];
                    
                    if(!in_array($username, $existingRequests)) {
                        file_put_contents($requestFile, "$username\n", FILE_APPEND);
                        
                        $timestamp = date('[Y-m-d H:i:s]');
                        file_put_contents("data/$new_friend/messages.txt", 
                            "$timestamp 系统> $username 向你发送了好友请求！\n", FILE_APPEND);
                        
                        $_SESSION['success'] = "好友请求已发送至 $new_friend";
                    } else {
                        $_SESSION['error'] = "已向该用户发送过请求，请等待对方处理";
                    }
                } else {
                    $_SESSION['error'] = "该用户已经是你的好友！";
                }
            } else {
                $_SESSION['error'] = "用户 $new_friend 不存在！";
            }
        } else {
            $_SESSION['error'] = "用户名只能包含字母和数字";
        }
        
        header("Location: chat.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>伪信 - <?= htmlspecialchars($username) ?></title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --error-color: #f44336;
            --bg-color: #f5f5f5;
            --card-bg: white;
            --text-color: #333;
            --border-color: #ddd;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
        }

        /* 移动端顶部菜单栏 */
        .mobile-top-bar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            background-color: var(--primary-color);
            color: white;
            z-index: 90;
            padding: 0 15px;
            align-items: center;
        }
        
        .menu-btn {
            font-size: 1.5rem;
            background: none;
            border: none;
            color: white;
            margin-right: 15px;
        }
        
        .mobile-title {
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        /* 移动端布局 */
        @media (max-width: 768px) {
            .mobile-top-bar {
                display: flex;
            }
            
            .sidebar {
                position: fixed;
                top: 50px;
                left: 0;
                width: 100%;
                height: calc(100% - 50px);
                z-index: 80;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .chat-area {
                margin-top: 50px;
                height: calc(100% - 50px);
            }
            
            .chat-header {
                padding-left: 15px;
            }
            
            .back-btn {
                display: block !important;
                font-size: 1.5rem;
                background: none;
                border: none;
                color: white;
                margin-right: 15px;
            }
            
            .message-form-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: #f0f0f0;
                border-top: 1px solid var(--border-color);
                padding: 10px;
                z-index: 100;
            }
        }
        
        /* PC端布局 */
        @media (min-width: 769px) {
            .app-container {
                flex-direction: row;
            }
            
            .sidebar {
                width: 300px;
                transform: none !important;
            }
            
            .mobile-top-bar, .back-btn {
                display: none !important;
            }
            
            .message-form-container {
                padding: 10px;
                background-color: #f0f0f0;
                border-top: 1px solid var(--border-color);
            }
        }
        
        .app-container {
            display: flex;
            height: 100vh;
        }
        
        .sidebar {
            background-color: var(--card-bg);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar-header {
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        
        .section-title {
            margin: 15px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .friend {
            display: flex;
            align-items: center;
            padding: 12px 10px;
            margin: 5px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .friend:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .friend.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .request-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 10px;
            margin: 8px 0;
            background-color: #fff8e1;
            border-radius: 8px;
        }
        
        .request-actions a {
            margin-left: 10px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .accept {
            color: var(--primary-color);
        }
        
        .reject {
            color: var(--error-color);
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--card-bg);
            position: relative;
        }
        
        .chat-header {
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #e5ddd5;
            background-image: url('https://web.whatsapp.com/img/bg-chat-tile-light_a4be512e7195b6b733d9110b408f075d.png');
            background-repeat: repeat;
        }
        
        .message {
            margin-bottom: 10px;
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message.received {
            background-color: white;
            margin-right: auto;
            border-top-left-radius: 5px;
        }
        
        .message.sent {
            background-color: #dcf8c6;
            margin-left: auto;
            border-top-right-radius: 5px;
        }
        
        .message.system {
            margin: 15px auto;
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            max-width: 90%;
        }
        
        .message-form {
            display: flex;
        }
        
        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 21px;
            outline: none;
            font-size: 1rem;
        }
        
        .send-btn {
            margin-left: 10px;
            padding: 0 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 21px;
            font-size: 1rem;
            cursor: pointer;
        }
        
        .add-friend-form {
            display: flex;
            margin-top: 15px;
        }
        
        .add-friend-input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            outline: none;
        }
        
        .add-friend-btn {
            margin-left: 10px;
            padding: 0 15px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        
        .notification.success {
            background-color: var(--primary-color);
        }
        
        .notification.error {
            background-color: var(--error-color);
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .no-friend-selected {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            padding: 20px;
        }
        
        .show-contacts-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- 移动端顶部菜单栏 -->
    <div class="mobile-top-bar">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <div class="mobile-title">
            <?= $current_friend ? htmlspecialchars($current_friend) : '伪信' ?>
        </div>
    </div>

    <div class="app-container">
        <!-- 侧边栏 -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span><?= htmlspecialchars($username) ?></span>
                <button class="menu-btn" onclick="toggleSidebar()">✕</button>
            </div>
            
            <div class="sidebar-content">
                <div class="section-title">好友列表</div>
                <div id="friend-list">
                    <?php
                    $friends = file_exists("$userDir/friends.txt") 
                        ? file("$userDir/friends.txt", FILE_IGNORE_NEW_LINES) 
                        : [];
                    
                    foreach($friends as $friend) {
                        $active = $friend == $current_friend ? 'active' : '';
                        echo "<div class='friend $active' onclick=\"selectFriend('$friend')\">
                            $friend
                        </div>";
                    }
                    ?>
                </div>
                
                <div class="section-title">好友请求</div>
                <div id="request-list">
                    <?php
                    $requests = file_exists("$userDir/requests.txt") 
                        ? array_unique(file("$userDir/requests.txt", FILE_IGNORE_NEW_LINES)) 
                        : [];
                    
                    foreach($requests as $requester) {
                        echo '
                        <div class="request-item">
                            <span>'.htmlspecialchars($requester).'</span>
                            <div class="request-actions">
                                <a href="chat.php?action=accept&from='.urlencode($requester).'" class="accept" title="接受">✓</a>
                                <a href="chat.php?action=reject&from='.urlencode($requester).'" class="reject" title="拒绝">✗</a>
                            </div>
                        </div>';
                    }
                    ?>
                </div>
                
                <div class="section-title">添加好友</div>
                <form method="post" class="add-friend-form">
                    <input type="text" name="friend_name" class="add-friend-input" 
                           placeholder="输入用户名" required
                           pattern="[a-zA-Z0-9]+"
                           title="用户名只能包含字母和数字">
                    <button type="submit" class="add-friend-btn">添加</button>
                </form>
            </div>
        </div>
        
        <!-- 聊天区域 -->
        <div class="chat-area" id="chat-area">
            <?php if($current_friend): ?>
                <div class="chat-header">
                    <button class="back-btn" onclick="toggleSidebar()">←</button>
                    <?= htmlspecialchars($current_friend) ?>
                </div>
                
                <div class="messages" id="messages">
                    <?php
                    if(file_exists("$userDir/messages.txt")) {
                        $messages = file("$userDir/messages.txt", FILE_IGNORE_NEW_LINES);
                        foreach($messages as $msg) {
                            if(strpos($msg, ">$current_friend:") !== false || strpos($msg, "$current_friend>") !== false) {
                                $isSelf = strpos($msg, "你>") !== false;
                                $isSystem = strpos($msg, "系统>") !== false;
                                
                                $class = $isSystem ? 'system' : ($isSelf ? 'sent' : 'received');
                                echo "<div class='message $class'>".htmlspecialchars($msg)."</div>";
                            }
                        }
                    }
                    ?>
                </div>
                
                <!-- 修复的消息输入框 -->
                <div class="message-form-container">
                    <form class="message-form" method="post">
                        <input type="hidden" name="friend" value="<?= htmlspecialchars($current_friend) ?>">
                        <input type="text" name="message" class="message-input" placeholder="输入消息..." required>
                        <button type="submit" class="send-btn">发送</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-friend-selected">
                    <h2>请选择好友开始聊天，我想退出登录请输入[你访问的网址]/logout.php即可，如果是软件用户，请点击右上角的退出按钮</h2>
                    <p style="color: #666; margin-top: 15px;">从侧边栏选择好友或添加新好友</p>
                    <button class="show-contacts-btn" onclick="toggleSidebar()">
                        显示好友列表
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 通知消息 -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="notification success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
        <script>
            setTimeout(() => {
                const noti = document.querySelector('.notification');
                if(noti) noti.remove();
            }, 3000);
        </script>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="notification error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
        <script>
            setTimeout(() => {
                const noti = document.querySelector('.notification');
                if(noti) noti.remove();
            }, 3000);
        </script>
    <?php endif; ?>

    <script>
        // 自动滚动到底部
        function scrollToBottom() {
            const messages = document.getElementById('messages');
            if(messages) {
                messages.scrollTop = messages.scrollHeight;
            }
        }
        
        // 侧边栏切换
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
            
            // 移动端点击外部关闭侧边栏
            if(sidebar.classList.contains('active')) {
                setTimeout(() => {
                    document.addEventListener('click', closeSidebarOnClickOutside);
                }, 10);
            } else {
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        }
        
        function closeSidebarOnClickOutside(e) {
            const sidebar = document.getElementById('sidebar');
            const mobileBar = document.querySelector('.mobile-top-bar');
            
            if(!sidebar.contains(e.target) && !mobileBar.contains(e.target)) {
                sidebar.classList.remove('active');
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        }
        
        // 选择好友
        function selectFriend(friend) {
            if(window.innerWidth <= 768) {
                toggleSidebar();
            }
            location.href = `chat.php?friend=${friend}`;
        }
        
        // 实时消息检查
        function checkNewMessages() {
            const friend = document.querySelector('input[name="friend"]')?.value;
            if(!friend) return;
            
            fetch(`chat.php?friend=${encodeURIComponent(friend)}`)
                .then(response => response.text())
                .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    
                    const newMessages = temp.querySelector('#messages')?.innerHTML;
                    if(newMessages) {
                        document.getElementById('messages').innerHTML = newMessages;
                        scrollToBottom();
                    }
                });
        }
        
        // 初始化
        document.addEventListener('DOMContentLoaded', () => {
            scrollToBottom();
            
            // 每2秒检查新消息
            if(document.querySelector('input[name="friend"]')) {
                setInterval(checkNewMessages, 1000);
            }
        });
    </script>
</body>
</html>
