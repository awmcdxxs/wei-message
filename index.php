//这个是登录页
<?php
session_start();
if(isset($_SESSION['username'])) {
    header("Location: chat.php");
    exit;
}

// 处理登录请求
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // 验证用户
    $users = file_exists("data/users.txt") ? file("data/users.txt", FILE_IGNORE_NEW_LINES) : [];
    foreach($users as $user) {
        list($savedUser, $savedPass) = explode('|', $user);
        if($savedUser === $username && password_verify($password, $savedPass)) {
            $_SESSION['username'] = $username;
            header("Location: chat.php");
            exit;
        }
    }
    $error = "用户名或密码错误";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>登录</title>
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
        
        .login-container {
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
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .error {
            color: var(--error-color);
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 6px;
            text-align: center;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .flex-container {
            display: flex;
            gap: 10px;
        }
        
        .flex-container button {
            flex: 1;
        }
        
        /* 移动端特定样式 */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                justify-content: flex-start;
                padding-top: 40px;
            }
            
            .login-container {
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
            
            .flex-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .flex-container button {
                width: 100%;
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
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>登录</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form action="index.php" method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="用户名" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="密码" required>
            </div>
            <div class="flex-container">
                <button type="submit" class="btn-primary">登录</button>
                <button type="button" class="btn-secondary" onclick="location.href='register.php'">注册账号</button>
            </div>
        </form>
    </div>
</body>
</html>
