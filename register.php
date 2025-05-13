//这个是注册页面
<?php
session_start();
if(isset($_SESSION['username'])) {
    header("Location: chat.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);
    
    // 验证输入
    if(empty($username) || empty($password)) {
        $error = "用户名和密码不能为空";
    } elseif($password !== $confirm) {
        $error = "两次输入的密码不一致";
    } elseif(strlen($username) > 20) {
        $error = "用户名不能超过20个字符";
    } else {
        // 检查用户是否已存在
        $users = file_exists("data/users.txt") ? file("data/users.txt", FILE_IGNORE_NEW_LINES) : [];
        foreach($users as $user) {
            if(explode('|', $user)[0] === $username) {
                $error = "用户名已存在";
                break;
            }
        }
        
        if(!$error) {
            // 创建用户
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            file_put_contents("data/users.txt", "$username|$hashedPassword" . PHP_EOL, FILE_APPEND);
            
            // 创建用户目录
            $userDir = "data/$username";
            if(!file_exists($userDir)) {
                mkdir($userDir, 0777, true);
                file_put_contents("$userDir/friends.txt", "");
                file_put_contents("$userDir/messages.txt", "");
            }
            
            $_SESSION['username'] = $username;
            header("Location: chat.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>注册账号</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --error-color: #f44336;
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
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        button {
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        button:hover {
            background-color: #3e8e41;
        }
        
        .error {
            color: var(--error-color);
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 6px;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        /* 移动端特定样式 */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                justify-content: flex-start;
                padding-top: 40px;
            }
            
            .register-container {
                padding: 20px;
                box-shadow: none;
                border: 1px solid var(--border-color);
            }
            
            input {
                padding: 14px 15px;
            }
            
            button {
                padding: 14px;
            }
        }
        
        /* 小屏幕手机优化 */
        @media (max-width: 360px) {
            body {
                padding: 10px;
                padding-top: 20px;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .register-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>注册新账号</h2>
        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="用户名" required maxlength="20">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="密码" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="确认密码" required>
            </div>
            <button type="submit">注册</button>
        </form>
        
        <div class="login-link">
            已有账号？ <a href="index.php">立即登录</a>
        </div>
    </div>
</body>
</html>
